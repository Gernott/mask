define([
    'TYPO3/CMS/Mask/Contrib/vue',
    'TYPO3/CMS/Backend/Tooltip',
    'jquery'
  ],
  function (Vue, Tooltip, $) {
    return Vue.component(
      'field',
      {
        props: {
          type: Object,
          addField: Function,
          typo3Version: Number,
          optionalExtensionStatus: Object,
        },
        mounted: function () {
          Tooltip.initialize(`.field-selectable-${this.type.name} [data-bs-toggle="tooltip"]`, {
              delay: {
                  'show': 50,
                  'hide': 100
              },
              trigger: 'hover',
              container: 'body'
          });
        },
        methods: {
          hideTooltip() {
            if (this.typo3Version > 10) {
              Tooltip.hide(this.$refs[this.type.name]);
            } else {
              Tooltip.hide($(this.$refs[this.type.name]));
            }
          },
        },
        template: `
          <li v-if="type.name != 'richtext' || optionalExtensionStatus.rte_ckeditor" @click="addField(type);" :class="'field-selectable-' + type.name" class="mask-field mask-field--selectable">
              <div class="mask-field__row">
                  <div @mousedown="hideTooltip()" class="mask-field__image" v-html="type.icon" data-bs-toggle="tooltip" :title="type.itemLabel" :ref="type.name"></div>
              </div>
          </li>
        `
      }
    )
  }
);
