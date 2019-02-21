<template>
  <div class="row">
    <div class="col-md-12">
      <Spinner v-if="isLoading"/>
      <div class="card">
        <div class="card-body">
          <v-client-table :columns="columns" :data="tableData" :options="options">
            <div slot="beforeTable" class="mb-2">
              <div class="d-flex align-items-center">
                <delivery-date-picker v-model="filters.delivery_dates"></delivery-date-picker>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn variant="primary" @click="print('pdf')">
                <i class="fa fa-print"></i>&nbsp;
                Print
              </b-btn>
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
              <thumbnail
                :key="props.row.image_thumb"
                :src="props.row.image_thumb"
                class="ingredient-img"
              ></thumbnail>
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
              <span v-else>Units</span>
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
import checkDateRange  from '../../mixins/deliveryDates';

export default {
  components: {
    Spinner
  },
  mixins: [
    checkDateRange
  ],
  data() {
    return {
      filters: {
        delivery_dates: {
          start: null,
          end: null
        }
      },
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
  watch: {
    "filters.delivery_dates": function(dates) {
      let params = {};
      if (dates.start || dates.end) {
        params.delivery_dates = {
          from: moment.utc(dates.start).toISOString(),
          to: moment.utc(dates.end).toISOString()
        };
      }
      this.refreshOrderIngredients(params);
    }
  },
  computed: {
    ...mapGetters({
      orderIngredients: "orderIngredients",
      ingredient: "ingredient",
      defaultWeightUnit: "defaultWeightUnit",
      isLoading: "isLoading"
    }),
    tableData() {
      return (
        _.map(this.orderIngredients, (orderIngredient, id) => {
          let ingredient = this.getIngredient(id);

          if (!ingredient) {
            return {};
          }

          const baseUnit = units.base(ingredient.unit_type);

          // Convert weight to selected unit
          if (baseUnit !== "unit") {
            ingredient.quantity = units.convert(
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
        const ingredient = this.getIngredient(id);

        if (unit || !ingredient) {
          return unit;
        }

        const baseUnit = units.base(ingredient.unit_type);
        if (baseUnit !== "unit") {
          return units.toBest(orderIngredient.quantity, baseUnit);
        } else return "unit";
      });
    }
  },
  mounted() {
    // Set initial weight unit to user default
    this.weightUnit = this.defaultWeightUnit || "oz";

    /*this.refreshIngredients().then(() => {
      this.refreshOrderIngredients().finally(() => {
        //this.loading = false;
      });
    });*/
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
      if (!unitType) {
        return [];
      }
      return units[unitType].selectOptions();
    },
    saveUnit(id, unit) {
      this.$store.commit("ingredientUnit", { id, unit });
      let data = {
        units: {}
      };
      data.units[id] = unit;
      axios
        .post(`/api/me/units`, data)
        .then(response => {
          //this.refreshIngredientUnits();
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async exportData(type = "csv") {
      const warning = this.checkDateRange({...this.filters.delivery_dates});
      if (warning) {
        try {
          let dialog = await this.$dialog.confirm(
            "You have selected a date range which includes delivery days which haven't passe" +
              "d their cutoff period. This means new orders can still come in for those days. Continue?"
          );
          dialog.close();
        } catch (e) {
          return;
        }
      }

      axios
        .get(`/api/me/orders/ingredients/export/${type}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            window.open(response.data.url);
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    },
    print(format = "pdf") {
      axios
        .get(`/api/me/orders/ingredients/export/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            win.addEventListener(
              "load",
              () => {
                win.print();
              },
              false
            );
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>