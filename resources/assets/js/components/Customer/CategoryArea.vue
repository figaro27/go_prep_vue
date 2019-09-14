<template>
  <div>
    <div class="filter-area">
      <b-button
        @click="$parent.viewFilterModal = true"
        class="brand-color white-text"
      >
        <i class="fa fa-filter"></i>
        <span class="d-none d-sm-inline">&nbsp;Filters</span>
      </b-button>
      <b-button @click="$parent(clearFilters)" class="gray white-text">
        <i class="fa fa-eraser"></i>
        <span class="d-none d-sm-inline">&nbsp;Clear Filters</span>
      </b-button>
    </div>

    <ul class="d-none d-sm-block">
      <li
        v-for="category in categories"
        :key="category"
        @click.prevent="goToCategory(slugify(category))"
        class="d-inline ml-sm-4"
      >
        {{ category }}
      </li>
    </ul>

    <div>
      <b-btn class="gray white-text pull-right" @click="clearAll">
        <i class="fa fa-eraser"></i>&nbsp;Clear Bag
      </b-btn>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      _categories: "viewedStoreCategories"
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
    },
    test() {
      alert('test"');
    }
  }
};
</script>
