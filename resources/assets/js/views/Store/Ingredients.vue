<template>
  <div class="row">
    <div class="col-md-12">
      <Spinner v-if="isLoading"/>
      <div class="card">
        <div class="card-header">Ingredients</div>
        <div class="card-body">
          <b-select v-model="weightUnit" :options="weightUnitOptions">
            <option slot="top" disabled>-- Select unit --</option>
          </b-select>
          <v-client-table :columns="columns" :data="tableData" :options="options"></v-client-table>
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
      weightUnit: 'oz',
      columns: ["food_name", "serving_qty", "serving_unit"],
      options: {
        headings: {
          food_name: "Ingredient",
          serving_qty: "Quantity",
          serving_unit: "Unit"
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      ingredients: "ingredients",
      defaultWeightUnit: "defaultWeightUnit",
    }),
    tableData() {
      return Object.values(this.ingredients).map(ingredient => {
        // Convert weight to selected unit
        ingredient.serving_qty = format.unit(ingredient.serving_qty, ingredient.serving_unit, this.weightUnit);
        ingredient.serving_unit = this.weightUnit;
        
        return ingredient;
      }) || [];
    },
    weightUnitOptions() {
      return units.weight.selectOptions();
    }
  },
  mounted() {
    // Set initial weight unit to user default
    this.weightUnit = this.defaultWeightUnit || 'oz';

    this.refreshIngredients().finally(() => {
      this.isLoading = false;
    });
  },
  methods: {
    ...mapActions(["refreshIngredients"])
  }
};
</script>