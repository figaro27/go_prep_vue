<template>
  <div class="modal-full modal-tabs">
    <b-modal
      size="xl"
      title="Edit Package"
      ref="modal"
      @ok.prevent="e => updateMealPackage(e, true)"
      @cancel.prevent="toggleModal()"
      @hidden="toggleModal"
      no-fade
    >
      <b-row>
        <b-col>
          <b-tabs>
            <b-tab title="General" active>
              <h4>Package Title</h4>
              <b-form-group label-for="meal-title" :state="true">
                <b-form-textarea
                  id="meal-title"
                  type="text"
                  v-model="mealPackage.title"
                  placeholder="Item Name"
                  required
                ></b-form-textarea>
              </b-form-group>
              <h4>Package Description</h4>
              <b-form-group label-for="meal-description" :state="true">
                <wysiwyg v-model.lazy="mealPackage.description" />
                <!-- <textarea
                  v-model.lazy="mealPackage.description"
                  id="meal-description"
                  class="form-control"
                  :rows="4"
                ></textarea> -->
              </b-form-group>
              <b-form-group>
                <h4>Price</h4>
                <money
                  required
                  v-model="mealPackage.price"
                  :min="0.1"
                  class="form-control"
                  v-bind="{ prefix: storeCurrencySymbol }"
                ></money>
              </b-form-group>

              <h4 class="mt-4">
                Categories
                <img
                  v-b-popover.hover="
                    'Categories show up as different sections of your menu to your customers. You can have the same meal show up in multiple categories.'
                  "
                  title="Categories"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </h4>
              <b-form-checkbox-group
                buttons
                v-model="mealPackage.category_ids"
                :options="categoryOptions"
                class="storeFilters"
                @input="
                  val =>
                    updateMealPackage(mealPackage.id, { category_ids: val })
                "
              ></b-form-checkbox-group>
              <h4 class="mt-4" v-if="store.modules.multipleDeliveryDays">
                Delivery Days
                <img
                  v-b-popover.hover="
                    'Here you can restrict this meal packages to be only available on the highlighted delivery days. Leave blank for the package to be available on ALL days.'
                  "
                  title="Delivery Days"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </h4>
              <b-form-checkbox-group
                v-if="store.modules.multipleDeliveryDays"
                buttons
                v-model="mealPackage.delivery_day_ids"
                :options="deliveryDayOptions"
                @input="
                  val =>
                    updateMealPackage(mealPackage.id, { delivery_day_ids: val })
                "
                class="storeFilters"
              ></b-form-checkbox-group>

              <h4 class="mt-4" v-if="store.modules.frequencyItems">
                Order Restrictions
                <img
                  v-b-popover.hover="
                    'Set items to be available for subscription only, one time order only, or no restrictions.'
                  "
                  title="Restrictions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </h4>
              <b-form-radio-group
                v-if="store.modules.frequencyItems && mealPackage.frequencyType"
                buttons
                v-model="mealPackage.frequencyType"
                :options="frequencyOptions"
                @input="
                  val =>
                    updateMealPackage(mealPackage.id, { frequencyType: val })
                "
                class="storeFilters"
              ></b-form-radio-group>

              <!-- <p class="mt-4">
                <span class="mr-1">Display Included Meals in Packages</span>
                <hint title="Display Included Meals in Packages">
                  Creates a slider in the meal package popup which allows users
                  to view the meals which are included in that package.
                </hint>
              </p> -->

              <!-- <b-form-group :state="true">
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="mealPackage.meal_carousel"
                />
              </b-form-group> -->
            </b-tab>
            <b-tab title="Meals">
              <h4>Meals</h4>
              <v-client-table
                ref="mealPackageMealsTable"
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
                    v-if="props.row.image != null && props.row.image.url_thumb"
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

                <div slot="meal_size_id" slot-scope="props">
                  <b-select
                    v-model="props.row.meal_size_id"
                    :options="sizeOptions(props.row)"
                    @change="val => setMealSizeId(props.row.id, val)"
                  ></b-select>
                </div>

                <div slot="delivery_day_id" slot-scope="props">
                  <b-select
                    v-model="props.row.delivery_day_id"
                    :options="deliveryDayOptions"
                    @change="val => setMealDeliveryDay(props.row.id, val)"
                  ></b-select>
                </div>
              </v-client-table>
            </b-tab>
            <b-tab title="Variations">
              <b-tabs pills>
                <b-tab
                  title="Sizes"
                  @click="$refs.meal_package_sizes.hideMealPicker()"
                >
                  <meal-package-sizes
                    ref="meal_package_sizes"
                    :mealPackage="mealPackage"
                    @change="val => (mealPackage.sizes = val)"
                    @changeDefault="
                      val => (mealPackage.default_size_title = val)
                    "
                    @save="
                      val =>
                        updateMealPackage(mealPackage.id, {
                          sizes: val,
                          default_size_title: mealPackage.default_size_title
                        })
                    "
                  ></meal-package-sizes>
                </b-tab>

                <b-tab
                  title="Components"
                  @click="$refs.meal_package_components.hideMealPicker()"
                >
                  <meal-package-components
                    ref="meal_package_components"
                    :meal_package="mealPackage"
                    @change="val => (mealPackage.components = val)"
                    @save="
                      val =>
                        updateMealPackage(mealPackage.id, {
                          components: val
                        })
                    "
                  ></meal-package-components>
                </b-tab>

                <b-tab
                  title="Addons"
                  @click="$refs.meal_package_addons.hideMealPicker()"
                >
                  <meal-package-addons
                    ref="meal_package_addons"
                    :meal_package="mealPackage"
                    @change="val => (mealPackage.addons = val)"
                    @save="
                      val => updateMealPackage(mealPackage.id, { addons: val })
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
  props: {
    meal_package: {
      required: true,
      type: Object
    }
  },
  data() {
    return {
      mealPackage: {
        meals: []
      },
      columns: [
        "included",
        "featured_image",
        "title",
        "quantity",
        "meal_size_id"
      ],
      options: {
        headings: {
          included: "Included",
          featured_image: "Image",
          title: "Title",
          quantity: "Quantity",
          meal_size_id: "Meal Size",
          delivery_day_id: "Delivery Day"
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
      storeCurrencySymbol: "storeCurrencySymbol",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories"
    }),
    categoryOptions() {
      return Object.values(this.storeCategories).map(cat => {
        return {
          text: cat.category,
          value: cat.id
        };
      });
    },
    deliveryDayOptions() {
      return Object.values(this.store.delivery_days).map(day => {
        return {
          text: day.day_long,
          value: day.id
        };
      });
    },
    frequencyOptions() {
      return [
        { text: "No Restriction", value: "none" },
        { text: "Subscription Only", value: "sub" },
        { text: "Order Only", value: "order" }
      ];
    },
    tableData() {
      return this.meals.map(meal => {
        meal.included = this.hasMeal(meal.id);
        meal.quantity = this.getMealQuantity(meal.id);
        meal.meal_size_id = this.getMealSizeId(meal.id);
        meal.delivery_day_id = this.getMealDeliveryDay(meal.id);
        return meal;
      });
    },
    mealPriceTotal() {
      let total = 0;
      this.mealPackage.meals.forEach(meal => {
        const _meal = this.findMeal(meal.id);
        if (_meal) {
          total += _meal.price * meal.quantity;
        }
      });
      return total;
    }
  },
  created() {
    this.mealPackage = { ...this.meal_package };
  },
  mounted() {
    if (this.store.modules.multipleDeliveryDays) {
      this.columns.push("delivery_day_id");
    }

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
    findMealIndex(id) {
      return _.findIndex(this.mealPackage.meals, { id });
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
      this.mealPackage.meals = _.filter(this.mealPackage.meals, meal => {
        return meal.id !== id;
      });
    },
    addMeal(id, quantity = 1) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        this.mealPackage.meals.push({
          id,
          quantity
        });
      } else {
        let meal = this.mealPackage.meals[index];
        meal.quantity += 1;
        this.$set(this.mealPackage.meals, index, meal);
      }
    },
    setMealQuantity(id, quantity) {
      const index = this.findMealIndex(id);
      quantity = parseInt(quantity);
      if (quantity <= 0) {
        return this.removeMeal(id);
      }
      if (index === -1) {
        this.mealPackage.meals.push({
          id,
          quantity
        });
      } else {
        let meal = { ...this.mealPackage.meals[index] };
        meal.quantity = quantity;
        this.$set(this.mealPackage.meals, index, meal);
      }
    },
    getMealQuantity(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.mealPackage.meals[index].quantity;
      }
    },
    getMealDeliveryDay(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.mealPackage.meals[index].delivery_day_id;
      }
    },
    setMealDeliveryDay(id, deliveryDayId) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.mealPackage.meals[index] };
        meal.delivery_day_id = deliveryDayId;
        this.$set(this.mealPackage.meals, index, meal);
      }
    },
    sizeOptions(meal) {
      return _.concat(
        {
          text: meal.default_size_title || "Default",
          value: null
        },
        meal.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    },
    setMealSizeId(id, mealSizeId) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.mealPackage.meals[index] };
        meal.meal_size_id = mealSizeId;
        this.$set(this.mealPackage.meals, index, meal);
      }
    },
    getMealSizeId(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return null;
      } else {
        return this.mealPackage.meals[index].meal_size_id;
      }
    },
    async updateMealPackage(e, close = false) {
      const req = {
        ...this.mealPackage,
        validate_all: true
      };

      if (!this.hasMeals(this.mealPackage)) {
        this.$toastr.w("Please add at least one meal to the meal package");
        return;
      }

      try {
        const { data } = await axios.patch(
          `/api/me/packages/${this.mealPackage.id}`,
          req
        );
      } catch (response) {
        e.preventDefault();
        let error = _.first(Object.values(response.response.data.errors));
        error = error.join(" ");
        this.$toastr.e(error, "Error");
        return;
      }

      this.$toastr.s("Package updated.");
      this.$emit("updated");

      if (close == true) {
        this.$refs.modal.hide();
        this.$parent.modal = false;
      }
    },
    async changeImage(val) {
      let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
      this.mealPackage.featured_image = b64;
    },
    toggleModal() {
      this.$parent.viewPackageModal = false;
    },
    hasMeals(mealPackage) {
      let hasMeals = false;
      if (this.mealPackage.meals.length > 0) {
        hasMeals = true;
      }
      this.mealPackage.sizes.forEach(size => {
        if (size.meals.length > 0) {
          hasMeals = true;
        }
      });
      this.mealPackage.components.forEach(component => {
        component.options.forEach(option => {
          if (option.meals.length > 0) {
            hasMeals = true;
          }
        });
      });
      this.mealPackage.addons.forEach(addon => {
        if (addon.meals.length > 0) {
          hasMeals = true;
        }
      });
      return hasMeals;
    }
  }
};
</script>
