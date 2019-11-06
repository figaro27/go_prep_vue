<template>
  <b-modal
    v-model="visible"
    title="Menu Categories"
    size="md"
    @ok="$emit('ok')"
    @cancel="$emit('cancel')"
    @hidden="$emit('hidden')"
    @hide="editing = false"
    no-fade
  >
    <b-form-group :state="true">
      <div class="categories">
        <draggable
          v-model="categories"
          @change="onChangeCategories"
          element="ol"
          class="plain"
        >
          <li
            v-for="category in categories"
            :key="`category-${category.id}`"
            class="category mb-3"
          >
            <h5 v-if="!editing || editingId !== category.id" class="d-flex">
              <span class="category-name">{{ category.category }}</span>
              <i
                v-if="category.id"
                @click="editCategory(category)"
                class="fa fa-edit text-warning"
              ></i>
              <i
                v-if="category.id"
                @click="deleteCategory(category.id)"
                class="fa fa-minus-circle text-danger"
              ></i>
            </h5>
            <div v-if="editing && editingId === category.id">
              <div class="d-flex">
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
                    >Disable other categories during this period</b-checkbox
                  >
                </b-form-group>
              </div>
            </div>
          </li>
        </draggable>
      </div>

      <b-form class="mt-2" @submit.prevent="onAddCategory" inline>
        <b-input
          v-model="new_category"
          type="text"
          placeholder="New Category..."
        ></b-input>
        <b-button type="submit" variant="primary ml-2">Create</b-button>
      </b-form>
    </b-form-group>
  </b-modal>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  data() {
    return {
      visible: true,
      categories: [],
      editing: null,
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
      const { id, category, date_range_from, date_range_to } = cat;
      this.editing = {
        ...cat,
        range: {
          start: new Date(date_range_from || new Date()),
          end: new Date(date_range_to)
        }
      };

      this.editingId = id;
    },
    updateCategory() {
      let editing = this.editing;
      editing.date_range_from = editing.range.start;
      editing.date_range_to = editing.range.end;

      axios
        .patch("/api/me/categories/" + this.editingId, editing)
        .then(resp => {
          this.editing = null;
          this.showCategoriesModal = false;
          this.fetchCategories();
          this.$toastr.s("Category updated.");
        });
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
