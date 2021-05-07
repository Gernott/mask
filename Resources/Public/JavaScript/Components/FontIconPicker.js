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
              language: Object,
              faIcons: Object
            },
            data() {
              return {
                iconPicker: {}
              }
            },
            mounted() {
              const iconPicker = $('#meta-icon').fontIconPicker({
                source: this.faIcons
              });
              iconPicker.setIcon(this.element.icon);
              this.iconPicker = $(iconPicker[0]).data('fontIconPicker');
            },
            template: `
    <div class="col-sm-6">
        <label class="t3js-formengine-label">
            {{ language.icon }}
            <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank" title="FontAwesome 4.7 Icons"><i class="fa fa-question-circle"></i></a>
        </label>
        <div class="t3js-formengine-field-item icon-field">
            <div class="form-control-wrap">
                <select id="meta-icon"></select>
            </div>
        </div>
    </div>
        `
          }
      )
    }
);
