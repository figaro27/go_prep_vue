<template>
  <b-modal
    title="Choose Options"
    ref="modal"
    size="lgx"
    @ok.prevent="e => ok(e)"
    class="meal-package-components-modal"
    no-fade
  >
    <div v-if="mealPackage">
      <b-row v-if="components.length" class="my-3">
        <b-col>
          <div
            v-for="(component, i) in components"
            :key="mealPackage.id + component.id"
            class
          >
            <h6>{{ getComponentLabel(component) }}</h6>

            <b-form-group :label="null">
              Remaining: {{ getRemainingMeals(component.id) }}
              <div v-for="option in getOptions(component)" :key="option.id">
                <b-checkbox
                  v-if="!option.selectable"
                  @input="toggleOption(component.id, option.id)"
                  :checked="optionSelected(component.id, option.id)"
                  >{{ option.text || "" }}</b-checkbox
                >

                <div v-else class="my-2">
                  <b-row>
                    <div
                      class="bag-item col-6 col-sm-4 col-lg-3 pb-4 mb-4"
                      v-for="meal in getMealOptions(
                        getOptionMeals(component.id, option.id),
                        false
                      )"
                      :key="meal.meal_id"
                    >
                      <div
                        v-if="meal && meal.quantity > 0"
                        class="d-flex align-items-center"
                      >
                        <div class="bag-item-quantity mr-2">
                          <div
                            @click="addOptionChoice(component, option, meal)"
                            class="bag-plus-minus brand-color white-text"
                          >
                            <i>+</i>
                          </div>
                          <p class="bag-quantity">
                            {{
                              getOptionChoiceQuantity(
                                component.id,
                                option.id,
                                meal.meal_id
                              )
                            }}
                          </p>
                          <div
                            @click="minusOptionChoice(component, option, meal)"
                            class="bag-plus-minus gray white-text"
                          >
                            <i>-</i>
                          </div>
                        </div>
                        <div class="bag-item-image mr-2">
                          <thumbnail
                            v-if="meal.meal.image.url_thumb"
                            :src="meal.meal.image.url_thumb"
                            :spinner="false"
                            class="cart-item-img"
                            width="80px"
                          ></thumbnail>
                        </div>
                        <div class="flex-grow-1 mr-2">
                          <span>{{ meal.meal.title }}</span>
                        </div>
                      </div>
                    </div>
                  </b-row>
                </div>
              </div>

              <div v-if="0 && $v.choices[component.id].$dirty">
                <div
                  v-if="false === $v.choices[component.id].required"
                  class="invalid-feedback d-block"
                >
                  This field is required
                </div>
                <div
                  v-if="false === $v.choices[component.id].minimum"
                  class="invalid-feedback d-block"
                >
                  Minimum {{ component.minimum }}
                </div>
                <div
                  v-if="false === $v.choices[component.id].maximum"
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

          <div v-for="addon in mealAddons" :key="addon.id">
            <b-checkbox @input="toggleAddon(addon.id)">{{
              addon.title
            }}</b-checkbox>

            <div
              v-if="addon.selectable && addonSelected(addon.id)"
              class="my-2 px-2 py-2 px-lg-3 py-lg-3 bg-light"
            >
              <b-checkbox-group
                class="meal-checkboxes"
                v-model="addons[addon.id]"
                :options="getMealOptions(addon.meals)"
                stacked
                @input.native="e => console.log(e)"
                @change="choices => onChangeAddonChoices(addon, choices)"
              ></b-checkbox-group>
            </div>
          </div>
        </b-col>
      </b-row>
    </div>
  </b-modal>
</template>

<script>
import modal from "../../mixins/modal";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

export default {
  mixins: [modal],
  props: {},
  data() {
    return {
      mealPackage: null,
      size: null,
      choices: {},
      meal_choices: {},
      addons: {}
    };
  },
  computed: {
    ...mapGetters({
      storeSettings: "storeSettings",
      getMeal: "viewedStoreMeal"
    }),
    sizeId() {
      return _.isObject(this.size) ? this.size.id : null;
    },
    sizeCriteria() {
      return { meal_package_size_id: this.sizeId };
    },
    components() {
      return _.filter(this.mealPackage.components, component => {
        return _.find(component.options, this.sizeCriteria);
      });
    },
    mealAddons() {
      return _.filter(this.mealPackage.addons, this.sizeCriteria);
    },
    choice_objects() {
      return _.transform(this.choices, (result, optionIds, componentId) => {
        let component = _.find(this.mealPackage.components, {
          id: parseInt(componentId)
        });
        if (!component) return result;

        if (!_.isArray(optionIds)) {
          result[componentId] = _.find(component.options, { id: optionIds });
        } else {
          result[componentId] = [];
          return _.map(optionIds, optionId => {
            result[componentId].push(
              _.find(component.options, { id: optionId })
            );
          });
        }

        return result;
      });
    }
  },
  validations() {
    if (!this.mealPackage) return {};

    let componentValidations = _.mapValues(
      _.keyBy(this.components, "id"),
      component => {
        const qty = this.getComponentQuantity(component.id);
        return {
          minimum: value => {
            return component.minimum === 0 || qty >= component.minimum;
          },
          maximum: value => {
            return !value || qty <= component.maximum;
          }
        };
      }
    );

    return {
      choices: { ...componentValidations }
    };
  },
  methods: {
    toggleOption(componentId, optionId) {
      const option = this.getComponentOption(componentId, optionId);
      let meals = option.selectable ? [] : option.meals;

      if (!this.choices[componentId]) {
        this.$set(this.choices, componentId, {});
      }

      if (
        this.choices[componentId][optionId] ||
        meals.length > this.getRemainingMeals(componentId)
      ) {
        this.$delete(this.choices[componentId], optionId);
      } else {
        this.$set(this.choices[componentId], optionId, meals);
      }
    },
    selectOption(componentId, optionId) {
      if (!this.choices[componentId]) {
        this.$set(this.choices, componentId, {});
      }
    },
    optionSelected(componentId, optionId) {
      return this.choices[componentId]
        ? !!this.choices[componentId][optionId]
        : false;
    },
    optionMealSelected(componentId, optionId, mealId) {
      return this.optionSelected(componentId, optionId)
        ? _.find(this.choices[componentId][optionId], { meal_id: mealId }) !==
            undefined
        : false;
    },
    getOptionChoiceQuantity(componentId, optionId, mealId) {
      return this.optionSelected(componentId, optionId)
        ? _.filter(this.choices[componentId][optionId], { meal_id: mealId })
            .length
        : 0;
    },
    addOptionChoice(component, option, choice) {
      if (!this.choices[component.id]) {
        this.$set(this.choices, component.id, {});
      }

      let choices = this.choices[component.id][option.id] || [];
      choices.push(choice);
      this.$set(this.choices[component.id], option.id, choices);
      this.onChangeOptionChoices(component, option, choices);
    },
    minusOptionChoice(component, option, choice) {
      let choices = this.choices[component.id][option.id];
      const index = _.findLastIndex(choices, { meal_id: choice.meal_id });
      if (index > -1) {
        choices.splice(index, 1);
      }
      this.onChangeOptionChoices(component, option, choices);
    },
    onChangeOptionChoices(component, option, choices) {
      _.forEach(component.options, opt => {
        if (opt.id === option.id) {
          return;
        }

        if (
          opt.restrict_meals_option_id === option.id &&
          this.optionSelected(component.id, opt.id)
        ) {
          // Check this option doesn't contain any restricted meals
          let optChoices = _.filter(opt.meals, meal => {
            return this.optionMealSelected(component.id, option.id, meal.id);
          });

          this.$set(this.choices[component.id], opt.id, optChoices);
        }
      });

      this.$nextTick(() => {
        // Ensure maximum hasn't been exceeded
        const remaining = this.getRemainingMeals(component.id);
        if (remaining < 0) {
          this.$toastr.w("You have selected the maximum number of options!");
          const truncated = choices.slice(0, remaining);
          this.$set(this.choices[component.id], option.id, truncated);
        }
      });
    },
    getOptionMeals(componentId, optionId) {
      const option = this.getComponentOption(componentId, optionId);

      if (!option) {
        return [];
      }

      if (option.restrict_meals_option_id) {
        const restrictOption = this.getComponentOption(
          componentId,
          option.restrict_meals_option_id
        );

        let m = _.filter(option.meals, meal => {
          return this.optionMealSelected(
            componentId,
            option.restrict_meals_option_id,
            meal.meal_id
          );
        });

        return m;
      }

      return option.meals;
    },
    getAddon(addonId) {
      return _.find(this.mealAddons, { id: addonId });
    },
    toggleAddon(addonId) {
      const addon = this.getAddon(addonId);

      if (!this.addons[addonId]) {
        let meals = addon.selectable ? [] : addon.meals;
        this.$set(this.addons, addonId, meals);
      } else {
        this.$delete(this.addons, addonId);
      }
    },
    onChangeAddonChoices(addon, choices) {},
    addonSelected(addonId) {
      return !!this.addons[addonId];
    },
    show(meal, size = null) {
      this.mealPackage = meal;
      this.size = size;
      this.choices = {};
      this.addons = [];

      this.$refs.modal.show();

      this.$forceUpdate();

      this.$nextTick(() => {
        this.$v.$reset();
      });

      return new Promise((resolve, reject) => {
        this.$refs.modal.$on("cancel", () => {
          resolve(null);
          this.mealPackage = null;
          this.size = null;
          this.choices = {};
          this.$v.$reset();
        });
        this.$on("done", () => {
          this.$v.$touch();

          if (this.$v.$invalid) {
            this.$forceUpdate();
          } else {
            if (!_.isEmpty(this.choices) || !_.isEmpty(this.addons)) {
              resolve({
                components: { ...this.choices },
                addons: { ...this.addons }
              });
            } else {
              resolve({
                components: {},
                addons: {}
              });
            }

            this.hide();
            this.mealPackage = null;
            this.size = null;
            this.choices = {};
            this.$v.$reset();
          }
        });
      });
    },
    ok() {
      this.$emit("done");
    },
    getOptions(component) {
      let options = _.filter(component.options, this.sizeCriteria);
      return _.map(options, option => {
        let title = option.title;
        if (option.price && option.price > 0) {
          title +=
            " - " + format.money(option.price, this.storeSettings.currency);
        }

        return {
          id: option.id,
          selectable: option.selectable,
          text: title
        };
      });
    },
    getComponent(id) {
      return _.find(this.mealPackage.components, { id });
    },
    getComponentChoices(id) {
      return this.choices[id] ? this.choices[id] : [];
    },
    getComponentOption(componentId, optionId) {
      const component = this.getComponent(componentId);
      return _.find(component.options, { id: optionId });
    },
    getMealOptions(mealOptions, checkboxes = true) {
      return _(mealOptions)
        .map(mealOption => {
          const meal = this.getMeal(mealOption.meal_id);

          if (!meal) return null;

          const size = meal.getSize(mealOption.meal_size_id);

          if (checkboxes) {
            return {
              text: size ? size.full_title : meal.title,
              value: mealOption
            };
          } else {
            return {
              ...mealOption,
              meal,
              size
            };
          }
        })
        .value();
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
    getRemainingMeals(componentId) {
      const component = this.getComponent(componentId);
      const max = component.maximum;
      const choices = this.getComponentChoices(componentId);

      return _.reduce(
        choices,
        (remaining, meals) => {
          return remaining - meals.length;
        },
        max
      );
    },
    getComponentQuantity(componentId) {
      const component = this.getComponent(componentId);
      const choices = this.getComponentChoices(componentId);

      return _.reduce(
        choices,
        (qty, meals) => {
          return qty + meals.length;
        },
        0
      );
    }
  }
};
</script>

<style lang="scss" scoped>
.meal-checkboxes {
  columns: 1;

  @media screen and (min-width: 768px) {
    columns: 2;
  }
  @media screen and (min-width: 1200px) {
    columns: 3;
  }

  .custom-checkbox {
  }
}
</style>
