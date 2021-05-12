<template>
  <div class="category-slider d-block d-md-none">
    <div v-if="showCategorySlider || mobile">
      <b-dd
        :text="activeCategory"
        class="mobileCategoryDropdown pt-2 pl-2"
        menu-class="overflow-hidden py-0"
      >
        <li>
          <ul
            class="dropdown-menu d-block scrollable-menu position-relative mt-0 border-0"
          >
            <b-dropdown-item
              v-for="category in categories"
              :key="category.id"
              @click="goToCategory(category.id)"
              >{{ category.category }}</b-dropdown-item
            >
          </ul>
        </li>
      </b-dd>

      <!-- <b-dropdown
        :text="activeCategory"
        class="mobileCategoryDropdown m-2"
      >
        <b-dropdown-item
          v-for="category in categories"
          @click="goToCategory(category.id)"
          >{{ category.category }}</b-dropdown-item
        >
      </b-dropdown> -->

      <div class="pull-right d-flex mr-3">
        <p @click.stop="showSearch = !showSearch" class="pt-3 pull-right">
          <i class="fas fa-search customer-nav-icon pr-2"></i>
        </p>
        <p @click.stop="showFilterArea()" class="pt-3 pull-right">
          <i class="fas fa-filter customer-nav-icon"></i>
        </p>
      </div>

      <!-- <b-modal
        v-model="showSearch"
        v-if="showSearch"
        size="sm"
        no-fade
        hide-header
        hide-footer
      > -->
      <!-- <div v-if="showSearch" style="background-color:#ffffff;height:50px" class="d-flex">
        <div class="d-center mt-3 mb-3">
          <i
            class="fas fa-times-circle clear-meal dark-gray pr-2"
            @click="$parent.search = ''"
          ></i>

          <b-form-textarea
            @input="val => ($parent.search = val)"
            @keyup.enter="val => ($parent.search = val)"
            v-model="$parent.search"
            placeholder="Search"
            class="meal-search center-text"
            style="font-size:16px"
          ></b-form-textarea>
        </div>
        <div class="d-flex d-center">
          <b-btn
            @click="($parent.search = ''), (showSearch = false)"
            class="secondary mr-2"
            >Clear</b-btn
          >
          <b-btn @click="showSearch = false" class="brand-color white-text"
            >Search</b-btn
          >
        </div>
      </div> -->
      <!-- </b-modal> -->

      <!-- <slick
        v-if="categories.length > 4 || categoriesCharacterCount > 30"
        :key="categories.length"
        ref="categorySlider"
        :options="{
          arrows: false,
          centerMode: false,
          slidesToShow: 1,
          variableWidth: true,
          infinite: false
        }"
      >
        <div
          v-for="category in categories"
          :key="category.id"
          @click.prevent="goToCategory(category.id)"
          class="m-2"
        >
          {{ category.category }}
        </div>
      </slick>

      <div v-else class="text-center">
        <span
          v-for="category in categories"
          :key="category.category"
          @click.prevent="goToCategory(category.id)"
          class="d-inline-block m-2"
          >{{ category.category }}</span
        >
      </div> -->
    </div>
    <div
      style="width:100%;background-color:#ffffff"
      v-if="showSearch"
      class="d-flex"
    >
      <i
        class="fas fa-times-circle clear-meal dark-gray pr-2 d-center"
        @click="(showSearch = false), ($parent.search = '')"
      ></i>

      <b-form-textarea
        @input="val => ($parent.search = val)"
        @keyup.enter="val => ($parent.search = val)"
        v-model="$parent.search"
        placeholder="Search"
        class="meal-search center-text d-center"
        style="font-size:16px"
      ></b-form-textarea>
    </div>
  </div>
</template>
<style>
.mobileCategoryDropdown .dropdown-toggle::after {
  display: inline-block !important;
}

.scrollable-menu {
  height: auto;
  max-height: 30vh;
  overflow-y: auto;
  overflow-x: hidden;
}
</style>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  data() {
    return {
      categories: [],
      activeCategory: null,
      showDropdown: false,
      showSearch: false
    };
    /*return {
      showCategorySlider: false
    };*/
  },
  mounted() {
    /*setTimeout(() => {
      this.showCategorySlider = true;
    }, 6000);*/
  },
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
      isLazy: "isLazy",
      mealMixItems: "mealMixItems"
    }),
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    categoriesCharacterCount() {
      return this.categories.reduce((acc, category) => {
        return acc + category.category.length;
      }, 0);
    },
    showCategorySlider() {
      let { finalCategories, isRunningLazy } = this.mealMixItems;

      this.categories = [];
      if (finalCategories && finalCategories.length > 0) {
        finalCategories.forEach((cat, index) => {
          if (this.isCategoryVisible(cat) && cat.visible) {
            this.categories.push(cat);
          }
        });
      }

      return true;
    },
    categoriesOld() {
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
      let activeCat = this.categories.find(cat => {
        return cat.id === categoryId;
      });
      if (activeCat) {
        this.activeCategory = activeCat.category;
      }
      if (this.$refs.categorySlider) {
        const index = _.findIndex(this.categories, { id: categoryId });
        this.$refs.categorySlider.goTo(index);
      }
    },
    goToCategory(category) {
      if ($("#xs").is(":visible") || $("#sm").is(":visible")) {
        const top = $(`#${category}`).offset().top;
        $(document).scrollTop(top - 125);
      } else {
        $(".main-menu-area").scrollTop(0);
        const top = $(`#${category}`).position().top;
        $(".main-menu-area").scrollTop(top);
      }
    },
    showFilterArea() {
      this.$eventBus.$emit("showFilterArea");
    }
  }
};
</script>
