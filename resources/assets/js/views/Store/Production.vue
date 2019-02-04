<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading"/>
          <v-client-table ref="mealsTable" :columns="columns" :data="tableData" :options="options">
            <div slot="beforeTable" class="mb-2">
              <div class="d-flex align-items-center">
                <delivery-date-picker v-model="filters.delivery_dates"></delivery-date-picker>
              </div>
            </div>
            <div slot="featured_image" slot-scope="props">
              <img class="thumb" :src="props.row.featured_image" v-if="props.row.featured_image">
            </div>
            <div slot="price" slot-scope="props">{{ format.money(props.row.price) }}</div>
            <div
              slot="total"
              slot-scope="props"
            >{{ format.money(props.row.price * props.row.quantity) }}</div>

            <span slot="beforeLimit">
              <b-btn variant="primary" @click="exportData('meal_orders', 'pdf', true)">
                <i class="fa fa-print"></i>&nbsp;
                Print
              </b-btn>
              <b-dropdown class="mx-1" right text="Export as">
                <b-dropdown-item @click="exportData('meal_orders', 'csv')">CSV</b-dropdown-item>
                <b-dropdown-item @click="exportData('meal_orders', 'xls')">XLS</b-dropdown-item>
                <b-dropdown-item @click="exportData('meal_orders', 'pdf')">PDF</b-dropdown-item>
              </b-dropdown>
            </span>
          </v-client-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from "../../lib/format";
import { Event } from "vue-tables-2";
import vSelect from "vue-select";
import Spinner from "../../components/Spinner";

export default {
  components: {
    vSelect,
    Spinner
  },
  data() {
    return {
      filters: {
        delivery_dates: {
          start: null,
          end: null
        }
      },
      columns: ["featured_image", "title", "price", "quantity", "total"],
      options: {
        headings: {
          featured_image: "Image",
          title: "Title",
          price: "Price",
          quantity: "Current Orders",
          total: "Total Amount"
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              var numA = moment(a.created_at);
              var numB = moment(b.created_at);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          }
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      meals: "storeMeals",
      getMeal: "storeMeal",
      orders: "storeOrders",
      isLoading: "isLoading"
    }),
    tableData() {
      let filters = { ...this.filters };

      let filtered = _.filter(this.orders, order => {
        if (
          "delivery_dates" in filters &&
          filters.delivery_dates.start &&
          filters.delivery_dates.end
        ) {
          let dateMatch = false;

          if (filters.delivery_dates.start && filters.delivery_dates.end) {
            dateMatch = order.delivery_date.isBetween(
              filters.delivery_dates.start,
              filters.delivery_dates.end
            );
          } else if (filters.delivery_dates.start) {
            dateMatch = order.delivery_date.isAfter(
              filters.delivery_dates.start
            );
          } else if (filters.delivery_dates.end) {
            dateMatch = order.delivery_date.isBefore(
              filters.delivery_dates.end
            );
          }

          if (!dateMatch) return false;
        }

        return true;
      });

      let mealCounts = {};

      filtered.forEach(order => {
        _.forEach(order.meal_quantities, (quantity, mealId) => {
          mealId = parseInt(mealId);

          if (!mealCounts[mealId]) {
            mealCounts[mealId] = 0;
          }
          mealCounts[mealId] += quantity;
        });
      });

      return _.map(mealCounts, (quantity, mealId) => {
        return { ...this.getMeal(mealId), quantity: quantity };
      });

      let meals = Object.values(this.meals);
      const production = meals.filter(meal => meal.active_orders > 0);
      return production;
    },
    storeMeals() {
      return this.meals;
    },
    storeOrders() {
      // return this.orders;
      // return this.orders.reduce(function(all, item){
      //     all[item.order_number] = item.amount;
      //     return all;

      // }, {})
      let meal_ids = this.orders.map(function(item) {
        return item.meal_ids;
      });
      let counts = {};
      meal_ids.forEach(arr =>
        arr.forEach(d => (counts[d] = (counts[d] || 0) + 1))
      );

      return counts;

      // return meal_ids.reduce(function(all, item, index){
      //     all[item[index]] += all[item]
      //     return all;
      // }, {})
    }
  },
  mounted() {
    let vue_select = document.createElement("script");
    vue_select.setAttribute("src", "https://unpkg.com/vue-select@latest");
    document.head.appendChild(vue_select);
  },
  methods: {
    formatMoney: format.money,
    // getTotalPrice(price, orders) {
    //     return (price * orders).toFixed(2);
    // },
    exportData(report, format = "pdf", print = false) {
      axios
        .get(`/api/me/print/${report}/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if (print) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                },
                false
              );
            }
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