<template>
  <div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <v-select multiple v-model="selected" :options="deliveryDates"></v-select>
            <button @click="print('meal_quantities', 'pdf')" class="btn btn-primary btn-md form-control">Print Meals Quantity</button>
            <p class="mt-4">Shows how many of each meal is required to be made based on your orders.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <v-select multiple v-model="selected" :options="deliveryDates"></v-select>
            <button @click="print('ingredient_quantities', 'pdf')" class="btn btn-primary btn-md form-control">Print Ingredients Quantity</button>
            <p class="mt-4">Shows how much of each ingredient is needed based on your orders.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <v-select multiple v-model="selected" :options="deliveryDates"></v-select>
            <button @click="print('orders_by_customer', 'pdf')" class="btn btn-primary btn-md form-control">Print Orders</button>
            <p class="mt-4">Shows which meals need to be packaged together for each customer.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <v-select multiple v-model="selected" :options="deliveryDates"></v-select>
            <button @click="print('packing_slips', 'pdf')" class="btn btn-primary btn-md form-control">Print Packing Slips</button>
            <p class="mt-4">Prints meal quantity summaries for each order.</p>
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
    return {};
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
        .get(`/api/me/print/${report}/${format}`)
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