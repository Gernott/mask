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
  'TYPO3/CMS/Backend/ActionButton/DeferredAction',
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
  DeferredAction,
) {
  if (!document.getElementById('mask')) {
    return;
  }

  new Vue({
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
        searchString: '',
        groups: [],
        elements: [],
        element: {},
        backendLayouts: [],
        fieldTypes: [],
        tcaFields: {},
        onlineMedia: [],
        linkHandlerList: [],
        tabs: {},
        fields: [],
        language: [],
        icons: {},
        faIcons: {},
        availableTca: {},
        multiUseElements: {},
        optionalExtensionStatus: {},
        migrationsDone: false,
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
          nonShareableFields: ['inline', 'content', 'palette', 'linebreak', 'tab'],
          maskPrefix: 'tx_mask_',
          deletedFields: [],
        },
        loaded: false,
        missingFilesOrFolders: {
          missing: false,
          missingFolders: {},
          missingTemplates: {},
        },
        setupConfiguration: {
          extension: '',
          loader: 'json',
          error: '',
        },
        saving: false,
        ticks: 0,
      }
    },
    mounted: function () {
      this.init();
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
        if (this.mode !== 'new') {
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
            async response => {
              const result = await response.resolve();
              this.fieldErrors.elementKeyAvailable = result.isAvailable;
            }
          );
      },
    },
    methods: {
      init() {
        const setupCompletePromise = new AjaxRequest(TYPO3.settings.ajaxUrls.mask_setup_complete).get()
          .then(
            async response => {
              return await response.resolve();
            }
          );

        Promise.resolve(setupCompletePromise)
          .then(
            setupCompleteResult => {
              const promises = [];

              // fetch mask and typo3 version
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_versions)).get()
                .then(
                  async response => {
                    const versions = await response.resolve();
                    this.version = versions.mask;
                    this.global.typo3Version = versions.typo3;
                  }
                ));

              // Fetch language
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_language)).get()
                .then(
                  async response => {
                    this.language = await response.resolve();
                  }
                ));

              // Return early, if setup is incomplete.
              if (!setupCompleteResult.setupComplete) {
                this.mode = 'setup';
                if (setupCompleteResult.loader !== '') {
                  this.setupConfiguration.loader = setupCompleteResult.loader;
                }
                Promise.all(promises).then(() => {
                  this.loaded = true;
                });
                return;
              } else {
                this.mode = 'list';
              }

              // Fetch tcaFields for existing core and mask fields
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_tca_fields)).get()
                .then(
                  async response => {
                    this.tcaFields = await response.resolve();
                  }
                ));

              // Fetch online media
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_online_media)).get()
                .then(
                  async response => {
                    this.onlineMedia = await response.resolve();
                  }
                ));

              // Fetch link handler
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_link_handler)).get()
                .then(
                  async response => {
                    this.linkHandlerList = await response.resolve();
                  }
                ));

              // fetch tab declarations
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_tabs)).get()
                .then(
                  async response => {
                    this.tabs = await response.resolve();
                  }
                ));

              // fetch richtext configuration
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_richtext_configuration)).get()
                .then(
                  async response => {
                    this.global.richtextConfiguration = await response.resolve();
                  }
                ));

              // fetch CTypes
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_ctypes)).get()
                .then(
                  async response => {
                    const result = await response.resolve();
                    this.global.ctypes = result.ctypes;
                  }
                ));

              // fetch field groups
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_field_groups)).get()
                .then(
                  async response => {
                    const result = await response.resolve();
                    this.groups = result.groups;
                  }
                ));

              // fetch elements
              promises.push(this.loadElements());

              // fetch tables
              promises.push(this.loadTables());

              // fetch backend layouts
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_backend_layouts)).get()
                .then(
                  async response => {
                    const backendLayouts = await response.resolve();
                    this.backendLayouts = backendLayouts['backendLayouts'];
                  }
                ));

              // fetch fontawesome icons
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_icons)).get()
                .then(
                  async response => {
                    this.faIcons = await response.resolve();
                  }
                ));

              // fetch possible missing files or folders
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_missing)).get()
                .then(
                  async response => {
                    this.missingFilesOrFolders = await response.resolve();
                  }
                ));

              // fetch optional extension status
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_optional_extension_status)).get()
                .then(
                  async response => {
                    this.optionalExtensionStatus = await response.resolve();
                  }
                ));

              // fetch migration status
              promises.push((new AjaxRequest(TYPO3.settings.ajaxUrls.mask_migrations_done)).get()
                .then(
                  async response => {
                    const migrationsDone = await response.resolve();
                    this.migrationsDone = migrationsDone.migrationsDone;
                  }
                ));

              promises.push(Icons.getIcon('actions-edit-delete', Icons.sizes.small).then(icon => {
                this.icons.delete = icon;
              }));
              promises.push(Icons.getIcon('actions-move-move', Icons.sizes.small).then(icon => {
                this.icons.move = icon;
              }));
              promises.push(Icons.getIcon('actions-add', Icons.sizes.small).then(icon => {
                this.icons.add = icon;
              }));
              promises.push(Icons.getIcon('actions-edit-pick-date', Icons.sizes.small).then(icon => {
                this.icons.date = icon;
              }));
              promises.push(Icons.getIcon('actions-open', Icons.sizes.small).then(icon => {
                this.icons.edit = icon;
              }));
              promises.push(Icons.getIcon('actions-save', Icons.sizes.small).then(icon => {
                this.icons.save = icon;
              }));
              promises.push(Icons.getIcon('actions-close', Icons.sizes.small).then(icon => {
                this.icons.close = icon;
              }));
              promises.push(Icons.getIcon('spinner-circle-dark', Icons.sizes.small).then(icon => {
                this.icons.spinner = icon;
              }));

              Promise.all(promises).then(() => {
                this.loaded = true;

                if (this.migrationsDone) {
                  Notification.info(
                    this.language.migrationsPerformedTitle,
                    this.language.migrationsPerformedMessage,
                    0,
                    [
                      {
                        label: this.language.updateMaskDefinition,
                        action: new DeferredAction(() => {
                          return new AjaxRequest(TYPO3.settings.ajaxUrls.mask_persist_definition)
                            .get()
                            .then(async response => {
                              const res = await response.resolve();
                              if (res.status === 'ok') {
                                Notification.success(res.title, res.message);
                              }
                              if (res.status === 'error') {
                                Notification.error(res.title, res.message);
                              }
                            });
                        })
                      }
                    ]
                  );
                }
              });

              // Trigger input change on TYPO3 datepicker change event.
              if (this.global.typo3Version === 10) {
                $(document).on('formengine.dp.change', () => {
                  document.querySelectorAll('.t3js-datetimepicker').forEach(input => {
                    input.dispatchEvent((new Event('input')));
                  });
                });
              }
            }
          );
      },
      save: function () {
        this.saving = true;
        this.validate();
        if (!this.hasErrors) {
          this.global.deletedFields = [];
          const payload = {
            element: this.getPostElement(),
            fields: JSON.stringify(this.getPostFields(this.fields)),
            type: this.type,
            isNew: this.mode === 'new' ? 1 : 0
          };
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_save).post(payload)
            .then(
              async response => {
                const res = await response.resolve();
                this.mode = 'edit';
                this.showMessages(res.messages);
                if (res.hasError) {
                  return;
                }
                this.loadElements();
                // load element fields
                new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_element)
                    .withQueryArguments({
                      type: payload.type,
                      key: payload.element.key
                    })
                    .get()
                    .then(
                        async response => {
                          const result = await response.resolve();
                          this.fields = result.fields;
                          this.addParentReferenceToFields({}, this.fields);
                          if (!this.isEmptyObject(this.global.activeField)) {
                            this.findActiveField(this.global.activeField, this.fields);
                          }
                          this.saving = false;
                        }
                    );
              }
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
                trigger: () => {
                  Modal.dismiss();
                  this.getErrorFields().every(errorFields => {
                    if (errorFields.length > 0) {
                      this.global.activeField = errorFields[0];
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
          const postElement = {
            key: this.element.key,
            icon: this.$refs.iconPicker.iconPicker.currentIcon,
            label: this.element.label,
            shortLabel: this.element.shortLabel,
            description: this.element.description,
            color: this.element.color,
            colorOverlay: this.element.colorOverlay,
            hidden: this.element.hidden,
            sorting: this.element.sorting,
            saveAndClose: this.element.saveAndClose,
          };

          if (this.global.typo3Version > 10) {
            postElement.iconOverlay = this.$refs.iconOverlayPicker.iconPicker.currentIcon;
          }
          return postElement;
        } else {
          return {
            key: this.element.key
          }
        }
      },
      getPostFields: function (fields) {
        const postFields = [];
        fields.forEach(item => {
          postFields.push({
            key: item.key,
            label: item.label,
            description: item.description,
            name: item.name,
            tca: Object.assign({}, item.tca),
            fields: this.getPostFields(item.fields),
            sql: item.sql
          });
        });
        return postFields;
      },
      closeEdit: function () {
        this.resetState();
        this.loadElements();
        this.mode = 'list';
      },
      /**
       * This method finds the last active field before saving and sets it again to active.
       * This is necessary, because the fields are loaded freshly after saving and the reference is gone.
       * @param activeField
       * @param fields
       */
      findActiveField: function (activeField, fields) {
        let found = false;
        fields.forEach(field => {
            if (field.key === activeField.key && (this.isEmptyObject(activeField.parent) && this.isEmptyObject(field.parent) || activeField.parent.key === field.parent.key)) {
              this.global.activeField = field;
              found = true;
            }
            if (!found) {
              this.findActiveField(activeField, field.fields);
            }
        });
      },
      loadElements: function () {
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_elements)).get()
            .then(
                async response => {
                  const result = await response.resolve();
                  this.elements = result.elements;
                }
            );
      },
      loadField: function () {
        if (this.canHaveMultiUsage(this.global.activeField)) {
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_field)
            .withQueryArguments({key: this.global.activeField.key, type: this.type})
            .get()
            .then(
              async response => {
                const result = await response.resolve();
                this.global.activeField.tca = result.field.tca;
                this.global.activeField.label = result.field.label;
                this.global.activeField.description = result.field.description;
                this.global.activeField.sql = result.field.sql;
              }
            );
          this.loadMultiUse();
        }
      },
      loadMultiUse: function () {
        // Check if field can have multi usage.
        if (!this.canHaveMultiUsage(this.global.activeField)) {
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
                async response => {
                  const result = await response.resolve();
                  // We need to use $set here for reactivity to work, as keys are added dynamically.
                  this.$set(this.multiUseElements, this.global.activeField.key, result.multiUseElements);
                }
            );
      },
      loadTables: function () {
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_tables)).get()
          .then(
            async response => {
              const result = await response.resolve();
              this.global.foreignTables = result.foreignTables;
            }
          );
      },
      validateKey: function (field) {
        if (this.isEmptyObject(this.global.activeField)) {
          return false;
        }

        // Force mask prefix if not a core field
        if (!this.isActiveCoreField && !this.hasMaskPrefix(field.key)) {
          field.key = this.global.maskPrefix;
          return false;
        }

        // Force lowercase and remove special chars
        field.key = this.checkAllowedCharacters(field.key);

        // Skip empty fields (these are validated by empty validator)
        if (field.key === this.global.maskPrefix) {
          return false;
        }

        // Step 1: Check if key is in current fields array
        let fields = this.getFields(field);
        let keyExistsInFields = this.checkIfKeyExistsInFields(fields, this.global.activeField);
        if (keyExistsInFields) {
          this.fieldErrors.existingFieldKeyFields.push(this.global.activeField);
        } else {
          this.removeExistingKeyField(this.global.activeField);
        }

        // Step 2: Check if another field is now valid due to the change
        this.fieldErrors.existingFieldKeyFields.every(errorField => {
          if (errorField !== field && !this.checkIfKeyExistsInFields(this.getFields(errorField), errorField)) {
            this.removeExistingKeyField(errorField);
          }
          return true;
        });

        // Step 3: Check if key is in possible tca array and avoid ajax check if so (only root level).
        if (this.isRoot(field) && this.getAvailableTcaKeys()[field.name].includes(field.key)) {
          return false;
        }

        // If key already exists in current fields.
        if (keyExistsInFields) {
          return false;
        }

        // The field isn't new (must be valid).
        if (!field.newField) {
          return true;
        }

        // TCA check is also not needed for fields inside inline. Exception: type inline and content.
        if (!this.isRoot(field) && field.name !== 'inline' && field.name !== 'content') {
          return true;
        }

        // Check if key already exists in table
        let arguments = {
          table: this.type,
          key: field.key,
          type: field.name,
          elementKey: ''
        };
        if (this.mode === 'edit') {
          arguments.elementKey = this.element.key;
        }
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_check_field_key)
          .withQueryArguments(arguments)
          .get()
          .then(
            async response => {
              const result = await response.resolve();
              if (result.isAvailable) {
                this.removeExistingKeyField(this.global.activeField);
                return true;
              } else {
                this.fieldErrors.existingFieldKeyFields.push(this.global.activeField);
                return false;
              }
            }
          ));
      },
      hasMaskPrefix: function (key) {
        return key.substr(0, this.global.maskPrefix.length) === this.global.maskPrefix;
      },
      isRoot: function (field) {
        return this.isEmptyObject(field.parent) || field.parent.name === 'palette' && this.isEmptyObject(field.parent.parent);
      },
      canHaveMultiUsage(field) {
        return this.canBeShared(field) && this.isRoot(field) && this.isExistingMaskField(field);
      },
      canBeShared: function (field) {
        return !this.global.nonShareableFields.includes(field.name);
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
        fields.every(field => {
          if (field !== checkField) {
            if (checkField.key === field.key) {
              error = true;
            } else {
              if (!error && field.name === 'palette') {
                error = this.checkIfKeyExistsInFields(field.fields, checkField);
              }
            }
            return !error;
          }
          return true;
        });
        return error;
      },
      removeExistingKeyField: function (removedField) {
        this.fieldErrors.existingFieldKeyFields = this.fieldErrors.existingFieldKeyFields.filter(field => {
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

          // Go to next slide by hitting enter key
          elementLabel.on('keyup', (event) => {
            if (event.originalEvent.code === 'Enter') {
              MultiStepWizard.triggerStepButton('next');
            }
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

          // Go to next slide by hitting enter key
          elementKey.on('keyup', (event) => {
            if (event.originalEvent.code === 'Enter') {
              MultiStepWizard.triggerStepButton('next');
            }
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
        }).then(() => {
          MultiStepWizard.show();
          if (this.isTYPO3v11) {
            MultiStepWizard.setup.forceSelection = false;
          }
        });
      },
      openEdit: function (type, element) {
        this.loaded = false;
        this.mode = 'edit';
        this.type = type;
        this.element = element;
        this.searchString = '';
        let requests = [];

        // load element fields
        requests.push(this.loadTca().then(() => {
          new AjaxRequest(TYPO3.settings.ajaxUrls.mask_load_element)
              .withQueryArguments({
                type: type,
                key: element.key
              })
              .get()
              .then(
                  async response => {
                    const result = await response.resolve();
                    this.fields = result.fields;
                    this.updateParents();
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
                async response => {
                  const result = await response.resolve();
                  if (result.multiUseElements.length !== 0) {
                    this.multiUseElements = result.multiUseElements;
                  }
                }
            ));

        Promise.all(requests).then(() => {
          this.loaded = true;
        });
      },
      updateParents: function () {
        this.addParentReferenceToFields({}, this.fields);
      },
      addParentReferenceToFields: function (parent, fields) {
        fields.forEach(field => {
          field.parent = parent;
          this.addParentReferenceToFields(field, field.fields);
        });
      },
      loadTca: function () {
        // Fetch fieldtypes and available tca
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_fieldtypes)).get()
          .then(
            async response => {
              this.fieldTypes = await response.resolve();
              this.fieldTypes.forEach(item => {
                new AjaxRequest(TYPO3.settings.ajaxUrls.mask_existing_tca).withQueryArguments({table: this.type, type: item.name}).get()
                  .then(
                    async response => {
                      this.availableTca[item.name] = await response.resolve();
                    }
                  )
              });
            }
          );
      },
      deleteElement: function (item, purge) {
        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_delete).post({key: item.key, purge: purge})
            .then(
                async response => {
                  const res = await response.resolve();
                  this.showMessages(res);
                  this.loadElements();
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
                trigger: () => {
                  Modal.dismiss();
                  this.deleteElement(item, 0);
                }
              },
              {
                text: this.language.deleteModal.purge,
                btnClass: 'btn-danger',
                trigger: () => {
                  Modal.dismiss();
                  this.deleteElement(item, 1);
                }
              }
            ]);
      },
      showMissingFilesOrFolder() {
        let template = '';
        // isArray means empty array from json_encode.
        if (!Array.isArray(this.missingFilesOrFolders.missingFolders)) {
          template += this.language.missingFolders + ':\n';
          for (const [key, value] of Object.entries(this.missingFilesOrFolders.missingFolders)) {
            template += `${value} (${key})\n`;
          }
        }

        // isArray means empty array from json_encode.
        if (!Array.isArray(this.missingFilesOrFolders.missingTemplates)) {
          if (template !== '') {
            template += '\n';
          }
          template += this.language.missingTemplates + ':\n';
          for (const [key, value] of Object.entries(this.missingFilesOrFolders.missingTemplates)) {
            template += `${value} (${key}) \n`;
          }
        }

        Modal.confirm(
          this.language.createMissingFilesOrFolders,
          template,
          Severity.info,
          [
            {
              text: this.language.close,
              btnClass: 'btn btn-default',
              trigger: () => {
                Modal.dismiss();
              }
            },
            {
              text: this.language.create,
              btnClass: 'btn btn-info',
              trigger: () => {
                Modal.dismiss();
                this.fixMissing();
              }
            }
          ]
        );
      },
      fixMissing(showMessages = true) {
        (new AjaxRequest(TYPO3.settings.ajaxUrls.mask_fix_missing)).get()
          .then(
            async response => {
              const result = await response.resolve();
              if (showMessages) {
                this.showMessages(result.messages);
              }
              new AjaxRequest(TYPO3.settings.ajaxUrls.mask_missing).get()
                .then(
                  async response => {
                    this.missingFilesOrFolders = await response.resolve();
                    this.loadElements();
                  }
                );
            }
          )
      },
      showMessages: function (res) {
        Object.keys(res).forEach(key => {
          const item = res[key];
          if (item.severity === 0) {
            Notification.success(item.title, item.message);
          } else if (item.severity === 1) {
            Notification.warning(item.title, item.message);
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
        fields.every(item => {
          if (item.key === this.global.maskPrefix) {
            this.fieldErrors.emptyKeyFields.push(item);
          }
          if (item.fields.length > 0) {
            this.checkFieldKeyIsEmpty(item.fields);
          }
          return true;
        });
      },
      checkTabLabelIsEmpty: function (fields) {
        fields.every(item => {
          if (item.name === 'tab' && item.label === '') {
            this.fieldErrors.emptyTabLabels.push(item);
          }
          if (item.fields.length > 0) {
            this.checkTabLabelIsEmpty(item.fields);
          }
          return true;
        });
      },
      checkEmptyGroupAllowed: function (fields) {
        fields.every(item => {
          if (this.isCoreField(item)) {
            return true;
          }
          if (item.tca['config.internal_type'] === 'db' && item.tca['config.allowed'] === '') {
            this.fieldErrors.emptyGroupAllowedFields.push(item);
          }
          if (item.fields.length > 0) {
            this.checkEmptyGroupAllowed(item.fields);
          }
          return true;
        });
      },
      checkEmptyRadioItems: function (fields) {
        fields.every(item => {
          if (this.isCoreField(item)) {
            return true;
          }
          if (item.name === 'radio') {
            const items = item.tca['config.items'];
            if (items.length === 0) {
              this.fieldErrors.emptyRadioItems.push(item);
            } else {
              items.every(radioItem => {
                if (!this.isNumeric(radioItem[1])) {
                  this.fieldErrors.emptyRadioItems.push(item);
                  return false;
                }
                return true;
              });
            }
          }
          if (item.fields.length > 0) {
            this.checkEmptyRadioItems(item.fields);
          }
          return true;
        });
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
      onSort: function () {
        this.updateParents();
      },
      validateMove: function (newParentKey, newParentName, draggedField) {
        // Rule #1: Palette and tab fields are not allowed in palette.
        if (newParentName === 'palette' && ['palette', 'tab'].includes(draggedField.name)) {
          return false;
        }

        // Rule #2: Linebreaks are only allowed in palettes.
        if (draggedField.name === 'linebreak' && newParentName !== 'palette') {
          return false;
        }

        // The rule below only applies for existing fields.
        // As it is the last rule, return early true.
        // Exception: Core fields are always treated as existing.
        if (draggedField.newField && !this.isCoreField(draggedField)) {
          return true;
        }

        // Rule #3: Existing fields must never leave their inline parent.
        // Create a Set to store the keys of the fields into which the dragged field can be dragged.
        let allowedFields = new Set();
        let parentField = draggedField.parent;
        // Go up until inline parent is found.
        // Can logically only loop 2-times at max (inline <- palette) or (root <- palette).
        while (true) {
          // If inline field or root is found, add it to the allowed list and traverse
          // all sub-fields to find possible new palette parents.
          if (parentField.name === 'inline') {
            allowedFields = this.getAllowedFields(parentField.key, parentField.fields);
            break;
          }
          // root field has an empty string as key.
          if (typeof parentField.name === 'undefined') {
            allowedFields = this.getAllowedFields('', this.fields);
            break;
          }
          parentField = parentField.parent;
        }

        // Check if the newParent is in the allowed list
        return allowedFields.has(newParentKey);
      },
      getAllowedFields: function (key, fields) {
        const allowedFields = new Set();
        allowedFields.add(key);
        return new Set([...allowedFields, ...this.getPaletteKeysOfField(fields)]);
      },
      getPaletteKeysOfField: function (fields, keys = []) {
        for (let childField of fields) {
          if (childField.name === 'palette') {
            keys.push(childField.key);
          }
        }
        return keys;
      },
      getNewElement: function () {
        return {
          key: '',
          label: '',
          shortLabel: '',
          description: '',
          icon: '',
          color: '#000000',
          iconOverlay: '',
          colorOverlay: '#000000',
          saveAndClose: 0,
        };
      },
      isParentField: function (field) {
        return ['palette', 'inline'].includes(field.name);
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
      // todo merge isCoreField and isExistingMaskField into one method
      isCoreField: function (field) {
        if (this.isEmptyObject(field)) {
          return false;
        }
        let isExisting = false;
        this.availableTca[field.name].core.forEach(item => {
          if (item.field === field.key) {
            isExisting = true;
          }
        });
        return isExisting;
      },
      isExistingMaskField: function (field) {
        if (this.isEmptyObject(this.global.activeField)) {
          return false;
        }
        let isExisting = false;
        this.availableTca[field.name].mask.forEach(function (item) {
          if (item.field === field.key) {
            isExisting = true;
          }
        });
        return isExisting;
      },
      availableTcaForField: function (type, field) {
        if (this.isEmptyObject(this.availableTca) || this.isEmptyObject(field)) {
          return [];
        }
        return this.availableTca[field.name][type].filter(item => {
          return (!this.currentFieldKeys.includes(item.field) && !this.deletedFieldKeys.includes(item.field)) || field.key === item.field;
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
        Object.keys(this.availableTca).forEach(key => {
          keys[key] = [];
          this.availableTca[key].core.forEach(item => {
            keys[key].push(item.field);
          });
          this.availableTca[key].mask.forEach(item => {
            keys[key].push(item.field);
          });
        });
        return keys;
      },
      openMultiUsageModal() {
        let template = '';
        this.activeMultiUseElements.forEach((item, index) => {
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
                trigger: () => {
                  Modal.dismiss();
                }
              },
            ]);
      },
      submitAutoConfiguration() {
        new AjaxRequest(TYPO3.settings.ajaxUrls.mask_setup_autoconfigure).post(this.setupConfiguration)
          .then(
            async response => {
              const resolvedResponse = await response.resolve();
              this.setupConfiguration.error = resolvedResponse.result.error;

              if (this.setupConfiguration.error === '') {
                Notification.success('', 'Successfully auto-configured Mask for ' + this.setupConfiguration.extension + '!');
                this.fixMissing(false);
                this.init();
              }
            }
          );
      },
      /**
       * Update tick to force rerender of date inputs
       */
      forceRenderer() {
        this.ticks += 1;
      },
      keyWithoutMask: function (key) {
        if (key.substr(0, 8) === this.global.maskPrefix) {
          return key.substr(8);
        } else {
          return key;
        }
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
        if (!this.canBeShared(this.global.activeField)) {
          return false;
        }
        if (!this.isRoot(this.global.activeField)) {
          return false;
        }
        return this.availableCoreTcaForActiveField.length > 0 || this.availableMaskTcaForActiveField.length > 0;
      },
      filteredElements() {
        if (this.lowerCaseSearchString === '') {
          return Object.values(this.elements);
        }
        return Object.values(this.elements).filter((element) => {
          return element.label.toLowerCase().includes(this.lowerCaseSearchString)
            || element.shortLabel.toLowerCase().includes(this.lowerCaseSearchString)
            || element.description.toLowerCase().includes(this.lowerCaseSearchString)
            || element.key.includes(this.lowerCaseSearchString);
        });
      },
      lowerCaseSearchString() {
        return this.searchString.toLowerCase();
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
      overrideDescriptionVisible: function () {
        return this.isGeneralTabOpen && this.isActiveCoreField;
      },
      isGeneralTabOpen: function () {
        return this.global.currentTab === 'general';
      },
      availableCoreTcaForActiveField: function () {
        return this.availableTcaForField('core', this.global.activeField);
      },
      availableMaskTcaForActiveField: function () {
        return this.availableTcaForField('mask', this.global.activeField);
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
        this.fieldTypes.forEach(item => {
          defaults[item.name] = item.tca;
        });
        return defaults;
      },
      activeMultiUseElements: function () {
        if (this.isRoot(this.global.activeField) && typeof this.multiUseElements[this.global.activeField.key] !== 'undefined') {
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
