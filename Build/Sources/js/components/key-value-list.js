import Vue from 'vue';
import draggable from 'vuedraggable';

export default Vue.component(
      'keyValueList',
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
          keyHasSelectItems() {
            return typeof this.tcaFields[this.tcaKey]['keyValueSelectItems'] !== 'undefined'
              && typeof this.tcaFields[this.tcaKey]['keyValueSelectItems']['key'] !== 'undefined';
          },
          valueHasSelectItems() {
            return typeof this.tcaFields[this.tcaKey]['keyValueSelectItems'] !== 'undefined'
              && typeof this.tcaFields[this.tcaKey]['keyValueSelectItems']['value'] !== 'undefined';
          },
          maxItemsReached() {
            if (typeof this.tcaFields[this.tcaKey]['maxItems'] === 'undefined') {
              return false;
            }
            return this.value.length === this.tcaFields[this.tcaKey]['maxItems'];
          }
        },
        methods: {
          add() {
            this.value.push({key: '', value: ''});
          },
          deleteItem(index) {
            this.value.splice(index, 1);
          },
          getKeySelectItems() {
            return this.tcaFields[this.tcaKey]['keyValueSelectItems']['key'];
          },
          getValueSelectItems() {
            return this.tcaFields[this.tcaKey]['keyValueSelectItems']['value'];
          }
        },
        template: `
              <div class="form-control-wrap">
                <table class="table table-bordered item-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ tcaFields[tcaKey].keyValueLabels.key }}</th>
                            <th>{{ tcaFields[tcaKey].keyValueLabels.value }}</th>
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
                            <td>
                                <select v-if="keyHasSelectItems" v-model="item.key" class="form-control form-select-sm form-select">
                                    <option v-for="option in getKeySelectItems()" :value="option.value">{{ option.label }} <span v-if="option.value !== ''">[{{ option.value }}]</span></option>
                                </select>
                                <input v-else v-model="item.key" class="form-control form-control-sm">
                            </td>
                            <td>
                                <select v-if="valueHasSelectItems" v-model="item.value" class="form-control form-select-sm form-select">
                                    <option v-for="option in getValueSelectItems()" :value="option.value">{{ option.label }} <span v-if="option.value !== ''">[{{ option.value }}]</span></option>
                                </select>
                                <input v-else v-model="item.value" class="form-control form-control-sm">
                            </td>
                            <td class="text-center"><a @click.prevent="deleteItem(index)" href="#" class="btn btn-default btn-sm" :title="language.delete" :ref="'delete' + tcaKey + index"><span v-html="icons.delete"></span></a></td>
                        </tr>
                        <tr v-if="!maxItemsReached">
                            <td class="text-center"><a @click.prevent="add" href="#" class="btn btn-default btn-sm" :title="language.add" :ref="'add' + tcaKey"><span v-html="icons.add"></span></a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </draggable>
                </table>
              </div>
        `
      }
    );
