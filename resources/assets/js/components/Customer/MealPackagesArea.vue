<template>
  <div>
    <meal-package-components-modal
      ref="packageComponentModal"
      :key="total"
    ></meal-package-components-modal>

    <div
      v-if="mealPackages.length && $parent.showMealPackagesArea"
      id="Packages"
      class="main-customer-container customer-menu-container left-right-box-shadow"
    >
      <h2 class="text-center mb-3 dbl-underline">Packages</h2>

      <div class="row">
        <div
          class="item col-sm-6 col-lg-4 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border"
          v-for="mealPkg in mealPackages"
          :key="mealPkg.id"
        >
          <!--<thumbnail
            v-if="mealPkg.image != null && mealPkg.image.url_medium"
            :src="mealPkg.image.url_medium"
            class="menu-item-img"
            width="100%"
            @click="$parent.showMealPackageModal(mealPkg)"
            style="background-color:#ffffff"
          ></thumbnail> !-->

          <thumbnail
            v-if="mealPkg.image != null && mealPkg.image.url_medium"
            :src="mealPkg.image.url_medium"
            class="menu-item-img"
            width="100%"
            style="background-color:#ffffff"
            @click="clickThumbnail(mealPkg)"
          ></thumbnail>

          <div
            class="d-flex justify-content-between align-items-center mb-2 mt-1"
          >
            <b-btn @click="minusOne(mealPkg, true)" class="plus-minus gray">
              <i>-</i>
            </b-btn>
            <b-form-input
              type="text"
              name
              id
              class="quantity"
              :value="quantity(mealPkg, true)"
              readonly
            ></b-form-input>
            <b-btn
              v-if="mealPkg.sizes.length === 0"
              @click="addMealPackage(mealPkg, true)"
              class="plus-minus menu-bag-btn"
            >
              <i>+</i>
            </b-btn>
            <b-dropdown
              v-else
              toggle-class="menu-bag-btn"
              :ref="'dropdown_' + mealPkg.id"
            >
              <span slot="button-content" :id="'dropdown_' + mealPkg.id"
                >+</span
              >
              <b-dropdown-item @click="addMealPackage(mealPkg, true)">
                {{ mealPkg.default_size_title }} -
                {{ format.money(mealPkg.price, storeSettings.currency) }}
              </b-dropdown-item>
              <b-dropdown-item
                v-for="size in mealPkg.sizes"
                :key="size.id"
                @click="addMealPackage(mealPkg, true, size)"
              >
                {{ size.title }} -
                {{ format.money(size.price, storeSettings.currency) }}
              </b-dropdown-item>
            </b-dropdown>
          </div>
          <p class="center-text strong featured">
            {{ mealPkg.title }}
          </p>
          <p class="center-text featured">
            {{ format.money(mealPkg.price, storeSettings.currency) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MenuBag from "../../mixins/menuBag";
import { mapGetters } from "vuex";
import MealPackageComponentsModal from "../../components/Modals/MealPackageComponentsModal";

export default {
  components: {
    MealPackageComponentsModal
  },
  props: {
    mealPackages: ""
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      storeSettings: "viewedStoreSetting",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    })
  },
  methods: {
    clickThumbnail(mealPackage) {
      if (mealPackage.sizes.length === 0) {
        this.addMealPackage(mealPackage, true);
      } else {
        //let bdropdown = this.$refs["dropdown_" + mealPackage.id]
        $("#dropdown_" + mealPackage.id).click();
      }
    },
    addMealPackage(mealPackage, condition = false, size) {
      this.addOne(mealPackage, condition, size);
      this.$parent.mealPackageModal = false;
      if (this.$parent.showBagClass.includes("hidden-right")) {
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass += " area-scroll";
      } else if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass -= " area-scroll";
      }
    }
  }
};
</script>
