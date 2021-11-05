define([
    'TYPO3/CMS/Mask/Contrib/vue',
  ],
  function (Vue) {
    return Vue.component(
      'field-key',
      {
        props: {
          global: Object,
          validateKey: Function,
          loadField: Function,
        },
        updated: function () {
          if (this.global.activeField.key === this.global.maskPrefix) {
            this.$refs.fieldKey.focus();
          }
        },
        methods: {
          onInput: function () {
            if (this.validateKey(this.global.activeField)) {
              this.loadField();
            }
          }
        },
        template: `
          <input
              v-model="global.activeField.key"
              id="form_key"
              class="form-control"
              required="required"
              :readOnly="!global.activeField.newField"
              @input="onInput"
              ref="fieldKey"
          />
        `
      }
    )
  }
);
