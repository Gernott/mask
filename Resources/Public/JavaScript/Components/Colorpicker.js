define([
      'TYPO3/CMS/Mask/Contrib/vue',
      'TYPO3/CMS/Backend/ColorPicker',
      'jquery'
    ],
    function (Vue, ColorPicker, $) {
      return Vue.component(
          'colorpicker',
          {
            props: {
              global: Object,
              tcaKey: String,
            },
            mounted: function () {
              ColorPicker.initialize();
              $(this.$refs['colorpicker']).minicolors('settings', {
                changeDelay: 200,
                change: function () {
                  this.global.activeField.tca[this.tcaKey] = $(this.$refs['colorpicker']).data('minicolorsLastChange')['value'];
                }.bind(this)
              });
            },
            methods: {
              value: function () { return this.global.activeField.tca[this.tcaKey]; },
            },
            template: `
              <div class="form-control-wrap">
                <input
                    class="form-control t3js-color-picker"
                    :value="value()"
                    ref="colorpicker"
                />
                <input type="hidden"/>
              </div>
        `
          }
      )
    }
);
