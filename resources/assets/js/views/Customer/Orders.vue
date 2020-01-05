<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading" />
        <!-- <b-alert
          v-if="_orders && _orders[0]"
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
                moment(_orders[0].delivery_date).format("dddd, MMM Do, Y") || ""
              }}
            </span>
          </p>
        </b-alert> -->
        <b-alert
          :show="null !== _orders && 0 === _orders.length"
          variant="warning"
        >
          <p class="center-text mt-3">You currently have no orders.</p>
        </b-alert>

        <v-client-table
          :columns="columns"
          :data="tableData"
          :options="options"
          v-show="initialized"
          class="table-countless"
        >
          <div slot="beforeFilter">
            <b-input
              @change="val => (filters.query = val)"
              ref="search"
              lazy
              placeholder="Search"
            />
          </div>

          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format("dddd, MMM Do") }}
          </div>
          <div slot="delivery_date" slot-scope="props">
            <template v-if="!props.row.isMultipleDelivery">{{
              moment(props.row.delivery_date).format("dddd, MMM Do")
            }}</template>
            <template v-else>
              Multiple Dates
              <!-- {{ order.multiple_dates }} -->
            </template>
          </div>
          <div slot="pickup" slot-scope="props">
            {{ props.row.pickup ? "Pickup" : "Delivery" }}
          </div>
          <div slot="dailyOrderNumber" slot-scope="props">
            {{ props.row.dailyOrderNumber }}
          </div>
          <div slot="balance" slot-scope="props">
            <span
              v-if="
                (props.row.balance > 0 || props.row.balance < 0) &&
                  props.row.balance !== null
              "
            >
              <!-- {{
                  ((props.row.balance / props.row.amount) * 100).toFixed(0)
                }}% --->
              {{ format.money(props.row.balance, store.settings.currency) }}
            </span>
            <span v-else>Paid in Full</span>
          </div>
          <div slot="actions" class="text-nowrap" slot-scope="props">
            <button
              class="btn view btn-primary btn-sm"
              @click="viewOrder(props.row.id)"
            >
              View Order
            </button>
          </div>

          <div slot="amount" slot-scope="props">
            <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
          </div>
        </v-client-table>
      </div>
    </div>

    <div class="modal-basic modal-wider">
      <b-modal
        v-if="viewOrderModal"
        v-model="viewOrderModal"
        size="xl"
        title="Order Information"
        no-fade
        no-close-on-backdrop
      >
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-5 pt-1">
            <span v-if="storeModules.dailyOrderNumbers">
              <h4>Daily Order #</h4>
              <p>{{ order.dailyOrderNumber }}</p>
            </span>
            <h4>Order ID</h4>
            <p>{{ order.order_number }}</p>
          </div>
          <div class="col-md-3 pt-1">
            <h4>Placed On</h4>
            <p>{{ moment(order.created_at).format("dddd, MMM Do") }}</p>
            <span v-if="!storeModules.hideTransferOptions" class="mt-2">
              <h4 v-if="!order.pickup">Delivery Day</h4>
              <h4 v-if="order.pickup">Pickup Day</h4>
              <template v-if="!order.isMultipleDelivery">
                {{ moment(order.delivery_date).format("dddd, MMM Do") }}
                <span v-if="order.transferTime">{{ order.transferTime }}</span>
              </template>
              <template v-else>
                <p>{{ order.multiple_dates }}</p>
              </template>
            </span>

            <h4 v-if="order.pickup_location_id" class="mt-3">
              Pickup Location:
            </h4>
            {{ order.pickup_location_name }}
            <br />
          </div>
          <div class="col-md-4 pt-1">
            <h4 v-if="order.cashOrder">
              {{ store.module_settings.cashOrderWording }}
            </h4>
            <p>
              Subtotal:
              {{ format.money(order.preFeePreDiscount, order.currency) }}
            </p>
            <p class="text-success" v-if="order.couponReduction > 0">
              Coupon {{ order.couponCode }}: ({{
                format.money(order.couponReduction, order.currency)
              }})
            </p>
            <p class="text-success" v-if="order.purchasedGiftCardReduction > 0">
              Gift Card {{ order.purchased_gift_card_code }} ({{
                format.money(order.purchasedGiftCardReduction, order.currency)
              }})
            </p>
            <p v-if="order.mealPlanDiscount > 0" class="text-success">
              Subscription Discount: ({{
                format.money(order.mealPlanDiscount, order.currency)
              }})
            </p>
            <p v-if="order.salesTax > 0">
              Sales Tax: {{ format.money(order.salesTax, order.currency) }}
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
              Refunded: {{ format.money(order.refundedAmount, order.currency) }}
            </p>
            <div class="d-inline">
              <p class="d-inline">
                Balance: {{ format.money(order.balance, order.currency) }}
              </p>
            </div>
            <br />
            <br />
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <h4>Items</h4>
            <hr />
            <v-client-table
              v-if="!order.isMultipleDelivery"
              striped
              stacked="sm"
              :columns="columnsMeal"
              :data="getMealTableData(order)"
              ref="mealsTable"
              foot-clone
            >
              <template slot="meal" slot-scope="props">
                <div v-html="props.row.meal"></div>
              </template>

              <template slot="FOOT_subtotal" slot-scope="row">
                <p>
                  Subtotal:
                  {{ format.money(order.preFeePreDiscount, order.currency) }}
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
              ref="mealsTable"
              foot-clone
            >
              <template slot="meal" slot-scope="props">
                <div v-html="props.row.meal"></div>
              </template>

              <template slot="FOOT_subtotal" slot-scope="row">
                <p>
                  Subtotal:
                  {{ format.money(order.preFeePreDiscount, order.currency) }}
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
          </div>
        </div>
        <div
          class="row mt-4"
          v-if="
            viewOrderModal &&
              order.line_items_order &&
              order.line_items_order.length
          "
        >
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
                    <span class="order-quantity">
                      {{ lineItemOrder.quantity }}
                    </span>
                    <img src="/images/store/x-modal.png" class="mr-1 ml-1" />
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
      </b-modal>
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
      order: null,
      viewOrderModal: false,
      columns: [
        "order_number",
        "store_name",
        // "user.user_detail.phone",
        "created_at",
        "delivery_date",
        "pickup",
        "amount",
        "actions"
      ],
      options: {
        filterable: false,
        headings: {
          dailyOrderNumber: "Daily Order #",
          order_number: "Order ID",
          store_name: "Store",
          // "user.user_detail.phone": "Phone",
          created_at: "Order Placed",
          delivery_date: "Delivery Date",
          pickup: "Type",
          amount: "Total",
          balance: "Balance",
          // chargeType: "Charge Type",
          actions: "Actions"
        }
      },
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
      store: "viewedStore",
      _orders: "orders",
      initialized: "initialized",
      isLoading: "isLoading",
      getStoreMeal: "viewedStoreMeal",
      storeModules: "viewedStoreModules"
    }),
    tableData() {
      let orders = this._orders;

      if (orders) {
        orders.forEach(order => {
          if (order.balance && !this.columns.includes("balance")) {
            this.columns.splice(6, 0, "balance");
            return;
          }
        });
      }

      //while(orders.length < this.orders.total) {
      //  orders.push({});
      //}

      return _.isArray(orders) ? orders : [];
    }
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshCustomerOrders", "addJob", "removeJob"]),
    formatMoney: format.money,
    getMealTableData() {
      let data = [];
      let order = this.order;

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
              subtotal:
                item.meal_package_variation && item.price > 0
                  ? "In Package " +
                    "(" +
                    this.store.settings.currency_symbol +
                    item.price +
                    ")"
                  : "In Package"
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
    },
    async viewOrder(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/orders/${id}`)
        .then(response => {
          this.order = response.data;
          this.viewOrderModal = true;
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    }
  }
};
</script>
