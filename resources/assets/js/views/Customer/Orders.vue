<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Orders</div>
        <div class="card-body">
          <Spinner v-if="isLoading"/>
          <div v-for="order in orders" :key="order.id">
            <div v-b-toggle="'collapse' + order.id">
              <b-list-group-item>
                <div class="row">
                  <div class="col-md-4">
                    <h4>Order ID</h4>
                    <p>{{ order.order_number }}</p>
                  </div>
                  <div class="col-md-4">
                    <h4>Placed On</h4>
                    <p>{{ order.created_at }}</p>
                  </div>
                  <div class="col-md-4">
                    <h2>{{ format.money(order.amount) }}</h2>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <h4>Delivery Status</h4>
                    <p v-if="!order.fulfilled">
                      Delivery day: {{ order.delivery_date }}
                    </p>
                    <p v-else>
                      Delivered on: {{ order.delivery_date }}
                    </p>
                  </div>
                </div>

                <b-collapse :id="'collapse' + order.id" class="mt-2">
                  <b-table striped :items="getMealTableData(order)" foot-clone>
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal">
                    </template>

                    <template slot="FOOT_subtotal" slot-scope="row">
                      {{ format.money(order.amount) }}
                    </template>
                  </b-table>
                </b-collapse>
              </b-list-group-item>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from '../../lib/format.js';
import Spinner from "../../components/Spinner";

export default {
  components: {
    Spinner
  },
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      orders: "orders"
    })
  },
  mounted() {
    this.refreshCustomerOrders();
  },
  methods: {
    ...mapActions(["refreshCustomerOrders"]),
    getMealTableData(order) {
      return order.meals.map(meal => {
        return {
          image: meal.featured_image,
          meal: meal.title,
          quantity: meal.pivot.quantity,
          subtotal: format.money(meal.price * meal.pivot.quantity),
        }
      })
    },
  }
};
</script>