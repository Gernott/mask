import Vue from 'vue';

export default Vue.component(
      'field',
      {
        props: {
          type: Object,
          addField: Function,
          typo3Version: Number,
          optionalExtensionStatus: Object,
        },
        template: `
          <li v-if="type.name != 'richtext' || optionalExtensionStatus.rte_ckeditor" @click="addField(type);" :class="'field-selectable-' + type.name" class="mask-field mask-field--selectable">
              <div class="mask-field__row">
                  <div class="mask-field__image" v-html="type.icon" :title="type.itemLabel" :ref="type.name"></div>
              </div>
          </li>
        `
      }
    );
