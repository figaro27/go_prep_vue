<template>
  <div class="row">
    <div class="col-md-12">
      <b-alert
        v-if="subscriptions[0]"
        :show="!!$route.query.created || false"
        variant="success"
      >
        <p class="center-text mt-3">
          Thank you for your order.
          <span v-if="!!$route.query.pickup"
            >You can pick up your order on</span
          >
          <span v-else>Your meals will be delivered on</span>
          {{ moment(subscriptions[0].delivery_day, "E").format("dddd") || "" }}
        </p>
      </b-alert>

      <b-alert
        v-if="subscriptions[0]"
        :show="!!$route.query.updated || false"
        variant="success"
      >
        <p class="center-text mt-3">
          You have successfully updated your meal plan.
        </p>
      </b-alert>

      <b-alert :show="0 === subscriptions.length || false" variant="warning">
        <p class="center-text mt-3">You have no meal plans.</p>
      </b-alert>

      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <div class="order-list">
            <div
              v-for="subscription in subscriptions"
              :key="subscription.id"
              class="mb-4"
            >
              <div v-b-toggle="'collapse' + subscription.id">
                <b-list-group-item class="order-list-item">
                  <div class="row">
                    <div class="col-md-4">
                      <h4>Meal Plan ID</h4>
                      <p>{{ subscription.stripe_id }}</p>
                    </div>
                    <div class="col-md-4">
                      <h4>Placed On</h4>
                      <p>
                        {{
                          moment
                            .utc(subscription.created_at)
                            .local()
                            .format("dddd, MMM Do, Y")
                        }}
                      </p>
                    </div>
                    <div
                      class="col-md-4"
                      v-if="subscription.status === 'active'"
                    >
                      <h2>
                        {{ format.money(subscription.amount) }} per
                        {{ subscription.interval }}
                      </h2>
                      <b-btn
                        variant="warning"
                        @click.stop="() => pauseSubscription(subscription)"
                        >Pause</b-btn
                      >
                      <b-btn
                        variant="danger"
                        @click.stop="() => cancelSubscription(subscription)"
                        >Cancel</b-btn
                      >
                      <b-btn
                        variant="success"
                        @click.stop="() => editSubscription(subscription)"
                        >Change Meals</b-btn
                      >
                    </div>
                    <div
                      class="col-md-4"
                      v-else-if="subscription.status === 'paused'"
                    >
                      <b-btn
                        variant="warning"
                        @click.stop="() => resumeSubscription(subscription)"
                        >Resume</b-btn
                      >
                    </div>
                    <div class="col-md-4" v-else>
                      <h4>Cancelled On</h4>
                      <p>
                        {{
                          moment
                            .utc(subscription.cancelled_at)
                            .local()
                            .format("dddd, MMM Do, Y")
                        }}
                      </p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4">
                      <h4>Delivery Day</h4>
                      <p v-if="!subscription.latest_order.fulfilled">
                        {{
                          moment(
                            subscription.latest_order.delivery_date
                          ).format("dddd, MMM Do")
                        }}
                      </p>
                      <p v-else>
                        Delivered On:
                        {{
                          moment(
                            subscription.latest_order.delivery_date
                          ).format("dddd, MMM Do")
                        }}
                      </p>
                    </div>
                    <div class="col-md-4">
                      <h4>Company</h4>
                      <p>{{ subscription.store_name }}</p>
                    </div>
                    <div class="col-md-4">
                      <img src="/images/collapse-arrow.png" class="mt-4 pt-3" />
                    </div>
                  </div>

                  <b-collapse :id="'collapse' + subscription.id" class="mt-2">
                    <div class="row">
                      <div class="col-md-12">
                        <p>
                          Your card will be charged on
                          {{
                            momentTimezone
                              .tz(
                                subscription.charge_time,
                                storeSettings.timezone
                              )
                              .format("dddd")
                          }}. You can Pause, Cancel, or Change Meals up until
                          that time to affect this weeks order. If you Pause,
                          Cancel, or Change Meals after this day, it will be
                          applied to <strong>next week's</strong> order.
                        </p>
                      </div>
                    </div>
                    <b-table
                      striped
                      stacked="sm"
                      :items="getMealTableData(subscription)"
                      foot-clone
                    >
                      <template slot="image" slot-scope="row">
                        <img :src="row.value" class="modalMeal" />
                      </template>

                      <template slot="FOOT_subtotal" slot-scope="row">
                        <p>
                          Subtotal:
                          {{ format.money(subscription.preFeePreDiscount) }}
                        </p>
                        <p v-if="subscription.mealPlanDiscount > 0">
                          Meal Plan Discount:
                          <span class="text-success"
                            >({{
                              format.money(subscription.mealPlanDiscount)
                            }})</span
                          >
                        </p>
                        <p v-if="subscription.deliveryFee > 0">
                          Delivery Fee:
                          {{ format.money(subscription.deliveryFee) }}
                        </p>
                        <p v-if="subscription.processingFee > 0">
                          Processing Fee:
                          {{ format.money(subscription.processingFee) }}
                        </p>
                        <p>
                          Sales Tax: {{ format.money(subscription.salesTax) }}
                        </p>
                        <p>
                          <strong
                            >Total:
                            {{ format.money(subscription.amount) }}</strong
                          >
                        </p>
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
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
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
    ...mapGetters(["subscriptions"]),
    ...mapGetters({
      storeSettings: "storeSettings"
    })
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    ...mapMutations(["emptyBag", "addBagItems"]),
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
          image: meal.image.url,
          meal: meal.title,
          quantity: meal.pivot.quantity,
          subtotal: format.money(meal.price * meal.pivot.quantity)
        };
      });
    },
    async pauseSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/pause`
        );
        this.$toastr.s("Meal Plan paused!");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to pause Meal Plan"
        );
      }

      this.refreshSubscriptions();
    },
    async resumeSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/resume`
        );
        this.$toastr.s("Meal Plan resumed!");
      } catch (e) {
        this.$toastr.e(e.response.data.error);
      }

      this.refreshSubscriptions();
    },
    async cancelSubscription(subscription) {
      try {
        const resp = await axios.delete(
          `/api/me/subscriptions/${subscription.id}`
        );
        this.$toastr.s("Meal Plan cancelled!");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to cancel Meal Plan"
        );
      }

      this.refreshSubscriptions();
    },
    editSubscription(subscription) {
      this.emptyBag();

      const items = _.map(subscription.meals, meal => {
        return {
          id: meal.id,
          meal: meal,
          quantity: meal.pivot.quantity,
          added: moment().unix()
        };
      });
      this.addBagItems(items);
      window.location = `${subscription.store.url}/customer/meal-plans/${
        subscription.id
      }`;
    }
  }
};
</script>
