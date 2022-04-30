define([
    'TYPO3/CMS/Mask/Contrib/vue',
    'TYPO3/CMS/Mask/Contrib/vuedraggable',
    'TYPO3/CMS/Mask/Components/FieldRow',
  ],
  function (Vue, draggable, fieldRow) {
    return Vue.component(
      'nested-draggable',
      {
        props: {
          fields: Array,
          icons: Object,
          global: Object,
          depth: Number,
          index: Number,
          move: Function,
          sort: Function,
          fieldHasError: Function,
          validateKey: Function,
          language: Object,
          loadMultiUse: Function,
          multiUseElements: Object,
          existingFieldKeyFields: Array,
          keyWithoutMask: Function,
          isRoot: Function
        },
        components: {
          draggable,
          fieldRow
        },
        methods: {
          uuid(e) {
            if (e.uid) {
              return e.uid;
            }
            const key = Math.random()
              .toString(16)
              .slice(2);

            this.$set(e, 'uid', key);
            // Auto set key on structural fields
            if ((e.key === this.global.maskPrefix || e.key === '') && this.global.sctructuralFields.includes(e.name)) {
              this.$set(e, 'key', this.global.maskPrefix + key);
            }
            return e.uid;
          },
          onAdd: function (e) {
            if (e.pullMode !== 'clone') {
              return;
            }
            this.global.activeField = this.global.clonedField;
            this.global.currentTab = 'general';
            if (this.depth > 0) {
              this.global.activeField.parent = this.$parent.list[this.index];
            } else {
              this.global.activeField.parent = {};
            }
            this.validateKey(this.global.activeField);
          },
          removeField: function (index) {
            if (this.fields[index - 1]) {
              this.global.activeField = this.fields[index - 1];
            } else if (this.fields[index + 1]) {
              this.global.activeField = this.fields[index + 1];
            }
            this.global.deletedFields.push(this.fields[index]);
            if (this.existingFieldKeyFields.includes(this.fields[index])) {
              let errorIndex = this.existingFieldKeyFields.indexOf(this.fields[index]);
              this.existingFieldKeyFields.splice(errorIndex, 1);
            }
            this.fields.splice(index, 1);
            if (this.fields.length === 0) {
              if (this.depth > 0) {
                this.$emit('set-parent-active', this.index);
              } else {
                this.global.activeField = {};
              }
            }
            // Reset current tab
            this.global.currentTab = 'general';
            this.validateKey(this.global.activeField);
          },
          setParentActive(index) {
            this.global.activeField = this.fields[index];
          },
          isParentField: function (field) {
            return ['inline', 'palette'].includes(field.name);
          }
        },
        template: `
<draggable
    tag="ul"
    class="dragtarget"
    :list="fields"
    group="fieldTypes"
    ghost-class="ghost"
    @add="onAdd"
    :move="move"
    @sort="sort"
  >
  <li v-for="(field, index) in fields" :key="uuid(field)" class="mask-field" :class="[{active: global.activeField == field}, 'mask-field--' + field.name, {'has-error': fieldHasError(field)}]">
    <field-row
        :global="global"
        :fields="fields"
        :field="field"
        :language="language"
        :icons="icons"
        :index="index"
        :load-multi-use="loadMultiUse"
        :multi-use-elements="multiUseElements"
        :field-key="uuid(field)"
        @remove-field="removeField($event)"
        :key-without-mask="keyWithoutMask"
        :is-root="isRoot"
    ></field-row>
    <div class="mask-field__dragarea" v-if="isParentField(field)">
        <nested-draggable
            @set-parent-active="setParentActive($event)"
            :depth="depth + 1"
            :index="index"
            :fields="field.fields"
            :icons="icons"
            :global="global"
            :move="move"
            :sort="sort"
            :field-has-error="fieldHasError"
            :validate-key="validateKey"
            :language="language"
            :load-multi-use="loadMultiUse"
            :multi-use-elements="multiUseElements"
            :existing-field-key-fields="existingFieldKeyFields"
            :key-without-mask="keyWithoutMask"
            :is-root="isRoot"
          />
    </div>
  </li>
</draggable>
        `
      }
    )
  }
);
