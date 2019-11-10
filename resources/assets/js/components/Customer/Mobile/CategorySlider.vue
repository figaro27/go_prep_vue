<template>
  <div class="category-slider d-block d-md-none">
    <slick
      v-if="categories.length > 4"
      ref="categorySlider"
      :options="{
        arrows: false,
        centerMode: true,
        variableWidth: true,
        infinite: false
      }"
    >
      <div
        v-for="category in categories"
        :key="category"
        @click.prevent="goToCategory(slugify(category))"
        class="m-2"
      >
        {{ category }}
      </div>
    </slick>

    <div v-else class="text-center">
      <span
        v-for="category in categories"
        :key="category"
        @click.prevent="goToCategory(slugify(category))"
        class="d-inline-block m-2"
        >{{ category }}</span
      >
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  watch: {
    categories(val, oldVal) {
      const ref = this.$refs.categorySlider;
      if (!ref) {
        return;
      }

      const currIndex = ref.currentSlide();

      ref.destroy();
      this.$nextTick(() => {
        ref.create();
        ref.goTo(currIndex, true);
      });
    }
  },
  computed: {
    ...mapGetters({
      _categories: "viewedStoreCategories",
      store: "viewedStore",
      storeSettings: "viewedStoreSetting"
    }),
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (
            category &&
            !_.includes(grouped, category.category) &&
            this.isCategoryVisible(category)
          ) {
            grouped.push(category.category);
          }
        });
      });

      let categories = _.orderBy(grouped, cat => {
        return cat in sorting ? sorting[cat] : 9999;
      });

      if (
        this.storeSettings &&
        this.storeSettings.meal_packages &&
        this.mealPackages.length
      ) {
        categories.push("Packages");
      }

      return categories;
    },
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
    }
  },
  methods: {
    goTo(index) {
      this.$refs.categorySlider.goTo(index);
    },
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
