<template>
  <div class="category-slider d-block d-md-none">
    <div class="text-center">
      <slick
        v-if="categories.length > 4"
        ref="categorySlider"
        :options="{
          arrows: false,
          centerMode: false,
          slidesToShow: 0,
          variableWidth: true,
          infinite: false
        }"
      >
        <div
          v-for="category in categories"
          :key="category.id"
          @click.prevent="goToCategory(slugify(category.category))"
          class="m-2"
        >
          {{ category.category }}
        </div>
      </slick>

      <div v-else class="text-center">
        <span
          v-for="category in categories"
          :key="category.category"
          @click.prevent="goToCategory(slugify(category.category))"
          class="d-inline-block m-2"
          >{{ category.category }}</span
        >
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  watch: {
    /*categories(val, oldVal) {
      const ref = this.$refs.categorySlider;
      if (!ref || val.length === oldVal.length) {
        return;
      }

      const currIndex = ref.currentSlide();

      ref.destroy();
      this.$nextTick(async () => {
        await ref.create();
        ref.goTo(0, true);
      });
    }*/
  },
  computed: {
    ...mapGetters({
      _categories: "viewedStoreCategories",
      store: "viewedStore",
      storeSettings: "viewedStoreSetting",
      isLazy: "isLazy"
    }),
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.id] = cat.order; //cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (
            category &&
            !_.find(grouped, { id: category.id }) &&
            this.isCategoryVisible(category)
          ) {
            grouped.push(category);
          }
        });
      });

      let categories = _.orderBy(grouped, cat => {
        return cat.id in sorting ? sorting[cat.id] : 9999;
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
    goTo(categoryId) {
      if (this.$refs.categorySlider) {
        const index = _.findIndex(this.categories, { id: categoryId });
        this.$refs.categorySlider.goTo(index);
      }
    },
    goToCategory(category) {
      if ($("#xs").is(":visible") || $("#sm").is(":visible")) {
        const top = $(`#${category}`).offset().top;
        $(document).scrollTop(top - 110);
      } else {
        $(".main-menu-area").scrollTop(0);
        const top = $(`#${category}`).position().top;
        $(".main-menu-area").scrollTop(top);
      }
    }
  }
};
</script>
