define([
  'TYPO3/CMS/Mask/Contrib/vue',
  'TYPO3/CMS/Mask/Contrib/vuedraggable',
  'TYPO3/CMS/Mask/Components/NestedDraggable',
  'TYPO3/CMS/Mask/Components/FormField',
  'TYPO3/CMS/Mask/Components/FieldKey',
  'TYPO3/CMS/Mask/Components/ElementKey',
  'TYPO3/CMS/Mask/Components/SplashScreen',
  'TYPO3/CMS/Mask/Components/ButtonBar',
  'TYPO3/CMS/Mask/Components/FontIconPicker',
  'TYPO3/CMS/Mask/Components/FieldGroup',
  'TYPO3/CMS/Mask/Components/ElementColorPicker',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Backend/Icons',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Backend/Notification',
  'TYPO3/CMS/Backend/MultiStepWizard',
], function (
  Vue,
  draggable,
  nestedDraggable,
  formField,
  fieldKey,
  elementKey,
  splashscreen,
  buttonBar,
  fontIconPicker,
  fieldGroup,
  elementColorPicker,
  AjaxRequest,
  Icons,
  Modal,
  Severity,
  Notification,
  MultiStepWizard,
) {
  if (!document.getElementById('mask')) {
    return;
  }

  const mask = new Vue({
    el: '#mask',
    components: {
      draggable,
      nestedDraggable,
      formField,
      elementKey,
      fieldKey,
      splashscreen,
      buttonBar,
      fontIconPicker,
      fieldGroup,
      elementColorPicker,
    },
    data: function () {
      return {
        version: '',
        mode: 'list',
        type: '',
        sidebar: 'fields',
        groups: [],
        elements: [],
        element: {},
        backendLayouts: [],
        fieldTypes: [],
        tcaFields: {},
        tabs: {},
        fields: [],
        language: [],
        icons: {},
        faIcons: {},
        availableTca: {},
        multiUseElements: {},
        fieldErrors: {
          elementKeyAvailable: true,
          elementKey: false,
          elementLabel: false,
          emptyKeyFields: [],
          emptyTabLabels: [],
          emptyGroupAllowedFields: [],
          emptyRadioItems: [],
          existingFieldKeyFields: []
        },
        global: {
          typo3Version: 10,
          activeField: {},
          clonedField: {},
          richtextConfiguration: {},
          currentTab: 'general',
          ctypes: {},
          sctructuralFields: ['linebreak', 'palette', 'tab'],
          maskPrefix: 'tx_mask_',
          deletedFields: [],
        },
        loaded: false,
        missingFilesOrFolders: false,
        saving: false,
        ticks: 0,
      }
    },
    mounted: function () {
      const promises = [];

      // Fetch language
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_language)).get()
        .then(
          async function (response) {
            mask.language = await response.resolve();
          }
        ));

      // Fetch tcaFields for existing core and mask fields
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_tca_fields)).get()
        .then(
          async function (response) {
            mask.tcaFields = await response.resolve();
          }
        ));

      // fetch tab declarations
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_tabs)).get()
        .then(
          async function (response) {
            mask.tabs = await response.resolve();
          }
        ));

      // fetch richtext configuration
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_richtext_configuration)).get()
        .then(
          async function (response) {
            mask.global.richtextConfiguration = await response.resolve();
          }
        ));

      // fetch CTypes
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_ctypes)).get()
        .then(
          async function (response) {
            const result = await response.resolve();
            mask.global.ctypes = result.ctypes;
          }
        ));

      // fetch field groups
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_field_groups)).get()
          .then(
              async function (response) {
                const result = await response.resolve();
                mask.groups = result.groups;
              }
          ));

      // fetch elements
      promises.push(this.loadElements());

      // fetch backend layouts
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_backend_layouts)).get()
          .then(
              async function (response) {
                const backendLayouts = await response.resolve();
                mask.backendLayouts = backendLayouts['backendLayouts'];
              }
          ));

      // fetch fontawesome icons
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_icons)).get()
        .then(
          async function (response) {
            mask.faIcons = await response.resolve();
          }
        ));

      // fetch possible missing files or folders
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_missing)).get()
          .then(
              async function (response) {
                const missing = await response.resolve();
                mask.missingFilesOrFolders = missing.missing;
              }
          ));

      // fetch mask and typo3 version
      promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_versions)).get()
          .then(
              async function (response) {
                const versions = await response.resolve();
                mask.version = versions.mask;
                mask.global.typo3Version = versions.typo3;
              }
          ));

      promises.push(Icons.getIcon('actions-edit-delete', Icons.sizes.small).done(function (icon) {
        mask.icons.delete = icon;
      }));
      promises.push(Icons.getIcon('actions-move-move', Icons.sizes.small).done(function (icon) {
        mask.icons.move = icon;
      }));
      promises.push(Icons.getIcon('actions-edit-pick-date', Icons.sizes.small).done(function (icon) {
        mask.icons.date = icon;
      }));
      promises.push(Icons.getIcon('actions-open', Icons.sizes.small).done(function (icon) {
        mask.icons.edit = icon;
      }));
      promises.push(Icons.getIcon('actions-save', Icons.sizes.small).done(function (icon) {
        mask.icons.save = icon;
      }));
      promises.push(Icons.getIcon('actions-close', Icons.sizes.small).done(function (icon) {
        mask.icons.close = icon;
      }));
      promises.push(Icons.getIcon('spinner-circle-dark', Icons.sizes.small).done(function (icon) {
        mask.icons.spinner = icon;
      }));

      Promise.all(promises).then(() => {
        mask.loaded = true;
      });

      // Trigger input change on TYPO3 datepicker change event.
      if (this.global.typo3Version === 10) {
        $(document).on('formengine.dp.change', function () {
          document.querySelectorAll('.t3js-datetimepicker').forEach(function (input) {
            input.dispatchEvent((new Event('input')));
          });
        });
      }
    },
    watch: {
      element: {
        handler() {
          this.validate();
        },
        deep: true
      },
      fields: {
        handler() {
          this.validate();
        },
        deep: true
      },
      'global.activeField.fields': function () {
        this.validate();
      },
      'element.key': function () {
        if (this.mode === 'edit') {
          return;
        }
        const validKey = this.checkAllowedCharacters(this.element.key);
        if (this.element.key !== validKey) {
          this.element.key = validKey;
          return;
        }
        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_check_element_key)
          .withQueryArguments({key: this.element.key})
          .get()
          .then(
            async function (response) {
              const result = await response.resolve();
              mask.fieldErrors.elementKeyAvailable = result.isAvailable;
            }
          );
      },
    },
    methods: {
      save: function () {
        this.saving = true;
        this.validate();
        if (!this.hasErrors) {
          this.mode = 'edit';
          this.global.deletedFields = [];
          const payload = {
            element: this.getPostElement(),
            fields: JSON.stringify(this.getPostFields(this.fields)),
            type: this.type,
            isNew: this.mode === 'new' ? 1 : 0
          };
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_save).post(payload)
            .then(
              async function (response) {
                const res = await response.resolve();
                this.showMessages(res);
                this.loadElements();
                // load element fields
                new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_element)
                    .withQueryArguments({
                      type: payload.type,
                      key: payload.element.key
                    })
                    .get()
                    .then(
                        async function (response) {
                          const result = await response.resolve();
                          this.fields = result.fields;
                          this.addParentReferenceToFields({}, this.fields);
                          if (!this.isEmptyObject(this.global.activeField)) {
                            this.findActiveField(this.global.activeField, this.fields);
                          }
                          this.saving = false;
                        }.bind(this)
                    );
              }.bind(this)
            );
        } else {
          this.saving = false;
          Modal.confirm(
            this.language.alert || 'Alert',
            this.language.fieldsMissing,
            Severity.error,
            [
              {
                text: this.language.ok || 'OK',
                btnClass: 'btn-default',
                active: true,
                name: 'ok',
                trigger: function () {
                  Modal.dismiss();
                  mask.getErrorFields().every(function (errorFields) {
                    if (errorFields.length > 0) {
                      mask.global.activeField = errorFields[0];
                      return false;
                    }
                    return true;
                  });
                }
              }
            ]
          )
        }
      },
      getPostElement() {
        if (this.type === 'tt_content') {
          return {
            key: this.element.key,
            icon: this.$refs.iconPicker.iconPicker.currentIcon,
            label: this.element.label,
            shortLabel: this.element.shortLabel,
            description: this.element.description,
            color: this.element.color,
            hidden: this.element.hidden,
          };
        } else {
          return {
            key: this.element.key
          }
        }
      },
      getPostFields: function (fields) {
        const postFields = [];
        fields.forEach(function (item) {
          postFields.push({
            key: item.key,
            label: item.label,
            description: item.description,
            name: item.name,
            tca: Object.assign({}, item.tca),
            fields: mask.getPostFields(item.fields),
            sql: item.sql
          });
        });
        return postFields;
      },
      /**
       * This method finds the last active field before saving and sets it again to active.
       * This is necessary, because the fields are loaded freshly after saving and the reference is gone.
       * @param activeField
       * @param fields
       */
      findActiveField: function (activeField, fields) {
        let found = false;
        fields.forEach(function (field) {
            if (field.key === activeField.key && (this.isEmptyObject(activeField.parent) && this.isEmptyObject(field.parent) || activeField.parent.key === field.parent.key)) {
              this.global.activeField = field;
              found = true;
            }
            if (!found) {
              this.findActiveField(activeField, field.fields);
            }
        }.bind(this));
      },
      loadElements: function () {
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_elements)).get()
            .then(
                async function (response) {
                  const result = await response.resolve();
                  mask.elements = result.elements;
                }
            );
      },
      loadField: function () {
        if (this.isExistingMaskField) {
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_field)
            .withQueryArguments({key: this.global.activeField.key, type: this.type})
            .get()
            .then(
              async function (response) {
                const result = await response.resolve();
                mask.global.activeField.tca = result.field.tca;
                mask.global.activeField.label = result.field.label;
              }
            );
            mask.loadMultiUse();
        }
      },
      loadMultiUse: function () {
        // If it's not an existing tca key there can't be multi usages.
        if (!this.isExistingMaskField) {
          return;
        }

        // If already cached, return.
        if (this.multiUseElements[this.global.activeField.key]) {
          return;
        }

        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_multiuse)
            .withQueryArguments({key: this.global.activeField.key, elementKey: this.element.key, newField: this.global.activeField.newField ? 1 : 0})
            .get()
            .then(
                async function (response) {
                  const result = await response.resolve();
                  // We need to use $set here for reactivity to work, as keys are added dynamically.
                  mask.$set(mask.multiUseElements, mask.global.activeField.key, result.multiUseElements);
                }
            );
      },
      validateKey: function (field) {
        if (this.isEmptyObject(this.global.activeField)) {
          return;
        }

        // Force mask prefix if not a core field
        if (!this.isActiveCoreField && !this.hasMaskPrefix(field.key)) {
          field.key = this.global.maskPrefix;
          return;
        }

        // Force lowercase and remove special chars
        field.key = this.checkAllowedCharacters(field.key);

        // Skip empty fields (these are validated by empty validator)
        if (field.key === this.global.maskPrefix) {
          return;
        }

        // Step 1: Check if key is in current fields array
        let fields = this.getFields(field);
        let error = this.checkIfKeyExistsInFields(fields, this.global.activeField);
        if (error) {
          this.fieldErrors.existingFieldKeyFields.push(this.global.activeField);
        } else {
          mask.removeExistingKeyField(this.global.activeField);
        }

        // Step 2: Check if another field is now valid due to the change
        this.fieldErrors.existingFieldKeyFields.every(function (errorField) {
          if (errorField !== field && !mask.checkIfKeyExistsInFields(mask.getFields(errorField), errorField)) {
            mask.removeExistingKeyField(errorField);
          }
          return true;
        });

        // Step 3: Check if key is in possible tca array and avoid ajax check if so
        if (this.getAvailableTcaKeys()[field.name].includes(field.key)) {
          return;
        }

        // If there is an error already from step 1 or we are not on root, cancel tca ajax check
        if (error || !this.isRoot(field)) {
          return;
        }

        // Check if key already exists in table
        let arguments = {
          key: field.key,
          type: field.name,
          elementKey: ''
        };
        if (this.mode === 'edit') {
          arguments.elementKey = this.element.key;
        }
        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_check_field_key)
          .withQueryArguments(arguments)
          .get()
          .then(
            async function (response) {
              const result = await response.resolve();
              if (result.isAvailable) {
                mask.removeExistingKeyField(mask.global.activeField);
              } else {
                mask.fieldErrors.existingFieldKeyFields.push(mask.global.activeField);
              }
            }
          );
      },
      hasMaskPrefix: function (key) {
        return key.substr(0, this.global.maskPrefix.length) === this.global.maskPrefix;
      },
      isRoot: function (field) {
        return this.isEmptyObject(field.parent) || field.parent.name === 'palette' && this.isEmptyObject(field.parent.parent);
      },
      getFields: function (field) {
        let fields = this.fields;
        if (!this.isRoot(field)) {
          if (field.parent.name !== 'palette' || !this.isEmptyObject(field.parent.parent)) {
            fields = field.parent.fields;
          } else {
            fields = field.parent.parent.fields;
          }
        }
        return fields;
      },
      checkIfKeyExistsInFields: function (fields, checkField) {
        let error = false;
        fields.every(function (field) {
          if (field !== checkField) {
            if (checkField.key === field.key) {
              error = true;
            } else {
              if (!error && field.name === 'palette') {
                error = mask.checkIfKeyExistsInFields(field.fields, checkField);
              }
            }
            return !error;
          }
          return true;
        });
        return error;
      },
      removeExistingKeyField: function (removedField) {
        mask.fieldErrors.existingFieldKeyFields = mask.fieldErrors.existingFieldKeyFields.filter(function (field) {
          return field !== removedField;
        });
      },
      openNew: function () {
        this.resetState();
        this.type = 'tt_content';
        this.element = this.getNewElement();

        const stepLabels = [null, null];
        if (this.isTYPO3v11) {
          stepLabels[0] = this.language.multistep.chooseLabel;
          stepLabels[1] = this.language.multistep.chooseKey;
        }

        /** Step 1: Choose element label */
        MultiStepWizard.addSlide('new-mask-element-step-1', this.language.multistep.chooseLabel, '', Severity.info, stepLabels[0], (slide) => {
          MultiStepWizard.blurCancelStep();
          MultiStepWizard.lockPrevStep();

          let html = '';
          html += '<p>' + this.language.multistep.text1 + '</p>';
          html += '<label class="control-label" for="mask-step-label">' + this.language.elementLabel + '</label>';
          html += '<input id="mask-step-label" class="form-control" placeholder="' + this.language.multistep.placeholder1 + '"/>';
          slide.html(html);

          const elementLabel = MultiStepWizard.setup.$carousel.closest('.modal').find('#mask-step-label');
          elementLabel.focus();
          MultiStepWizard.set('elementLabel', '');
          elementLabel.on('change', function () {
            MultiStepWizard.set('elementLabel', $(this).val());
          });
        });

        /** Step 2: Choose element key. Generate suggestion from chosen label. */
        MultiStepWizard.addSlide('new-mask-element-step-2', this.language.multistep.chooseKey, '', Severity.info, stepLabels[1], (slide) => {
          // In v10 the buttons disappear, that's why we don't unlock here.
          if (this.isTYPO3v11) {
            MultiStepWizard.unlockPrevStep();
          }
          let html = '';
          html += '<p>' + this.language.multistep.text2 + '</p>';
          html += '<label class="control-label" for="mask-step-key">' + this.language.elementKey + '</label>';
          html += '<input id="mask-step-key" class="form-control" placeholder="' + this.language.multistep.placeholder2 + '"/>';
          slide.html(html);

          const elementKey = MultiStepWizard.setup.$carousel.closest('.modal').find('#mask-step-key');
          elementKey.val(this.checkAllowedCharacters(MultiStepWizard.setup.settings['elementLabel']));
          MultiStepWizard.set('elementKey', elementKey.val());
          elementKey.on('change', function () {
            MultiStepWizard.set('elementKey', $(this).val());
          });

          let modal = MultiStepWizard.setup.$carousel.closest('.modal');
          let nextButton = modal.find('.modal-footer').find('button[name="next"]');
          nextButton.focus();
        });

        MultiStepWizard.addFinalProcessingSlide(() => {
          this.element.label = MultiStepWizard.setup.settings['elementLabel'];
          this.element.key = MultiStepWizard.setup.settings['elementKey'];
          this.loaded = false;
          this.mode = 'new';
          this.validate();

          Promise.resolve(this.loadTca()).then(() => {
            this.loaded = true;
            MultiStepWizard.dismiss();
          });
        }).done(() => {
          MultiStepWizard.show();
          if (this.isTYPO3v11) {
            MultiStepWizard.setup.forceSelection = false;
          }
        });
      },
      openEdit: function (type, element) {
        this.loaded = false;
        this.resetState();
        this.mode = 'edit';
        this.type = type;
        this.element = element;
        let requests = [];

        // load element fields
        requests.push(this.loadTca().then(function () {
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_element)
              .withQueryArguments({
                type: type,
                key: element.key
              })
              .get()
              .then(
                  async function (response) {
                    const result = await response.resolve();
                    mask.fields = result.fields;
                    mask.addParentReferenceToFields({}, mask.fields);
                  }
              )
        }));

        requests.push(new AjaxRequest(TYPO3.settings.ajaxUrls.mask_all_multiuse)
            .withQueryArguments({
              table: this.type,
              elementKey: element.key
            })
            .get()
            .then(
                async function (response) {
                  const result = await response.resolve();
                  if (result.multiUseElements.length !== 0) {
                    mask.multiUseElements = result.multiUseElements;
                  }
                }
            ));

        Promise.all(requests).then(function () {
          mask.loaded = true;
        });
      },
      addParentReferenceToFields: function (parent, fields) {
        fields.forEach(function (field) {
          field.parent = parent;
          this.addParentReferenceToFields(field, field.fields);
        }.bind(this));
      },
      loadTca: function () {
        // Fetch fieldtypes and available tca
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_fieldtypes)).get()
          .then(
            async function (response) {
              mask.fieldTypes = await response.resolve();
              mask.fieldTypes.forEach(function (item) {
                new AjaxRequest(TYPO3.settings.ajaxUrls.mask_existing_tca).withQueryArguments({table: mask.type, type: item.name}).get()
                  .then(
                    async function (response) {
                      mask.availableTca[item.name] = await response.resolve();
                    }
                  )
              });
            }
          );
      },
      deleteElement: function (item, purge) {
        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_delete).post({key: item.key, purge: purge})
            .then(
                async function (response) {
                  const res = await response.resolve();
                  mask.showMessages(res);
                  mask.loadElements();
                }
            );
      },
      openDeleteDialog(item) {
        Modal.confirm(
            this.language.deleteModal.title + ': ' + item.label,
            this.language.deleteModal.content,
            Severity.warning,
            [
              {
                text: this.language.deleteModal.close,
                btnClass: 'btn-default',
                trigger: function () {
                  Modal.dismiss();
                }
              },
              {
                text: this.language.deleteModal.delete,
                active: true,
                btnClass: 'btn-warning',
                trigger: function () {
                  Modal.dismiss();
                  mask.deleteElement(item, 0);
                }
              },
              {
                text: this.language.deleteModal.purge,
                btnClass: 'btn-danger',
                trigger: function () {
                  Modal.dismiss();
                  mask.deleteElement(item, 1);
                }
              }
            ]);
      },
      fixMissing() {
        (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_fix_missing)).get()
          .then(
            async function (response) {
              const fixed = await response.resolve();
              if (fixed['success']) {
                Notification.success('', mask.language.missingCreated);
                mask.missingFilesOrFolders = false;
                mask.loadElements();
              } else {
                Notification.error('', 'Something went wrong while trying to create missing files.');
              }
            }
          )
      },
      showMessages: function (res) {
        Object.keys(res).forEach(function (key) {
          const item = res[key];
          if (item.severity === 0) {
            Notification.success(item.title, item.message);
          } else {
            Notification.error(item.title, item.message);
          }
        });
      },
      resetState: function () {
        this.type = '';
        this.element = {};
        this.fields = [];
        this.sidebar = 'fields';
        this.multiUseElements = {};
        this.global.deletedFields = [];
        this.global.activeField = {};
        this.global.clonedField = {};
        this.fieldErrors = {
          elementKeyAvailable: true,
          elementKey: false,
          elementLabel: false,
          emptyKeyFields: [],
          emptyTabLabels: [],
          emptyGroupAllowedFields: [],
          emptyRadioItems: [],
          existingFieldKeyFields: []
        };
      },
      fieldHasError: function (field) {
        if (!this.hasFieldErrors) {
          return false;
        }
        if (this.fieldErrors.emptyKeyFields.includes(field)) {
          return true;
        }
        if (this.fieldErrors.emptyTabLabels.includes(field)) {
          return true;
        }
        if (this.fieldErrors.existingFieldKeyFields.includes(field)) {
          return true;
        }
        if (this.fieldErrors.emptyGroupAllowedFields.includes(field)) {
          return true;
        }
        if (this.fieldErrors.emptyRadioItems.includes(field)) {
          return true;
        }
        return false;
      },
      validate: function () {
        this.fieldErrors.elementKey = this.element.key === '';
        this.fieldErrors.elementLabel = this.element.label === '';

        this.fieldErrors.emptyKeyFields = [];
        this.fieldErrors.emptyTabLabels = [];
        this.fieldErrors.emptyGroupAllowedFields = [];
        this.fieldErrors.emptyRadioItems = [];

        this.checkFieldKeyIsEmpty(this.fields);
        this.checkTabLabelIsEmpty(this.fields);
        this.checkEmptyGroupAllowed(this.fields);
        this.checkEmptyRadioItems(this.fields);
      },
      getErrorFields: function () {
        return [
          this.fieldErrors.emptyKeyFields,
          this.fieldErrors.emptyTabLabels,
          this.fieldErrors.emptyGroupAllowedFields,
          this.fieldErrors.emptyRadioItems
        ];
      },
      checkFieldKeyIsEmpty: function (fields) {
        fields.every(function (item) {
          if (item.key === mask.global.maskPrefix) {
            mask.fieldErrors.emptyKeyFields.push(item);
          }
          if (item.fields.length > 0) {
            mask.checkFieldKeyIsEmpty(item.fields);
          }
          return true;
        });
      },
      checkTabLabelIsEmpty: function (fields) {
        fields.every(function (item) {
          if (item.name === 'tab' && item.label === '') {
            mask.fieldErrors.emptyTabLabels.push(item);
          }
          if (item.fields.length > 0) {
            mask.checkTabLabelIsEmpty(item.fields);
          }
          return true;
        });
      },
      checkEmptyGroupAllowed: function (fields) {
        fields.every(function (item) {
          if (mask.isCoreField(item)) {
            return true;
          }
          if (item.tca['config.internal_type'] === 'db' && item.tca['config.allowed'] === '') {
            mask.fieldErrors.emptyGroupAllowedFields.push(item);
          }
          if (item.fields.length > 0) {
            mask.checkEmptyGroupAllowed(item.fields);
          }
          return true;
        });
      },
      checkEmptyRadioItems: function (fields) {
        fields.every(function (item) {
          if (this.isCoreField(item)) {
            return true;
          }
          if (item.name === 'radio') {
            const items = item.tca['config.items'].split("\n");
            if (items.length < 2) {
              this.fieldErrors.emptyRadioItems.push(item);
            } else {
              items.every(function (radioItem) {
                const parts = radioItem.split(',');
                if (parts.length < 2 || !this.isNumeric(parts[1])) {
                  this.fieldErrors.emptyRadioItems.push(item);
                  return false;
                }
                return true;
              }.bind(this));
            }
          }
          if (item.fields.length > 0) {
            this.checkEmptyRadioItems(item.fields);
          }
          return true;
        }.bind(this));
      },
      handleClone: function (item) {
        // Create a fresh copy of item
        let cloneMe = JSON.parse(JSON.stringify(item));
        this.$delete(cloneMe, 'uid');
        this.global.clonedField = cloneMe;
        return cloneMe;
      },
      /**
       * This adds a field by click on the field.
       * @param type
       */
      addField: function (type) {
        const newField = this.handleClone(type);
        const parent = this.global.activeField.parent;
        let parentKey = '';
        let parentName = '';
        let fields = this.fields;
        if (typeof parent === 'undefined' || parent.length === 0) {
          newField.parent = {};
        } else {
          parentName = parent.name;
          parentKey = parent.key;
          newField.parent = parent;
          if (typeof parent.fields !== 'undefined') {
            fields = parent.fields;
          }
        }
        if (this.validateMove(parentKey, parentName, newField)) {
          const index = fields.indexOf(this.global.activeField) + 1;
          fields.splice(index, 0, newField);
          this.global.activeField = newField;
          this.global.currentTab = 'general';
          this.validateKey(newField);
        }
      },
      onMove: function (e) {
        const draggedField = e.draggedContext.element;
        let parent = e.relatedContext.component.$parent;
        const depth = parent.depth;
        const index = parent.index;
        let parentName = '';
        let parentKey = '';

        if (depth > 0) {
          parentName = parent.$parent.list[index].name;
          parentKey = parent.$parent.list[index].key;
        }

        return this.validateMove(parentKey, parentName, draggedField);
      },
      validateMove: function (newParentKey, newParentName, draggedField) {
        if (newParentName !== '') {
          // Elements palette and tab are not allowed in palette
          if (newParentName === 'palette' && ['palette', 'tab'].includes(draggedField.name)) {
            return false;
          }

          // Existing fields are not allowed as new inline field, but allow moving items inside
          if (newParentName === 'inline' && !draggedField.newField && newParentKey !== draggedField.parent.key) {
            return false;
          }

          // Palettes or inline fields with elements are not allowed in inline fields
          if (newParentName === 'inline' && ['palette', 'inline'].includes(draggedField.name) && draggedField.fields.length > 0) {
            return false;
          }
        }

        // Existing fields in inline can't be dragged out besides in palette
        if (
            draggedField.parent.name === 'inline'
            && !draggedField.newField
            && newParentKey !== draggedField.parent.key
            && (newParentName !== 'palette' || newParentName === '')
        ) {
          return false;
        }

        // Linebreaks are only allowed in palette
        if (draggedField.name === 'linebreak' && newParentName !== 'palette') {
          return false;
        }

        return true;
      },
      getNewElement: function () {
        return {
          key: '',
          label: '',
          shortLabel: '',
          description: '',
          icon: '',
          color: '#000000'
        };
      },
      resetActiveField: function () {
        this.global.activeField.tca = Object.assign({}, this.defaultTca[this.global.activeField.name]);
        Notification.success('', this.language.reset);
      },
      isEmptyObject: function (obj) {
        return Object.keys(obj).length === 0 && obj.constructor === Object;
      },
      isNumeric: function (str) {
        // we only process strings!
        if (typeof str != "string") {
          return false
        }
        return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
            !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
      },
      checkAllowedCharacters: function (key) {
        key = key.toLowerCase();
        key = key.replace(/\s/g, '_');
        key = key.replace(/[^a-z0-9_]/g, '');
        return key;
      },
      isCoreField: function (field) {
        if (this.isEmptyObject(field)) {
          return false;
        }
        let isExisting = false;
        this.availableTca[field.name].core.forEach(function (item) {
          if (item.field === field.key) {
            isExisting = true;
          }
        });
        return isExisting;
      },
      availableTcaForActiveField: function (type) {
        if (this.isEmptyObject(this.availableTca) || this.isEmptyObject(this.global.activeField)) {
          return [];
        }
        return this.availableTca[this.global.activeField.name][type].filter(function (item) {
          return (!mask.currentFieldKeys.includes(item.field) && !mask.deletedFieldKeys.includes(item.field))
            || mask.global.activeField.key === item.field;
        });
      },
      getFieldKeys: function (fields) {
        const keys = [];
        fields.forEach(function (item) {
          if (item.name === 'palette') {
            item.fields.forEach(function (item) {
              if (!item.newField) {
                keys.push(item.key);
              }
            });
          }
          if (!item.newField) {
            keys.push(item.key);
          }
        });
        return keys;
      },
      getAvailableTcaKeys: function () {
        const keys = {};
        Object.keys(this.availableTca).forEach(function (key) {
          keys[key] = [];
          mask.availableTca[key].core.forEach(function (item) {
            keys[key].push(item.field);
          });
          mask.availableTca[key].mask.forEach(function (item) {
            keys[key].push(item.field);
          });
        });
        return keys;
      },
      openMultiUsageModal() {
        let template = '';
        this.activeMultiUseElements.forEach(function (item, index) {
          template += `${index + 1}: ${item.label} (${item.key})\n`;
        });
        Modal.confirm(
            'Content elements with same field',
            template,
            Severity.info,
            [
              {
                text: this.language.close,
                btnClass: 'btn-default',
                trigger: function () {
                  Modal.dismiss();
                }
              },
            ]);
      },
      /**
       * Update tick to force rerender of date inputs
       */
      forceRenderer() {
        this.ticks += 1;
      }
    },
    computed: {
      hasErrors: function () {
        return this.hasElementErrors || this.hasFieldErrors;
      },
      hasElementErrors: function () {
        return this.fieldErrors.elementKey || this.fieldErrors.elementLabel || !this.fieldErrors.elementKeyAvailable;
      },
      hasFieldErrors: function () {
        return this.fieldErrors.emptyKeyFields.length > 0
          || this.fieldErrors.emptyTabLabels.length > 0
          || this.fieldErrors.emptyGroupAllowedFields.length > 0
          || this.fieldErrors.emptyRadioItems.length > 0
          || this.fieldErrors.existingFieldKeyFields.length > 0;
      },
      maskBuilderOpen: function () {
        return this.mode === 'edit' || this.mode === 'new';
      },
      isActiveCoreField: function () {
        return this.isCoreField(this.global.activeField);
      },
      isExistingMaskField: function () {
        if (this.isEmptyObject(this.global.activeField)) {
          return false;
        }
        let isExisting = false;
        this.availableMaskTcaForActiveField.forEach(function (item) {
          if (item.field === mask.global.activeField.key) {
            isExisting = true;
          }
        });
        return isExisting;
      },
      fieldTabs: function () {
        if (!this.global.activeField.name) {
          return [];
        }
        return this.tabs[this.global.activeField.name];
      },
      chooseFieldVisible: function () {
        if (this.isEmptyObject(this.global.activeField)) {
          return false;
        }
        if (!this.global.activeField.newField && !this.isActiveCoreField) {
          return false;
        }
        if (['inline', 'palette', 'linebreak', 'tab'].includes(this.global.activeField.name)) {
          return false;
        }
        if (this.global.activeField.parent.name === 'inline') {
          return false;
        }
        return this.availableCoreTcaForActiveField.length > 0 || this.availableMaskTcaForActiveField.length > 0;
      },
      keyFieldVisible: function () {
        return !this.global.sctructuralFields.includes(this.global.activeField.name) && this.maskFieldGeneralTabOpen;
      },
      maskFieldGeneralTabOpen: function () {
        return this.isGeneralTabOpen && !this.isActiveCoreField;
      },
      overrideLabelVisible: function () {
        return this.isGeneralTabOpen && this.isActiveCoreField;
      },
      isGeneralTabOpen: function () {
        return this.global.currentTab === 'general';
      },
      availableCoreTcaForActiveField: function () {
        return this.availableTcaForActiveField('core');
      },
      availableMaskTcaForActiveField: function () {
        return this.availableTcaForActiveField('mask');
      },
      activeFieldHasKeyError: function () {
          return this.fieldErrors.emptyKeyFields.includes(this.global.activeField)
          || this.fieldErrors.existingFieldKeyFields.includes(this.global.activeField);
      },
      activeTabHasEmptyLabel: function () {
        return this.fieldErrors.emptyTabLabels.includes(this.global.activeField);
      },
      currentFieldKeys: function () {
        return this.getFieldKeys(this.fields);
      },
      deletedFieldKeys: function () {
        return this.getFieldKeys(this.global.deletedFields);
      },
      defaultTca: function () {
        if (this.isEmptyObject(this.fieldTypes)) {
          return [];
        }
        const defaults = {};
        this.fieldTypes.forEach(function (item) {
          defaults[item.name] = item.tca;
        });
        return defaults;
      },
      activeMultiUseElements: function () {
        if (this.multiUseElements[this.global.activeField.key]) {
          return this.multiUseElements[this.global.activeField.key]
        }
        return [];
      },
      metaVisible: function () {
        return this.sidebar === 'meta';
      },
      fieldsVisible: function () {
        return this.sidebar === 'fields';
      },
      isTYPO3v11: function () {
        return this.global.typo3Version === 11;
      }
    }
  });
});
