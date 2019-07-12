<template>
  <div>
    <v-client-table
      ref="mealPackageMealsTable"
      :columns="columns"
      :data="tableData"
      :options="options"
    >
      <div slot="beforeTable" class="mb-2">
        <b-form-group label="Selection type" v-if="selectable_toggle">
          <b-form-radio
            v-model="_selectable"
            :value="false"
            name="radio-options"
          >
            Preset <hint title="Preset">Hint content</hint>
          </b-form-radio>

          <b-form-radio
            v-model="_selectable"
            :value="true"
            name="radio-options"
          >
            Selectable <hint title="Selectable">Hint content</hint>
          </b-form-radio>
        </b-form-group>

        <b-button
          variant="primary"
          :disabled="!canSave"
          @click.prevent="save"
          class="pull-right"
          >Save Meals</b-button
        >
      </div>

      <span slot="beforeLimit" class="d-flex align-items-center">
        <div class="mr-2">
          <b-form-radio-group
            v-model="filter_deselected"
            :options="[
              { text: 'Show all', value: false },
              { text: 'Show included', value: true }
            ]"
            buttons
            button-variant="outline-primary"
          >
          </b-form-radio-group>
        </div>
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

      <div slot="added_price" slot-scope="props">
        <money
          v-model="props.row.added_price"
          class="form-control"
          v-bind="{ prefix: storeCurrencySymbol }"
          @blur.native="e => setMealAddedPrice(props.row.id, e.target.value)"
        ></money>
      </div>

      <div slot="meal_size_id" slot-scope="props">
        <b-select
          v-model="props.row.meal_size_id"
          :options="sizeOptions(props.row)"
          @change="val => setMealSizeId(props.row.id, val)"
        ></b-select>
      </div>

      <div slot="afterTable">
        <b-button variant="primary" :disabled="!canSave" @click.prevent="save"
          >Save Meals</b-button
        >
      </div>
    </v-client-table>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";

export default {
  components: {
    cSwitch
  },
  props: {
    meal_sizes: {
      type: Boolean,
      default: false
    },
    value: {
      type: Array,
      default: []
    },
    selectable_toggle: {
      type: Boolean,
      default: false
    },
    selectable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      selected: [],
      _selectable: false,
      filter_deselected: false,
      options: {
        perPage: 100,
        headings: {
          included: "Included",
          featured_image: "Image",
          title: "Title",
          meal_size_id: "Meal Size",
          quantity: "Quantity",
          added_price: "Added Price"
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
      meals: "storeMeals",
      findMeal: "storeMeal",
      isLoading: "isLoading",
      storeCurrencySymbol: "storeCurrencySymbol",
      storeSettings: "storeSettings"
    }),
    columns() {
      let cols = ["included", "featured_image", "title"];
      if (this.meal_sizes) {
        cols.push("meal_size_id");
      }
      cols.push("quantity");
      if (this.selectable_toggle && this.selectable) {
        cols.push("added_price");
      }
      return cols;
    },
    canSave() {
      return this.selected.length > 0;
    },
    tableData() {
      let meals = this.meals.map(meal => {
        meal.included = this.hasMeal(meal.id);
        meal.quantity = this.getMealQuantity(meal.id);
        meal.meal_size_id = this.getMealSizeId(meal.id);
        return meal;
      });

      if (this.filter_deselected) {
        meals = _.filter(meals, meal => meal.included);
      }

      return meals;
    },
    mealPriceTotal() {
      let total = 0;
      this.selected.forEach(_meal => {
        const meal = this.findMeal(_meal.id);

        if (this.selectable_toggle && this.selectable) {
          total += _meal.price ? parseFloat(_meal.price) : 0;
        } else {
          if (_meal) {
            total += meal.price * _meal.quantity;
          }
        }
      });
      return total;
    }
  },
  created() {
    this.selected = _.isArray(this.value) ? this.value : [];
    this._selectable = !!this.selectable;
  },
  methods: {
    save() {
      this.$emit("save", {
        meals: this.selected,
        selectable: this._selectable
      });
    },
    findMealIndex(id) {
      return _.findIndex(this.selected, { id });
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
      this.selected = _.filter(this.selected, meal => {
        return meal.id !== id;
      });
    },
    addMeal(id, quantity = 1, meal_size_id = null) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        this.selected.push({
          id,
          quantity,
          meal_size_id
        });
      } else {
        let meal = this.selected[index];
        meal.quantity += 1;
        this.$set(this.selected, index, meal);
      }
    },
    setMealQuantity(id, quantity) {
      const index = this.findMealIndex(id);
      quantity = parseInt(quantity);
      if (quantity <= 0) {
        return this.removeMeal(id);
      }
      if (index === -1) {
        this.selected.push({
          id,
          quantity
        });
      } else {
        let meal = { ...this.selected[index] };
        meal.quantity = quantity;
        this.$set(this.selected, index, meal);
      }
    },
    getMealQuantity(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.selected[index].quantity;
      }
    },
    setMealSizeId(id, mealSizeId) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.selected[index] };
        meal.meal_size_id = mealSizeId;
        this.$set(this.selected, index, meal);
      }
    },
    getMealSizeId(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return null;
      } else {
        return this.selected[index].meal_size_id;
      }
    },
    setMealAddedPrice(id, price) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.selected[index] };
        meal.price = parseFloat(price.replace(/[^\d.-]/g, ""));
        this.$set(this.selected, index, meal);
      }
    }
  }
};
</script>
