<template>
  <div>
    <div class="row">



      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Orders Summary</h4>
            <delivery-date-picker v-model="delivery_dates.orders_by_customer"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how to bag up your meals for each customer.</p>
            <div class="row">
              <div class="col-md-6">
                <button @click="print('orders_by_customer', 'pdf')" class="btn btn-primary btn-md center mt-2 pull-right">Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('orders_by_customer', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('orders_by_customer', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('orders_by_customer', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-md-6">
        <div class="card">
          <Spinner v-if="isLoading"/>
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Meal Orders</h4>
            <delivery-date-picker v-model="delivery_dates.meal_quantities"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how many of each meal to make based on your orders.</p>
            <div class="row">
              <div class="col-md-6">
                <button @click="print('meal_quantities', 'pdf')" class="btn btn-primary btn-md center mt-2 pull-right">Print</button>
              </div>
              <div class="col-md-6">
              <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                <b-dropdown-item @click="exportData('meal_quantities', 'csv')">CSV</b-dropdown-item>
                <b-dropdown-item @click="exportData('meal_quantities', 'xls')">XLS</b-dropdown-item>
                <b-dropdown-item @click="exportData('meal_quantities', 'pdf')">PDF</b-dropdown-item>
              </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
      

    </div>
    <div class="row">
      
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Packing Slips</h4>
            <delivery-date-picker v-model="delivery_dates.packing_slips"></delivery-date-picker>
            <p class="mt-4 center-text">Show packing slips or order summaries to include in your bag to the customers.</p>
            <div class="row">
              <div class="col-md-6">
                <button @click="print('packing_slips', 'pdf')" class="btn btn-primary btn-md center mt-2 pull-right">Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('packing_slips', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('packing_slips', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('packing_slips', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Ingredients</h4>
            <delivery-date-picker v-model="delivery_dates.ingredient_quantities"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how much of each ingredient is needed based on your orders.</p>
            <div class="row">
              <div class="col-md-6">
                <button @click="print('ingredient_quantities', 'pdf')" class="btn btn-primary btn-md center mt-2 pull-right">Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>



    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import vSelect from 'vue-select'
import Spinner from "../../components/Spinner";

export default {
  components: {
    vSelect,
    Spinner
  },
  data() {
    return {
      delivery_dates: {
        meal_quantities: [],
        ingredient_quantities: [],
        orders_by_customer: [],
        packing_slips: [],
      },
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading"
    }),
    selected(){
      return this.deliveryDates;
    },
    deliveryDates(){
      let grouped = [];
      this.orders.forEach(order => {
          if (!_.includes(grouped, order.delivery_date)) {
            grouped.push(order.delivery_date);
        }
      });
      this.deliveryDate = grouped[0];
      return grouped;
    },
  },
  mounted() {},
  methods: {
    print(report, format = "pdf") {
      axios
        .get(`/api/me/print/${report}/${format}`, {
          params: {
            delivery_dates: this.delivery_dates[report]
          }
        })
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            win.addEventListener('load', () => {
              win.print();
            }, false);
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    },
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