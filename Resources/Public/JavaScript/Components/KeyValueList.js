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
        methods: {
          add() {
            this.value.push({key: '', value: ''});
          },
          deleteItem(index) {
            this.hideTooltip('delete' + index);
            this.value.splice(index, 1);
          },
          hideTooltip(id) {
            if (this.global.typo3Version > 10) {
              Tooltip.hide(this.$refs[id][0]);
            } else {
              Tooltip.hide($(this.$refs[id][0]));
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
                            <td><input v-model="item.key" class="form-control"></td>
                            <td><input v-model="item.value" class="form-control"></td>
                            <td class="text-center"><a @click.prevent="deleteItem(index)" href="#" class="btn btn-default" data-bs-toggle="tooltip" title="Delete" :ref="'delete' + index"><span v-html="icons.delete"></span></a></td>
                        </tr>
                        <tr>
                            <td class="text-center"><a @mousedown="hideTooltip('add')" @click.prevent="add" href="#" class="btn btn-default" data-bs-toggle="tooltip" title="Add" :ref="'add'"><span v-html="icons.add"></span></a></td>
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
