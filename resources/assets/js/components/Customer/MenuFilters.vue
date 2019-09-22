<template>
  <div class="d-none d-md-block">
    <div class="filter-header">
      <h3 class="white-text p-2 ml-3 center-text">
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
      <!-- <ul>
        <li
          v-for="category in categories"
          :key="category"
          @click.prevent="goToCategory(slugify(category))"
          class="mt-4"
        >
          {{ category }}
        </li>
      </ul> -->

      <div class="row">
        <div class="col-md-12">
          <h4 class="center-text mt-4">Allergies</h4>
          <p class="center-text">Hide Items That Contain:</p>
        </div>
        <div class="col-md-6">
          <div
            v-for="(allergy, index) in allergies"
            v-if="index <= 4"
            :key="`allergy-${allergy.id}`"
            class="filters small ml-5"
          >
            <b-button
              :pressed="$parent.active[allergy.id]"
              @click="$parent.filterByAllergy(allergy.id)"
              >{{ allergy.title }}</b-button
            >
          </div>
        </div>
        <div class="col-md-6">
          <div
            v-for="(allergy, index) in allergies"
            v-if="index > 4"
            :key="`allergy-${allergy.id}`"
            class="filters small ml-1"
          >
            <b-button
              :pressed="$parent.active[allergy.id]"
              @click="$parent.filterByAllergy(allergy.id)"
              >{{ allergy.title }}</b-button
            >
          </div>
        </div>
        <div class="col-md-12">
          <h4 class="center-text mt-5">Nutrition</h4>
          <p class="center-text">Show Items That Are:</p>
        </div>
        <div class="col-md-6">
          <div
            v-for="(tag, index) in tags"
            :key="`tag-${tag}`"
            class="filters small ml-5"
            v-if="index <= 3"
          >
            <b-button
              :pressed="$parent.active[tag]"
              @click="$parent.filterByTag(tag)"
            >
              {{ tag }}
            </b-button>
          </div>
        </div>
        <div class="col-md-6">
          <div
            v-for="(tag, index) in tags"
            :key="`tag-${tag}`"
            class="filters small ml-1"
            v-if="index > 3"
          >
            <b-button
              :pressed="$parent.active[tag]"
              @click="$parent.filterByTag(tag)"
            >
              {{ tag }}
            </b-button>
          </div>
        </div>
      </div>

      <b-button
        @click="$parent.clearFilters"
        class="center mt-4 brand-color white-text"
        >Clear All</b-button
      >
    </div>

    <v-style>
      .filter-header{ height:70px !important; background-color:
      {{ store.settings.color }}; margin-bottom: 15px; padding-top: 10px }
      .filters .active { color: #ffffff !important; border-color: #dedede
      !important; background-color: {{ store.settings.color }} !important; }
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
