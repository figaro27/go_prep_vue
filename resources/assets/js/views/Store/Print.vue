<template>
  <div>
    <div class="row">



      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text">Order Summary</h4>
            <delivery-date-picker v-model="delivery_dates.orders_by_customer"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how to bag up your meals for each customer.</p>
            <button @click="print('orders_by_customer', 'pdf')" class="btn btn-primary btn-lg center mt-2">Print</button>
          </div>
        </div>
      </div>


      <div class="col-md-6">
        <div class="card">
          <Spinner v-if="isLoading"/>
          <div class="card-body m-4">
            <h4 class="center-text">Meal Orders</h4>
            <delivery-date-picker v-model="delivery_dates.meal_quantities"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how many of each meal to make based on your orders.</p>
            <button @click="print('meal_quantities', 'pdf')" class="btn btn-primary btn-lg center mt-2">Print</button>
          </div>
        </div>
      </div>
      

    </div>
    <div class="row">
      
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text">Packing Slips</h4>
            <delivery-date-picker v-model="delivery_dates.packing_slips"></delivery-date-picker>
            <p class="mt-4 center-text">Show packing slips or order summaries to include in your bag to the customers.</p>
            <button @click="print('packing_slips', 'pdf')" class="btn btn-primary btn-lg center mt-2">Print</button>
          </div>
        </div>
      </div>


      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text">Ingredients</h4>
            <delivery-date-picker v-model="delivery_dates.ingredient_quantities"></delivery-date-picker>
            <p class="mt-4 center-text">Shows how much of each ingredient is needed based on your orders.</p>
            <button @click="print('ingredient_quantities', 'pdf')" class="btn btn-primary btn-lg center mt-2">Print</button>
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
    }
  }
};
</script>