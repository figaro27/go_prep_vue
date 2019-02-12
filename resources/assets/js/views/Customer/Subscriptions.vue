<template>
  <div class="row">
    <div class="col-md-12">
      <b-alert v-if="subscriptions[0]" :show="!!$route.query.created || false" variant="success">
        <p class="center-text mt-3">
          Thank you for your order.
          Your meals will be delivered on
          {{ moment(subscriptions[0].delivery_day, 'E').format('dddd') || '' }}
        </p>
      </b-alert>

      <b-alert :show="0 === subscriptions.length || false" variant="warning">
        <p class="center-text mt-3">You have no meal plans.</p>
      </b-alert>

      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading"/>
          <div v-for="subscription in subscriptions" :key="subscription.id">
            <div v-b-toggle="'collapse' + subscription.id">
              <b-list-group-item>
                <div class="row">
                  <div class="col-md-4">
                    <h4>Meal Plan ID</h4>
                    <p>{{ subscription.stripe_id }}</p>
                  </div>
                  <div class="col-md-4">
                    <h4>Placed On</h4>
                    <p>{{ moment(subscription.created_at).format('dddd, MMM Do, Y') }}</p>
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

                <div class="row">
                  <div class="col-md-4">
                    <h4>Delivery Day</h4>
                    <p v-if="!subscription.latest_order.fulfilled">{{ moment(subscription.latest_order.delivery_date).format('dddd, MMM Do') }}</p>
                    <p v-else>Delivered On: {{ moment(subscription.latest_order.delivery_date).format('dddd, MMM Do') }}</p>
                  </div>
                  <div class="col-md-4">
                    <h4>Company</h4>
                    <p>{{ subscription.store_name }}</p>
                  </div>
                  <div class="col-md-4">
                    <img src="/images/collapse-arrow.png" class="mt-4 pt-3">
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
import Spinner from "../../components/Spinner";

export default {
  components: {
    Spinner
  },
  data() {
    return {
      isLoading: false
    };
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