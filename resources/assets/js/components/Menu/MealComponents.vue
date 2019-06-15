<template>
  <div>
    <div v-if="ingredientComponentId !== null && ingredientOptionId !== null">
      <ingredient-picker
        ref="ingredientPicker"
        v-model="
          meal.components[ingredientComponentId].options[ingredientOptionId]
            .ingredients
        "
        :options="{ saveButton: true }"
        :meal="meal"
        @save="val => onChangeIngredients(val)"
      ></ingredient-picker>
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
        v-for="(component, i) in meal.components"
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
            <th>Meal Size</th>
            <th>Ingredients</th>
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
                  v-model="option.meal_size_id"
                  :options="sizeOptions"
                ></b-select>
              </td>
              <td>
                <b-btn variant="primary" @click="changeOptionIngredients(i, x)"
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
                      ingredients: [],
                      meal_size_id: null
                    })
                  "
                  >Add Option</b-btn
                >
              </td>
            </tr>
          </tfoot>
        </table>

        <hr v-if="i < meal.components.length - 1" class="my-4" />
      </div>

      <div v-if="meal.components.length" class="mt-4">
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
    meal: {
      required: true
    }
  },
  data() {
    return {
      ingredientComponentId: null,
      ingredientOptionId: null
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
    sizeOptions() {
      return _.concat(
        {
          text: this.meal.default_size_title || "Default",
          value: null
        },
        this.meal.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    }
  },
  watch: {
    "meal.components": function() {
      this.onChangeComponents();
    }
  },
  created() {
    this.onChangeComponents = _.debounce(this.onChangeComponents, 2000);
  },
  mounted() {},
  methods: {
    addComponent() {
      this.meal.components.push({
        id: 1000000 + this.meal.components.length, // push to the end of table
        title: "",
        minimum: 1,
        maximum: 1,
        options: [
          {
            id: 0,
            title: "",
            price: null,
            ingredients: [],
            meal_size_id: null
          }
        ]
      });
    },
    deleteComponent(id) {
      this.meal.components = _.filter(this.meal.components, component => {
        return component.id !== id;
      });
      this.onChangeComponents();
    },
    deleteComponentOption(componentIndex, optionIndex) {
      let options = this.meal.components[componentIndex].options;

      options = _.filter(options, (option, i) => {
        return i !== optionIndex;
      });

      this.meal.components[componentIndex].options = options;
      this.onChangeComponents();
    },
    onChangeComponents() {
      if (!_.isArray(this.meal.components)) {
        throw new Error("Invalid components");
      }

      // Validate all rows
      for (let component of this.meal.components) {
        if (!component.title || !component.minimum || !component.maximum) {
          return;
        }
      }

      this.$emit("change", this.meal.components);
    },
    changeOptionIngredients(componentId, optionId) {
      this.ingredientComponentId = componentId;
      this.ingredientOptionId = optionId;
    },
    onChangeIngredients(ingredients) {
      try {
        this.meal.components[this.ingredientComponentId].options[
          this.ingredientOptionId
        ].ingredients = ingredients;
      } catch (e) {}
      this.ingredientComponentId = null;
      this.ingredientOptionId = null;
    },
    save() {
      this.$emit("save", this.meal.components);
      this.$toastr.s("Meal variation saved.");
    }
  }
};
</script>
