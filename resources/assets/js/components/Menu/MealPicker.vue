<template>
  <div>
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

      <div slot="meal_size_id" slot-scope="props">
        <b-select
          v-model="props.row.meal_size_id"
          :options="sizeOptions(props.row)"
          @change="val => setMealSizeId(props.row.id, val)"
        ></b-select>
      </div>

      <div slot="afterTable">
        <b-button variant="primary" :disabled="!canSave" @click.prevent="save"
          >Save</b-button
        >
      </div>
    </v-client-table>
  </div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  props: {
    meal_sizes: {
      type: Boolean,
      default: false
    },
    value: {
      type: Array,
      default: []
    }
  },
  data() {
    return {
      selected: [],
      options: {
        headings: {
          included: "Included",
          featured_image: "Image",
          title: "Title",
          meal_size_id: "Meal Size",
          quantity: "Quantity"
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
      return cols;
    },
    canSave() {
      return this.selected.length > 0;
    },
    tableData() {
      return this.meals.map(meal => {
        meal.included = this.hasMeal(meal.id);
        meal.quantity = this.getMealQuantity(meal.id);
        meal.meal_size_id = this.getMealSizeId(meal.id);
        return meal;
      });
    },
    mealPriceTotal() {
      let total = 0;
      this.selected.forEach(meal => {
        const _meal = this.findMeal(meal.id);
        if (_meal) {
          total += _meal.price * meal.quantity;
        }
      });
      return total;
    }
  },
  created() {
    this.selected = _.isArray(this.value) ? this.value : [];
  },
  methods: {
    save() {
      this.$emit("save", this.selected);
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
    }
  }
};
</script>
