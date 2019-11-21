f<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="row">
      <div class="col-md-12">
        <b-alert
          v-if="activeSubscriptions && activeSubscriptions[0]"
          :show="!!$route.query.created || false"
          variant="success"
        >
          <p class="center-text mt-3">
            Thank you for your order.
            <span v-if="!storeModules.hideTransferOptions">
              <span v-if="!!$route.query.pickup"
                >You can pick up your order on</span
              >
              <span v-else>Your order will be delivered on</span>
              {{
                moment(activeSubscriptions[0].next_delivery_date).format(
                  "dddd, MMM Do, Y"
                ) || ""
              }}
            </span>
          </p>
        </b-alert>

        <b-alert
          v-if="activeSubscriptions && activeSubscriptions[0]"
          :show="!!$route.query.updated || false"
          variant="success"
        >
          <p class="center-text mt-3">
            You have successfully updated your subscription.
          </p>
        </b-alert>

        <b-alert
          :show="
            null !== activeSubscriptions && 0 === activeSubscriptions.length
          "
          variant="warning"
        >
          <p class="center-text mt-3">You have no active subscriptions.</p>
        </b-alert>

        <Spinner v-if="isLoading" />
        <div class="order-list" v-if="null !== activeSubscriptions">
          <div
            v-for="subscription in activeSubscriptions"
            :key="subscription.id"
            class="mb-4"
          >
            <div v-b-toggle="'collapse' + subscription.id" class="mb-4">
              <div class="order-list-item">
                <div class="row">
                  <div class="col-md-4">
                    <h4>Subscription ID</h4>
                    <p>{{ subscription.stripe_id }}</p>
                    <h4>Interval</h4>
                    <p>{{ subscription.interval_title }}</p>
                    <span v-if="!storeModules.hideTransferOptions">
                      <h4>
                        {{
                          subscription.pickup ? "Pickup Day" : "Delivery Day"
                        }}
                      </h4>
                      <p
                        v-if="
                          subscription.latest_order &&
                            !subscription.latest_order.fulfilled &&
                            subscription.next_order
                        "
                      >
                        {{
                          moment(subscription.next_order.delivery_date).format(
                            "dddd, MMM Do"
                          )
                        }}
                        <span v-if="subscription.transferTime">
                          {{ subscription.transferTime }}</span
                        >
                      </p>
                      <p v-else-if="subscription.latest_order">
                        Delivered On:
                        {{
                          moment(
                            subscription.latest_paid_order.delivery_date
                          ).format("dddd, MMM Do")
                        }}
                      </p>
                    </span>
                    <p v-if="subscription.pickup_location_id != null">
                      {{ subscription.pickup_location.name }}<br />
                      {{ subscription.pickup_location.address }},
                      {{ subscription.pickup_location.city }},
                      {{ subscription.pickup_location.state }}
                      {{ subscription.pickup_location.zip }}
                    </p>
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
                    <h4>Company</h4>
                    <p>{{ subscription.store_name }}</p>
                  </div>
                  <div class="col-md-4" v-if="subscription.status === 'active'">
                    <h4>Amount</h4>
                    <p>
                      Subtotal:
                      {{
                        format.money(
                          subscription.preFeePreDiscount,
                          subscription.currency
                        )
                      }}
                    </p>
                    <p
                      class="text-success"
                      v-if="subscription.couponReduction > 0"
                    >
                      Coupon {{ subscription.couponCode }}: ({{
                        format.money(
                          subscription.couponReduction,
                          subscription.currency
                        )
                      }})
                    </p>
                    <p
                      v-if="subscription.mealPlanDiscount > 0"
                      class="text-success"
                    >
                      Subscription Discount: ({{
                        format.money(
                          subscription.mealPlanDiscount,
                          subscription.currency
                        )
                      }})
                    </p>
                    <p v-if="subscription.salesTax > 0">
                      Sales Tax:
                      {{
                        format.money(
                          subscription.salesTax,
                          subscription.currency
                        )
                      }}
                    </p>
                    <p v-if="subscription.deliveryFee > 0">
                      Delivery Fee:
                      {{
                        format.money(
                          subscription.deliveryFee,
                          subscription.currency
                        )
                      }}
                    </p>
                    <p v-if="subscription.processingFee > 0">
                      Processing Fee:
                      {{ format.money(subscription.processingFee) }}
                    </p>

                    <p class="strong">
                      Total:
                      {{
                        format.money(subscription.amount, subscription.currency)
                      }}
                      per {{ subscription.interval }}.
                    </p>
                    <div v-if="subscription.latest_paid_order">
                      <p>
                        <span v-if="!storeModules.hideTransferOptions">
                          Any changes to this subscription will be applied to
                          the following order on
                          <strong>
                            {{
                              moment(subscription.latest_paid_order.created_at)
                                .add(getIntervalDays(subscription), "days")
                                .format("dddd, MMM Do")
                            }}</strong
                          >
                          for the delivery date of
                          <strong>
                            {{
                              moment(
                                subscription.latest_paid_order.delivery_date
                              )
                                .add(getIntervalDays(subscription), "days")
                                .format("dddd, MMM Do")
                            }}
                          </strong>
                        </span>
                        <span v-else>
                          Any changes to this subscription will be applied to
                          next {{ subscription.interval }}'s order.
                        </span>
                      </p>
                      <!--Removing pause functionality for the time being -->
                      <!-- <b-btn
                        variant="warning"
                        @click.stop="() => pauseSubscription(subscription)"
                        >Pause</b-btn
                      >
                      <div
                    class="col-md-4"
                    v-else-if="subscription.status === 'paused'"
                  >
                    <b-btn
                      variant="warning"
                      @click.stop="() => resumeSubscription(subscription)"
                      >Resume</b-btn
                    >
                    <img src="/images/collapse-arrow.png" class="mt-4 pt-3" />
                  </div>

                    -->
                      <b-btn
                        variant="danger"
                        @click.stop="() => cancelSubscription(subscription)"
                        >Cancel</b-btn
                      >
                      <router-link
                        :to="`/customer/subscriptions/${subscription.id}`"
                      >
                        <b-btn variant="success">Edit Subscription</b-btn>
                      </router-link>
                    </div>
                    <img src="/images/collapse-arrow.png" class="mt-4 pt-3" />
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
                    <img src="/images/collapse-arrow.png" class="mt-4 pt-3" />
                  </div>
                </div>

                <b-collapse :id="'collapse' + subscription.id" class="mt-2">
                  <b-table
                    striped
                    stacked="sm"
                    :items="getMealTableData(subscription)"
                    foot-clone
                  >
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal" />
                    </template>

                    <template slot="meal" slot-scope="row">
                      <div v-html="row.value"></div>
                    </template>

                    <template slot="FOOT_subtotal" slot-scope="row">
                      <p>
                        Subtotal:
                        {{
                          format.money(
                            subscription.preFeePreDiscount,
                            subscription.currency
                          )
                        }}
                      </p>
                      <p
                        class="text-success"
                        v-if="subscription.couponReduction > 0"
                      >
                        Coupon {{ subscription.couponCode }}: ({{
                          format.money(
                            subscription.couponReduction,
                            subscription.currency
                          )
                        }})
                      </p>
                      <p
                        v-if="subscription.mealPlanDiscount > 0"
                        class="text-success"
                      >
                        Subscription Discount: ({{
                          format.money(
                            subscription.mealPlanDiscount,
                            subscription.currency
                          )
                        }})
                      </p>
                      <p v-if="subscription.deliveryFee > 0">
                        Delivery Fee:
                        {{
                          format.money(
                            subscription.deliveryFee,
                            subscription.currency
                          )
                        }}
                      </p>
                      <p v-if="subscription.processingFee > 0">
                        Processing Fee:
                        {{
                          format.money(
                            subscription.processingFee,
                            subscription.currency
                          )
                        }}
                      </p>
                      <p>
                        Sales Tax:
                        {{
                          format.money(
                            subscription.salesTax,
                            subscription.currency
                          )
                        }}
                      </p>
                      <p class="strong">
                        Total:
                        {{
                          format.money(
                            subscription.amount,
                            subscription.currency
                          )
                        }}
                      </p>
                    </template>
                  </b-table>
                </b-collapse>
                <hr />
                <div class="space-divider-20"></div>
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
import moment from "moment";
import store from "../../store";

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
    ...mapGetters({
      subscriptions: "subscriptions",
      storeSettings: "storeSettings",
      initialized: "initialized",
      getStoreMeal: "viewedStoreMeal",
      storeModules: "viewedStoreModules",
      isLazy: "isLazy"
    }),
    activeSubscriptions() {
      if (this.subscriptions)
        return this.subscriptions.filter(
          subscription => subscription.status != "cancelled"
        );
    }
  },
  mounted() {
    if (!this.isLazy) {
      store.dispatch("refreshLazy");
    }
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    ...mapMutations([
      "emptyBag",
      "addBagItems",
      "setBagMealPlan",
      "setBagCoupon"
    ]),
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
      if (!this.initialized) return [];

      let data = subscription.items.map(item => {
        const meal = this.getStoreMeal(item.meal_id);
        if (!meal) {
          return null;
        }

        const size = meal.getSize(item.meal_size_id);
        const title = meal.getTitle(
          true,
          size,
          item.components,
          item.addons,
          item.special_instructions
        );

        let image = null;
        if (meal.image != null) image = meal.image.url_thumb;

        return {
          image: image,
          meal: title,
          quantity: item.quantity,
          unit_price: format.money(item.unit_price, subscription.currency),
          subtotal: format.money(item.price, subscription.currency)
        };
      });

      return _.filter(data);
    },
    async pauseSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/pause`
        );
        this.$toastr.s("Subscription paused.");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to pause Subscription"
        );
      }

      this.refreshSubscriptions();
    },
    async resumeSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/resume`
        );
        this.$toastr.s("Subscription resumed.");
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
        this.$toastr.s("Subscription cancelled.");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to cancel Subscription"
        );
      }

      this.refreshSubscriptions();
    },
    getIntervalDays(subscription) {
      if (subscription.interval === "week") {
        return 7;
      }
      if (subscription.interval === "month") {
        return 30;
      }
    }
  }
};
</script>
