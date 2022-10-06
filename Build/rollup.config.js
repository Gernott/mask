// rollup.config.js
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import { terser } from "rollup-plugin-minification";
import replace from '@rollup/plugin-replace';

const typo3Exports = [
    'autosize',
    'bootstrap',
    'broadcastchannel.js',
    'cropperjs',
    'd3-dispatch',
    'd3-drag',
    'd3-selection',
    'ev-emitter',
    'imagesloaded',
    'interactjs',
    'jquery',
    '@lit/reactive-element',
    'lit',
    'lit-element',
    'lit-html',
    'moment',
    'moment-timezone',
    'nprogress',
    'sortablejs',
    'tablesort.dotsep.js',
    'tablesort',
    'taboverride',
];

const typo3Prefixes = [
    '@typo3/',
    '@lit/reactive-element/',
    'lit/',
    'lit-element/',
    'lit-html/',
    'flatpickr/',
];

const lowerDashedToUpperCamelCase = (str) => str.replace(/([-\/])([a-z])/g, (_str, sep, letter) => (sep === '/' ? '/' : '') + letter.toUpperCase());

export function vueResolve() {
  return {
    name: 'vueResolve',
    resolveId(id) {
      if (id === 'vue') {
        // We need the vue template compiler, so load vue.esm.js instead of vue.runtime.esm.js (as defined in vue/package.json)
        return 'node_modules/vue/dist/vue.esm.js';
      }
    }
  }
}

export function typo3Resolve(config = {}) {
    return {
        name: 'typo3Resolve',

        resolveId(id) {
            const external = true

            for (const exportName of typo3Exports) {
                if (id === exportName) {
                    return { id, external }
                }
            }

            for (const exportPrefix of typo3Prefixes) {
                if (id.startsWith(exportPrefix)) {
                    if (!id.endsWith('.js')) {
                        id += '.js'
                    }
                    return { id, external }
                }
            }
        },
        renderChunk(code, chunk, outputOptions) {
            if (outputOptions.format !== 'amd') {
                return;
            }

            // Resolve "@typo3/ext-name/module-name.js" into "TYPO3/CMS/ExtName/ModuleName" for TYPO3 v11 (AMD) builds
            return code.replace(
                /(["'])@typo3\/([^\/]+)\/([^"']+)\.js\1/g,
                (match, quotes, extension, path) => lowerDashedToUpperCamelCase(`${quotes}TYPO3/CMS/${extension}/${path}${quotes}`)
            )
        }
    }
}

export default {
  input: 'Sources/js/mask.js',
  output: [
    {
      file: '../Resources/Public/JavaScript/mask.js',
      format: 'es',
    },
    {
      file: '../Resources/Public/JavaScript/AmdBundle.js',
      format: 'amd',
    },
  ],
  plugins: [
    vueResolve(),
    typo3Resolve(),
    resolve({
      mainFields: ['module', 'main'],
    }),
    commonjs(),
    replace({
      preventAssignment: true,
      'process.env.NODE_ENV': JSON.stringify( 'production' )
    }),
    terser(),
  ],
}
