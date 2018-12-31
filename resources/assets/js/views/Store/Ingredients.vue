<template>
  <div class="row">
    <div class="col-md-12">
      <Spinner v-if="loading"/>
      <div class="card">
        <div class="card-header">Ingredients</div>
        <div class="card-body">
          <v-client-table :columns="columns" :data="tableData" :options="options">
            <span slot="beforeLimit">
              <b-dropdown class="mx-1" right text="Export as">
                <b-dropdown-item @click="exportData('csv')">CSV</b-dropdown-item>
                <b-dropdown-item @click="exportData('xls')">XLS</b-dropdown-item>
                <b-dropdown-item @click="exportData('pdf')">PDF</b-dropdown-item>
              </b-dropdown>
              <!--
              <label>Weight unit:</label>
              <b-select v-model="weightUnit" :options="weightUnitOptions">
                <option slot="top" disabled>-- Select unit --</option>
              </b-select>-->
            </span>

            <div slot="image" slot-scope="props">
              <thumbnail :src="props.row.image_thumb" :width="64"/>
            </div>

            <div slot="actions" slot-scope="props">
              <b-select
                v-if="props.row.unit_type !== 'unit'"
                :value="displayUnits[props.row.id]"
                :options="unitOptions(props.row.unit_type)"
                @change="(val) => saveUnit(props.row.id, val)"
              >
                <option slot="top" disabled>-- Select unit --</option>
              </b-select>
            </div>
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
      loading: true,
      columns: ["image", "food_name", "quantity", "actions"],
      options: {
        headings: {
          image: "",
          food_name: "Ingredient",
          quantity: "Quantity",
          quantity_unit: "Unit",
          actions: "Unit"
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      orderIngredients: "orderIngredients",
      ingredient: "ingredient",
      defaultWeightUnit: "defaultWeightUnit"
    }),
    tableData() {
      return (
        _.map(this.orderIngredients, (orderIngredient, id) => {
          const ingredient = this.getIngredient(id);

          const baseUnit = format.baseUnit(ingredient.unit_type);

          // Convert weight to selected unit
          if (baseUnit !== "unit") {
            ingredient.quantity = format.unit(
              orderIngredient.quantity,
              baseUnit,
              this.displayUnits[id] || baseUnit
            );
          } else {
            ingredient.quantity = orderIngredient.quantity;
          }
          return ingredient;
        }) || []
      );
    },
    weightUnitOptions() {
      return units.mass.selectOptions();
    },
    displayUnits() {
      return _.mapValues(this.orderIngredients, (orderIngredient, id) => {
        const unit = this.getIngredientUnit(id);
        if (unit) {
          return unit;
        }
        const ingredient = this.getIngredient(id);
        const baseUnit = format.baseUnit(ingredient.unit_type);
        if (baseUnit !== "unit") {
          return format.bestUnit(orderIngredient.quantity, baseUnit);
        } else return "unit";
      });
    }
  },
  mounted() {
    // Set initial weight unit to user default
    this.weightUnit = this.defaultWeightUnit || "oz";

    this.refreshIngredients().then(() => {
      this.refreshOrderIngredients().finally(() => {
        this.loading = false;
      });
    });
  },
  methods: {
    ...mapActions([
      "refreshIngredients",
      "refreshOrderIngredients",
      "refreshIngredientUnits"
    ]),
    getIngredient(id) {
      return this.$store.getters.ingredient(id);
    },
    getIngredientUnit(id) {
      return this.$store.getters.ingredientUnit(id);
    },
    unitOptions(unitType) {
      return units[unitType].selectOptions();
    },
    saveUnit(id, unit) {
      let data = {
        units: {}
      };
      data.units[id] = unit;
      axios
        .post(`/api/me/units`, data)
        .then(response => {
          this.refreshIngredientUnits();
        })
        .finally(() => {
          this.loading = false;
        });
    },
    exportData(type = "csv") {
      axios
        .get(`/api/me/orders/ingredients/export/${type}`)
        .then(response => {
          window.open(response.data.url);
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>