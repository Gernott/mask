define([
      'TYPO3/CMS/Mask/Contrib/vue',
      'TYPO3/CMS/Backend/ColorPicker',
      'jquery'
    ],
    function (Vue, ColorPicker, $) {
      return Vue.component(
          'element-color-picker',
          {
            props: {
              element: Object,
              label: String,
              property: String,
            },
            mounted: function () {
              ColorPicker.initialize();
              $(this.$refs['colorpicker-' + this.property]).minicolors('settings', {
                changeDelay: 200,
                change: function () {
                  this.element[this.property] = $(this.$refs['colorpicker-' + this.property]).data('minicolorsLastChange')['value'];
                }.bind(this)
              });
            },
            template: `
    <div class="col-xs-6 col-6">
        <label class="t3js-formengine-label" for="meta_color">
            {{ label }}
        </label>
        <div class="t3js-formengine-field-item">
            <div class="form-control-wrap">
                <input
                    class="form-control t3js-color-picker"
                    :value="element[property]"
                    :ref="'colorpicker-' + property"
                />
            </div>
            <input type="hidden" ref="colorvalue"/>
        </div>
    </div>
        `
          }
      )
    }
);
