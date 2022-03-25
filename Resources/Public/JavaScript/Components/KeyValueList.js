define([
    'TYPO3/CMS/Mask/Contrib/vue',
    'TYPO3/CMS/Backend/Tooltip',
    'jquery'
  ],
  function (Vue, Tooltip, $) {
    return Vue.component(
      'keyValueList',
      {
        props: {
          global: Object,
          icons: Object,
          tcaKey: String,
          value: Array,
          tcaFields: Object,
        },
        mounted() {
          this.initializeTooltip();
        },
        updated() {
          this.initializeTooltip();
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
            this.hideTooltip('delete' + this.tcaKey + index);
            this.value.splice(index, 1);
          },
          hideTooltip(id) {
            let ref;
            if (typeof this.$refs[id][0] !== 'undefined') {
              ref = this.$refs[id][0];
            } else {
              ref = this.$refs[id];
            }
            if (this.global.typo3Version > 10) {
              Tooltip.hide(ref);
            } else {
              Tooltip.hide($(ref));
            }
          },
          initializeTooltip() {
            Tooltip.initialize(`.key-value-table [data-bs-toggle="tooltip"]`, {
              delay: {
                'show': 50,
                'hide': 100
              },
              trigger: 'hover',
              container: 'body'
            });
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
                <table class="table table-bordered key-value-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ tcaFields[tcaKey].keyValueLabels.key }}</th>
                            <th>{{ tcaFields[tcaKey].keyValueLabels.value }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in value">
                            <td></td>
                            <td>
                                <select v-if="keyHasSelectItems" v-model="item.key" class="form-control form-select">
                                    <option v-for="option in getKeySelectItems()" :value="option.value">{{ option.label }} <span v-if="option.value !== ''">[{{ option.value }}]</span></option>
                                </select>
                                <input v-else v-model="item.key" class="form-control">
                            </td>
                            <td>
                                <select v-if="valueHasSelectItems" v-model="item.value" class="form-control form-select">
                                    <option v-for="option in getValueSelectItems()" :value="option.value">{{ option.label }} <span v-if="option.value !== ''">[{{ option.value }}]</span></option>
                                </select>
                                <input v-else v-model="item.value" class="form-control">
                            </td>
                            <td class="text-center"><a @click.prevent="deleteItem(index)" href="#" class="btn btn-default" data-bs-toggle="tooltip" title="Delete" :ref="'delete' + tcaKey + index"><span v-html="icons.delete"></span></a></td>
                        </tr>
                        <tr v-if="!maxItemsReached">
                            <td class="text-center"><a @mousedown="hideTooltip('add' + tcaKey)" @click.prevent="add" href="#" class="btn btn-default" data-bs-toggle="tooltip" title="Add" :ref="'add' + tcaKey"><span v-html="icons.add"></span></a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
              </div>
        `
      }
    )
  }
);
