<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <!--<Spinner v-if="isLoading" />!-->

          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="initialized"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="table-before d-flex flex-wrap align-items-center">
                <div class="d-inline-block mb-2 mb-md-0 mr-2 flex-grow-0">
                  <!-- <b-btn
                    @click="$set(filters, 'has_notes', !filters.has_notes)"
                    :selected="filters.has_notes"
                    variant="primary"
                    class="filter-btn"
                    >Filter Notes</b-btn
                  > -->
                </div>
                <div class="d-inline-block mr-2 flex-grow-0">
                  <!-- <b-btn
                    @click="showFulfilledOrders()"
                    :selected="filters.fulfilled"
                    variant="warning"
                    class="filter-btn"
                    v-if="!filters.fulfilled"
                    >View Completed Orders</b-btn
                  >
                  <b-btn
                    @click="showUnfulfilledOrders()"
                    :selected="filters.fulfilled"
                    variant="danger"
                    class="filter-btn"
                    v-if="filters.fulfilled"
                    >View Open Orders</b-btn
                  > -->

                  <!--<router-link
                    to="/store/manual-order"
                    v-if="storeModules.manualOrders"
                  >
                    <b-btn class="btn btn-success filter-btn"
                      >Create Manual Order</b-btn
                    >
                  </router-link>!-->

                  <router-link
                    v-if="storeModules.manualOrders"
                    :to="{
                      name: 'store-manual-order',
                      params: {
                        storeView: true,
                        manualOrder: true,
                        forceValue: true
                      }
                    }"
                  >
                    <b-btn class="btn btn-success filter-btn"
                      >Create Manual Order</b-btn
                    >
                  </router-link>
                </div>

                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  @change="onChangeDateFilter"
                  class="mt-3 mt-sm-0"
                  ref="deliveryDates"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn
                variant="warning"
                @click="exportData('packing_slips', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Packing Slips
              </b-btn>
              <b-btn
                variant="success"
                @click="exportData('orders_by_customer', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Orders Summary
              </b-btn>
              <b-btn
                variant="primary"
                @click="exportData('orders', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Orders
              </b-btn>
              <b-dropdown class="mx-1 mt-2 mt-sm-0" right text="Export as">
                <b-dropdown-item @click="exportData('orders', 'csv')"
                  >CSV</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('orders', 'xls')"
                  >XLS</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('orders', 'pdf')"
                  >PDF</b-dropdown-item
                >
              </b-dropdown>
            </span>

            <div slot="icons" class="text-nowrap" slot-scope="props">
              <p>
                <i
                  v-if="props.row.manual"
                  class="fas fa-hammer text-primary"
                  v-b-popover.hover.top="
                    'This is a manual order created by you for your customer.'
                  "
                ></i>
                <i
                  v-if="props.row.adjusted"
                  class="fas fa-asterisk text-warning"
                  v-b-popover.hover.top="'This order was adjusted.'"
                ></i>
                <i
                  v-if="props.row.cashOrder"
                  class="fas fa-money-bill text-success"
                  v-b-popover.hover.top="
                    'A credit card wasn\'t processed through GoPrep for this order.'
                  "
                ></i>
                <i
                  v-if="props.row.notes"
                  class="fas fa-sticky-note text-muted"
                  v-b-popover.hover.top="'This order has notes.'"
                ></i>
                <i
                  v-if="props.row.refundedAmount === props.row.originalAmount"
                  class="fas fa-undo-alt purple"
                  v-b-popover.hover.top="'This order was refunded fully.'"
                ></i>
                <i
                  v-if="
                    props.row.refundedAmount &&
                      props.row.refundedAmount < props.row.originalAmount
                  "
                  class="fas fa-undo-alt purple"
                  v-b-popover.hover.top="'This order was refunded partially.'"
                ></i>
                <i
                  v-if="props.row.voided"
                  class="fas fa-ban text-danger"
                  v-b-popover.hover.top="
                    'This order was voided and taken out of your reports.'
                  "
                ></i>
                <i
                  v-if="props.row.subscription_id"
                  class="fa fa-shopping-cart orange-text"
                  v-b-popover.hover.top="
                    'This order was created from an active subscription.'
                  "
                ></i>
              </p>
            </div>
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format("dddd, MMM Do") }}
            </div>
            <div slot="delivery_date" slot-scope="props">
              <template v-if="!props.row.isMultipleDelivery">
                {{ moment(props.row.delivery_date).format("dddd, MMM Do") }}
              </template>
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
                ><!-- {{
                  ((props.row.balance / props.row.amount) * 100).toFixed(0)
                }}% - -->
                {{
                  format.money(props.row.balance, storeSettings.currency)
                }}</span
              >
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
    </div>

    <div class="modal-basic modal-wider">
      <b-modal
        v-model="viewOrderModal"
        size="xl"
        title="Order Information"
        no-fade
        no-close-on-backdrop
      >
        <div class="row">
          <div class="col-md-12 light-background">
            <div class="text-nowrap">
              <p>
                <i
                  v-if="order.manual"
                  class="fas fa-hammer text-primary"
                  v-b-popover.hover.top="
                    'This is a manual order created by you for your customer.'
                  "
                ></i>
                <i
                  v-if="order.adjusted"
                  class="fas fa-asterisk text-warning"
                  v-b-popover.hover.top="'This order was adjusted.'"
                ></i>
                <i
                  v-if="order.cashOrder"
                  class="fas fa-money-bill text-success"
                  v-b-popover.hover.top="
                    'A credit card wasn\'t processed through GoPrep for this order.'
                  "
                ></i>
                <i
                  v-if="order.notes"
                  class="fas fa-sticky-note text-muted"
                  v-b-popover.hover.top="'This order has notes.'"
                ></i>
                <i
                  v-if="order.refundedAmount === order.originalAmount"
                  class="fas fa-undo-alt purple"
                  v-b-popover.hover.top="'This order was refunded fully.'"
                ></i>
                <i
                  v-if="
                    order.refundedAmount &&
                      order.refundedAmount < order.originalAmount
                  "
                  class="fas fa-undo-alt purple"
                  v-b-popover.hover.top="'This order was refunded partially.'"
                ></i>
                <i
                  v-if="order.voided"
                  class="fas fa-ban text-danger"
                  v-b-popover.hover.top="
                    'This order was voided and taken out of your reports.'
                  "
                ></i>
                <i
                  v-if="order.subscription_id"
                  class="fa fa-shopping-cart orange-text"
                  v-b-popover.hover.top="
                    'This order was created from an active subscription.'
                  "
                ></i>
              </p>
            </div>
          </div>
        </div>
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-5 pt-1">
            <span v-if="storeModules.dailyOrderNumbers">
              <h4>Daily Order #</h4>
              <p>{{ order.dailyOrderNumber }}</p>
            </span>
            <h4>Order ID</h4>
            <p>{{ order.order_number }}</p>
            <div
              class="d-inline"
              v-if="
                !order.cashOrder &&
                  store.settings.payment_gateway !== 'authorize'
              "
            >
              <b-form-checkbox v-model="applyToBalanceCharge"
                >Apply Charge to Balance</b-form-checkbox
              ><br />
              <b-btn
                class="btn mb-2 d-inline mr-1"
                variant="success"
                @click="charge"
                >Charge</b-btn
              >
              <b-form-input
                v-model="chargeAmount"
                placeholder="$0.00"
                class="d-inline width-100"
              ></b-form-input>
              <img
                v-b-popover.hover="
                  'Charges allow you to charge your customer directly for any balance on the order. As of right now, you can\'t refund additional charges, only the Original Total.'
                "
                title="Charges"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
            <br />
            <div
              class="d-inline"
              v-if="
                !order.cashOrder &&
                  store.settings.payment_gateway !== 'authorize'
              "
            >
              <b-form-checkbox v-model="applyToBalanceRefund"
                >Apply Refund to Balance</b-form-checkbox
              ><br />
              <b-btn
                :disabled="fullyRefunded"
                class="btn mb-2 d-inline mr-1 purpleBG"
                @click="refund"
                >Refund</b-btn
              >
              <b-form-input
                v-model="refundAmount"
                placeholder="$0.00"
                class="d-inline width-100"
              ></b-form-input>
              <img
                v-b-popover.hover="
                  'Refund your customer partially or fully. As of right now, you can only refund up to the Original Total, not any additional charges. Refunds take 5-10 days to show on your customer\'s statements.'
                "
                title="Refunds"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
            <div>
              <router-link
                :to="{
                  name: 'store-adjust-order',
                  params: { orderId: orderId }
                }"
              >
                <b-btn class="btn btn-warning mb-2 mt-1">Adjust</b-btn>
              </router-link>
            </div>
            <div class="d-inline">
              <b-btn
                v-if="order.voided === 0"
                class="btn mb-2"
                variant="danger"
                @click="voidOrder"
                >Void</b-btn
              >
              <img
                v-if="order.voided === 0"
                v-b-popover.hover="
                  'Voiding an order removes the order information & meals from all of your reporting.'
                "
                title="Voids"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
            <div>
              <b-btn
                v-if="order.voided === 1"
                class="btn mb-2"
                variant="danger"
                @click="voidOrder"
                >Unvoid</b-btn
              >
            </div>
            <div>
              <b-btn
                class="btn mb-2"
                variant="primary"
                @click="printPackingSlip(order.id)"
                >Print Packing Slip</b-btn
              >
            </div>

            <div>
              <b-btn
                class="btn mb-2 white-text d-inline"
                variant="secondary"
                @click="emailCustomerReceipt(order.id)"
                >Email Receipt</b-btn
              >
              <img
                v-if="order.voided === 0"
                v-b-popover.hover="
                  'Customers receive emails after they checkout. This would just be a second copy if they didn\'t receive the first for any reason.'
                "
                title="Email Receipt"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
          </div>
          <div class="col-md-3 pt-1">
            <h4>Placed On</h4>
            <p>{{ moment(order.created_at).format("dddd, MMM Do") }}</p>
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

              <span v-if="editingBalance">
                <b-form-input
                  type="number"
                  v-model="balance"
                  class="d-inline mb-1 "
                  style="width:100px"
                ></b-form-input>
                <i
                  class="fas fa-check-circle text-primary d-inline"
                  @click="updateBalance(order.id)"
                ></i>
              </span>
              <i
                v-if="!editingBalance"
                @click="editingBalance = true"
                class="fa fa-edit text-warning d-inline"
              ></i>
            </div>
            <br /><br />
            <div
              class="d-inline"
              v-if="order.balance !== null && order.balance != 0"
            >
              <b-btn
                class="btn mb-2 d-inline mr-1"
                variant="success"
                @click="settle"
                >Settle Balance</b-btn
              >
              <img
                v-b-popover.hover="
                  'This settles the balance on the order to $0 for your records without charging or refunding your customer.'
                "
                title="Settle Balance"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <span v-if="editingCustomer">
              <b-form-input
                v-model="user_detail.firstname"
                class="d-inline width-70 mb-3"
              ></b-form-input>
              <b-form-input
                v-model="user_detail.lastname"
                class="d-inline width-70 mb-3"
              ></b-form-input>
            </span>
            <span v-else>
              <p>{{ user_detail.firstname }} {{ user_detail.lastname }}</p>
            </span>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <span v-if="editingCustomer">
              <b-form-input
                v-model="user_detail.address"
                class="d-inline width-70 mb-3"
              ></b-form-input>
              <b-form-input
                v-model="user_detail.city"
                class="d-inline width-70 mb-3"
              ></b-form-input>
              <b-form-input
                v-model="user_detail.state"
                class="d-inline width-70 mb-3"
              ></b-form-input>
              <b-form-input
                v-model="user_detail.zip"
                class="d-inline width-70 mb-3"
              ></b-form-input>
            </span>
            <span v-else>
              <p>{{ user_detail.address }}</p>
              <p>
                {{ user_detail.city }}, {{ user_detail.state }}
                {{ user_detail.zip }}
              </p>
            </span>
          </div>
          <div class="col-md-4">
            <span v-if="!storeModules.hideTransferOptions">
              <h4 v-if="!order.pickup">Delivery Day</h4>
              <h4 v-if="order.pickup">Pickup Day</h4>
              <template v-if="!order.isMultipleDelivery">
                {{ moment(order.delivery_date).format("dddd, MMM Do") }}
                <span v-if="order.transferTime"> {{ order.transferTime }}</span>
              </template>
              <template v-else>
                <p>{{ order.multiple_dates }}</p>
              </template>
            </span>
            <p v-if="order.pickup_location_id != null" class="mt-1">
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
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <h4>Phone</h4>
            <span v-if="editingCustomer">
              <b-form-input v-model="user_detail.phone"></b-form-input>
            </span>
            <span v-else>
              <p>{{ user_detail.phone }}</p>
            </span>

            <div v-if="order.added_by_store_id === store.id">
              <b-btn
                variant="warning"
                class="d-inline mb-2 mt-2"
                @click="editCustomer"
                >Edit Customer</b-btn
              >
              <b-btn
                v-if="editingCustomer"
                variant="primary"
                class="d-inline mb-2 mt-2"
                @click="updateCustomer(user_detail.user_id)"
                >Save</b-btn
              >
            </div>
          </div>
          <div class="col-md-4">
            <h4>Email</h4>
            <span v-if="editingCustomer">
              <b-form-input
                v-model="email"
                class="d-inline width-70 mb-3"
              ></b-form-input>
            </span>
            <span v-else>
              <p>{{ email }}</p>
            </span>
          </div>
          <div class="col-md-4">
            <h4 v-if="!order.pickup">Delivery Instructions</h4>
            <span v-if="editingCustomer">
              <b-form-input
                v-model="user_detail.delivery"
                class="d-inline width-70 mb-3"
              ></b-form-input>
            </span>
            <span v-else>
              <p v-if="!order.pickup">{{ user_detail.delivery }}</p>
            </span>
          </div>
        </div>
        <div class="row" v-if="storeModules.orderNotes">
          <div class="col-md-12">
            <h4>Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="deliveryNote"
              placeholder="Optional."
            ></textarea>
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="saveNotes(orderId)"
            >
              Save
            </button>
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
                    <span class="order-quantity">{{
                      lineItemOrder.quantity
                    }}</span>
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
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import { sidebarCssClasses } from "../../shared/classes";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      editingCustomer: false,
      editingBalance: false,
      balance: 0,
      chargeAmount: 0,
      refundAmount: 0,
      applyToBalanceRefund: false,
      applyToBalanceCharge: false,
      checkingOut: false,
      ordersByDate: [],
      email: "",
      deliveryDate: "All",
      filter: false,
      pastOrder: false,
      filters: {
        fulfilled: 0,
        paid: 1,
        delivery_dates: {
          start: null,
          end: null
        },
        has_notes: false
      },
      viewOrderModal: false,
      order: {},
      orderId: "",
      user_detail: {},
      meals: {},
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      columnsMealMultipleDelivery: [
        "delivery_date",
        "size",
        "meal",
        "quantity",
        "unit_price",
        "subtotal"
      ],
      columns: [
        "icons",
        "order_number",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        // "user.user_detail.phone",
        "created_at",
        "delivery_date",
        "pickup",
        "amount",
        "actions"
      ],
      options: {
        headings: {
          icons: "Status",
          dailyOrderNumber: "Daily Order #",
          order_number: "Order ID",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          // "user.user_detail.phone": "Phone",
          created_at: "Order Placed",
          delivery_date: "Delivery Date",
          pickup: "Type",
          amount: "Total",
          balance: "Balance",
          // chargeType: "Charge Type",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          classes += row.viewed ? "" : " strong";
          return classes;
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              a = a.created_at;
              b = b.created_at;

              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return a.isAfter(b, "day") ? 1 : -1;
            };
          },
          delivery_date: function(ascending) {
            return function(a, b) {
              a = a.delivery_date;
              b = b.delivery_date;

              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return a.isAfter(b, "day") ? 1 : -1;
            };
          }
        }
      },
      deliveryNote: ""
    };
  },
  created() {},
  mounted() {
    if (this.storeModules.dailyOrderNumbers) {
      this.columns.splice(1, 0, "dailyOrderNumber");
    }

    if (this.$route.params.autoPrintPackingSlip) {
      axios.get("/api/me/getLatestOrder").then(resp => {
        this.printPackingSlip(resp.data.id);
      });
    }
    /* Sidebar Check */
    let isOpen = false;

    for (let i in sidebarCssClasses) {
      if ($("body").hasClass(sidebarCssClasses[i])) {
        isOpen = true;
        break;
      }
    }

    if (!isOpen && $(".navbar-toggler").length > 0) {
      $(".navbar-toggler").click();
    }
    /* Sidebar Check End */

    // if (this.storeModules.manualOrders || this.storeModules.cashOrders) {
    //   this.columns.splice(8, 0, "chargeType");
    // }
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      upcomingOrdersWithoutItems: "storeUpcomingOrdersWithoutItems",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates",
      getMeal: "storeMeal",
      storeModules: "storeModules",
      storeSettings: "storeSettings",
      getStoreMeal: "storeMeal"
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = [];

      if (this.filters.delivery_dates.start === null) {
        orders = this.upcomingOrdersWithoutItems;
      } else {
        orders = this.ordersByDate;
      }

      orders.forEach(order => {
        if (order.balance && !this.columns.includes("balance")) {
          this.columns.splice(9, 0, "balance");
          return;
        }
      });

      return orders;
    },
    fullyRefunded() {
      // return false;

      // Add back in after a week or two when new orders have these figures logged
      if (this.order.originalAmount - this.order.refundedAmount <= 0)
        return true;
      else return false;
    }
  },
  beforeDestroy() {
    this.updateViewedOrders();
    // this.refreshOrders();
  },
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders",
      refreshOrdersWithFulfilled: "refreshOrdersWithFulfilled",
      refreshUpcomingOrdersWithoutItems: "refreshUpcomingOrdersWithoutItems",
      refreshUpcomingOrders: "refreshUpcomingOrders",
      refreshOrdersToday: "refreshOrdersToday",
      refreshStoreCustomers: "refreshStoreCustomers",
      updateOrder: "updateOrder",
      addJob: "addJob",
      removeJob: "removeJob"
    }),
    refreshTable() {
      this.refreshOrders();
    },
    refreshTableWithFulfilled() {
      this.refreshOrdersWithFulfilled();
    },
    formatMoney: format.money,
    syncEditables() {
      this.editing = _.keyBy({ ...this.tableData }, "id");
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, ["id", id]);
    },
    getTableDataById(id) {
      return _.find(this.tableData, ["id", id]);
    },
    async fulfill(id) {
      await this.updateOrder({ id, data: { fulfilled: 1 } });
      this.$toastr.s("Order fulfilled.");
      this.$forceUpdate();
    },
    async unfulfill(id) {
      await this.updateOrder({ id, data: { fulfilled: 0 } });
      this.$forceUpdate();
    },
    async saveNotes(id) {
      let data = { notes: this.deliveryNote };
      axios.patch(`/api/me/orders/${id}`, data).then(resp => {
        this.refreshTable();
        this.$toastr.s("Order notes saved.");
      });
    },
    printPackingSlip(order_id) {
      axios
        .get(`/api/me/print/packing_slips/pdf`, {
          params: { order_id }
        })
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            win.addEventListener(
              "load",
              () => {
                win.print();
              },
              false
            );
          }
        })
        .catch(err => {
          this.$toastr.e("Failed to print report.");
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async viewOrder(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/orders/${id}`)
        .then(response => {
          this.orderId = response.data.id;
          this.deliveryNote = response.data.notes;
          this.order = response.data;
          this.user_detail = response.data.user.user_detail;
          this.meals = response.data.meals;
          this.viewOrderModal = true;
          this.email = response.data.user.email;
          this.originalAmount = response.data.originalAmount;
          this.chargedAmount = response.data.chargedAmount
            ? response.data.chargedAmount
            : 0;
          this.chargeAmount =
            response.data.balance <= 0 ? null : response.data.balance;
          this.refundAmount =
            response.data.balance < 0 ? response.data.balance * -1 : null;
          this.balance = response.data.balance;

          if (
            this.order.adjusted === 1 &&
            this.order.adjustedDifference < 0 &&
            this.order.balance != 0
          ) {
            this.applyToBalanceRefund = true;
          }

          if (
            this.order.adjusted === 1 &&
            this.order.adjustedDifference > 0 &&
            this.order.balance != 0
          ) {
            this.applyToBalanceCharge = true;
          }

          this.$nextTick(function() {
            window.dispatchEvent(new window.Event("resize"));
          });
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },
    filterPastOrders() {
      this.pastOrder = !this.pastOrder;
      this.refreshTable();
    },
    filterNotes() {
      this.filter = !this.filter;
      this.refreshTable();
    },
    async exportData(report, format = "pdf", print = false) {
      const warning = this.checkDateRange({ ...this.filters.delivery_dates });
      if (warning) {
        try {
          let dialog = await this.$dialog.confirm(
            "You have selected a date range which includes delivery days which haven't passe" +
              "d their cutoff period. This means new orders can still come in for those days. Continue?"
          );
          dialog.close();
        } catch (e) {
          return;
        }
      }

      let params = {
        has_notes: this.filters.has_notes ? 1 : 0,
        fulfilled: this.filters.fulfilled ? 1 : 0
      };

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      }

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if (print) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                },
                false
              );
            }
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    },
    onChangeDateFilter() {
      axios
        .post("/api/me/getOrdersWithDatesWithoutItems", {
          start: this.filters.delivery_dates.start,
          end: this.filters.delivery_dates.end
        })
        .then(response => {
          this.ordersByDate = response.data;
        });
    },
    updateViewedOrders() {
      axios.get(`/api/me/ordersUpdateViewed`);
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    },
    showFulfilledOrders() {
      this.filters.fulfilled = 1;
      this.refreshTableWithFulfilled();
    },
    showUnfulfilledOrders() {
      this.filters.fulfilled = 0;
      this.refreshTable();
    },
    charge() {
      axios
        .post("/api/me/charge", {
          orderId: this.orderId,
          chargeAmount: this.chargeAmount,
          applyToBalance: this.applyToBalanceCharge
        })
        .then(response => {
          this.viewOrderModal = false;
          this.chargeAmount = 0;
          this.refreshUpcomingOrders();
          this.$toastr.s(response.data);
          this.applyToBalanceCharge = false;
          this.applyToBalanceRefund = false;
        });
    },
    refund() {
      axios
        .post("/api/me/refundOrder", {
          orderId: this.orderId,
          refundAmount: this.refundAmount,
          applyToBalance: this.applyToBalanceRefund
        })
        .then(response => {
          if (response.data === 1) {
            this.$toastr.e(
              "The refund amount is greater than the original amount plus additional charges which is $" +
                (this.originalAmount + this.chargedAmount),
              "Error"
            );
          } else {
            this.viewOrderModal = false;
            this.refundAmount = 0;
            this.refreshUpcomingOrders();
            this.$toastr.s(response.data);
            this.applyToBalanceCharge = false;
            this.applyToBalanceRefund = false;
          }
        })
        .catch(err => {
          this.$toastr.e("Error. Please contact GoPrep.");
        });
    },
    settle() {
      axios
        .post("/api/me/settleBalance", { orderId: this.orderId })
        .then(response => {
          this.$toastr.s("Balance has been settled to 0");
          this.viewOrderModal = false;
          this.refreshUpcomingOrders();
          this.applyToBalanceCharge = false;
          this.applyToBalanceRefund = false;
        });
    },
    voidOrder() {
      axios
        .post("/api/me/voidOrder", {
          orderId: this.orderId
        })
        .then(response => {
          this.viewOrderModal = false;
          this.refreshUpcomingOrders();
          this.$toastr.s(response.data);
        });
    },
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
          if (item.meal_package_order_id === meal_package_item.id) {
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
              //meal: meal.title,
              size: size ? size.title : meal.default_size_title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal: "In Package"
            });
          }
        });
      });

      order.items.forEach(item => {
        if (item.meal_package_order_id === null) {
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
            //meal: meal.title,
            size: size ? size.title : meal.default_size_title,
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

      return _.filter(data);
    },
    updateBalance(id) {
      axios
        .post("/api/me/updateBalance", { id: id, balance: this.balance })
        .then(resp => {
          this.editingBalance = false;
          this.viewOrder(id);
          this.$toastr.s("Balance updated.");
        });
    },
    editCustomer() {
      this.editingCustomer = !this.editingCustomer;
    },
    updateCustomer(id) {
      axios
        .post(`/api/me/updateCustomerUserDetails`, {
          id: id,
          details: this.user_detail,
          email: this.email
        })
        .then(resp => {
          this.viewOrder(this.orderId);
          this.refreshStoreCustomers();
          this.refreshUpcomingOrdersWithoutItems();
          this.$toastr.s("Customer updated.");
          this.editingCustomer = false;
        });
    },
    emailCustomerReceipt(id) {
      axios.post("/api/me/emailCustomerReceipt", { id: id }).then(resp => {
        this.$toastr.s("Customer emailed.");
      });
    }
  }
};
</script>
