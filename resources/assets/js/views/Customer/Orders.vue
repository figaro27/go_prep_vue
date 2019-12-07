<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading" />
        <b-alert
          v-if="orders && orders[0]"
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
                moment(orders[0].delivery_date).format("dddd, MMM Do, Y") || ""
              }}
            </span>
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
              <div class="order-list-item">
                <div class="row">
                  <div class="col-md-4">
                    <h4>Order ID</h4>
                    <p>{{ order.order_number }}</p>
                    <h4 v-if="storeModules.dailyOrderNumbers">Daily Order #</h4>
                    <p v-if="storeModules.dailyOrderNumbers">
                      {{ order.dailyOrderNumber }}
                    </p>
                    <span v-if="!storeModules.hideTransferOptions">
                      <h4>
                        {{ order.pickup ? "Pickup Day" : "Delivery Day" }}
                      </h4>
                      <template v-if="!order.isMultipleDelivery">
                        <p v-if="!order.fulfilled">
                          {{
                            moment(order.delivery_date).format("dddd, MMM Do")
                          }}
                          <span v-if="order.transferTime">
                            {{ order.transferTime }}</span
                          >
                        </p>
                        <p v-else>
                          Delivered On:
                          {{
                            moment(order.delivery_date).format("dddd, MMM Do")
                          }}
                        </p>
                      </template>
                      <template v-else>
                        <p>{{ order.multiple_dates }}</p>
                      </template>
                      <p v-if="order.pickup_location_id != null">
                        <b>Pickup Location:</b>
                        {{ order.pickup_location.name }},
                        {{ order.pickup_location.address }},
                        {{ order.pickup_location.city }},
                        {{ order.pickup_location.state }}
                        {{ order.pickup_location.zip }}<br />
                        <span v-if="order.pickup_location.instructions">
                          <b>Instructions:</b>
                          {{ order.pickup_location.instructions }}
                        </span>
                      </p>
                    </span>
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
                      Subtotal:
                      {{
                        format.money(order.preFeePreDiscount, order.currency)
                      }}
                    </p>
                    <p class="text-success" v-if="order.couponReduction > 0">
                      Coupon {{ order.couponCode }}: ({{
                        format.money(order.couponReduction, order.currency)
                      }})
                    </p>
                    <p
                      class="text-success"
                      v-if="order.purchasedGiftCardReduction > 0"
                    >
                      Gift Card {{ order.purchased_gift_card_code }} ({{
                        format.money(
                          order.purchasedGiftCardReduction,
                          order.currency
                        )
                      }})
                    </p>
                    <p v-if="order.mealPlanDiscount > 0" class="text-success">
                      Subscription Discount: ({{
                        format.money(order.mealPlanDiscount, order.currency)
                      }})
                    </p>
                    <p v-if="order.salesTax > 0">
                      Sales Tax:
                      {{ format.money(order.salesTax, order.currency) }}
                    </p>
                    <p v-if="order.deliveryFee > 0">
                      Delivery Fee:
                      {{ format.money(order.deliveryFee, order.currency) }}
                    </p>
                    <p v-if="order.processingFee > 0">
                      Processing Fee:
                      {{ format.money(order.processingFee, order.currency) }}
                    </p>

                    <p class="strong">
                      Total: {{ format.money(order.amount, order.currency) }}
                    </p>
                    <p v-if="order.balance !== null">
                      Original Total:
                      {{ format.money(order.originalAmount, order.currency) }}
                    </p>
                    <p v-if="order.chargedAmount">
                      Additional Charges:
                      {{ format.money(order.chargedAmount, order.currency) }}
                    </p>
                    <p v-if="order.refundedAmount">
                      Refunded:
                      {{ format.money(order.refundedAmount, order.currency) }}
                    </p>
                    <p v-if="order.balance !== null">
                      Balance: {{ format.money(order.balance, order.currency) }}
                    </p>
                    <img src="/images/collapse-arrow.png" class="mt-2 pt-3" />
                  </div>
                </div>

                <b-collapse :id="'collapse' + order.id" class="mt-2">
                  <v-client-table
                    v-if="!order.isMultipleDelivery"
                    striped
                    stacked="sm"
                    :columns="columnsMeal"
                    :data="getMealTableData(order)"
                    foot-clone
                  >
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal" />
                    </template>

                    <template slot="meal" slot-scope="props">
                      <div v-html="props.row.meal"></div>
                    </template>

                    <template slot="FOOT_subtotal" slot-scope="row">
                      <p>
                        Subtotal:
                        {{
                          format.money(order.preFeePreDiscount, order.currency)
                        }}
                      </p>
                      <p class="text-success" v-if="order.couponReduction > 0">
                        Coupon {{ order.couponCode }}: ({{
                          format.money(order.couponReduction, order.currency)
                        }})
                      </p>
                      <p v-if="order.mealPlanDiscount > 0" class="text-success">
                        Subscription Discount: ({{
                          format.money(order.mealPlanDiscount, order.currency)
                        }})
                      </p>
                      <p v-if="order.deliveryFee > 0">
                        Delivery Fee:
                        {{ format.money(order.deliveryFee, order.currency) }}
                      </p>
                      <p v-if="order.processingFee > 0">
                        Processing Fee:
                        {{ format.money(order.processingFee, order.currency) }}
                      </p>
                      <p v-if="order.salesTax > 0">
                        Sales Tax:
                        {{ format.money(order.salesTax, order.currency) }}
                      </p>
                      <p class="strong">
                        Total:
                        {{ format.money(order.amount, order.currency) }}
                      </p>
                    </template>

                    <template slot="table-caption"></template>
                  </v-client-table>

                  <v-client-table
                    v-if="order.isMultipleDelivery"
                    striped
                    stacked="sm"
                    :columns="columnsMealMultipleDelivery"
                    :data="getMealTableData(order)"
                    foot-clone
                  >
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal" />
                    </template>

                    <template slot="meal" slot-scope="props">
                      <div v-html="props.row.meal"></div>
                    </template>

                    <template slot="FOOT_subtotal" slot-scope="row">
                      <p>
                        Subtotal:
                        {{
                          format.money(order.preFeePreDiscount, order.currency)
                        }}
                      </p>
                      <p class="text-success" v-if="order.couponReduction > 0">
                        Coupon {{ order.couponCode }}: ({{
                          format.money(order.couponReduction, order.currency)
                        }})
                      </p>
                      <p v-if="order.mealPlanDiscount > 0" class="text-success">
                        Subscription Discount: ({{
                          format.money(order.mealPlanDiscount, order.currency)
                        }})
                      </p>
                      <p v-if="order.deliveryFee > 0">
                        Delivery Fee:
                        {{ format.money(order.deliveryFee, order.currency) }}
                      </p>
                      <p v-if="order.processingFee > 0">
                        Processing Fee:
                        {{ format.money(order.processingFee, order.currency) }}
                      </p>
                      <p v-if="order.salesTax > 0">
                        Sales Tax:
                        {{ format.money(order.salesTax, order.currency) }}
                      </p>
                      <p class="strong">
                        Total:
                        {{ format.money(order.amount, order.currency) }}
                      </p>
                    </template>

                    <template slot="table-caption"></template>
                  </v-client-table>
                </b-collapse>
                <div class="row mt-4" v-if="order.line_items_order.length">
                  <div class="col-md-12">
                    <h4>Extras</h4>
                    <hr />
                    <ul class="meal-quantities">
                      <li
                        v-for="lineItemOrder in order.line_items_order"
                        v-bind:key="lineItemOrder.id"
                      >
                        <div class="row">
                          <div class="col-md-3">
                            <span class="order-quantity">{{
                              lineItemOrder.quantity
                            }}</span>
                            <img
                              src="/images/store/x-modal.png"
                              class="mr-1 ml-1"
                            />
                          </div>
                          <div class="col-md-9">
                            <p class="mt-1">{{ lineItemOrder.title }}</p>
                            <p class="strong">
                              {{
                                format.money(
                                  lineItemOrder.price * lineItemOrder.quantity,
                                  order.currency
                                )
                              }}
                            </p>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
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
import store from "../../store";

export default {
  components: {
    Spinner
  },
  data() {
    return {
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      columnsMealMultipleDelivery: [
        "delivery_date",
        "size",
        "meal",
        "quantity",
        "Unit_Price",
        "subtotal"
      ]
    };
  },
  computed: {
    ...mapGetters({
      _orders: "orders",
      initialized: "initialized",
      isLoading: "isLoading",
      getStoreMeal: "viewedStoreMeal",
      storeModules: "viewedStoreModules"
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
      if (!this.initialized || !order.items) return [];

      let data = [];

      order.meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            delivery_date: moment(meal_package_item.delivery_date).format(
              "dddd, MMM Do"
            ),
            size: meal_package_item.meal_package.default_size_title,
            meal: meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            )
          });
        } else {
          data.push({
            delivery_date: moment(meal_package_item.delivery_date).format(
              "dddd, MMM Do"
            ),
            size: meal_package_item.meal_package_size.title,
            meal: meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            )
          });
        }

        order.items.forEach(item => {
          if (
            item.meal_package_order_id === meal_package_item.id &&
            !item.hidden
          ) {
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
              item.special_instructions,
              false
            );

            data.push({
              delivery_date: moment(item.delivery_date).format("dddd, MMM Do"),
              size: size ? size.title : meal.default_size_title,
              //meal: meal.title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal: "In Package"
            });
          }
        });
      });

      order.items.forEach(item => {
        if (item.meal_package_order_id === null && !item.hidden) {
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
            item.special_instructions,
            false
          );

          data.push({
            delivery_date: moment(item.delivery_date).format("dddd, MMM Do"),
            size: size ? size.title : meal.default_size_title,
            //meal: meal.title,
            meal: title,
            quantity: item.quantity,
            unit_price:
              item.attached || item.free
                ? "Included"
                : format.money(item.unit_price, order.currency),
            subtotal:
              item.attached || item.free
                ? "Included"
                : format.money(item.price, order.currency)
          });
        }
      });

      order.purchased_gift_cards.forEach(purchasedGiftCard => {
        data.push({
          meal: "Gift Card Code: " + purchasedGiftCard.code,
          quantity: 1,
          unit_price: format.money(purchasedGiftCard.amount, order.currency),
          subtotal: format.money(purchasedGiftCard.amount, order.currency)
        });
      });

      return _.filter(data);
    }
  }
};
</script>
