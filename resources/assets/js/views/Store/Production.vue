<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <v-client-table
            ref="mealsTable"
            :columns="columns"
            :data="tableData"
            :options="options"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="d-flex align-items-center">
                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  @change="onChangeDateFilter"
                  ref="deliveryDates"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>

                <b-form-radio-group
                  v-if="storeModules.productionGroups"
                  buttons
                  v-model="productionGroupId"
                  null
                  class="storeFilters ml-2 mt-1"
                  @change="val => {}"
                  :options="productionGroupOptions"
                ></b-form-radio-group>
              </div>
            </div>

            <div slot="featured_image" slot-scope="props">
              <thumbnail
                v-if="props.row.image != null && props.row.image.url_thumb"
                :src="props.row.image.url_thumb"
                :spinner="false"
                class="thumb"
              ></thumbnail>
            </div>

            <div slot="title" slot-scope="props" v-html="props.row.title"></div>

            <div slot="price" slot-scope="props">
              {{ format.money(props.row.price, storeSettings.currency) }}
            </div>

            <div slot="total" slot-scope="props">
              {{ format.money(props.row.total, storeSettings.currency) }}
            </div>

            <span slot="beforeLimit">
              <b-form-checkbox
                v-model="filters.group_by_date"
                :value="true"
                :unchecked-value="false"
              >
                Group By Day
              </b-form-checkbox>
              <b-btn
                variant="success"
                v-if="storeModules.productionGroups"
                @click="exportData('meal_orders_all', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print All Groups
              </b-btn>
              <b-btn
                variant="primary"
                @click="exportData('meal_orders', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print
              </b-btn>
              <b-dropdown class="mx-1" right text="Export as">
                <b-dropdown-item @click="exportData('meal_orders', 'csv')"
                  >CSV</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('meal_orders', 'xls')"
                  >XLS</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('meal_orders', 'pdf')"
                  >PDF</b-dropdown-item
                >
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
import checkDateRange from "../../mixins/deliveryDates";

export default {
  components: {
    vSelect,
    Spinner
  },
  mixins: [checkDateRange],
  data() {
    return {
      productionGroupId: null,
      ordersByDate: [],
      filters: {
        delivery_dates: {
          start: null,
          end: null
        },
        group_by_date: false
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
      getMeal: "storeMeal",
      upcomingOrders: "storeUpcomingOrders",
      // orders: "storeOrders",
      isLoading: "isLoading",
      nextDeliveryDates: "storeNextDeliveryDates",
      storeSettings: "storeSettings",
      storeModules: "storeModules",
      storeProductionGroups: "storeProductionGroups"
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = {};
      if (this.filters.delivery_dates.start === null) {
        orders = this.upcomingOrders;
      } else {
        orders = this.ordersByDate;
      }

      let mealCounts = {};
      let mealIds = {};

      orders.forEach(order => {
        _.forEach(order.items, item => {
          let meal = this.getMeal(item.meal_id);
          if (this.productionGroupId != null) {
            if (meal.production_group_id !== this.productionGroupId)
              return null;
          }
          let size = meal.getSize(item.meal_size_id);
          let title = meal.getTitle(
            true,
            size,
            item.components,
            item.addons,
            item.special_instructions
          );

          if (!mealCounts[title]) {
            mealCounts[title] = 0;
            mealIds[title] = item.meal_id;
          }
          mealCounts[title] += item.quantity;
        });
      });

      return _.map(mealCounts, (quantity, title) => {
        let meal = this.getMeal(mealIds[title]);
        let size = null;
        let price = meal.price;

        return {
          ...meal,
          title,
          price,
          size,
          quantity: quantity,
          total: quantity * price
        };
      });
    },
    productionGroupOptions() {
      let prodGroups = this.storeProductionGroups;
      let prodGroupOptions = [{ text: "All", value: null }];

      prodGroups.forEach(prodGroup => {
        prodGroupOptions.push({ text: prodGroup.title, value: prodGroup.id });
      });
      return prodGroupOptions;
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
    async exportData(report, format = "pdf", print = false) {
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

      let params = {
        group_by_date: this.filters.group_by_date
      };

      if (this.productionGroupId !== null) {
        params.productionGroupId = this.productionGroupId;
      }

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      }

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
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
    },
    onChangeDateFilter() {
      axios
        .post("/api/me/getOrdersWithDates", {
          start: this.filters.delivery_dates.start,
          end: this.filters.delivery_dates.end
        })
        .then(response => {
          this.ordersByDate = response.data;
        });
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    }
  }
};
</script>
