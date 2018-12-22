<template>
  <div class="row">
    <div class="col-md-12">
      <Spinner v-if="isLoading"/>
      <div class="card">
        <div class="card-header">Ingredients</div>
        <div class="card-body">
          <v-client-table :columns="columns" :data="tableData" :options="options">
            <span slot="beforeLimit">
              <label>Weight unit:</label>
              <b-select v-model="weightUnit" :options="weightUnitOptions">
                <option slot="top" disabled>-- Select unit --</option>
              </b-select>
            </span>
          </v-client-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import Spinner from "../../components/Spinner";
import format from "../../lib/format.js";
import units from "../../data/units.js";

export default {
  components: {
    Spinner
  },
  data() {
    return {
      isLoading: true,
      weightUnit: "oz",
      columns: ["food_name", "quantity", "quantity_unit"],
      options: {
        headings: {
          food_name: "Ingredient",
          quantity: "Quantity",
          quantity_unit: "Unit"
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      ingredients: "orderIngredients",
      defaultWeightUnit: "defaultWeightUnit"
    }),
    tableData() {
      return (
        Object.values(this.ingredients).map(ingredient => {
          // Convert weight to selected unit
          ingredient.quantity = format.unit(
            ingredient.quantity,
            ingredient.quantity_unit,
            this.weightUnit
          );
          ingredient.quantity_unit = this.weightUnit;

          return ingredient;
        }) || []
      );
    },
    weightUnitOptions() {
      return units.weight.selectOptions();
    }
  },
  mounted() {
    // Set initial weight unit to user default
    this.weightUnit = this.defaultWeightUnit || "oz";

    this.refreshIngredients().finally(() => {
      this.isLoading = false;
    });
  },
  methods: {
    ...mapActions(["refreshIngredients"])
  }
};
</script>