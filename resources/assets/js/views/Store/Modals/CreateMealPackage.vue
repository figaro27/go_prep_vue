<template>
  <div class="modal-full modal-tabs">
    <b-modal
      title="Add Package"
      ref="modal"
      @ok.prevent="e => storePackage(e)"
      @cancel.prevent="toggleModal()"
      @hidden="toggleModal"
    >
      <b-row>
        <b-col>
          <b-tabs>
            <b-tab title="General" active>
              <h4>Package Title</h4>
              <b-form-group label-for="meal-title" :state="true">
                <b-form-input
                  id="meal-title"
                  type="text"
                  v-model="package.title"
                  placeholder="Meal Name"
                  required
                ></b-form-input>
              </b-form-group>
              <h4>Package Description</h4>
              <b-form-group label-for="meal-description" :state="true">
                <textarea
                  v-model.lazy="package.description"
                  id="meal-description"
                  class="form-control"
                  :rows="4"
                ></textarea>
              </b-form-group>
              <b-form-group>
                <h4>Price</h4>
                <money
                  required
                  v-model="package.price"
                  :min="0.1"
                  class="form-control"
                  v-bind="{ prefix: storeCurrencySymbol }"
                ></money>
              </b-form-group>
            </b-tab>
            <b-tab title="Meals">
              <h4>Meals</h4>
              <v-client-table
                ref="packageMealsTable"
                :columns="columns"
                :data="tableData"
                :options="options"
              >
                <div slot="beforeTable" class="mb-2"></div>

                <span slot="beforeLimit">
                  <div class="mr-2">
                    Total meal price:
                    {{ format.money(mealPriceTotal, storeSettings.currency) }}
                  </div>
                </span>

                <div slot="included" slot-scope="props">
                  <b-form-checkbox
                    class="largeCheckbox"
                    type="checkbox"
                    v-model="props.row.included"
                    :value="true"
                    :unchecked-value="false"
                    @change="val => toggleMeal(props.row.id)"
                  ></b-form-checkbox>
                </div>

                <div slot="featured_image" slot-scope="props">
                  <thumbnail
                    v-if="props.row.image.url_thumb"
                    :src="props.row.image.url_thumb"
                    width="64px"
                  ></thumbnail>
                </div>

                <div slot="quantity" slot-scope="props">
                  <b-input
                    type="number"
                    v-model="props.row.quantity"
                    @change="val => setMealQuantity(props.row.id, val)"
                  ></b-input>
                </div>
              </v-client-table>
            </b-tab>
            <b-tab title="Variations">
              <b-tabs pills>
                <b-tab title="Sizes">
                  <meal-package-sizes
                    :package="package"
                    @change="val => (package.sizes = val)"
                    @changeDefault="val => (package.default_size_title = val)"
                    @save="
                      val =>
                        updateMealPackage(package.id, {
                          sizes: val,
                          default_size_title: package.default_size_title
                        })
                    "
                  ></meal-package-sizes>
                </b-tab>

                <b-tab title="Components">
                  <meal-package-components
                    :package="package"
                    @change="val => (package.components = val)"
                    @save="
                      val => updateMealPackage(package.id, { components: val })
                    "
                  ></meal-package-components>
                </b-tab>

                <b-tab title="Addons">
                  <meal-package-addons
                    :package="package"
                    @change="val => (package.addons = val)"
                    @save="
                      val => updateMealPackage(package.id, { addons: val })
                    "
                  ></meal-package-addons>
                </b-tab>
              </b-tabs>
            </b-tab>
          </b-tabs>
        </b-col>

        <b-col md="3" lg="2">
          <picture-input
            ref="featuredImageInput"
            :alertOnError="false"
            :autoToggleAspectRatio="true"
            margin="0"
            size="10"
            button-class="btn"
            @change="val => changeImage(val)"
          ></picture-input>
          <!-- <p class="center-text mt-2">
            Image size too big?
            <br />You can compress images
            <a href="https://imagecompressor.com/" target="_blank">here.</a>
          </p> -->
        </b-col>
      </b-row>
    </b-modal>
  </div>
</template>

<style lang="scss" scoped>
.meal {
  &.active {
  }
}
</style>

<script>
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from "vue-picture-input";
import units from "../../../data/units";
import format from "../../../lib/format";
import { mapGetters, mapActions, mapMutations } from "vuex";
import Spinner from "../../../components/Spinner";
import IngredientPicker from "../../../components/IngredientPicker";
import fs from "../../../lib/fs.js";
import MealPackageSizes from "../../../components/Menu/MealPackageSizes";
import MealPackageComponents from "../../../components/Menu/MealPackageComponents";
import MealPackageAddons from "../../../components/Menu/MealPackageAddons";

export default {
  components: {
    Spinner,
    PictureInput,
    MealPackageSizes,
    MealPackageComponents,
    MealPackageAddons
  },
  data() {
    return {
      package: {
        active: true,
        meals: []
      },
      columns: ["included", "featured_image", "title", "quantity"],
      options: {
        headings: {
          included: "Included",
          featured_image: "Image",
          title: "Title",
          quantity: "Quantity"
        },
        rowClassCallback: function(row) {
          let classes = `meal meal-${row.id}`;
          classes += row.included ? "" : " faded";
          return classes;
        },
        orderBy: {
          column: "title",
          ascending: true
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      meals: "storeMeals",
      findMeal: "storeMeal",
      isLoading: "isLoading",
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
    tableData() {
      return this.meals.map(meal => {
        meal.included = this.hasMeal(meal.id);
        meal.quantity = this.getMealQuantity(meal.id);
        return meal;
      });
    },
    mealPriceTotal() {
      let total = 0;
      this.package.meals.forEach(meal => {
        const _meal = this.findMeal(meal.id);
        if (_meal) {
          total += _meal.price * meal.quantity;
        }
      });
      return total;
    }
  },
  mounted() {
    this.$refs.modal.show();
    setTimeout(() => {
      this.$refs.featuredImageInput.onResize();
    }, 100);
  },
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals",
      _updateMeal: "updateMeal"
    }),
    storeSettings() {
      return this.store.settings;
    },
    findMealIndex(id) {
      return _.findIndex(this.package.meals, { id });
    },
    hasMeal(id) {
      return this.findMealIndex(id) !== -1;
    },
    toggleMeal(id) {
      if (this.hasMeal(id)) {
        this.removeMeal(id);
      } else {
        this.addMeal(id, 1);
      }
    },
    removeMeal(id) {
      this.package.meals = _.filter(this.package.meals, meal => {
        return meal.id !== id;
      });
    },
    addMeal(id, quantity = 1) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        this.package.meals.push({
          id,
          quantity
        });
      } else {
        let meal = this.package.meals[index];
        meal.quantity += 1;
        this.$set(this.package.meals, index, meal);
      }
    },
    setMealQuantity(id, quantity) {
      const index = this.findMealIndex(id);
      quantity = parseInt(quantity);
      if (quantity <= 0) {
        return this.removeMeal(id);
      }
      if (index === -1) {
        this.package.meals.push({
          id,
          quantity
        });
      } else {
        let meal = { ...this.package.meals[index] };
        meal.quantity = quantity;
        this.$set(this.package.meals, index, meal);
      }
    },
    getMealQuantity(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.package.meals[index].quantity;
      }
    },
    async storePackage(e) {
      if (this.isLoading) {
        return;
      }

      try {
        const { data } = await axios.post("/api/me/packages", this.package);
      } catch (response) {
        e.preventDefault();
        let error = _.first(Object.values(response.response.data.errors));
        error = error.join(" ");
        this.$toastr.e(error, "Error");
        return;
      }

      this.$toastr.s("Package created!");
      this.$emit("created");
      this.$refs.modal.hide();
      this.$parent.modal = false;
    },
    async changeImage(val) {
      let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
      this.package.featured_image = b64;
    },
    toggleModal() {
      this.$parent.createPackageModal = false;
    }
  }
};
</script>
