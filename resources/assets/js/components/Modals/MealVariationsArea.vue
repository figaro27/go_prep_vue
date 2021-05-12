<template>
  <div v-if="meal">
    <b-row v-if="components.length && sizeCheck" class="my-1 pt-3">
      <b-col>
        <div
          v-for="component in components"
          :key="meal.id + component.id"
          class
        >
          <h6>{{ component.title }}</h6>
          <p class="font-13">{{ getComponentLabel(component) }}</p>
          <b-form-group :label="null">
            <b-checkbox-group
              v-model="choices[component.id]"
              :options="getOptions(component)"
              :min="component.minimum"
              :max="component.maximum"
              stacked
              @input="setChoices"
            ></b-checkbox-group>

            <div v-if="invalid || modalInvalid">
              <div
                v-if="false === $v.choices[component.id].required"
                class="invalid-feedback d-block"
              >
                <h6>This field is required</h6>
              </div>
              <div
                v-if="$v.choices[component.id].minimum === false"
                class="invalid-feedback d-block"
              >
                <h6>Minimum {{ component.minimum }}</h6>
              </div>
              <div
                v-if="$v.choices[component.id].maximum === false"
                class="invalid-feedback d-block"
              >
                <h6>Maximum {{ component.maximum }}</h6>
              </div>
            </div>
          </b-form-group>
        </div>
      </b-col>
    </b-row>

    <b-row v-if="mealAddons.length" class="my-1 pt-3">
      <b-col>
        <h6>Add-ons</h6>
        <b-form-group label>
          <b-checkbox-group
            v-model="addons"
            :options="getAddonOptions(mealAddons)"
            @input="setChoices"
            stacked
          ></b-checkbox-group>
        </b-form-group>
      </b-col>
    </b-row>

    <div v-if="fromMealsArea" class="d-flex">
      <h4 class="mb-3 dark-gray ">
        {{ format.money(mealVariationPrice, storeSettings.currency) }}
      </h4>
    </div>
    <div v-if="fromMealsArea">
      <b-form-input
        v-model="quant"
        type="number"
        class="mb-2 width-115"
      ></b-form-input>
      <button
        type="button"
        :style="brandColor"
        class="btn btn-lg white-text d-inline mb-2"
        @click="addMeal(meal)"
      >
        <h6 class="strong pt-1">Add To Bag</h6>
      </button>
      <button
        type="button"
        class="btn btn-lg btn-secondary white-text d-inline mb-2"
        @click="back"
      >
        <h6 class="strong pt-1">Back</h6>
      </button>
    </div>
  </div>
</template>

<script>
import modal from "../../mixins/modal";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  props: {
    meal: {},
    sizeId: null,
    fromMealsArea: false,
    invalid: false
  },
  mixins: [modal, MenuBag],
  data() {
    return {
      quant: 1,
      mealPackage: false,
      size: null,
      choices: {},
      addons: [],
      validated: false,
      mealVariationPrice: null,
      modalInvalid: null
    };
  },
  mounted() {
    if (this.sizeCheck) {
      this.$parent.invalidCheck = this.$v.$invalid;
    } else {
      this.$parent.invalidCheck = false;
    }
    this.getMealVariationPrice();
    this.initializeChoiceCheckbox();
  },
  updated() {
    if (this.sizeCheck) {
      this.$parent.invalidCheck = this.$v.$invalid;
    } else {
      this.$parent.invalidCheck = false;
    }
    this.getMealVariationPrice();
  },
  computed: {
    ...mapGetters({
      storeSettings: "storeSettings",
      getMeal: "viewedStoreMeal",
      menuSettings: "viewedStoreMenuSettings"
    }),
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    headingClass() {
      if (this.fromMealsArea) {
        return "center-text";
      } else return "";
    },
    hasVariations() {
      if (this.meal.components && this.meal.components.length > 0) return true;
      else return false;
    },
    brandColor() {
      if (this.storeSettings) {
        let style = "background-color:";
        style += this.storeSettings.color;
        return style;
      }
    },
    sizeCriteria() {
      return !this.mealPackage
        ? { meal_size_id: this.sizeId }
        : { meal_package_size_id: this.sizeId };
    },
    sizeCheck() {
      let check = false;

      if (this.components.length === 1) return true;

      this.components.forEach(component => {
        component.options.forEach(option => {
          if (option.meal_size_id === this.sizeId) check = true;
        });
      });

      return check;
    },
    components() {
      return _.filter(this.meal.components, component => {
        return _.find(component.options, this.sizeCriteria);
      });
    },
    mealAddons() {
      return _.filter(this.meal.addons, addon => {
        return addon.meal_size_id === this.sizeId;
      });
    },
    totalComponentPrice() {
      let total = 0;

      this.components.forEach(component => {
        let options = component.options;
        let componentId = component.id;

        options.forEach(option => {
          if ((this.choices[componentId] || []).includes(option.id)) {
            total += option.price;
          }
        });
      });

      return total;
    },
    totalAddonPrice() {
      let total = 0;

      this.mealAddons.forEach(addon => {
        if (this.addons.includes(addon.id)) total += addon.price;
      });

      return total;
    }
  },
  validations() {
    if (!this.meal) return {};

    let componentValidations = _.mapValues(
      _.keyBy(this.components, "id"),
      component => {
        return {
          minimum: value => {
            return (
              component.minimum === 0 ||
              (_.isArray(value) && value.length >= component.minimum)
            );
          },
          maximum: value => {
            return (
              !value || (_.isArray(value) && value.length <= component.maximum)
            );
          }
        };
      }
    );

    return {
      choices: { ...componentValidations }
    };
  },
  methods: {
    initializeChoiceCheckbox() {
      if (this.components) {
        this.components.forEach(component => {
          Vue.set(this.choices, component.id, []);

          // Automatically select the first option

          // let selectedSizeId = this.fromMealsArea
          //   ? this.sizeId
          //   : this.$parent.mealSize;
          // let firstComponentOptionId = component.options[0].id;
          // if (selectedSizeId) {
          //   let firstSizeComponentOption = component.options.find(option => {
          //     return option.meal_size_id === selectedSizeId;
          //   });
          //   if (firstSizeComponentOption) {
          //     firstComponentOptionId = firstSizeComponentOption.id;
          //   }
          // }
          // Vue.set(this.choices, component.id, [firstComponentOptionId]);
        });
      }
    },
    getOptions(component) {
      let options = _.filter(component.options, option => {
        return option.meal_size_id == this.sizeId;
      });

      return _.map(options, option => {
        let title = option.title;
        if (option.price && option.price > 0) {
          title +=
            " - " + format.money(option.price, this.storeSettings.currency);
        }

        return {
          value: option.id,
          text: title
        };
      });
    },
    getAddonOptions(addons) {
      addons = _.filter(addons, addon => {
        return addon.meal_size_id == this.sizeId;
      });

      return _.map(addons, addon => {
        let title = addon.title;
        if (addon.price && addon.price > 0) {
          title +=
            " - " + format.money(addon.price, this.storeSettings.currency);
        }

        return {
          value: addon.id,
          text: title
        };
      });
    },
    getComponentLabel(component) {
      let qty = "";
      if (
        component.minimum === component.maximum &&
        this.choices[component.id]
      ) {
        qty = `${component.minimum -
          this.choices[component.id].length} Remaining`;
      } else {
        qty = `Choose up to ${component.maximum}`;
      }

      return `(${qty})`;
    },
    setChoices() {
      if (!this.fromMealsArea) {
        if (this.sizeCheck) {
          this.$parent.invalidCheck = this.$v.$invalid;
        } else {
          this.$parent.invalidCheck = false;
          this.$parent.invalid = false;
        }

        this.$parent.components = this.choices;
        this.$parent.addons = this.addons;
        this.$parent.totalAddonPrice = this.totalAddonPrice;
        this.$parent.totalComponentPrice = this.totalComponentPrice;

        this.$parent.getMealVariationPrice();
        this.$parent.selectedComponentOptions = [];
        this.$parent.selectedAddons = [];
        this.$parent.getComponentIngredients();
        this.$parent.getAddonIngredients();
        this.$parent.refreshNutritionFacts();

        if (this.fromMealsArea) {
          this.getMealVariationPrice();
        }
      }
      this.preventOverMaximumChoices();
    },
    preventOverMaximumChoices() {
      Object.keys(this.choices).forEach(choiceId => {
        let max = this.components.find(component => {
          return parseInt(component.id) === parseInt(choiceId);
        }).maximum;
        let selectedOptions = this.choices[choiceId];
        if (selectedOptions.length > max) {
          selectedOptions.shift();
        }
      });
    },
    resetVariations() {
      //this.choices = {};
      this.addons = [];
      this.initializeChoiceCheckbox();
    },
    isAdjustOrder() {
      if (
        this.adjustOrder ||
        this.$route.params.adjustOrder ||
        this.$route.name == "store-adjust-order"
      ) {
        return true;
      }
      return false;
    },
    isManualOrder() {
      if (
        this.manualOrder ||
        this.$route.params.manualOrder ||
        this.$route.name == "store-manual-order"
      ) {
        return true;
      }
      return false;
    },
    getMealVariationPrice() {
      let selectedMealSize = null;
      if (this.sizeId) {
        selectedMealSize = _.find(this.meal.sizes, size => {
          return size.id === this.sizeId;
        });
      }

      if (selectedMealSize) {
        this.mealVariationPrice =
          selectedMealSize.price +
          this.totalAddonPrice +
          this.totalComponentPrice;
      } else {
        this.mealVariationPrice =
          this.meal.price + this.totalAddonPrice + this.totalComponentPrice;
      }
    },
    getPackageBagItems() {
      const items = [];
      const bag = this.bag;

      if (bag) {
        bag.forEach(item => {
          if (item.meal_package) {
            if (!this.isMultipleDelivery) {
              items.push(item);
            } else {
              if (
                item.delivery_day &&
                this.store.delivery_day &&
                item.delivery_day.id == this.store.delivery_day.id
              ) {
                items.push(item);
              }
            }
          }
        });
      }

      return items;
    },
    back() {
      this.$emit("closeVariationsModal");
    },
    addMeal(meal) {
      meal.quantity = this.getMealQuantity(meal, this.quant);

      if (this.$v.$invalid && this.hasVariations) {
        this.modalInvalid = true;
        this.$toastr.w("Please select the minimum/maximum required choices.");
        return;
      }

      let size = this.sizeId;

      this.addOne(
        meal,
        false,
        size,
        this.choices,
        this.addons,
        this.special_instructions
      );

      this.choices = null;
      this.addons = [];
      this.defaultMealSize = null;
      this.special_instructions = null;

      this.showcaseNutrition = false;
      this.$emit("closeVariationsModal");

      if (this.menuSettings.redirectToCheckout) {
        this.$router.replace("/customer/bag");
      }
    }
  }
};
</script>

<style></style>
