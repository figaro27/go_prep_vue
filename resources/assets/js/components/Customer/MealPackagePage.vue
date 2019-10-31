<template>
  <div :class="mealPackagePageClass" v-if="showPage" style="min-height: 100%;">
    <!-- Content Begin !-->
    <div slot="modal-header" class="row w-100">
      <div class="col-md-12 text-center">
        <h5 class="modal-title">{{ packageTitle }}</h5>
      </div>
    </div>

    <div v-if="mealPackage">
      <b-row v-if="components.length" class="my-3">
        <b-col>
          <div
            v-for="component in components"
            :key="mealPackage.id + component.id"
            v-if="componentVisible(component)"
          >
            <h4 class="center-text mb-3">
              {{ getComponentLabel(component) }} - Remaining:
              {{ getRemainingMeals(component.id) }}
            </h4>

            <b-form-group :label="null">
              <div v-for="option in getOptions(component)" :key="option.id">
                <div v-if="$v.choices[component.id].$dirty">
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
                    Minimum {{ component.minimum }} selections
                  </div>

                  <div
                    v-if="false === $v.choices[component.id].maximum"
                    class="invalid-feedback d-block"
                  >
                    Maximum {{ component.maximum }} selections
                  </div>
                </div>

                <b-checkbox
                  v-if="!option.selectable"
                  @input="toggleOption(component.id, option.id)"
                  :checked="optionSelected(component.id, option.id)"
                >
                  {{ option.text || "" }}
                  <small v-if="option.price && option.price > 0">
                    +{{ format.money(option.price, storeSettings.currency) }}
                  </small>
                </b-checkbox>

                <div v-else class="my-2">
                  <b-row v-if="storeSettings.menuStyle === 'image'">
                    <div
                      class="bag-item col-6 col-sm-4 col-lg-3 pb-4 mb-4"
                      v-for="mealOption in getMealOptions(
                        getOptionMeals(component.id, option.id),
                        false
                      )"
                      :key="mealOption.meal_id"
                    >
                      <div
                        v-if="mealOption && mealOption.quantity > 0"
                        class="d-flex align-items-center"
                      >
                        <div class="bag-item-quantity mr-2">
                          <div
                            @click="
                              addOptionChoice(component, option, mealOption)
                            "
                            class="bag-plus-minus brand-color white-text"
                          >
                            <i>+</i>
                          </div>
                          <p class="bag-quantity">
                            {{
                              getOptionChoiceQuantity(
                                component.id,
                                option.id,
                                mealOption.meal_id
                              )
                            }}
                          </p>
                          <div
                            @click="
                              minusOptionChoice(component, option, mealOption)
                            "
                            class="bag-plus-minus gray white-text"
                          >
                            <i>-</i>
                          </div>
                        </div>
                        <div class="bag-item-image mr-2">
                          <thumbnail
                            v-if="
                              mealOption.meal.image != null &&
                                mealOption.meal.image.url_thumb
                            "
                            :src="mealOption.meal.image.url_thumb"
                            :spinner="false"
                            class="cart-item-img"
                            width="80px"
                            v-b-popover.hover="`${mealOption.meal.description}`"
                          ></thumbnail>
                        </div>
                        <div class="flex-grow-1 mr-2">
                          <span>
                            {{ mealOption.title }}
                            <small v-if="mealOption.price > 0"
                              >+{{ format.money(mealOption.price) }}</small
                            >
                          </span>
                        </div>
                      </div>
                    </div>
                  </b-row>

                  <b-row v-if="storeSettings.menuStyle === 'text'">
                    <div
                      class="bag-item col-4 col-sm-4 col-md-4 col-lg-4 pb-3"
                      v-for="mealOption in getMealOptions(
                        getOptionMeals(component.id, option.id),
                        false
                      )"
                      :key="mealOption.meal_id"
                    >
                      <div class="card card-text-menu border-light p-3">
                        <div
                          v-if="mealOption && mealOption.quantity > 0"
                          class="d-flex align-items-center"
                        >
                          <div class="bag-item-quantity mr-2">
                            <div
                              @click="
                                addOptionChoice(component, option, mealOption)
                              "
                              class="bag-plus-minus brand-color white-text small-buttons"
                            >
                              <i>+</i>
                            </div>
                            <p class="bag-quantity">
                              {{
                                getOptionChoiceQuantity(
                                  component.id,
                                  option.id,
                                  mealOption.meal_id
                                )
                              }}
                            </p>
                            <div
                              @click="
                                minusOptionChoice(component, option, mealOption)
                              "
                              class="bag-plus-minus gray white-text small-buttons"
                            >
                              <i>-</i>
                            </div>
                          </div>
                          <div class="flex-grow-1 mr-2">
                            <span class="strong">
                              {{ mealOption.title }}
                              <small v-if="mealOption.price > 0"
                                >+{{ format.money(mealOption.price) }}</small
                              >
                            </span>
                            <p class="small">
                              {{ mealOption.meal.description }}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </b-row>
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
            <b-checkbox @input="toggleAddon(addon.id)">
              {{ addon.title }}
              <small v-if="addon.price > 0"
                >+{{ format.money(addon.price, storeSettings.currency) }}</small
              >
            </b-checkbox>

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
    <!-- Content End !-->
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";

export default {
  data() {
    return {
      choices: {}
    };
  },
  components: {},
  props: {
    mealPackage: {},
    mealPackageSize: null,
    storeSettings: {}
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      getMeal: "viewedStoreMeal"
    }),
    showPage() {
      return this.$parent.mealPackagePageView;
    },
    mealPackagePageClass() {
      return this.storeSettings.menuStyle === "image"
        ? "left-right-box-shadow main-customer-container"
        : "left-right-box-shadow main-customer-container gray-background";
    },
    packageTitle() {
      if (this.mealPackageSize) {
        return this.mealPackage.title + " - " + this.mealPackageSize.title;
      } else {
        return (
          this.mealPackage.title + " - " + this.mealPackage.default_size_title
        );
      }
    },
    sizeId() {
      return _.isObject(this.mealPackageSize) ? this.mealPackageSize.id : null;
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
    getOptions(component) {
      let options = _.filter(component.options, this.sizeCriteria);
      return _.map(options, option => {
        let title = option.title;

        return {
          id: option.id,
          selectable: option.selectable,
          text: title,
          price: option.price
        };
      });
    },
    getComponent(id) {
      return _.find(this.mealPackage.components, { id });
    },
    getComponentLabel(component) {
      let qty = "";
      if (component.minimum === component.maximum) {
        qty = `Choose ${component.minimum}`;
      } else {
        qty = `Choose up to ${component.maximum}`;
      }

      return `${component.title}`;
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
    getComponentChoices(id) {
      return this.choices[id] ? this.choices[id] : [];
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
    },
    getComponentOption(componentId, optionId) {
      if (componentId) {
        const component = this.getComponent(componentId);
        return _.find(component.options, { id: optionId });
      } else {
        let result = null;
        this.mealPackage.components.forEach(component => {
          const opt = _.find(component.options, { id: optionId });
          if (opt) {
            result = opt;
          }
        });

        return result;
      }
      return null;
    },
    getMealOptions(mealOptions, checkboxes = true) {
      mealOptions = _.filter(mealOptions, mealOption => {
        const meal = this.getMeal(mealOption.meal_id);
        if (!meal) return false;

        if (
          this.search &&
          !meal.title.toLowerCase().includes(this.search.toLowerCase())
        ) {
          return false;
        }

        return true;
      });

      return _(mealOptions)
        .map(mealOption => {
          const meal = this.getMeal(mealOption.meal_id);
          if (!meal) return null;

          const size = meal.getSize(mealOption.meal_size_id);

          let title = size ? size.full_title : meal.full_title;

          if (checkboxes) {
            if (mealOption.price > 0) {
              title += ` <small>+${format.money(
                mealOption.price,
                this.storeSettings.currency
              )}</small>`;
            }

            return {
              text: title,
              value: mealOption
            };
          } else {
            return {
              ...mealOption,
              meal,
              size,
              title
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
    componentVisible(component) {
      const { options } = component;
      const restrictedTo = this.getComponent(
        options[0].restrict_meals_component_id
      );
      if (
        component.minimum === 1 &&
        component.maximum === 1 &&
        restrictedTo &&
        restrictedTo.minimum === 1 &&
        restrictedTo.maximum === 1
      ) {
        return false;
      }

      return true;
    },
    getOptionMeals(componentId, optionId) {
      const option = this.getComponentOption(componentId, optionId);

      if (!option) {
        return [];
      }

      if (option.restrict_meals_option_id) {
        const restrictOption = this.getComponentOption(
          option.restrict_meals_component_id,
          option.restrict_meals_option_id
        );

        let m = _.filter(option.meals, meal => {
          return this.optionMealSelected(
            option.restrict_meals_component_id,
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
    getOptionChoiceQuantity(componentId, optionId, mealId) {
      return this.optionSelected(componentId, optionId)
        ? _.filter(this.choices[componentId][optionId], { meal_id: mealId })
            .length
        : 0;
    },
    addonSelected(addonId) {
      return !!this.addons[addonId];
    },
    onChangeAddonChoices(addon, choices) {},
    addOptionChoice(component, option, choice) {
      if (!this.choices[component.id]) {
        this.$set(this.choices, component.id, {});
      }

      let choices = this.choices[component.id][option.id] || [];
      choices.push(choice);
      this.$set(this.choices[component.id], option.id, choices);
      this.onChangeOptionChoices(component, option, choices);
    },
    onChangeOptionChoices(component, option, choices) {
      _.forEach(component.options, opt => {
        if (opt.id === option.id) {
          return;
        }

        if (
          opt.restrict_meals_option_id === option.id &&
          this.optionSelected(
            opt.restrict_meals_component_id || component.id,
            opt.id
          )
        ) {
          let optChoices = _.filter(opt.meals, meal => {
            return this.optionMealSelected(
              opt.restrict_meals_component_id || component.id,
              option.id,
              meal.id
            );
          });

          this.$set(this.choices[component.id], opt.id, optChoices);
        }
      });

      this.$nextTick(() => {
        const remaining = this.getRemainingMeals(component.id);
        if (remaining < 0) {
          this.$toastr.w("You have selected the maximum number of options.");
          const truncated = choices.slice(0, remaining);
          this.$set(this.choices[component.id], option.id, truncated);
        }

        choices = this.choices[component.id][option.id];
        this.components.forEach(comp => {
          const opt = _.find(comp.options, {
            restrict_meals_component_id: component.id,
            restrict_meals_option_id: option.id
          });
          if (!opt) {
            return;
          }

          if (component.minimum === 1 && component.maximum === 1) {
            let choice = _.find(opt.meals, { meal_id: choices[0].meal_id });
            this.addOptionChoice(comp, opt, choice || choices[0]);
          }

          // Deselected meal in parent. Remove from restricted
          let oChoices = this.choices[comp.id][opt.id] || [];
          oChoices.forEach(oChoice => {
            const sel = _.find(choices, { meal_id: oChoice.meal_id });
            if (!sel) {
              this.minusOptionChoice(comp, opt, oChoice);
            }
          });
        });
      });
    }
  }
};
</script>
