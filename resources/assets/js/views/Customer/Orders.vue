<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <b-alert
            v-if="orders && orders[0]"
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
                moment(orders[0].delivery_date).format("dddd, MMM Do, Y") || ""
              }}
            </p>
          </b-alert>
          <b-alert
            :show="null !== orders && 0 === orders.length"
            variant="warning"
          >
            <p class="center-text mt-3">You currently have no orders.</p>
          </b-alert>
          <div class="order-list" v-if="null !== orders">
            <div v-for="order in orders" :key="order.id" class="mb-4">
              <div v-b-toggle="'collapse' + order.id">
                <b-list-group-item class="order-list-item">
                  <div class="row">
                    <div class="col-md-4">
                      <h4>Order ID</h4>
                      <p>{{ order.order_number }}</p>
                      <h4>
                        {{ order.pickup ? "Pickup Day" : "Delivery Day" }}
                      </h4>
                      <p v-if="!order.fulfilled">
                        {{ moment(order.delivery_date).format("dddd, MMM Do") }}
                      </p>
                      <p v-else>
                        Delivered On:
                        {{ moment(order.delivery_date).format("dddd, MMM Do") }}
                      </p>
                      <p v-if="order.pickup_location_id != null">
                        {{ order.pickup_location.name }}<br />
                        {{ order.pickup_location.address }},
                        {{ order.pickup_location.city }},
                        {{ order.pickup_location.state }}
                        {{ order.pickup_location.zip }}
                      </p>
                    </div>
                    <div class="col-md-4">
                      <h4>Placed On</h4>
                      <p>
                        {{
                          moment
                            .utc(order.created_at)
                            .local()
                            .format("dddd, MMM Do, Y")
                        }}
                      </p>
                      <h4>Company</h4>
                      <p>{{ order.store_name }}</p>
                    </div>
                    <div class="col-md-4">
                      <h4>Amount</h4>
                      <p>
                        Subtotal: {{ format.money(order.preFeePreDiscount) }}
                      </p>
                      <p class="text-success" v-if="order.couponReduction > 0">
                        Coupon {{ order.couponCode }}: ({{
                          format.money(order.couponReduction)
                        }})
                      </p>
                      <p v-if="order.mealPlanDiscount > 0" class="text-success">
                        Meal Plan Discount: ({{
                          format.money(order.mealPlanDiscount)
                        }})
                      </p>
                      <p v-if="order.deliveryFee > 0">
                        Delivery Fee: {{ format.money(order.deliveryFee) }}
                      </p>
                      <p v-if="order.processingFee > 0">
                        Processing Fee:
                        {{ format.money(order.processingFee) }}
                      </p>
                      <p>Sales Tax: {{ format.money(order.salesTax) }}</p>
                      <p class="strong">
                        Total: {{ format.money(order.amount) }}
                      </p>
                      <img src="/images/collapse-arrow.png" class="mt-2 pt-3" />
                    </div>
                  </div>

                  <b-collapse :id="'collapse' + order.id" class="mt-2">
                    <b-table
                      striped
                      stacked="sm"
                      :items="getMealTableData(order)"
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
                          Subtotal: {{ format.money(order.preFeePreDiscount) }}
                        </p>
                        <p
                          class="text-success"
                          v-if="order.couponReduction > 0"
                        >
                          Coupon {{ order.couponCode }}: ({{
                            format.money(order.couponReduction)
                          }})
                        </p>
                        <p
                          v-if="order.mealPlanDiscount > 0"
                          class="text-success"
                        >
                          Meal Plan Discount: ({{
                            format.money(order.mealPlanDiscount)
                          }})
                        </p>
                        <p v-if="order.deliveryFee > 0">
                          Delivery Fee: {{ format.money(order.deliveryFee) }}
                        </p>
                        <p v-if="order.processingFee > 0">
                          Processing Fee:
                          {{ format.money(order.processingFee) }}
                        </p>
                        <p>Sales Tax: {{ format.money(order.salesTax) }}</p>
                        <p class="strong">
                          Total: {{ format.money(order.amount) }}
                        </p>
                      </template>

                      <template slot="table-caption"></template>
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
    return {};
  },
  computed: {
    ...mapGetters({
      _orders: "orders",
      initialized: "initialized",
      isLoading: "isLoading",
      getStoreMeal: "viewedStoreMeal"
    }),
    orders() {
      if (_.isNull(this._orders)) {
        return null;
      }
      return this._orders.filter(meal => {
        return meal.paid === 1;
      });
    }
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshCustomerOrders"]),
    getMealTableData(order) {
      if (!this.initialized) return [];

      let data = order.items.map(item => {
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
          unit_price: format.money(item.unit_price),
          subtotal: format.money(item.price)
        };
      });

      return _.filter(data);
    }
  }
};
</script>
