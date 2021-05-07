define([
    'TYPO3/CMS/Mask/Contrib/vue',
  ],
  function (Vue) {
    return Vue.component(
      'element-key',
      {
        props: {
          element: Object,
          mode: String,
        },
        mounted: function () {
          if (this.element.key === '') {
            this.$refs.elementKey.focus();
          }
        },
        template: `
            <input
                v-model="element.key"
                id="meta_key"
                class="form-control"
                required="required"
                :readonly="mode == 'edit'"
                ref="elementKey"
            />
        `
      }
    )
  }
);
