<template>
  <div v-if="meal">
    <b-row v-if="components.length && sizeCheck" class="my-3">
      <b-col>
        <div
          v-for="component in components"
          :key="meal.id + component.id"
          class
        >
          <h6>{{ getComponentLabel(component) }}</h6>
          <b-form-group :label="null">
            <b-checkbox-group
              v-model="choices[component.id]"
              :options="getOptions(component)"
              :min="component.minimum"
              :max="component.maximum"
              stacked
              @input="setChoices"
            ></b-checkbox-group>

            <div v-if="invalid">
              <div
                v-if="false === $v.choices[component.id].required"
                class="invalid-feedback d-block"
              >
                This field is required
              </div>
              <div
                v-if="$v.choices[component.id].minimum === false"
                class="invalid-feedback d-block"
              >
                Minimum {{ component.minimum }}
              </div>
              <div
                v-if="$v.choices[component.id].maximum === false"
                class="invalid-feedback d-block"
              >
                Maximum {{ component.maximum }}
              </div>
            </div>
          </b-form-group>
        </div>
      </b-col>
    </b-row>

    <b-row v-if="mealAddons.length" class="my-3">
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
  </div>
</template>

<script>
import modal from "../../mixins/modal";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

export default {
  props: {
    meal: {},
    sizeId: null,
    invalid: false
  },
  mixins: [modal],
  data() {
    return {
      test: {},
      mealPackage: false,
      size: null,
      choices: {},
      addons: [],
      validated: false
    };
  },
  mounted() {
    if (this.sizeCheck) {
      this.$parent.invalidCheck = this.$v.$invalid;
    } else {
      this.$parent.invalidCheck = false;
    }
  },
  updated() {
    if (this.sizeCheck) {
      this.$parent.invalidCheck = this.$v.$invalid;
    } else {
      this.$parent.invalidCheck = false;
    }
  },
  computed: {
    ...mapGetters(["storeSettings"]),
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
      if (component.minimum === component.maximum) {
        qty = `Choose ${component.minimum}`;
      } else {
        qty = `Choose up to ${component.maximum}`;
      }

      return `${component.title} - ${qty}`;
    },
    setChoices() {
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
    },
    resetVariations() {
      this.choices = {};
      this.addons = [];
    }
  }
};
</script>

<style></style>
