import Vue from 'vue';
import draggable from 'vuedraggable';

export default Vue.component(
      'itemList',
      {
        props: {
          global: Object,
          language: Object,
          icons: Object,
          tcaKey: String,
          value: Array,
          tcaFields: Object,
        },
        components: {
          draggable,
        },
        computed: {
          tcaField() {
            if (typeof this.tcaFields[this.tcaKey]['collision'] === 'undefined') {
              return this.tcaFields[this.tcaKey];
            }
            return this.tcaFields[this.tcaKey][this.global.activeField.name];
          },
          properties() {
            const properties = Object.assign({}, this.tcaField.properties);
            for (const [key, value] of Object.entries(properties)) {
              if (typeof value['renderType'] !== 'undefined' && this.global.activeField.tca['config.renderType'] !== value['renderType']) {
                delete properties[key];
              }
            }
            return properties;
          },
          maxItemsReached() {
            if (typeof this.tcaField.maxItems === 'undefined') {
              return false;
            }
            return this.value.length === this.tcaField.maxItems;
          },
          itemGroups() {
            return this.global.activeField.tca['config.itemGroups'] ?? [];
          },
        },
        methods: {
          add() {
            let newObj = {};
            for (const [key, value] of Object.entries(this.properties)) {
              if (value['type'] === 'checkbox') {
                newObj[key] = 0;
              } else {
                newObj[key] = '';
              }
            }
            this.value.push(newObj);
          },
          deleteItem(index) {
            this.value.splice(index, 1);
          },
        },
        template: `
              <div class="form-control-wrap">
                <table class="table table-bordered table-hover item-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th v-for="property in properties">{{ property.label }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <draggable
                          tag="tbody"
                          :list="value"
                          group="listItems"
                          ghost-class="ghost"
                          handle=".js-draggable"
                          draggable=".js-drag-item"
                        >
                        <tr v-for="(item, index) in value" :class="{'js-drag-item': value.length > 1}">
                            <td class="text-center" :class="{'js-draggable': value.length > 1}" :title="language.drag"><span v-if="value.length > 1" v-html="icons.move"></td>
                            <td v-for="(property, propertyKey) in properties">
                                <input v-if="property.type == 'text'" class="form-control form-control-sm" v-model="item[propertyKey]"/>
                                <div v-if="property.type == 'checkbox'" class="checkbox checkbox-type-toggle form-check form-switch">
                                    <input class="checkbox-input form-check-input" v-model="item[propertyKey]" type="checkbox" true-value="1" false-value="0">
                                </div>
                                <select v-if="property.type == 'group'" v-model="item[propertyKey]" class="form-control form-select-sm form-select">
                                    <option value="">{{ language.noGroup }}</option>
                                    <option v-for="itemGroup in itemGroups" :value="itemGroup.key">{{ itemGroup.key }}</option>
                                </select>
                            </td>
                            <td class="text-center"><a @click.prevent="deleteItem(index)" href="#" class="btn btn-default btn-sm" :title="language.delete" :ref="'delete' + tcaKey + index"><span v-html="icons.delete"></span></a></td>
                        </tr>
                        <tr v-if="!maxItemsReached">
                            <td class="text-center"><a @click.prevent="add" href="#" class="btn btn-default btn-sm" :title="language.add" :ref="'add' + tcaKey"><span v-html="icons.add"></span></a></td>
                            <td v-for="property in properties"></td>
                            <td></td>
                        </tr>
                    </draggable>
                </table>
              </div>
        `
      }
    );
