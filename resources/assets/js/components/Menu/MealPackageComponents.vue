<template>
  <div>
    <div v-if="!_.isNull(meal_picker_component_id)">
      <b-btn @click.prevent="hideMealPicker()" class="mb-3">Back</b-btn>
      <meal-picker
        ref="mealPicker"
        :meal_sizes="true"
        :selectable_toggle="true"
        :selectable="meal_picker_selectable"
        :mealPackageComponentPage="true"
        v-model="meal_picker_meals"
        @save="val => onChangeMeals(val.meals, val.selectable)"
      ></meal-picker>
    </div>

    <div v-else>
      <div class="mb-4">
        <b-button variant="primary" @click="addComponent()"
          >Add Meal Package Component</b-button
        >
        <!--         <img
          v-b-popover.hover="
            'Example: Choose your protein. Choose your vegetable. Minimum and maximum sets the requirement that the customer needs to choose. For example - minimum 1 would be \'Choose at least 1 protein.\' Maximum 3 would be \'Choose up to 3 veggies.\''
          "
          title="Meal Components"
          src="/images/store/popover.png"
          class="popover-size"
        /> -->
      </div>

      <div
        v-for="(component, i) in mealPackageComponents"
        :key="component.id"
        role="tablist"
      >
        <!-- <div class="component-header mb-2">
          <h5 class="d-inline-block">#{{ i + 1 }}. {{ component.title }}</h5>
          <b-btn
            variant="danger"
            class="pull-right"
            @click="deleteComponent(component.id)"
            >Delete</b-btn
          >
        </div> -->
        <b-row>
          <b-col cols="4">
            <b-form-group label="Component Title" class="font-weight-bold">
              <b-input
                v-model="component.title"
                placeholder="i.e. Choose Your Entree"
              ></b-input>
            </b-form-group>
          </b-col>
          <b-col cols="1">
            <b-form-group label="Minimum" class="font-weight-bold">
              <b-input v-model="component.minimum"></b-input>
            </b-form-group>
          </b-col>
          <b-col cols="1">
            <b-form-group label="Maximum" class="font-weight-bold">
              <b-input v-model="component.maximum"></b-input>
            </b-form-group>
          </b-col>
          <b-col cols="2">
            <b-form-group label="Price" class="font-weight-bold">
              <b-input type="number" v-model="component.price"></b-input>
            </b-form-group>
          </b-col>
          <b-col cols="2" v-if="storeModules.multipleDeliveryDays">
            <b-form-group label="Delivery Day" class="font-weight-bold">
              <b-form-select
                v-model="component.delivery_day_id"
                :options="deliveryDayOptions"
              ></b-form-select>
            </b-form-group>
          </b-col>
          <b-col cols="2">
            <b-form-group>
              <b-btn
                class="mt-4 ml-4"
                variant="danger"
                @click="deleteComponent(component.id)"
                >Delete</b-btn
              >
            </b-form-group>
          </b-col>
        </b-row>
        <table class="table">
          <thead>
            <th class="border-top-0">Option Title</th>
            <th class="border-top-0">Price</th>
            <th class="border-top-0">Meal Package Size</th>
            <th
              class="border-top-0"
              v-if="storeModules.packageComponentRestrictions"
            >
              Restrict Items To
            </th>
            <th class="border-top-0">Items</th>
            <th class="border-top-0"></th>
          </thead>

          <tbody>
            <tr v-for="(option, x) in component.options" :key="option.id">
              <td>
                <b-input
                  v-model="option.title"
                  v-if="!option.selectable && option.meals.length"
                ></b-input>
                <div v-else>
                  N/A
                </div>
              </td>
              <td>
                <money
                  :disabled="option.id === -1"
                  required
                  v-model="option.price"
                  :min="0.1"
                  :max="999.99"
                  class="form-control"
                  v-bind="{ prefix: storeCurrencySymbol }"
                ></money>
              </td>
              <td>
                <b-select
                  v-model="option.meal_package_size_id"
                  :options="sizeOptions"
                ></b-select>
              </td>
              <td v-if="storeModules.packageComponentRestrictions">
                <b-select
                  v-model="option.restrict_meals_option_id"
                  :options="restrictMealsOptions(component, option.id)"
                  v-if="canRestrictMeals(component, option)"
                ></b-select>
                <div v-else>
                  N/A
                </div>
              </td>
              <td>
                <b-btn variant="primary" @click="changeOptionMeals(i, x)"
                  >Adjust</b-btn
                >
              </td>
              <td>
                <b-btn variant="link" @click="deleteComponentOption(i, x)">
                  <i class="fa fa-close"></i>
                </b-btn>
              </td>
            </tr>
          </tbody>

          <tfoot>
            <tr>
              <td colspan="5">
                <b-btn
                  variant="success"
                  @click="
                    component.options.push({
                      id: 1000000 + component.options.length,
                      title: '',
                      price: null,
                      meals: [],
                      meal_package_size_id: null,
                      restrict_meals_option_id: null
                    })
                  "
                  >Add Option</b-btn
                >
                <b-btn
                  variant="warning"
                  v-if="meal_package.sizes.length > 0"
                  @click="duplicateOptions(component)"
                  :disabled="duplicated[component.id]"
                  >Duplicate Options for All Sizes</b-btn
                >
              </td>
            </tr>
          </tfoot>
        </table>

        <hr v-if="i < meal_package.components.length - 1" class="my-4" />
      </div>

      <div v-if="meal_package.components.length" class="mt-4">
        <b-button variant="primary" @click="addComponent()"
          >Add Meal Package Component</b-button
        >
        <!-- <b-button variant="primary" @click.prevent="save()" class="pull-right"
          >Save</b-button
        > -->
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ingredient-picker {
  //position: absolute;
}
.component-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
#__BVID__242 {
  width: 150px !important;
}
</style>

<script>
import { mapGetters } from "vuex";
import IngredientPicker from "../IngredientPicker";

export default {
  components: {
    IngredientPicker
  },
  props: {
    meal_package: {
      required: true
    }
  },
  data() {
    return {
      meal_picker_component_id: null,
      meal_picker_option_id: null,
      meal_picker_meals: [],
      meal_picker_selectable: true,
      duplicated: {}
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol",
      storeModules: "storeModules",
      store: "viewedStore"
    }),
    deliveryDayOptions() {
      let options = [];
      this.store.delivery_days.forEach(day => {
        options.push({
          value: day.id,
          text:
            day.day_long +
            " - " +
            day.type.charAt(0).toUpperCase() +
            day.type.slice(1)
        });
      });
      return options;
    },
    sizeOptions() {
      return _.concat(
        {
          text: this.meal_package.default_size_title || "Default",
          value: null
        },
        this.meal_package.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    },
    mealPackageComponents() {
      return this.meal_package.components;
    }
  },
  watch: {
    "meal_package.components": function() {
      this.onChangeComponents();
    }
  },
  created() {
    this.onChangeComponents = _.debounce(this.onChangeComponents, 2000);
  },
  mounted() {
    this.mealPackageComponents.forEach(component => {
      this.duplicated[component.id] = false;
    });
  },
  methods: {
    addComponent() {
      this.meal_package.components.push({
        id: 1000000 + this.meal_package.components.length, // push to the end of table
        title: "",
        minimum: 1,
        maximum: 1,
        price: 0,
        options: [
          {
            id: 0,
            title: "",
            price: null,
            meals: [],
            meal_package_size_id: null
          }
        ]
      });
    },
    deleteComponent(id) {
      this.meal_package.components = _.filter(
        this.meal_package.components,
        component => {
          return component.id !== id;
        }
      );
      this.save();
      this.onChangeComponents();
    },
    deleteComponentOption(componentIndex, optionIndex) {
      let options = this.meal_package.components[componentIndex].options;

      options = _.filter(options, (option, i) => {
        return i !== optionIndex;
      });

      this.meal_package.components[componentIndex].options = options;
      this.save();
      this.onChangeComponents();
    },
    onChangeComponents() {
      if (!_.isArray(this.meal_package.components)) {
        throw new Error("Invalid components");
      }

      // Validate all rows
      for (let component of this.meal_package.components) {
        if (!component.title || !component.minimum || !component.maximum) {
          return;
        }
      }

      this.$emit("change", this.meal_package.components);
    },
    changeOptionMeals(componentIndex, optionIndex) {
      let component = this.meal_package.components[componentIndex];
      let option = component ? component.options[optionIndex] : null;

      if (!component || !option) {
        return;
      }

      this.meal_picker_component_id = componentIndex;
      this.meal_picker_option_id = optionIndex;

      // this.meal_picker_selectable = !!option.selectable;
      this.meal_picker_meals = option
        ? _.map(option.meals, meal => {
            return {
              id: meal.id,
              meal_size_id: meal.meal_size_id,
              quantity: meal.quantity,
              price: meal.price || 0
            };
          })
        : [];
    },
    onChangeMeals(meals, selectable = false) {
      this.meal_package.components[this.meal_picker_component_id].options[
        this.meal_picker_option_id
      ].meals = meals;

      this.meal_package.components[this.meal_picker_component_id].options[
        this.meal_picker_option_id
      ].selectable = selectable;

      // this.hideMealPicker();
    },
    save() {
      this.$emit("save", this.meal_package.components);
      this.$toastr.s("Meal variation saved.");
    },
    hideMealPicker() {
      this.meal_picker_meals = [];
      this.meal_picker_selectable = true;
      this.meal_picker_component_id = null;
      this.meal_picker_option_id = null;
    },
    canRestrictMeals(component, option) {
      if (!option.selectable) {
        return false;
      }

      if (option.restrict_meals_option_id) {
        return true;
      }

      return true;

      // cannot if
      // - the LAST unrestricted

      let siblingOptions = _(component.options)
        .filter(opt => {
          return opt.meal_package_size_id === option.meal_package_size_id;
        })
        .value();

      let unrestricted = _(siblingOptions)
        .filter(opt => {
          return opt.restrict_meals_option_id === null;
        })
        .value();

      return unrestricted.length !== 1;
    },
    restrictMealsOptions(component, optionId) {
      let allOptions = [];
      _.forEach(this.meal_package.components, cmp => {
        _.forEach(cmp.options, opt => {
          if (opt.id !== optionId && !opt.restrict_meals_option_id) {
            allOptions.push({
              text: `${cmp.title} - ${opt.title}`,
              value: opt.id
            });
          }
        });
      });

      return allOptions;

      let options = _(component.options)
        .filter(opt => {
          return opt.id !== optionId && !opt.restrict_meals_option_id;
        })
        .map(opt => {
          return {
            text: opt.title,
            value: opt.id
          };
        })
        .value();

      return _.concat(
        {
          text: "Unrestricted",
          value: null
        },
        options
      );
    },
    duplicateOptions(component) {
      let options = [...component.options];
      this.meal_package.sizes.forEach(size => {
        options.forEach(option => {
          component.options.push({
            id: 1000000 + component.options.length,
            title: option.title,
            price: option.price,
            meals: option.meals,
            selectable: option.selectable,
            meal_package_size_id: size.id,
            restrict_meals_option_id: null
          });
        });
      });
      this.duplicated[component.id] = true;
    }
  }
};
</script>
