.. include:: ../Includes.txt

.. _loader:

======
Loader
======

.. versionadded:: 7.1.0

The concept of a Loader is to abstract the source of the Mask configuration
away. The configuration can come from anywhere you want. It can be one huge
json file (like it always was) or many small json files, which will be merged
by the loader. The only requirement is to implement the
:php:`MASK\Mask\Loader\LoaderInterface`:

::

   interface LoaderInterface
   {
       /**
        * Loads the Mask configuration from any resource.
        *
        * @return TableDefinitionCollection
        */
       public function load(): TableDefinitionCollection;

       /**
        * Takes the table definition collection as input and writes it to the given resource.
        *
        * @param TableDefinitionCollection $tableDefinitionCollection
        */
       public function write(TableDefinitionCollection $tableDefinitionCollection): void;
   }


There are only two methods, which need to be implemented: :php:`load` and
:php:`write`. The crucial part is, that they receive and return an object of the
class :php:`MASK\Mask\Definition\TableDefinitionCollection`. This class
describes the structure of the Mask configuration, which is used internally.
The Loader is therefore responsible to prepare the source data into that format
when loading and, the other way around, when writing updated definitions.

Available Loaders
=================

Mask comes with two Loaders, which you can freely choose from:

*  JsonLoader
*  JsonSplitLoader

JsonLoader
__________

The JsonLoader is the default Loader, which implements the standard behaviour of
having one single mask.json file. It will kick in, if you didn't configure
otherwise.

Related extension configuration
+++++++++++++++++++++++++++++++

*  :ref:`loader_identifier <extension-settings-loader_identifier>` (json)
*  :ref:`json <extension-settings-json>`

JsonSplitLoader
_______________

The new JsonSplitLoader splits all Mask elements and page templates into
separate files. The advantage is, that you can easily copy a definition from one
project to another. You just have to make sure the used keys are unique. Right
now, Mask does not warn you and will just use the last field. Another neat thing
is, that version control diffs are much easier to read.

Related extension configuration
+++++++++++++++++++++++++++++++

*  :ref:`loader_identifier <extension-settings-loader_identifier>` (json-split)
*  :ref:`content_elements_folder <extension-settings-content_elements_folder>`
*  :ref:`backend_layouts_folder <extension-settings-backend_layouts_folder>`


Registering Loaders
===================

Loaders need to be registered via DI. This can be done in `Services.yaml` or
`Services.php`. They receive the tag `mask.loader` and an identifier of your
choice.

Example for Services.yaml:

.. code-block:: yaml

   MASK\Mask\Loader\JsonSplitLoader:
     tags:
       - name: mask.loader
         identifier: json-split


The identifier is used to choose the active Loader in the extension
configuration: :ref:`loader_identifier <extension-settings-loader_identifier>`.
