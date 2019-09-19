<template>
  <div>
    <b-modal
      ref="mealPackageModal"
      size="lg"
      :title="$parent.mealPackage.title"
      v-model="mealPackageModal"
      v-if="mealPackageModal"
      @shown="$forceUpdate()"
      @hide="$parent.mealPackageModal = false"
      :hide-footer="true"
      no-fade
    >
      <carousel
        ref="carousel"
        :perPage="1"
        @mounted="
          () => {
            hasLoaded = true;
          }
        "
      >
        <slide>
          <div v-if="hasLoaded" class="row">
            <div class="col-lg-6 modal-meal-image">
              <thumbnail
                v-if="mealPackage.image.url"
                :src="mealPackage.image.url"
                :aspect="false"
                width="100%"
                :spinner="false"
                :lazy="false"
              ></thumbnail>
              <img v-else :src="mealPackage.featured_image" />
            </div>
            <div class="col-lg-6">
              <div class="modal-meal-package-description mt-2">
                <p>{{ mealPackage.description }}</p>
              </div>
              <div class="modal-meal-package-meals">
                <h5 class="mt-2">Meals</h5>

                <li v-for="meal in mealPackage.meals" :key="meal.id">
                  {{ meal.title }} x {{ meal.quantity }}
                </li>

                <div class="modal-meal-package-price">
                  <h5 class="mt-3 mb-3">
                    {{
                      format.money(mealPackage.price, storeSettings.currency)
                    }}
                  </h5>
                </div>
                <b-btn
                  v-if="mealPackage.sizes.length === 0"
                  @click="addMealPackage(mealPackage, true)"
                  class="menu-bag-btn"
                  >+ ADD</b-btn
                >
                <b-dropdown v-else toggle-class="menu-bag-btn">
                  <span slot="button-content">+ ADD</span>
                  <b-dropdown-item @click="addMealPackage(mealPackage, true)">
                    {{ mealPackage.default_size_title }} -
                    {{
                      format.money(mealPackage.price, storeSettings.currency)
                    }}
                  </b-dropdown-item>
                  <b-dropdown-item
                    v-for="size in mealPackage.sizes"
                    :key="size.id"
                    @click="addMealPackage(mealPackage, true, size)"
                  >
                    {{ size.title }} -
                    {{ format.money(size.price, storeSettings.currency) }}
                  </b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </slide>

        <slide
          v-for="meal in mealPackage.meals"
          v-if="mealPackage.meal_carousel"
          :key="meal.id"
          :caption="meal.title"
        >
          <div class="row">
            <div class="col-lg-6 modal-meal-image">
              <h4 class="center-text">{{ meal.title }}</h4>
              <thumbnail
                v-if="meal.image.url"
                :src="meal.image.url"
                :aspect="false"
                width="100%"
                :lazy="false"
                :spinner="false"
              ></thumbnail>
              <img v-else :src="meal.featured_image" />
              <p
                v-if="storeSettings.showNutrition"
                v-html="mealDescription"
              ></p>
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
                :id="`nutritionFacts${meal.id}`"
                :ref="`nutritionFacts${meal.id}`"
                class="mt-2 mt-lg-0"
              ></div>
              <b-btn
                @click="addMealPackage(mealPackage, true)"
                class="menu-bag-btn width-80 mt-3"
                >+ ADD PACKAGE</b-btn
              >
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
              <b-btn
                @click="addMealPackage(mealPackage, true)"
                class="menu-bag-btn width-80 mt-3"
                >+ ADD PACKAGE</b-btn
              >
            </div>
          </div>
        </slide>
      </carousel>
    </b-modal>
  </div>
</template>

<script>
import MenuBag from "../../mixins/menuBag";
import { mapGetters } from "vuex";
import { Carousel, Slide } from "vue-carousel";

export default {
  components: {
    Carousel,
    Slide
  },
  props: {
    mealPackageModal: false,
    mealPackage: null,
    loaded: false,
    mealDescription: ""
  },
  mixins: [MenuBag],
  data() {
    return {
      hasLoaded: false
    };
  },
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
    addMealPackage(mealPackage, condition = false) {
      this.addOne(mealPackage, condition);
      this.$parent.mealPackageModal = false;
    }
  }
};
</script>
