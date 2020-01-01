<template>
  <div :class="mealPackagePageClass" v-if="showPage" style="min-height: 100%;">
    <!-- Content Begin !-->
    <div slot="modal-header" class="row w-100">
      <div class="col-md-12 text-center">
        <h5 class="modal-title dbl-underline mb-4">{{ packageTitle }}</h5>
      </div>
    </div>

    <b-modal
      size="lg"
      :title="mealTitle"
      v-model="mealPackageMealModal"
      v-if="mealPackageMealModal"
      hide-backdrop
    >
      <p v-html="mealDescription"></p>
    </b-modal>

    <!-- v-model="viewMealModal"
        v-if="viewMealModal"
        :key="`view-meal-modal${meal.id}`"
        @ok.prevent="onViewMealModalOk" -->

    <div v-if="mealPackage">
      <b-row class="my-3" v-if="mealPackage.description != null">
        <b-col>
          <div>
            <p>{{ mealPackage.description }}</p>
          </div>
        </b-col>
      </b-row>

      <b-row v-if="components.length" class="my-3">
        <b-col>
          <div
            v-for="component in components"
            :key="mealPackage.id + component.id"
          >
            <div
              v-if="componentVisible(component)"
              class="categorySection"
              :target="'categorySection_' + component.id"
            >
              <h3 class="center-text mb-3">
                {{ getComponentLabel(component) }} - Remaining:
                {{ getRemainingMeals(component.id) }}
              </h3>

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
                      <h5>
                        Please Choose A Minimum of {{ component.minimum }}
                      </h5>
                    </div>

                    <div
                      v-if="false === $v.choices[component.id].maximum"
                      class="invalid-feedback d-block"
                    >
                      <h5>
                        Please Choose A Maximum of {{ component.maximum }}
                      </h5>
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
                      <!--class="bag-item col-6 col-sm-4 col-lg-3 pb-4 mb-4"!-->
                      <div
                        class="item col-sm-6 col-md-6 col-lg-6 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border pb-2 mb-2"
                        v-for="mealOption in getMealOptions(
                          getOptionMeals(component.id, option.id),
                          false
                        )"
                        :key="mealOption.meal_id"
                      >
                        <div class="item-wrap">
                          <div class="title d-md-none center-text">
                            {{ mealOption.title }}
                          </div>

                          <div class="image">
                            <thumbnail
                              v-if="
                                mealOption.meal.image != null &&
                                  mealOption.meal.image.url_medium
                              "
                              :src="mealOption.meal.image.url_medium"
                              :spinner="false"
                              class="menu-item-img"
                              width="100%"
                              style="background-color:#ffffff"
                              @click="
                                showMealPackageMealModal(
                                  mealOption.meal.description,
                                  mealOption.meal.title
                                )
                              "
                            ></thumbnail>

                            <div class="price" v-if="mealOption.price > 0">
                              {{
                                format.money(
                                  mealOption.price,
                                  storeSettings.currency
                                )
                              }}
                            </div>
                          </div>
                          <!-- Image End !-->

                          <div class="meta">
                            <div class="title d-none d-md-block center-text">
                              {{ mealOption.title }}
                            </div>

                            <b-form-textarea
                              v-if="
                                (storeModules.specialInstructions &&
                                  !storeModuleSettings.specialInstructionsStoreOnly) ||
                                  (storeModuleSettings.specialInstructionsStoreOnly &&
                                    isStoreView)
                              "
                              class="mt-4"
                              v-model="special_instructions[mealOption.meal_id]"
                              placeholder="Special instructions"
                              rows="3"
                              max-rows="6"
                            ></b-form-textarea>

                            <div
                              class="actions"
                              v-if="mealOption && mealOption.quantity > 0"
                            >
                              <div
                                class="d-flex justify-content-between align-items-center mt-1"
                              >
                                <b-btn
                                  @click="
                                    minusOptionChoice(
                                      component,
                                      option,
                                      mealOption
                                    )
                                  "
                                  class="plus-minus gray"
                                >
                                  <i>-</i>
                                </b-btn>

                                <b-form-input
                                  type="text"
                                  name
                                  id
                                  class="quantity"
                                  :value="
                                    getOptionChoiceQuantity(
                                      component.id,
                                      option.id,
                                      mealOption.meal_id
                                    )
                                  "
                                  readonly
                                ></b-form-input>

                                <b-btn
                                  @click="
                                    addOptionChoice(
                                      component,
                                      option,
                                      mealOption
                                    )
                                  "
                                  class="menu-bag-btn plus-minus"
                                >
                                  <i>+</i>
                                </b-btn>
                              </div>
                            </div>
                            <!-- Actions End !-->
                          </div>
                          <!-- Meta End !-->
                        </div>
                        <!-- Item Wrap End !-->
                      </div>
                    </b-row>

                    <b-row v-if="storeSettings.menuStyle === 'text'">
                      <!-- class="bag-item col-4 col-sm-4 col-md-4 col-lg-4 pb-3" !-->
                      <div
                        class="item item-text col-sm-6 col-md-6 col-lg-12 col-xl-6"
                        v-for="mealOption in getMealOptions(
                          getOptionMeals(component.id, option.id),
                          false
                        )"
                        :key="mealOption.meal_id"
                        style="margin-bottom: 10px !important;"
                      >
                        <div
                          class="card card-text-menu border-light p-3 mr-1"
                          style="height: 100%;"
                          v-if="mealOption && mealOption.quantity > 0"
                        >
                          <div
                            class="bag-item-quantity"
                            style="display: flex; min-height: 128px !important;"
                          >
                            <div
                              class="button-area"
                              style="position: relative;"
                            >
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
                                  minusOptionChoice(
                                    component,
                                    option,
                                    mealOption
                                  )
                                "
                                class="bag-plus-minus gray white-text small-buttons"
                              >
                                <i>-</i>
                              </div>
                            </div>
                            <!-- Button Area End !-->

                            <div
                              v-if="mealOption.meal.image != null"
                              class="content-area"
                              style="position: relative;"
                            >
                              <div
                                class="image-area"
                                style="position: relative;"
                              >
                                <thumbnail
                                  class="text-menu-image"
                                  v-if="mealOption.meal.image != null"
                                  :src="mealOption.meal.image.url_thumb"
                                  :spinner="false"
                                ></thumbnail>

                                <div
                                  class="price"
                                  style="top: 5px !important; right: 5px !important;"
                                  v-if="mealOption.price > 0"
                                >
                                  {{
                                    format.money(
                                      mealOption.price,
                                      storeSettings.currency
                                    )
                                  }}
                                </div>
                              </div>
                              <!-- Image Area End !-->

                              <div class="content-text-wrap">
                                <strong>{{ mealOption.title }}</strong>
                                <div class="mt-1 content-text">
                                  {{ mealOption.meal.description }}
                                </div>

                                <b-form-textarea
                                  v-if="
                                    (storeModules.specialInstructions &&
                                      !storeModuleSettings.specialInstructionsStoreOnly) ||
                                      (storeModuleSettings.specialInstructionsStoreOnly &&
                                        isStoreView)
                                  "
                                  class="mt-2"
                                  v-model="
                                    special_instructions[mealOption.meal_id]
                                  "
                                  placeholder="Special instructions"
                                  rows="3"
                                  max-rows="6"
                                ></b-form-textarea>
                              </div>
                              <!-- Content Text Wrap End !-->
                            </div>

                            <div
                              v-else
                              class="content-area"
                              style="position: relative;"
                            >
                              <div class="content-text-wrap">
                                <strong>{{ mealOption.title }}</strong>
                                <div class="mt-1 content-text">
                                  {{ mealOption.meal.description }}
                                </div>
                                <div
                                  class="price-no-bg"
                                  style="top: 0 !important; right: 0 !important;"
                                  v-if="mealOption.price > 0"
                                >
                                  {{
                                    format.money(
                                      mealOption.price,
                                      storeSettings.currency
                                    )
                                  }}
                                </div>

                                <b-form-textarea
                                  v-if="
                                    (storeModules.specialInstructions &&
                                      !storeModuleSettings.specialInstructionsStoreOnly) ||
                                      (storeModuleSettings.specialInstructionsStoreOnly &&
                                        isStoreView)
                                  "
                                  class="mt-2"
                                  v-model="
                                    special_instructions[mealOption.meal_id]
                                  "
                                  placeholder="Special instructions"
                                  rows="3"
                                  max-rows="6"
                                ></b-form-textarea>
                              </div>
                            </div>
                          </div>
                          <!-- Bag Item Quantity End !-->
                        </div>
                        <!-- Card End !-->
                      </div>
                    </b-row>
                  </div>
                </div>
              </b-form-group>
            </div>
          </div>
        </b-col>
      </b-row>

      <b-row
        v-if="mealAddons.length"
        class="categorySection my-3"
        :target="'categorySection_addons'"
      >
        <b-col>
          <h3 class="center-text mb-3">Add-ons</h3>

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
                stacked
                @input.native="e => e"
                @change="choices => onChangeAddonChoices(addon, choices)"
              >
                <b-checkbox
                  v-for="(option, index) in getMealOptions(addon.meals)"
                  :value="option.value"
                  v-bind:key="index"
                >
                  {{ option.text }}

                  <b-form-textarea
                    v-if="
                      (storeModules.specialInstructions &&
                        !storeModuleSettings.specialInstructionsStoreOnly) ||
                        (storeModuleSettings.specialInstructionsStoreOnly &&
                          isStoreView)
                    "
                    style="width: 100%;"
                    class="mb-2"
                    v-model="special_instructions[option.id]"
                    placeholder="Special instructions"
                    rows="3"
                    max-rows="6"
                  ></b-form-textarea>
                </b-checkbox>
              </b-checkbox-group>
            </div>
          </div>
        </b-col>
      </b-row>

      <b-row class="my-3">
        <b-col>
          <div>
            <div
              class="categorySection"
              target="categorySection_top"
              v-if="isStoreView"
            >
              <h3 class="center-text mb-3" v-if="getTopLevel().length > 0">
                Included Items
              </h3>

              <b-form-group :label="null">
                <div class="my-2">
                  <b-row v-if="storeSettings.menuStyle === 'image'">
                    <div
                      class="item col-sm-6 col-md-6 col-lg-6 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border pb-2 mb-2"
                      v-for="mealOption in getTopLevel()"
                      :key="mealOption.meal_id"
                    >
                      <div class="item-wrap">
                        <div class="title d-md-none center-text">
                          {{ mealOption.title }}
                        </div>

                        <div class="image">
                          <thumbnail
                            v-if="
                              mealOption.meal.image != null &&
                                mealOption.meal.image.url_medium
                            "
                            :src="mealOption.meal.image.url_medium"
                            :spinner="false"
                            class="menu-item-img"
                            width="100%"
                            style="background-color:#ffffff"
                            @click="
                              showMealPackageMealModal(
                                mealOption.meal.description,
                                mealOption.meal.title
                              )
                            "
                          ></thumbnail>

                          <div class="price" v-if="mealOption.price > 0">
                            {{
                              format.money(
                                mealOption.price,
                                storeSettings.currency
                              )
                            }}
                          </div>
                        </div>
                        <!-- Image End !-->

                        <div class="meta">
                          <div class="title d-none d-md-block center-text">
                            {{ mealOption.title }}
                          </div>

                          <b-form-textarea
                            v-if="
                              (storeModules.specialInstructions &&
                                !storeModuleSettings.specialInstructionsStoreOnly) ||
                                (storeModuleSettings.specialInstructionsStoreOnly &&
                                  isStoreView)
                            "
                            class="mt-4"
                            v-model="special_instructions[mealOption.meal_id]"
                            placeholder="Special instructions"
                            rows="3"
                            max-rows="6"
                          ></b-form-textarea>
                        </div>
                        <!-- Meta End !-->
                      </div>
                      <!-- Item Wrap End !-->
                    </div>
                  </b-row>

                  <b-row v-if="storeSettings.menuStyle === 'text'">
                    <div
                      class="item item-text col-sm-6 col-md-6 col-lg-12 col-xl-6"
                      v-for="mealOption in getTopLevel()"
                      :key="mealOption.meal_id"
                      style="margin-bottom: 10px !important;"
                    >
                      <div
                        class="card card-text-menu border-light p-3 mr-1"
                        style="height: 100%;"
                        v-if="mealOption && mealOption.quantity > 0"
                      >
                        <div
                          class="bag-item-quantity"
                          style="display: flex; min-height: 128px !important;"
                        >
                          <div
                            v-if="mealOption.meal.image != null"
                            class="content-area"
                            style="position: relative;"
                          >
                            <div class="image-area" style="position: relative;">
                              <thumbnail
                                class="text-menu-image"
                                v-if="mealOption.meal.image != null"
                                :src="mealOption.meal.image.url_thumb"
                                :spinner="false"
                              ></thumbnail>

                              <div
                                class="price"
                                style="top: 5px !important; right: 5px !important;"
                                v-if="mealOption.price > 0"
                              >
                                {{
                                  format.money(
                                    mealOption.price,
                                    storeSettings.currency
                                  )
                                }}
                              </div>
                            </div>
                            <!-- Image Area End !-->

                            <div class="content-text-wrap">
                              <strong>{{ mealOption.title }}</strong>
                              <div class="mt-1 content-text">
                                {{ mealOption.meal.description }}
                              </div>

                              <b-form-textarea
                                v-if="
                                  (storeModules.specialInstructions &&
                                    !storeModuleSettings.specialInstructionsStoreOnly) ||
                                    (storeModuleSettings.specialInstructionsStoreOnly &&
                                      isStoreView)
                                "
                                class="mt-2"
                                v-model="
                                  special_instructions[mealOption.meal_id]
                                "
                                placeholder="Special instructions"
                                rows="3"
                                max-rows="6"
                              ></b-form-textarea>
                            </div>
                            <!-- Content Text Wrap End !-->
                          </div>

                          <div
                            v-else
                            class="content-area"
                            style="position: relative;"
                          >
                            <div class="content-text-wrap">
                              <strong>{{ mealOption.title }}</strong>
                              <div class="mt-1 content-text">
                                {{ mealOption.meal.description }}
                              </div>
                              <div
                                class="price-no-bg"
                                style="top: 0 !important; right: 0 !important;"
                                v-if="mealOption.price > 0"
                              >
                                {{
                                  format.money(
                                    mealOption.price,
                                    storeSettings.currency
                                  )
                                }}
                              </div>

                              <b-form-textarea
                                v-if="
                                  (storeModules.specialInstructions &&
                                    !storeModuleSettings.specialInstructionsStoreOnly) ||
                                    (storeModuleSettings.specialInstructionsStoreOnly &&
                                      isStoreView)
                                "
                                class="mt-2"
                                v-model="
                                  special_instructions[mealOption.meal_id]
                                "
                                placeholder="Special instructions"
                                rows="3"
                                max-rows="6"
                              ></b-form-textarea>
                            </div>
                          </div>
                        </div>
                        <!-- Bag Item Quantity End !-->
                      </div>
                      <!-- Card End !-->
                    </div>
                  </b-row>
                </div>
              </b-form-group>
            </div>
          </div>
        </b-col>
      </b-row>
    </div>

    <div class="modal-footer">
      <button @click="back" type="button" class="btn btn-secondary btn-lg">
        Back
      </button>
      <button
        @click="done"
        type="button"
        class="btn btn-lg brand-color white-text"
      >
        Add
      </button>
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
      choices: {},
      addons: [],
      special_instructions: {},
      mealDescription: null,
      mealTitle: null,
      mealPackageMealModal: false
    };
  },
  updated() {
    if (this.components)
      this.$parent.mealPackagePageComponents = this.components.length;
  },
  components: {},
  props: {
    mealPackage: {},
    mealPackageSize: null,
    storeSettings: {},
    storeView: false
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      storeModules: "viewedStoreModules",
      storeModuleSettings: "viewedStoreModuleSettings",
      deliveryDays: "viewedStoreDeliveryDays",
      deliveryDay: "viewedStoreDeliveryDay",
      store: "viewedStore"
    }),
    isMultipleDelivery() {
      return this.store.modules.multipleDeliveryDays == 1 ? true : false;
    },
    isStoreView() {
      if (this.$route.params.storeView || this.storeView) {
        return true;
      }
      return false;
    },
    showPage() {
      if (this.$parent.mealPackagePageView) {
        let finalCategoriesSub = [];

        if (this.components && this.components.length > 0) {
          this.components.map(component => {
            if (this.componentVisible(component)) {
              finalCategoriesSub.push({
                id: component.id,
                title: component.title
              });
            }
          });
        }

        if (this.mealAddons && this.mealAddons.length > 0) {
          finalCategoriesSub.push({
            id: "addons",
            title: "Addons"
          });
        }

        if (this.isStoreView) {
          finalCategoriesSub.push({
            id: "top",
            title: "Included Items"
          });
        }

        this.$parent.finalCategoriesSub = finalCategoriesSub;
      }

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
        if (this.mealPackage.default_size_title) {
          return (
            this.mealPackage.title + " - " + this.mealPackage.default_size_title
          );
        } else {
          return this.mealPackage.title;
        }
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
    back() {
      this.special_instructions = {};

      if (this.mealPackage.sizes.length > 0) {
        this.mealPackage.sizes.forEach(size => {
          size.meals.forEach(meal => {
            meal.special_instructions = null;
          });
        });
      }

      this.choices = {};
      this.addons = [];
      this.$parent.showMealsArea = true;
      this.$parent.showMealPackagesArea = true;
      this.$parent.mealPackagePageView = false;
      this.$parent.finalCategoriesSub = [];
    },
    done() {
      this.$v.$touch();

      if (this.$v.$invalid) {
        this.$forceUpdate();
        this.$toastr.e("Please select the minimum number of items required.");
      } else {
        let components = {};
        let addons = {};

        if (!_.isEmpty(this.choices) || !_.isEmpty(this.addons)) {
          if (this.addons) {
            this.addons = this.addons.map(addon => {
              return addon.map(item => {
                item.meal = this.getMeal(item.meal_id);
                return item;
              });
            });
          }

          components = { ...this.choices };
          addons = { ...this.addons };
        }

        /* Checking Special Instructions */
        const meals = this.mealPackageSize
          ? this.mealPackageSize.meals
          : this.mealPackage.meals;
        if (meals) {
          meals.forEach(meal => {
            if (this.special_instructions[meal.id]) {
              meal.special_instructions = this.special_instructions[meal.id];
            }
          });
        }

        if (components) {
          for (let i in components) {
            for (let option in components[i]) {
              components[i][option].forEach(mealOption => {
                if (this.special_instructions[mealOption.meal_id]) {
                  mealOption.special_instructions = this.special_instructions[
                    mealOption.meal_id
                  ];
                }
              });
            }
          }
        }

        if (addons) {
          for (let i in addons) {
            addons[i].forEach(mealOption => {
              if (this.special_instructions[mealOption.meal_id]) {
                mealOption.special_instructions = this.special_instructions[
                  mealOption.meal_id
                ];
              }
            });
          }
        }
        /* Checking Special Instructions End */

        if (this.isMultipleDelivery) {
          const deliveryDays = this.deliveryDays;
          const deliveryDay = this.deliveryDay;

          if (components && deliveryDays && deliveryDay) {
            for (let i in components) {
              if (i && !isNaN(i)) {
                const component = this.getComponent(parseInt(i));
                if (component && component.maximum) {
                  const max = component.maximum;
                  const dayLength = deliveryDays.length;
                  const count = parseInt(max / dayLength);

                  const deliveryDaysNew = [];
                  deliveryDaysNew.push(deliveryDay);
                  deliveryDays.forEach(day => {
                    if (day.id != deliveryDay.id) {
                      deliveryDaysNew.push(day);
                    }
                  });

                  for (let option in components[i]) {
                    const items = components[i][option];

                    if (items && items.length > 0) {
                      for (let index = 0; index < dayLength; index++) {
                        const startIndex = count * index;
                        const endIndex = count * (index + 1) - 1;
                        if (i == dayLength - 1 || endIndex >= items.length) {
                          endIndex = items.length - 1;
                        }

                        if (
                          startIndex < items.length &&
                          endIndex < items.length &&
                          startIndex <= endIndex
                        ) {
                          const itemsNew = items.slice(
                            startIndex,
                            endIndex + 1
                          );

                          if (itemsNew && itemsNew.length > 0) {
                            const mealPackageNew = JSON.parse(
                              JSON.stringify(this.mealPackage)
                            );
                            const componentsNew = {};
                            componentsNew[i] = {};
                            componentsNew[i][option] = itemsNew;

                            mealPackageNew.delivery_day =
                              deliveryDaysNew[index];

                            mealPackageNew.price = mealPackageNew.price / 2;

                            this.addOne(
                              mealPackageNew,
                              true,
                              this.mealPackageSize,
                              componentsNew,
                              addons,
                              null
                            );
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        } else {
          this.addOne(
            this.mealPackage,
            true,
            this.mealPackageSize,
            components,
            addons,
            null
          );
        }

        this.back();
        if (this.$parent.showBagClass.includes("hidden"))
          this.$parent.showBag();
      }
    },
    optionMealSelected(componentId, optionId, mealId) {
      return this.optionSelected(componentId, optionId)
        ? _.find(this.choices[componentId][optionId], { meal_id: mealId }) !==
            undefined
        : false;
    },
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
      let remainingMeals = _.reduce(
        choices,
        (remaining, meals) => {
          return remaining - meals.length;
        },
        max
      );
      this.$parent.remainingMeals = remainingMeals;
      return remainingMeals;
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
    getTopLevel() {
      let mealOptions = this.mealPackageSize
        ? this.mealPackageSize.meals
        : this.mealPackage.meals;

      mealOptions = _.filter(mealOptions, mealOption => {
        const meal = this.getMeal(mealOption.id);
        if (!meal) return false;

        mealOption.meal_id = mealOption.id;

        if (
          this.$parent.search &&
          !meal.title.toLowerCase().includes(this.$parent.search.toLowerCase())
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

          return {
            ...mealOption,
            meal,
            size,
            title
          };
        })
        .value();
    },
    getMealOptions(mealOptions, checkboxes = true) {
      mealOptions = _.filter(mealOptions, mealOption => {
        const meal = this.getMeal(mealOption.meal_id);
        if (!meal) return false;

        if (
          this.$parent.search &&
          !meal.title.toLowerCase().includes(this.$parent.search.toLowerCase())
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
              title += ` +${format.money(
                mealOption.price,
                this.storeSettings.currency
              )}`;
            }

            return {
              text: title,
              value: mealOption,
              id: mealOption.meal_id
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
    minusOptionChoice(component, option, choice) {
      if (!this.choices[component.id]) {
        return;
      }

      let choices = this.choices[component.id][option.id];
      const index = _.findLastIndex(choices, { meal_id: choice.meal_id });
      if (index > -1) {
        choices.splice(index, 1);
      }
      this.onChangeOptionChoices(component, option, choices);
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

      // Ensure meal obj is set
      if (!choice.meal) {
        choice.meal = this.getMeal(choice.meal_id);
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
        } else if (remaining == 0) {
          // Next Part
          let elem = $(
            '.categoryNavItem[target="categorySection_' + component.id + '"]'
          ).next();
          if (elem && elem.length > 0) {
            elem.click();
          }
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
            choice = choice || choices[0];
            this.addOptionChoice(comp, opt, choice);
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
    },
    showMealPackageMealModal(description, title) {
      this.mealDescription = description.replace(/(\r\n|\n|\r)/gm, "<br />");
      this.mealTitle = title;
      this.mealPackageMealModal = true;
    }
  }
};
</script>
