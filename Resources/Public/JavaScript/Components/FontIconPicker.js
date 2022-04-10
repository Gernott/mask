define([
      'TYPO3/CMS/Mask/Contrib/vue',
      'jquery',
      'TYPO3/CMS/Mask/Contrib/FontIconPicker'
    ],
    function (Vue, $) {
      return Vue.component(
          'font-icon-picker',
          {
            props: {
              element: Object,
              label: String,
              faIcons: Object,
              property: String,
            },
            data() {
              return {
                iconPicker: {}
              }
            },
            mounted() {
              const iconPicker = $(this.$refs['meta-icon-' + this.property]).fontIconPicker({
                source: this.faIcons
              });
              iconPicker.setIcon(this.element[this.property]);
              this.iconPicker = $(iconPicker[0]).data('fontIconPicker');
            },
            template: `
    <div class="col-xs-6 col-6">
        <label class="t3js-formengine-label">
            {{ label }}
            <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank" title="FontAwesome 4.7 Icons"><i class="fa fa-question-circle"></i></a>
        </label>
        <div class="t3js-formengine-field-item icon-field">
            <div class="form-control-wrap">
                <select :ref="'meta-icon-' + property"></select>
            </div>
        </div>
    </div>
        `
          }
      )
    }
);
