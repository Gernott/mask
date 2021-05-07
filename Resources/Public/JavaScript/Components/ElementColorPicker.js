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
              language: Object,
            },
            mounted: function () {
              ColorPicker.initialize();
              $(this.$refs['colorpicker']).minicolors('settings', {
                changeDelay: 200,
                change: function () {
                  this.element.color = $(this.$refs['colorpicker']).data('minicolorsLastChange')['value'];
                }.bind(this)
              });
            },
            template: `
    <div class="col-sm-6">
        <label class="t3js-formengine-label" for="meta_color">
            {{ language.color }}
        </label>
        <div class="t3js-formengine-field-item">
            <div class="form-control-wrap">
                <input
                    class="form-control t3js-color-picker"
                    :value="element.color"
                    ref="colorpicker"
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
