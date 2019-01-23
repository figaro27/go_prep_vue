<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Meal Plans</div>
        <div class="card-body">
          <div v-for="subscription in subscriptions" :key="subscription.id">
            <div v-b-toggle="'collapse' + subscription.id">
              <b-list-group-item>
                <div class="row">
                  <div class="col-md-4">
                    <h4>Meal Plan ID</h4>
                    <p>{{ subscription.id }}</p>
                  </div>
                  <div class="col-md-4">
                    <h4>Placed On</h4>
                    <p>{{ subscription.created_at }}</p>
                  </div>
                  <div class="col-md-4" v-if="subscription.status === 'active'">
                    <h2>{{ format.money(subscription.amount) }} per {{subscription.interval}}</h2>
                    <b-btn variant="danger" @click="() => cancelSubscription(subscription)">Cancel</b-btn>
                  </div>
                  <div class="col-md-4" v-else>
                    <h4>Cancelled On</h4>
                    <p>{{ subscription.cancelled_at }}</p>
                  </div>
                </div>

                <b-collapse :id="'collapse' + subscription.id" class="mt-2">
                  <b-table striped :items="getMealTableData(subscription)" foot-clone>
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal">
                    </template>

                    <template
                      slot="FOOT_subtotal"
                      slot-scope="row"
                    >{{ format.money(subscription.amount) }}</template>
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
import { mapGetters, mapActions } from "vuex";
import format from "../../lib/format.js";

export default {
  components: {},
  data() {
    return {};
  },
  computed: {
    ...mapGetters(["subscriptions"])
  },
  mounted() {
    this.refreshSubscriptions();
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    getOrderTableData(subscription) {
      if (!subscription || !_.isArray(subscription.orders)) {
        return [];
      }

      return subscription.orders.map(order => {
        return {
          date: order.created_at,
          delivery_date: order.delivery_date,
          delivered: order.fulfilled ? "Yes" : "No",
          meals: order.meals
            .map(meal => {
              return meal.title + " x " + meal.pivot.quantity;
            })
            .join(", ")
        };
      });
    },
    getMealTableData(subscription) {
      if (!subscription || !_.isArray(subscription.meals)) {
        return [];
      }

      return subscription.meals.map(meal => {
        return {
          image: meal.featured_image,
          meal: meal.title,
          quantity: meal.pivot.quantity,
          subtotal: format.money(meal.price * meal.pivot.quantity)
        };
      });
    },
    cancelSubscription(subscription) {
      axios.delete(`/api/me/subscriptions/${subscription.id}`).then(resp => {
        this.refreshSubscriptions();
      });
    }
  }
};
</script>