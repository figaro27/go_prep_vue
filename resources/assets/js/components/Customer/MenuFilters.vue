<template>
  <div>
    <div class="filter-header">
      <h3 class="white-text p-2 ml-3">
        Filters
        <i
          class="fa fa-angle-left mr-3 float-right"
          @click="$parent.showFilterArea()"
        ></i>
      </h3>
    </div>
    <div>
      <b-form-textarea
        v-model="$parent.search"
        placeholder="Search"
        class="meal-search"
      ></b-form-textarea>
      <ul>
        <li
          v-for="category in categories"
          :key="category"
          @click.prevent="goToCategory(slugify(category))"
          class="mt-4"
        >
          {{ category }}
        </li>
      </ul>

      <div class="row">
        <div class="col-md-6">
          <p>Hide Meals That Contain</p>
          <div
            v-for="allergy in allergies"
            :key="`allergy-${allergy.id}`"
            class="filters small"
          >
            <b-button
              :pressed="$parent.active[allergy.id]"
              @click="$parent.filterByAllergy(allergy.id)"
              >{{ allergy.title }}</b-button
            >
          </div>
        </div>
        <div class="col-md-6">
          <p>Show Meals That Are</p>
          <div v-for="tag in tags" :key="`tag-${tag}`" class="filters small">
            <b-button
              :pressed="$parent.active[tag]"
              @click="$emit('filterByTag', tag)"
            >
              {{ tag }}
            </b-button>
          </div>
        </div>
      </div>

      <b-button
        @click="$emit('clearFilters')"
        class="center mt-4 brand-color white-text"
        >Clear All</b-button
      >
    </div>

    <v-style>
      .filter-header{ height:70px !important; background-color:
      {{ store.settings.color }}; margin-bottom: 15px; padding-top: 10px }
    </v-style>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      _categories: "viewedStoreCategories",
      allergies: "allergies"
    }),
    mealPackages() {
      return _.map(
        _.filter(this.store.packages, mealPackage => {
          return mealPackage.active;
        }) || [],
        mealPackage => {
          mealPackage.meal_package = true;
          return mealPackage;
        }
      );
    },
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (category && !_.includes(grouped, category.category)) {
            grouped.push(category.category);
          }
        });
      });

      let categories = _.orderBy(grouped, cat => {
        return cat in sorting ? sorting[cat] : 9999;
      });

      if (this.store.settings.meal_packages && this.mealPackages.length) {
        categories.push("Packages");
      }

      return categories;
    },
    tags() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.tags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      });
      return grouped;
    }
  },
  methods: {
    goToCategory(category) {
      if ($("#xs").is(":visible") || $("#sm").is(":visible")) {
        const top = $(`#${category}`).offset().top;
        $(document).scrollTop(top - 90);
      } else {
        $(".main-menu-area").scrollTop(0);
        const top = $(`#${category}`).position().top;
        $(".main-menu-area").scrollTop(top);
      }
    }
  }
};
</script>
