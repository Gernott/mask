import Vue from 'vue';
import draggable from 'vuedraggable';
import field from './field.js';

export default Vue.component(
          'field-group',
          {
            components: {
              draggable,
              field
            },
            props: {
              group: String,
              label: String,
              fieldTypes: Array,
              addField: Function,
              onMove: Function,
              handleClone: Function,
              typo3Version: Number,
              optionalExtensionStatus: Object,
            },
            template: `
                <div class="mask-field-group">
                    <span class="mask-field-group__label">{{label}}</span>
                    <ul class="mask-field-group__list">
                        <draggable
                                :list="fieldTypes"
                                :group="{name: 'fieldTypes', pull: 'clone', 'put': false}"
                                :sort="false"
                                :clone="handleClone"
                                :move="onMove"
                        >
                            <field
                                v-if="type.group == group"
                                v-for="type in fieldTypes"
                                :key="type.name"
                                :type="type"
                                :add-field="addField"
                                :typo3-version="typo3Version"
                                :optional-extension-status="optionalExtensionStatus"
                            ></field>
                        </draggable>
                    </ul>
                </div>
        `
          }
);
