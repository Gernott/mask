import Vue from 'vue';
import Icons from '@typo3/backend/icons.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Modal from '@typo3/backend/modal.js';

export default Vue.component(
          'button-bar',
          {
            props: {
              element: Object,
              showMessages: Function,
              icons: Object,
              openEdit: Function,
              openDeleteDialog: Function,
              language: Object,
              table: String
            },
            data() {
              return {
                toggleIcons: {
                  actionsEditHide: '',
                  actionsEditUnhide: '',
                  spinnerCircleDark: '',
                },
                htmlIcon: '',
                loading: false
              };
            },
            methods: {
              toggleVisibility() {
                this.loading = true;
                (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_toggle_visibility)).post({element: this.element})
                    .then(
                        async response => {
                          const res = await response.resolve();
                          this.loading = false;
                          this.showMessages(res);
                          this.$emit('toggle');
                        }
                    )
              },
              openFluidCodeModal(element) {
                const url = new URL(TYPO3.settings.ajaxUrls.mask_html, window.location.origin);
                url.searchParams.append('key', element.key);
                url.searchParams.append('table', this.table);

                Modal.advanced({
                  type: Modal.types.ajax,
                  size: Modal.sizes.large,
                  title: 'Example Fluid Code for element: ' + element.label,
                  content: url.href
                });
              },
            },
            computed: {
              toggleIcon() {
                if (this.loading) {
                  return this.toggleIcons.spinnerCircleDark;
                }
                return this.element.hidden ? this.toggleIcons.actionsEditUnhide : this.toggleIcons.actionsEditHide;
              }
            },
            mounted() {
              Icons.getIcon('actions-edit-hide', Icons.sizes.small).then((icon) => {
                this.toggleIcons.actionsEditHide = icon;
              });
              Icons.getIcon('actions-edit-unhide', Icons.sizes.small).then((icon) => {
                this.toggleIcons.actionsEditUnhide = icon;
              });
              Icons.getIcon('spinner-circle-dark', Icons.sizes.small).then((icon) => {
                this.toggleIcons.spinnerCircleDark = icon;
              });
              Icons.getIcon('sysnote-type-2', Icons.sizes.small).then((icon) => {
                this.htmlIcon = icon;
              });
            },
            template: `
            <div :class="table + '-' + element.key + '-bar'" class="mask-elements__btn-group">
              <div class="btn-group">
                <a :ref="element.key + 'html'" @click="openFluidCodeModal(element);" class="btn btn-default" :title="language.tooltip.html">
                    <span v-html="htmlIcon"></span>
                </a>
                <a :ref="element.key + 'edit'" class="btn btn-default" @click="openEdit(table, element);" :title="language.tooltip.editElement">
                    <span v-html="icons.edit"></span>
                </a>
                <a v-if="table == 'tt_content'" v-show="!element.hidden" :ref="element.key + 'hide'" class="btn btn-default" :class="{'disable-pointer': loading}" @click="toggleVisibility('hide');" :title="language.tooltip.disableElement">
                   <span v-html="toggleIcon"></span>
                </a>
                <a v-if="table == 'tt_content'" v-show="element.hidden" :ref="element.key + 'enable'" class="btn btn-default" :class="{'disable-pointer': loading}" @click="toggleVisibility('enable');" :title="language.tooltip.enableElement">
                   <span v-html="toggleIcon"></span>
                </a>
                <a v-if="table == 'tt_content'" :ref="element.key + 'delete'" class="btn btn-default" @click="openDeleteDialog(element)" :title="language.tooltip.deleteElement">
                    <span v-html="icons.delete"></span>
                </a>
              </div>
            </div>
        `
          }
      );
