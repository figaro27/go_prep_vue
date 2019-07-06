<template>
  <div>
    <div v-if="!_.isNull(meal_picker_component_id)">
      <meal-picker
        ref="mealPicker"
        :meal_sizes="true"
        :selectable_toggle="true"
        :selectable="meal_picker_selectable"
        v-model="meal_picker_meals"
        @save="val => onChangeMeals(val.meals, val.selectable)"
      ></meal-picker>
    </div>

    <div v-else>
      <div class="mb-4">
        <b-button variant="primary" @click="addComponent()"
          >Add Meal Component</b-button
        >
        <img
          v-b-popover.hover="
            'Example: Choose your protein. Choose your vegetable. Minimum and maximum sets the requirement that the customer needs to choose. For example - minimum 1 would be \'Choose at least 1 protein.\' Maximum 3 would be \'Choose up to 3 veggies.\''
          "
          title="Meal Components"
          src="/images/store/popover.png"
          class="popover-size"
        />
      </div>

      <div
        v-for="(component, i) in meal_package.components"
        :key="component.id"
        role="tablist"
      >
        <div class="component-header mb-2">
          <h5 class="d-inline-block">#{{ i + 1 }}. {{ component.title }}</h5>
          <b-btn
            variant="danger"
            class="pull-right"
            @click="deleteComponent(component.id)"
            >Delete</b-btn
          >
        </div>
        <b-row>
          <b-col cols="6">
            <b-form-group label="Title">
              <b-input
                v-model="component.title"
                placeholder="i.e. Choose Your Protein"
              ></b-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Minimum">
              <b-input v-model="component.minimum"></b-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Maximum">
              <b-input v-model="component.maximum"></b-input>
            </b-form-group>
          </b-col>
        </b-row>

        <table class="table">
          <thead>
            <th>Title</th>
            <th>Price</th>
            <th>Meal Package Size</th>
            <th>Meals</th>
            <th></th>
          </thead>

          <tbody>
            <tr v-for="(option, x) in component.options" :key="option.id">
              <td>
                <b-input v-model="option.title"></b-input>
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
                  variant="secondary"
                  @click="
                    component.options.push({
                      //id: 100 + component.options.length,
                      title: '',
                      price: null,
                      meals: [],
                      meal_package_size_id: null
                    })
                  "
                  >Add Option</b-btn
                >
              </td>
            </tr>
          </tfoot>
        </table>

        <hr v-if="i < meal_package.components.length - 1" class="my-4" />
      </div>

      <div v-if="meal_package.components.length" class="mt-4">
        <b-button variant="primary" @click="addComponent()"
          >Add Meal Component</b-button
        >
        <b-button variant="primary" @click="save()" class="pull-right"
          >Save</b-button
        >
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
      meal_picker_selectable: false
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
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
  mounted() {},
  methods: {
    addComponent() {
      this.meal_package.components.push({
        id: 1000000 + this.meal_package.components.length, // push to the end of table
        title: "",
        minimum: 1,
        maximum: 1,
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
      this.onChangeComponents();
    },
    deleteComponentOption(componentIndex, optionIndex) {
      let options = this.meal_package.components[componentIndex].options;

      options = _.filter(options, (option, i) => {
        return i !== optionIndex;
      });

      this.meal_package.components[componentIndex].options = options;
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

      this.meal_picker_selectable = option.selectable;
      this.meal_picker_meals = option
        ? _.map(option.meals, meal => {
            return {
              id: meal.id,
              meal_size_id: meal.meal_size_id,
              quantity: meal.quantity
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

      this.meal_picker_meals = [];
      this.meal_picker_selectable = false;
      this.meal_picker_component_id = null;
      this.meal_picker_option_id = null;
    },
    save() {
      this.$emit("save", this.meal_package.components);
      this.$toastr.s("Meal variation saved.");
    }
  }
};
</script>
