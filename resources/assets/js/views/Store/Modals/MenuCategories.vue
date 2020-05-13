<template>
  <b-modal
    v-model="visible"
    title="Menu Categories"
    size="lg"
    @ok="$emit('ok')"
    @cancel="$emit('cancel')"
    @hidden="$emit('hidden')"
    @hide="editing = false"
    no-fade
  >
    <div>
      <b-form-group :state="true">
        <div>
          <div class="center-flex">
            <b-form class="mt-2" @submit.prevent="onAddCategory" inline>
              <b-input
                v-model="new_category"
                type="text"
                placeholder="New Category..."
              ></b-input>
              <b-button type="submit" variant="primary ml-2">Create</b-button>
            </b-form>
          </div>
          <div class="center-flex mt-3">
            <p>
              Click and drag to change the order the categories are shown on
              your menu.
            </p>
          </div>
          <draggable
            v-model="categories"
            @change="onChangeCategories"
            element="ul"
            class="plain mt-2"
          >
            <li
              v-for="category in categories"
              :key="`category-${category.id}`"
              class="mb-3"
            >
              <div class="center-flex">
                <h5 v-if="!editing || editingId !== category.id" class="d-flex">
                  <i
                    v-if="category.id"
                    @click="editCategory(category)"
                    class="fa fa-edit text-warning mr-2"
                  ></i>
                  <i
                    v-if="category.id"
                    @click="deleteCategory(category.id)"
                    class="fa fa-minus-circle text-danger mr-2"
                  ></i>
                  <span class="category-name">{{ category.category }}</span>
                </h5>
              </div>
              <div v-if="editing && editingId === category.id">
                <div class="d-flex mr-2">
                  <div class="d-flex mr-2 pt-1">
                    <div class="mr-1">
                      <b-form-checkbox
                        v-model="category.active"
                        @change="setActive(category.active)"
                      ></b-form-checkbox>
                    </div>
                    <div>Active for Customers</div>
                  </div>
                  <div class="d-flex mr-2 pt-1">
                    <div class="mr-1">
                      <b-form-checkbox
                        v-model="category.activeForStore"
                        @change="setActiveForStore(category.activeForStore)"
                      ></b-form-checkbox>
                    </div>
                    <div>Active for You</div>
                  </div>
                  <b-input
                    v-model="editing.category"
                    placeholder="Enter updated category name."
                    class="w-50 mr-2"
                  ></b-input>
                  <b-btn @click.prevent="updateCategory" variant="primary"
                    >Save</b-btn
                  >
                </div>

                <div v-if="storeModules.category_restrictions" class="mt-3">
                  <b-form-group>
                    <b-checkbox v-model="editing.date_range"
                      >Enable category between dates</b-checkbox
                    >
                  </b-form-group>
                  <b-form-group v-if="editing.date_range">
                    <v-date-picker
                      mode="range"
                      v-model="editing.range"
                      is-inline
                    />
                  </b-form-group>
                  <b-form-group v-if="editing.date_range">
                    <b-checkbox v-model="editing.date_range_exclusive"
                      >Disable other categories between dates</b-checkbox
                    >
                  </b-form-group>
                  <b-form-group v-if="editing.date_range_exclusive">
                    <v-date-picker
                      mode="range"
                      v-model="editing.range_exclusive"
                      :min-date="editing.range.start"
                      :max-date="editing.range.end"
                      is-inline
                    />
                  </b-form-group>
                </div>
              </div>
            </li>
          </draggable>
        </div>
      </b-form-group>
    </div>
  </b-modal>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  data() {
    return {
      visible: true,
      categories: [],
      editing: {},
      editingId: null,
      new_category: "",
      newCategoryName: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSettings: "storeSettings",
      storeDetail: "storeDetail",
      meals: "storeMeals",
      mealPackages: "mealPackages",
      getMeal: "storeMeal",
      storeCategories: "storeCategories",
      getCategoryTitle: "storeCategoryTitle",
      getAllergyTitle: "storeAllergyTitle",
      storeModules: "storeModules"
    })
  },
  created() {
    this.fetchCategories();
  },
  methods: {
    ...mapActions({
      refreshCategories: "refreshCategories"
    }),
    async fetchCategories() {
      await this.refreshCategories();
      this.categories = _.chain(this.storeCategories)
        .orderBy("order")
        .toArray()
        .value();
    },
    onAddCategory() {
      if (!this.new_category) {
        return;
      }
      axios
        .post("/api/me/categories", { category: this.new_category })
        .then(response => {
          this.fetchCategories();
          this.new_category = "";
        });
    },
    onChangeCategories(e) {
      if (_.isObject(e.moved)) {
        let newCats = _.toArray({ ...this.categories });

        newCats = _.map(newCats, (cat, i) => {
          cat.order = i;
          return cat;
        });

        axios
          .post("/api/me/categories", { categories: newCats })
          .then(response => {
            this.fetchCategories();
          });
      }
    },
    deleteCategory(id) {
      axios.delete("/api/me/categories/" + id).then(response => {
        this.fetchCategories();
      });
    },
    editCategory(cat) {
      const {
        id,
        category,
        date_range_from,
        date_range_to,
        date_range_exclusive_from,
        date_range_exclusive_to
      } = cat;

      const rangeFrom = new Date(date_range_from || new Date());
      const rangeTo = new Date(date_range_to);

      const rangeExclusiveFrom = date_range_exclusive_from
        ? new Date(date_range_exclusive_from)
        : rangeFrom;
      const rangeExclusiveTo = date_range_exclusive_to
        ? new Date(date_range_to)
        : rangeTo;

      this.editing = {
        ...cat,
        range: {
          start: rangeFrom,
          end: rangeTo
        },
        range_exclusive: {
          start: rangeExclusiveFrom,
          end: rangeExclusiveTo
        }
      };
      this.editingId = id;
    },
    updateCategory() {
      let editing = this.editing;
      editing.date_range_from = editing.range.start;
      editing.date_range_to = editing.range.end;
      editing.date_range_exclusive_from = editing.range_exclusive.start;
      editing.date_range_exclusive_to = editing.range_exclusive.end;

      axios
        .patch("/api/me/categories/" + this.editingId, editing)
        .then(resp => {
          this.showCategoriesModal = false;
          this.fetchCategories();
          this.$toastr.s("Category updated.");
        });
    },
    setActive(value) {
      value = !value;
      this.editing.active = value;
      this.updateCategory();
    },
    setActiveForStore(value) {
      value = !value;
      this.editing.activeForStore = value;
      this.updateCategory();
    }
  }
};
</script>

<style lang="scss" scoped>
.category {
  &:hover {
  }
}
.category-name {
  cursor: n-resize;
  display: inline-block;
  min-width: 200px;
  flex-grow: 1;
}
</style>
