import Vue from 'vue';
import $ from 'jquery';
import Tooltip from '@typo3/backend/tooltip.js';

export default Vue.component(
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
            Tooltip.hide(this.$refs[this.type.name]);
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
    );
