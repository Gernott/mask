import Vue from 'vue';

export default Vue.component(
      'fieldRow',
      {
        props: {
          global: Object,
          fields: Array,
          field: Object,
          language: Object,
          icons: Object,
          index: Number,
          loadMultiUse: Function,
          multiUseElements: Object,
          fieldKey: String,
          keyWithoutMask: Function,
          isRoot: Function
        },
        methods: {
          setActiveField() {
            this.global.activeField = this.field;
            this.global.currentTab = 'general';
            this.loadMultiUse();
            if (window.innerWidth < 1100) {
              // Wait a bit until the Form is rerendered.
              // @todo Use an updated event for this.
              setTimeout(function () {
                document.querySelector('.mask-field-form').scrollIntoView({
                  behavior: 'smooth'
                });
              }, 50);
            }
          }
        },
        computed: {
          isMultiUse: function () {
            return this.isRoot(this.field) && (typeof this.multiUseElements[this.field.key] !== 'undefined') && this.multiUseElements[this.field.key].length;
          }
        },
        template: `
    <div :class="'field-row-' + fieldKey" class="mask-field__row" @click="setActiveField">
        <i v-if="isMultiUse" class="mask-field__multiuse"></i>
        <div class="mask-field__image">
            <div v-html="field.icon"></div>
        </div>
        <div class="mask-field__body">
          <div class="mask-field__text">
            <span v-if="field.name == 'linebreak'" class="mask-field__label">Linebreak</span>
            <span v-else-if="field.translatedLabel != ''" class="mask-field__label">{{ field.translatedLabel }}</span>
            <span v-else class="mask-field__label">{{ field.label }}</span>
            <span class="mask-field__key" v-if="!global.structuralFields.includes(field.name)">{{ keyWithoutMask(field.key) }}</span>
          </div>
          <div class="mask-field__actions">
              <a class="btn btn-default btn-sm" @click.stop="$emit('remove-field', index);" :title="language.tooltip.deleteField" v-html="icons.delete" :ref="'row' + index"></a>
          </div>
        </div>
    </div>
        `
      }
    );
