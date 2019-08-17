<template>
  <div class="row">
    <div class="col-md-12">
      <b-alert
        v-if="subscriptions && subscriptions[0]"
        :show="!!$route.query.created || false"
        variant="success"
      >
        <p class="center-text mt-3">
          Thank you for your order.
          <span v-if="!!$route.query.pickup"
            >You can pick up your order on</span
          >
          <span v-else>Your meals will be delivered on</span>
          {{
            moment(subscriptions[0].next_delivery_date).format(
              "dddd, MMM Do, Y"
            ) || ""
          }}
        </p>
      </b-alert>

      <b-alert
        v-if="subscriptions && subscriptions[0]"
        :show="!!$route.query.updated || false"
        variant="success"
      >
        <p class="center-text mt-3">
          You have successfully updated your meal plan.
        </p>
      </b-alert>

      <b-alert
        :show="null !== subscriptions && 0 === subscriptions.length"
        variant="warning"
      >
        <p class="center-text mt-3">You have no meal plans.</p>
      </b-alert>

      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <div class="order-list" v-if="null !== subscriptions">
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
                      <h4>
                        {{
                          subscription.pickup ? "Pickup Day" : "Delivery Day"
                        }}
                      </h4>
                      <p
                        v-if="
                          subscription.latest_paid_order &&
                            !subscription.latest_paid_order.fulfilled
                        "
                      >
                        {{
                          moment(subscription.next_delivery_date).format(
                            "dddd, MMM Do"
                          )
                        }}
                        <span v-if="subscription.transferTime">
                          - {{ subscription.transferTime }}</span
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
                    <div
                      class="col-md-4"
                      v-if="subscription.status === 'active'"
                    >
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
                        Meal Plan Discount: ({{
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
                        {{ format.money(subscription.processingFee) }}
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
                      <p class="strong">
                        Total:
                        {{
                          format.money(
                            subscription.amount,
                            subscription.currency
                          )
                        }}
                        per week.
                      </p>
                      <div v-if="subscription.latest_paid_order">
                        <p>
                          <!-- Your order is locked in for the upcoming delivery day
                          of
                          <strong>
                            {{
                              moment(
                                subscription.next_order.delivery_date
                              ).format("dddd, MMM Do")
                            }}.</strong
                          > -->
                          Any changes to this meal plan will be applied to the
                          following order on
                          <strong>
                            {{
                              moment(
                                subscription.latest_paid_order.delivery_date
                              )
                                .add(7, "days")
                                .format("dddd, MMM Do")
                            }}.</strong
                          >
                        </p>
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

                      <!-- <div
                        v-if="
                          subscription.latest_paid_order.delivery_date >
                            moment().format()
                        "
                      >
                        <p>
                          Your order is locked in for this week. You will be
                          able to pause, cancel, or change meals in your meal
                          plan the day after your food gets delivered on
                          {{
                            moment(
                              subscription.latest_paid_order.delivery_date
                            ).format("dddd, MMM Do")
                          }}.
                        </p>
                      </div> -->
                      <img src="/images/collapse-arrow.png" class="mt-4 pt-3" />
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
                    <!-- <div class="row">
                      <div class="col-md-12">
                        <p>
                          Your card will be charged on
                          {{
                            moment(subscription.next_renewal_at).format("dddd")
                          }}. You can Pause, Cancel, or Change Meals up until
                          that time to affect this weeks order. If you Pause,
                          Cancel, or Change Meals after this day, it will be
                          applied to <strong>next week's</strong> order.
                        </p>
                      </div>
                    </div> -->
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
                          Meal Plan Discount: ({{
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
import moment from "moment";

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
      storeSettings: "storeSettings",
      initialized: "initialized",
      getStoreMeal: "viewedStoreMeal"
    })
  },
  mounted() {},
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
        const title = meal.getTitle(true, size, item.components, item.addons);

        return {
          image: meal.image.url_thumb,
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
      this.setBagCoupon(null);
      this.setBagMealPlan(true);

      const items = _.map(subscription.meals, meal => {
        return {
          id: meal.id,
          meal: meal,
          quantity: meal.quantity,
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
