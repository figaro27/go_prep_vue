<template>
  <div>
    <div
      class="main-customer-container customer-menu-container box-shadow"
      v-if="$parent.mealPageView"
    >
      <div class="row">
        <div class="col-md-2">
          <b-btn @click="back" class="menu-bag-btn">BACK</b-btn>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-lg-6 modal-meal-image">
          <thumbnail
            v-if="meal.image.url"
            :src="meal.image.url"
            :aspect="false"
            width="100%"
            @click="$refs.lightbox.showImage(0)"
          ></thumbnail>
          <img v-else :src="meal.featured_image" />

          <LightBox
            ref="lightbox"
            :images="getMealGallery(meal)"
            :showLightBox="false"
          ></LightBox>

          <slick ref="mealGallery" :options="slickOptions">
            <div v-for="(image, i) in getMealGallery(meal)" :key="image.id">
              <div style="image">
                <thumbnail
                  v-if="image.url"
                  :src="image.url"
                  :aspect="true"
                  :lazy="false"
                  :spinner="false"
                  :width="'70px'"
                  @click="$refs.lightbox.showImage(i)"
                ></thumbnail>
              </div>
            </div>
          </slick>

          <div class="title" v-if="meal.macros && storeSettings.showMacros">
            <div class="row">
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Calories</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.calories }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Carbs</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.carbs }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Protein</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.protein }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Fat</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.fat }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <b-form-radio-group
            buttons
            v-model="mealSize"
            :options="sizes"
            class="filters small"
            required
          ></b-form-radio-group>

          <meal-components-modal
            :meal="meal"
            :sizeId="mealSize"
            ref="componentModal"
            :key="total"
          ></meal-components-modal>

          <p v-if="storeSettings.showNutrition" v-html="mealDescription"></p>
          <div class="row mt-3 mb-5" v-if="storeSettings.showNutrition">
            <div class="col-lg-6">
              <h5>Tags</h5>
              <li v-for="tag in meal.tags">{{ tag.tag }}</li>
            </div>
            <div class="col-lg-6">
              <h5>Contains</h5>
              <li v-for="allergy in meal.allergy_titles">
                {{ allergy }}
              </li>
            </div>
          </div>
        </div>
        <div class="col-lg-6" v-if="storeSettings.showNutrition">
          <div
            id="nutritionFacts"
            ref="nutritionFacts"
            class="mt-2 mt-lg-0"
          ></div>

          <div class="row mt-2" v-if="storeSettings.showNutrition">
            <div class="col-lg-5 mt-3">
              <h5>
                {{ format.money(meal.price, storeSettings.currency) }}
              </h5>
            </div>
            <div class="col-lg-5">
              <b-btn @click="addMeal(meal, null)" class="menu-bag-btn"
                >+ ADD</b-btn
              >
            </div>
          </div>
        </div>
        <div class="col-lg-6" v-if="!storeSettings.showNutrition">
          <p v-html="mealDescription"></p>
          <div class="row">
            <div class="col-lg-6">
              <h5>Tags</h5>
              <li v-for="tag in meal.tags">{{ tag.tag }}</li>
            </div>
            <div class="col-lg-6">
              <h5>Contains</h5>
              <li v-for="allergy in meal.allergy_titles">
                {{ allergy }}
              </li>
            </div>
          </div>
          <div class="row mt-3 mb-3" v-if="storeSettings.showIngredients">
            <div class="col-lg-12">
              <h5>Ingredients</h5>
              {{ ingredients }}
            </div>
          </div>
          <div class="row mt-5" v-if="storeSettings.showNutrition">
            <div class="col-lg-8">
              <h5>
                {{ format.money(meal.price, storeSettings.currency) }}
              </h5>
            </div>
            <div class="col-lg-4">
              <b-btn @click="addMeal(meal)" class="menu-bag-btn">+ ADD</b-btn>
            </div>
          </div>
          <div class="row mt-5" v-if="!storeSettings.showNutrition">
            <div class="col-lg-6">
              <h5>
                {{ format.money(meal.price, storeSettings.currency) }}
              </h5>
            </div>
            <div class="col-lg-6">
              <b-btn @click="addMeal(meal, null)" class="menu-bag-btn"
                >+ ADD</b-btn
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import LightBox from "vue-image-lightbox";
import nutritionFacts from "nutrition-label-jquery-plugin";
import keyboardJS from "keyboardjs";
import units from "../../data/units";
import nutrition from "../../data/nutrition";
import format from "../../lib/format";
import "vue-image-lightbox/src/components/style.css";
import { Carousel, Slide } from "vue-carousel";
import MealComponentsModal from "../../components/Modals/MealComponentsModal";

export default {
  data() {
    return {
      mealSize: -1,
      choices: {}
    };
  },
  components: {
    LightBox,
    MealComponentsModal
  },
  props: {
    showMealModal: false,
    meal: {},
    slickOptions: {},
    storeSettings: {},
    mealDescription: "",
    ingredients: "",
    nutritionalFacts: {}
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    viewedMeal() {
      return this.meal;
    },
    sizes() {
      let meal = this.meal;
      let sizes = meal.sizes;
      sizes.unshift({
        full_title: meal.title + " - " + meal.default_size_title || "Regular",
        id: meal.id,
        price: meal.item_price,
        title: meal.default_size_title || "Regular"
      });

      return Object.values(sizes).map(size => {
        return {
          text: size.title,
          price: size.price,
          value: size.id
        };
      });
    }
  },
  updated() {
    this.getNutritionFacts();
  },
  methods: {
    getMealGallery(meal) {
      return meal.gallery.map((item, i) => {
        return {
          id: i,
          url: item.url_original,
          src: item.url_original,
          thumb: item.url_thumb
        };
      });
    },
    getNutritionFacts() {
      let ref = this.$refs.nutritionFacts;
      this.$nextTick(() => {
        this.$nextTick(() => {
          $(ref).nutritionLabel(this.nutritionalFacts);
        });
      });
    },
    addMeal(meal, mealPackage) {
      this.addOne(meal, mealPackage, this.mealSize, this.choices);
      this.mealSize = null;
      this.back();
      if (this.$parent.showBagClass.includes("hidden")) this.$parent.showBag();
    },
    back() {
      this.$parent.showMealsArea = true;
      this.$parent.showMealPackagesArea = true;
      this.$parent.mealPageView = false;
    }
  }
};
</script>
