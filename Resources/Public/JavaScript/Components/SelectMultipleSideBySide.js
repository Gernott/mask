define([
    'TYPO3/CMS/Mask/Contrib/vue',
    'TYPO3/CMS/Mask/Contrib/vuedraggable',
  ],
  function (Vue, draggable) {
    return Vue.component(
      'selectMultipleSideBySide',
      {
        data() {
          return {
            search: '',
          }
        },
        props: {
          items: Array,
          value: Array,
          language: Object,
          version: Number,
        },
        components: {
          draggable,
        },
        computed: {
          availableItems() {
            const availableItems = [];
            for (const value of Object.entries(this.items)) {
              if (
                !this.value.includes(value[0])
                && (
                  this.search === ''
                  || value[0].toLowerCase().includes(this.search.toLowerCase())
                  || this.items[value[0]].toLowerCase().includes(this.search.toLowerCase())
                )
              ) {
                availableItems.push(value[0]);
              }
            }
            return availableItems;
          }
        },
        methods: {
          add(value) {
            this.value.push(value);
          },
          remove(value) {
            this.value.splice(this.value.indexOf(value), 1);
          }
        },
        template: `
<div class="form-wizards-wrap">
    <div class="form-wizards-element">
        <div class="form-multigroup-wrap t3js-formengine-field-group">
            <div class="form-multigroup-item form-multigroup-element">
                <label>
                    {{ language.selectedItems }}
                </label>
                <div class="form-wizards-wrap form-wizards-aside">
                    <div class="form-wizards-element">
                        <draggable
                            class="list-group mask-multiple-side-by-side-list"
                            :list="value"
                            group="items"
                            ghost-class="active"
                        >
                            <div @click="remove(key)" class="list-group-item list-group-item-action" v-for="key in value" :value="key">{{ items[key] }} <span v-if="key !== ''">[{{ key }}]</span></div>
                        </draggable>
                    </div>
                </div>
            </div>
            <div class="form-multigroup-item form-multigroup-element">
                <label>
                    {{ language.availableItems }}
                </label>
                <div class="form-wizards-wrap form-wizards-aside">
                    <div class="form-wizards-element">
                        <div class="form-multigroup-item-wizard">
                            <span class="input-group input-group-sm">
                            <span class="input-group-text" :class="{'input-group-addon': version === 10}">
                                <span class="fa fa-filter"></span>
                            </span>
                            <input v-model="search" class="t3js-formengine-multiselect-filter-textfield form-control" value="">
                            </span>
                        </div>
                        <draggable
                            class="list-group mask-multiple-side-by-side-list"
                            :list="availableItems"
                            group="items"
                            ghost-class="active"
                            :sort="false"
                        >
                            <div @click="add(key)" class="list-group-item list-group-item-action" v-for="key in availableItems" :value="key">{{ items[key] }} <span v-if="key !== ''">[{{ key }}]</span></div>
                        </draggable>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        `
      }
    )
  }
);
