<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <div class="card">
        <div class="card-body">
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="d-flex align-items-center">
                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  ref="deliveryDates"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
              </div>
            </div>

            <span slot="beforeLimit">
              <!-- <b-btn
                variant="success"
                @click="print('pdf', 'ingredients_by_meal')"
              >
                <i class="fa fa-print"></i>&nbsp; Print Ingredients By Meal
              </b-btn> -->
              <div class="d-flex">
                <b-form-checkbox class="pt-1 mr-2" v-model="ingredientsByMeal"
                  >Ingredients By Meal</b-form-checkbox
                >
                <b-btn variant="primary" @click="print('pdf')">
                  <i class="fa fa-print"></i>&nbsp; Print
                </b-btn>
                <b-dropdown class="mx-1" right text="Export as">
                  <b-dropdown-item @click="exportData('csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
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
                v-if="
                  props.row.unit_type !== 'unit' &&
                    displayUnits[props.row.id] !== 'fl-oz'
                "
                :value="displayUnits[props.row.id]"
                :options="unitOptions(props.row.unit_type)"
                @change="val => saveUnit(props.row.id, val)"
              >
                <option slot="top" disabled>-- Select unit --</option>
              </b-select>
              <span v-if="displayUnits[props.row.id] == 'fl-oz'">fl. oz.</span>
              <span v-if="props.row.unit_type == 'unit'">Units</span>
            </div>

            <div slot="adjuster" slot-scope="props">
              <div class="flex-container">
                <div>
                  <b-form-input v-model="props.row.adjuster"></b-form-input>
                </div>
                <div>
                  <b-btn
                    variant="primary ml-2 mb-1"
                    @click="adjustQuantity(props.row.id, props.row.adjuster)"
                    >Adjust</b-btn
                  >
                </div>
              </div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>
    <v-style>
      .input-date{ color: {{ dateColor }}
      }
    </v-style>
  </div>
</template>

<style>
.flex-container {
  display: flex;
  flex-wrap: nowrap;
}

.flex-container > div {
  width: 70px;
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import Spinner from "../../components/Spinner";
import format from "../../lib/format.js";
import units from "../../data/units.js";
import checkDateRange from "../../mixins/deliveryDates";

export default {
  components: {
    Spinner
  },
  mixins: [checkDateRange],
  data() {
    return {
      ingredientsByMeal: false,
      dateColor: "",
      filters: {
        delivery_dates: {
          start: null,
          end: null
        }
      },
      columns: ["image", "food_name", "quantity", "actions", "adjuster"],
      options: {
        headings: {
          image: "",
          food_name: "Ingredient",
          adjuster: "Adjustment %",
          quantity: "Quantity",
          quantity_unit: "Unit",
          actions: "Unit"
        },
        orderBy: {
          column: "food_name",
          ascending: true
        }
      }
    };
  },
  watch: {
    "filters.delivery_dates": function(dates) {
      let params = {};
      if (dates.start && dates.end) {
        params.delivery_dates = {
          from: moment.utc(dates.start).toISOString(),
          to: moment.utc(dates.end).toISOString()
        };
      } else if (dates.start && !dates.end) {
        params.delivery_dates = {
          from: moment.utc(dates.start).toISOString(),
          to: moment.utc(dates.start).toISOString()
        };
      }
      this.refreshOrderIngredients(params);
    }
  },
  computed: {
    ...mapGetters({
      orderIngredientsSpecial: "orderIngredientsSpecial",
      ingredient: "ingredient",
      defaultWeightUnit: "defaultWeightUnit",
      isLoading: "isLoading",
      storeModules: "storeModules"
    }),
    tableData() {
      return (
        _.map(this.orderIngredientsSpecial, (orderIngredient, id) => {
          //let ingredient = this.getIngredient(id);
          let ingredient = orderIngredient.ingredient;

          if (!ingredient) {
            return {};
          }

          const baseUnit = units.base(ingredient.unit_type);

          // Convert weight to selected unit
          if (baseUnit !== "unit") {
            if (this.displayUnits[id] !== "fl-oz") {
              ingredient.quantity = units.convert(
                orderIngredient.quantity,
                baseUnit,
                this.displayUnits[id] || baseUnit
              );
            }
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
      return _.mapValues(
        this.orderIngredientsSpecial,
        (orderIngredient, id) => {
          const unit = this.getIngredientUnit(id);
          const ingredient = this.getIngredient(id);

          if (unit || !ingredient) {
            return unit;
          }

          const baseUnit = units.base(ingredient.unit_type);
          if (baseUnit !== "unit") {
            return units.toBest(orderIngredient.quantity, baseUnit);
          } else return "unit";
        }
      );
    }
  },
  created() {
    // Set initial weight unit to user default
    this.weightUnit = this.defaultWeightUnit || "oz";
  },
  mounted() {
    this.refreshIngredients();
    this.refreshIngredientUnits();
    this.refreshOrderIngredients();
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
      return units.normalize(this.$store.getters.ingredientUnit(id));
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
    adjustQuantity(id, adjustment) {
      axios
        .post(`/api/me/ingredients/adjust`, {
          id: id,
          adjustment: adjustment
        })
        .then(async response => {
          await this.refreshIngredients();
          await this.refreshIngredientUnits();
          this.refreshOrderIngredients();
        });
    },
    async exportData(format = "csv") {
      const warning = this.checkDateRange({ ...this.filters.delivery_dates });
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

      let params = {};
      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      } else if (
        this.filters.delivery_dates.start &&
        !this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.start
        };
      }

      let report = this.ingredientsByMeal
        ? "ingredients_by_meal"
        : "ingredient_quantities";
      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
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
    async print(format = "pdf") {
      // if (
      //   this.filters.delivery_dates.start === null &&
      //   this.storeModules.multipleDeliveryDays
      // ) {
      //   this.$toastr.w("Please select a delivery date.");
      //   return;
      // }
      const warning = this.checkDateRange({ ...this.filters.delivery_dates });
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

      let params = {};

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      } else if (
        this.filters.delivery_dates.start &&
        !this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.start
        };
      }
      let report = this.ingredientsByMeal
        ? "ingredients_by_meal"
        : "ingredient_quantities";
      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
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
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
      this.refreshOrderIngredients();
    }
  }
};
</script>
