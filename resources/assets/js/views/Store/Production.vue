<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="alert alert-success center-text" role="alert">
        Please select the date range to see production.
      </div>
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

                <div class="ml-2 d-flex">
                  <div class="d-inline">
                    <div
                      v-if="
                        storeModules.pickupHours || storeModules.deliveryHours
                      "
                    >
                      From:
                      <b-form-select
                        :class="transferTimeClass"
                        v-model="selectedTimeRange.start_time"
                        :value="transferTime"
                        :options="transferTimeOptions"
                        @input="onChangeDateFilter"
                      ></b-form-select>
                      To:
                      <b-form-select
                        :class="transferTimeClass"
                        v-model="selectedTimeRange.end_time"
                        :value="transferTime"
                        :options="transferTimeOptions"
                        @input="onChangeDateFilter"
                      ></b-form-select>
                    </div>
                  </div>
                </div>
                <b-btn @click="clear" class="ml-2">Clear</b-btn>
                <div
                  v-if="storeModules.productionGroups"
                  class="d-flex ml-2"
                  v-click-outside="hideGroups"
                >
                  <b-btn
                    variant="primary"
                    @click="showGroups = !showGroups"
                    class="d-inline mr-2"
                    >Select Groups</b-btn
                  >
                  <div class="prodGroupCheckbox d-inline" v-if="showGroups">
                    <div class="d-flex mb-2">
                      <b-btn
                        @click="addAllGroups"
                        class="btn btn-success btn-sm d-inline mr-1"
                        >Add All</b-btn
                      >
                      <b-btn
                        @click="removeAllGroups"
                        class="btn btn-danger btn-sm d-inline"
                        >Remove All</b-btn
                      >
                    </div>
                    <p v-if="productionGroupOptions.length == 0" class="small">
                      Add Production Groups using the yellow button on the
                      right, then assign each meal to a group on the Menu page.
                    </p>
                    <b-form-checkbox-group
                      v-model="productionGroupIds"
                      :options="productionGroupOptions"
                      :reduce="group => group.value"
                      stacked
                    ></b-form-checkbox-group>
                  </div>

                  <button
                    class="btn btn-warning d-inline"
                    @click="productionGroupModal = true"
                    v-if="storeModules.productionGroups"
                  >
                    Edit Groups
                  </button>
                  <!-- <v-select
                    class="col-md-6"
                    v-model="productionGroupIds"
                    label="text"
                    :options="productionGroupOptions"
                    :reduce="group => group.value"
                  >
                  </v-select> -->
                  <!-- <b-form-select class="col-md-6" v-model="productionGroupIds" :options="productionGroupOptions" multiple :reduce="group => group.value" :select-size="1"></b-form-select> -->
                </div>
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

            <div
              slot="size"
              slot-scope="props"
              v-html="props.row.base_size"
            ></div>

            <div
              slot="title"
              slot-scope="props"
              v-html="props.row.base_title"
            ></div>

            <div slot="price" slot-scope="props">
              {{ format.money(props.row.price, storeSettings.currency) }}
            </div>

            <div slot="total" slot-scope="props">
              {{ format.money(props.row.total, storeSettings.currency) }}
            </div>

            <span slot="beforeLimit">
              <div class="d-flex">
                <b-form-checkbox
                  v-if="storeModules.pickupHours || storeModules.deliveryHours"
                  id="time_breakdown"
                  v-model="show_time_breakdown"
                  name="time_breakdown"
                  value="true"
                  unchecked-value="false"
                  class="d-inline mr-2 pt-2"
                >
                  Show Times
                </b-form-checkbox>
                <b-form-checkbox
                  v-model="filters.group_by_date"
                  :value="true"
                  :unchecked-value="false"
                  class="d-inline mr-2 pt-2"
                >
                  Group By Day
                </b-form-checkbox>
                <b-btn
                  class="d-inline mr-1"
                  variant="success"
                  v-if="storeModules.productionGroups"
                  @click="exportData('meal_orders_all', 'pdf', true)"
                >
                  <i class="fa fa-print"></i>&nbsp; Print All Groups
                </b-btn>
                <b-btn
                  class="d-inline mr-1"
                  variant="primary"
                  @click="exportData('meal_orders', 'pdf', true)"
                >
                  <i class="fa fa-print"></i>&nbsp; Print
                </b-btn>
                <b-dropdown class="mx-1 d-inline" right text="Export as">
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
              </div>
            </span>
          </v-client-table>
        </div>
      </div>
    </div>
    <b-modal
      size="md"
      title="Production Groups"
      v-model="productionGroupModal"
      v-if="productionGroupModal"
    >
      <div class="center-flex mt-1">
        <div style="max-width:100%">
          <b-form class="mt-1 mb-2 d-flex" @submit.prevent="onAddGroup">
            <b-input
              v-model="new_group"
              type="text"
              placeholder="Production group name"
              class="d-inline"
            ></b-input>
            <b-button type="submit" variant="primary ml-2 d-inline"
              >Add</b-button
            >
          </b-form>
          <ol>
            <li
              v-for="group in storeProductionGroups"
              :key="`group-${group.id}`"
            >
              <h5 v-if="editingId !== group.id">
                <i
                  @click="editingId = group.id"
                  class="fa fa-edit text-warning mr-2 font-12"
                ></i>
                <span class="category-name">{{ group.title }}</span>
              </h5>
              <div v-if="editingId === group.id">
                <div class="d-flex">
                  <b-form @submit.prevent="updateGroup(group.title)">
                    <b-input
                      v-model="group.title"
                      required
                      class="w-50 mr-2 d-inline"
                    ></b-input>
                    <b-btn
                      type="submit"
                      variant="primary"
                      class="mb-1 mt-1 d-inline"
                      >Update</b-btn
                    >
                  </b-form>
                </div>
              </div>
            </li>
          </ol>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from "../../lib/format";
import { Event } from "vue-tables-2";
import vSelect from "vue-select";
import Spinner from "../../components/Spinner";
import checkDateRange from "../../mixins/deliveryDates";
import store from "../../store";
import ClickOutside from "vue-click-outside";
import VueTimepicker from "vue2-timepicker";

export default {
  components: {
    vSelect,
    Spinner,
    VueTimepicker
  },
  mixins: [checkDateRange],
  data() {
    return {
      selectedTimeRange: {
        start_time: null,
        end_time: null
      },
      show_time_breakdown: false,
      showGroups: false,
      productionGroupModal: false,
      editingId: null,
      productionGroupIds: [],
      new_group: null,
      ordersByDate: [],
      mealOrdersByDate: [],
      mealOrdersByDateMD: [],
      filters: {
        delivery_dates: {
          start: null,
          end: null
        },
        group_by_date: false
      },
      columns: [
        "featured_image",
        "size",
        "title",
        // "price",
        "quantity"
        // "total"
      ],
      options: {
        headings: {
          featured_image: "Image",
          title: "Title",
          // price: "Price",
          quantity: "Current Orders"
          // total: "Total Amount"
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
      getLineItem: "storeLineItem",
      upcomingOrders: "storeUpcomingOrders",
      // orders: "storeOrders",
      isLoading: "isLoading",
      nextDeliveryDates: "storeNextDeliveryDates",
      storeSettings: "storeSettings",
      storeModules: "storeModules",
      storeProductionGroups: "storeProductionGroups"
    }),
    transferTimeOptions() {
      let options = [];
      let start = "00:00:00";
      for (let i = 0; i < 48; i++) {
        options.push(start);
        start = moment(start, "HH:mm:ss")
          .add(30, "minutes")
          .format("HH:mm A");
      }

      options = options.map(option => {
        return moment(option, "HH:mm:ss").format("h:mm A");
      });

      return options;
    },
    tableData() {
      let filters = { ...this.filters };

      let orders = {};
      if (this.filters.delivery_dates.start === null) {
        orders = this.upcomingOrders;
      } else {
        orders = this.ordersByDate;
      }

      orders = orders.filter(order => {
        return order.voided === 0;
      });

      if (!this.storeModules.multipleDeliveryDays) {
        return this.mealOrdersByDate;
      } else {
        return this.mealOrdersByDateMD;
      }
    },
    productionGroupOptions() {
      let prodGroups = this.storeProductionGroups;
      let prodGroupOptions = [];

      if (prodGroups.length > 0) {
        prodGroups.forEach(prodGroup => {
          prodGroupOptions.push({ text: prodGroup.title, value: prodGroup.id });
        });
        return prodGroupOptions;
      } else {
        return [];
      }
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
    this.productionGroupOptions.forEach(option => {
      this.productionGroupIds.push(option.value);
    });
  },
  directives: {
    ClickOutside
  },
  methods: {
    ...mapActions({
      refreshStoreProductionGroups: "refreshStoreProductionGroups"
    }),
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

      params.productionGroupIds = this.productionGroupIds;

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      }
      params.startTime =
        this.selectedTimeRange.start_time == "12:00 AM"
          ? "12:01 AM"
          : this.selectedTimeRange.start_time;
      params.endTime = this.selectedTimeRange.end_time;

      params.show_time_breakdown = this.show_time_breakdown;

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
      if (!this.storeModules.multipleDeliveryDays) {
        axios
          .post("/api/me/getMealOrdersWithDates", {
            start: this.filters.delivery_dates.start,
            end: this.filters.delivery_dates.end,
            transferTimeStart:
              this.selectedTimeRange.start_time == "12:00 AM"
                ? "12:01 AM"
                : this.selectedTimeRange.start_time,
            transferTimeEnd: this.selectedTimeRange.end_time
          })
          .then(response => {
            this.mealOrdersByDate = response.data;
          });
      } else {
        axios
          .post("/api/me/getMealOrdersWithDatesMD", {
            start: this.filters.delivery_dates.start,
            end: this.filters.delivery_dates.end
          })
          .then(response => {
            this.mealOrdersByDateMD = response.data;
          });
      }
    },
    clear() {
      this.transferTimeStart = null;
      this.transferTimeEnd = null;
      this.selectedTimeRange.start_time = null;
      this.selectedTimeRange.end_time = null;
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    },
    updateGroup(groupTitle) {
      axios
        .post("/api/me/updateProdGroups", {
          id: this.editingId,
          title: groupTitle
        })
        .then(resp => {
          this.editingId = null;
          this.refreshStoreProductionGroups();
          this.$toastr.s("Group updated.");
        });
    },
    onAddGroup() {
      if (!this.new_group) {
        return;
      }
      axios
        .post("/api/me/productionGroups", { group: this.new_group })
        .then(response => {
          this.refreshStoreProductionGroups();
          this.new_group = "";
        });
    },
    addAllGroups() {
      this.productionGroupIds = [];
      this.productionGroupOptions.forEach(option => {
        this.productionGroupIds.push(option.value);
      });
    },
    removeAllGroups() {
      this.productionGroupIds = [];
    },
    hideGroups() {
      this.showGroups = false;
    }
  }
};
</script>

<style>
.prodGroupCheckbox {
  background-color: #ffffff;
  -webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15) !important;
  border-radius: 8px !important;
  position: absolute;
  top: 115px;
  width: 190px;
  padding: 20px;
}
</style>
