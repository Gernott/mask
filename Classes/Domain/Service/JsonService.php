<?php
namespace MASK\Mask\Domain\Service;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class JsonService
 * @package MASK\Mask\Domain\Service
 */
class JsonService implements SingletonInterface
{
    /**
     * @var self|null
     */
    private static $_instance = null;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @return JsonService
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;

            /* @var $message \TYPO3\CMS\Core\Messaging\FlashMessage */
            self::$_instance->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }
        return self::$_instance;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function getConfiguration($path = '')
    {
        $data = [];
        // check if path points to a file
        if (substr($path, -strlen('.json')) == '.json') {
            $data = GeneralUtility::getUrl($path) ?: array();
        } else {
            // path points to directory
            $configurationFiles = GeneralUtility::getFilesInDir($path, 'json', true);
            foreach ($configurationFiles as $filePath) {
                $content = json_decode(GeneralUtility::getUrl($filePath), true) ?: array();
                $errors = $this->searchForMergeError($data, $content);
                if (!empty($errors)) {
                    /* @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
                    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                    $messageQueue = $flashMessageService->getMessageQueueByIdentifier();

                    /* @var $message \TYPO3\CMS\Core\Messaging\FlashMessage */
                    $message = GeneralUtility::makeInstance(FlashMessage::class, sprintf(
                        'The file "%s" couldn\'t be included due to following merge errors:%s',
                        basename($filePath),
                        implode('<br/>-', array_map(function ($value, $key) {
                            return $key . ': ' . $value;
                        }, $errors, array_keys($errors)))
                    ));
                    $message->setSeverity(FlashMessage::WARNING);
                    $messageQueue->addMessage($message);
                } else {
                    ArrayUtility::mergeRecursiveWithOverrule($data, $content);
                }
            }
        }

        return $data;
    }

    /**
     * @param string $path
     * @param array  $data
     */
    public function saveConfiguration($path, $data = [])
    {
        // check if path points to a file
        if (substr($path, -strlen('.json')) == '.json') {
            GeneralUtility::writeFile($path, $this->encodeJSON($data));
        } else {
            // path points to directory
            if (!is_dir($path)) {
                GeneralUtility::mkdir_deep($path);
            }

            $modules = $this->extractModules($data);
            foreach ($modules as $key => $data) {
                $filePath = rtrim($path, '/') . '/' . $key . '.json';
                GeneralUtility::writeFile($filePath, $this->encodeJSON($data));
            }
        }
    }

    /**
     * extracts all modules from configuration
     *
     * @param $data
     *
     * @return array
     */
    private function extractModules($data)
    {
        $modules = array();

        if (isset($data['tt_content']['elements'])) {
            foreach ($data['tt_content']['elements'] as $elementKey => $elementConfig) {
                // TODO: extract modules from json
                // key => should be filename
            }
        }

        return $modules;
    }

    /**
     * Recursively computes the intersection of arrays using keys for comparison.
     *
     * @param   array $array1 The array with master keys to check.
     * @param   array $array2 An array to compare keys against.
     *
     * @return  array associative array containing all the entries of array1 which have keys that are present in array2.
     **/
    private function searchForMergeError(array $array1, array $array2)
    {
        $flatArray1 = ArrayUtility::flatten($array1);
        $flatArray2 = ArrayUtility::flatten($array2);
        $intersection = array_intersect_key($flatArray1, $flatArray2);

        foreach ($intersection as $key => $value) {
            if ($value == $flatArray2[$key]) {
                unset($intersection[$key]);
            } else {
                $intersection[$key] .= ' => ' . $flatArray2[$key];
            }
        }

        return $intersection;
    }


    /**
     * Return JSON formatted in PHP 5.4.0 and higher
     *
     * @param $data
     *
     * @return string
     */
    private function encodeJSON($data)
    {
        if (version_compare(phpversion(), '5.4.0', '<')) {
            return json_encode($data);
        }
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}