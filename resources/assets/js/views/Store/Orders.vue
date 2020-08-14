<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <!-- <b-alert style="background-color:#EBFAFF" show dismissible>
        <h5>
          New Feature - Cooler Bag Deposit
        </h5>
        <p>
          Have your customer optionally pay a deposit on a cooler bag which
          their food will be delivered in.<br />You will then see an indication
          on each new order that a cooler bag deposit was paid, and you'll have
          the option to refund the customer the deposit once the bag is returned
          to you.<br />
          <router-link to="/store/account/settings"
            ><strong
              >Enable in the Advanced tab of the Settings page here.</strong
            ></router-link
          >
        </p>
      </b-alert>
     -->
      <div class="card">
        <div class="card-body">
          <Spinner v-if="orders.loading" />
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="initialized"
            @pagination="onChangePage"
            :class="{ 'table-loading': this.orders.loading }"
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
            <div slot="beforeTable" class="mb-2">
              <div class="table-before d-flex flex-wrap align-items-center">
                <div class="d-inline<v--block mb-2 mb-md-0 mr-2 flex-grow-0">
                  <!-- <b-btn
                    @click="$set(filters, 'has_notes', !filters.has_notes)"
                    :selected="filters.has_notes"
                    variant="primary"
                    class="filter-btn"
                    >Filter Notes</b-btn
                  >-->
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
                  >-->

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

                <b-btn variant="light" class="ml-auto" @click="refreshTable()">
                  <i class="fa fa-refresh"></i>
                </b-btn>
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
                  v-if="props.row.notes || props.row.publicNotes"
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
                <i
                  v-if="props.row.hot"
                  class="fas fa-fire red"
                  v-b-popover.hover.top="
                    'This order needs to be hot upon delivery/pickup.'
                  "
                ></i>
                <i
                  v-if="
                    props.row.coolerDeposit > 0 && !props.row.coolerReturned
                  "
                  class="fas fa-icicles text-primary"
                  v-b-popover.hover.top="
                    'A deposit for a cooler bag was paid and needs to be refunded upon return.'
                  "
                >
                </i>
                <i
                  v-if="props.row.coolerDeposit > 0 && props.row.coolerReturned"
                  class="fas fa-icicles text-secondary"
                  v-b-popover.hover.top="
                    'A deposit for a cooler bag was paid, the bag was returned, and the customer was refunded.'
                  "
                >
                </i>
              </p>
            </div>
            <div slot="paid_at" slot-scope="props">
              {{ moment(props.row.paid_at).format("dddd, MMM Do") }}
            </div>
            <div slot="delivery_date" slot-scope="props">
              <template v-if="!props.row.isMultipleDelivery">{{
                moment(props.row.delivery_date).format("dddd, MMM Do")
              }}</template>
              <template v-else>
                Multiple
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
                {{ format.money(props.row.balance, storeSettings.currency) }}
              </span>
              <span v-else>Paid in Full</span>
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <!-- Keeping but hiding for purposes of double clicking the row to open the modal -->
              <button
                v-show="false"
                class="btn view btn-primary btn-sm"
                @click="viewOrder(props.row.id)"
              >
                View Order
              </button>
              <button
                type="button"
                class="btn btn-primary dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                @click="(order = props.row), (orderId = props.row.id)"
              >
                Actions
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" @click="viewOrder(props.row.id)"
                  >View</a
                >
                <a
                  class="dropdown-item"
                  @click="adjustOrder(props.row.id)"
                  v-if="!mealMixItems.isRunningLazy"
                  >Adjust</a
                >
                <a
                  class="dropdown-item"
                  @click="voidOrder"
                  v-if="!props.row.voided"
                  >Void</a
                >
                <a
                  class="dropdown-item"
                  @click="voidOrder"
                  v-if="props.row.voided"
                  >Unvoid</a
                >
                <a class="dropdown-item" @click="printPackingSlip(props.row.id)"
                  >Print Packing Slip</a
                >
                <a
                  class="dropdown-item"
                  @click="printLabel(props.row.id, 'labels', 'b64')"
                  >Print Label</a
                >
                <a
                  class="dropdown-item"
                  @click="emailCustomerReceipt(props.row.id)"
                  >Email Customer Receipt</a
                >
                <a
                  class="dropdown-item"
                  v-if="
                    props.row.coolerDeposit > 0 &&
                      !props.row.coolerReturned &&
                      !props.row.cashOrder
                  "
                  @click="refund(true)"
                  >Refund Cooler Deposit</a
                >
                <a
                  class="dropdown-item"
                  v-if="
                    props.row.coolerDeposit > 0 &&
                      !props.row.coolerReturned &&
                      props.row.cashOrder
                  "
                  @click="coolerReturned()"
                  >Mark Cooler as Returned</a
                >
              </div>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
            </div>
          </v-client-table>

          <div class="text-center">
            <b-pagination
              v-model="orders.page"
              :total-rows="orders.total"
              :per-page="10"
              align="center"
              :hide-ellipsis="true"
            ></b-pagination>
            {{ orders.total }} Records
          </div>
        </div>
      </div>
    </div>

    <div class="modal-basic modal-wider">
      <b-modal
        v-model="viewOrderModal"
        size="xl"
        title="Order Information"
        no-fade
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
                  v-if="order.notes || order.publicNotes"
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
                <i
                  v-if="order.hot"
                  class="fas fa-fire red"
                  v-b-popover.hover.top="
                    'This order needs to be hot upon delivery/pickup.'
                  "
                >
                </i>
                <i
                  v-if="order.coolerDeposit > 0 && !order.coolerReturned"
                  class="fas fa-icicles text-primary"
                  v-b-popover.hover.top="
                    'A deposit for a cooler bag was paid and needs to be refunded upon return.'
                  "
                >
                </i>
                <i
                  v-if="order.coolerDeposit > 0 && order.coolerReturned"
                  class="fas fa-icicles text-secondary"
                  v-b-popover.hover.top="
                    'A deposit for a cooler bag was paid, the bag was returned, and the customer was refunded.'
                  "
                >
                </i>
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
            <div class="d-inline" v-if="!order.cashOrder">
              <b-form-checkbox v-model="applyToBalanceCharge"
                >Apply Charge to Balance</b-form-checkbox
              >
              <br />
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
                  'Charges allow you to charge your customer directly for any balance on the order.'
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
              >
              <br />
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
                type="number"
              ></b-form-input>
              <img
                v-b-popover.hover="
                  'Refund your customer partially or fully. Refunds take 5-10 days to show on your customer\'s statements. Leave the field blank for a full refund. Refunding does not remove the order from all of your reports. Please void the order for that.'
                "
                title="Refunds"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
            <div
              class="d-inline"
              v-if="
                !order.cashOrder &&
                  store.settings.payment_gateway !== 'authorize' &&
                  order.coolerDeposit > 0 &&
                  !order.coolerReturned
              "
            >
              <br />
              <b-btn
                variant="primary"
                class="btn mb-2 d-inline mr-1"
                @click="refund(true)"
                >Refund Cooler Deposit
                {{ format.money(order.coolerDeposit, storeSettings.currency) }}
              </b-btn>
              <img
                v-b-popover.hover="
                  'Refund your customer for their cooler deposit upon return of the bag.'
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
                  params: { order: order, orderId: orderId }
                }"
              >
                <b-btn
                  class="btn btn-warning mb-2 mt-1"
                  :disabled="mealMixItems.isRunningLazy"
                  >Adjust</b-btn
                >
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
                  'Voiding an order removes the order information & meals from all of your reporting. It does not refund the customer.'
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
            <div class="d-flex">
              <b-btn
                class="btn mb-2 d-inline mr-1 royalBlueBG"
                @click="printLabel(order.id, 'labels', 'b64')"
                >Print Label</b-btn
              >
              <b-btn
                class="btn mb-2 d-inline btn-secondary"
                @click="printLabel(order.id, 'labels', 'pdf')"
                >Preview Label</b-btn
              >
            </div>
            <div>
              <b-btn
                class="btn mb-2 white-text d-inline"
                variant="success"
                @click="emailCustomerReceipt(order.id)"
                >Email Receipt</b-btn
              >
              <img
                v-if="order.voided === 0"
                v-b-popover.hover="
                  'Customers automatically receive email receipts after they checkout. This button would send a second copy if they didn\'t receive the first for any reason.'
                "
                title="Email Receipt"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
          </div>
          <div class="col-md-3 pt-1">
            <h4>Placed On</h4>
            <p>{{ moment(order.paid_at).format("LLLL") }}</p>
            <div class="mt-3" v-if="order.staff_id">
              <h4>Order Taken By</h4>
              <p>{{ order.staff_member }}</p>
            </div>
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
            <p class="text-success" v-if="order.purchasedGiftCardReduction > 0">
              Gift Card
              {{ order.purchased_gift_card_code }} ({{
                format.money(order.purchasedGiftCardReduction, order.currency)
              }})
            </p>
            <p class="text-success" v-if="order.referralReduction > 0">
              Referral Discount: ({{
                format.money(order.referralReduction, order.currency)
              }})
            </p>
            <p class="text-success" v-if="order.promotionReduction > 0">
              Promotional Discount: ({{
                format.money(order.promotionReduction, order.currency)
              }})
            </p>
            <p class="text-success" v-if="order.pointsReduction > 0">
              Points Used: ({{
                format.money(order.pointsReduction, order.currency)
              }})
            </p>
            <p v-if="order.gratuity > 0">
              Gratuity:
              {{ format.money(order.gratuity, order.currency) }}
            </p>
            <p v-if="order.coolerDeposit > 0">
              Cooler Deposit:
              {{ format.money(order.coolerDeposit, order.currency) }}
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
                  class="d-inline mb-1"
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
            <br />
            <br />
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
                  'This sets the balance on the order to $0 for your records without actually charging or refunding your customer.'
                "
                title="Settle Balance"
                src="/images/store/popover.png"
                class="popover-size d-inline"
              />
            </div>
            <div
              v-if="
                order.subscription &&
                  order.subscription.monthlyPrepay &&
                  (order.subscription.weekCount !== 1 ||
                    order.subscription.weekCount % 4 !== 1)
              "
            >
              Prepaid
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <span v-if="user_detail.companyname">
              <span v-if="editingCustomer">
                <b-form-input
                  v-model="user_detail.companyname"
                  class="d-inline width-70 mb-3"
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ user_detail.companyname }}</p>
              </span>
            </span>
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
        </div>
        <div class="row">
          <div class="col-md-4">
            <h4>Phone</h4>
            <span v-if="editingCustomer">
              <b-form-input
                v-model="user_detail.phone"
                @input="asYouType()"
              ></b-form-input>
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
            <h4>Private Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="deliveryNote"
              placeholder="Private notes found on your orders page and Order Summary report."
            ></textarea>
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="saveNotes(orderId)"
            >
              Save
            </button>
          </div>
          <div class="col-md-12">
            <h4>Public Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="publicOrderNotes"
              placeholder="Public notes sent to the customer in their emails and shown on your packing slips."
            ></textarea>
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="savePublicNotes(orderId)"
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
              striped
              stacked="sm"
              :columns="mealColumns"
              :data="getMealTableData(order)"
              :options="optionsMeal"
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
                <p v-if="order.gratuity > 0">
                  Gratuity:
                  {{ format.money(order.gratuity, order.currency) }}
                </p>
                <p v-if="order.coolerDeposit > 0">
                  Cooler Deposit:
                  {{ format.money(order.coolerDeposit, order.currency) }}
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
      </b-modal>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import { createInstance } from "vuex-pagination";
import checkDateRange from "../../mixins/deliveryDates";
import { sidebarCssClasses } from "../../shared/classes";
import store from "../../store";
import { AsYouType } from "libphonenumber-js";
import printer from "../../mixins/printer";
import { sleep } from "../../lib/utils";
import { PrintJob, PrintSize } from "../../store/printer";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange, printer],
  data() {
    return {
      page: 1,
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
        has_notes: false,
        query: null
      },
      viewOrderModal: false,
      order: {},
      orderId: "",
      user_detail: {},
      meals: {},
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      optionsMeal: {
        headings: {
          unit_price: "Unit Price"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          classes += row.meal_package ? " strong" : "";
          return classes;
        }
      },
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
        "customer_name",
        "customer_address",
        "customer_zip",
        // "user.user_detail.phone",
        "paid_at",
        "delivery_date",
        "pickup",
        "amount",
        "actions"
      ],
      options: {
        filterable: false,
        headings: {
          icons: "Status",
          dailyOrderNumber: "Daily Order #",
          order_number: "Order ID",
          customer_name: "Name",
          customer_address: "Address",
          customer_zip: "Zip Code",
          // "user.user_detail.phone": "Phone",
          paid_at: "Order Placed",
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
          paid_at: function(ascending) {
            return function(a, b) {
              a = a.paid_at;
              b = b.paid_at;

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
      deliveryNote: "",
      publicOrderNotes: ""
    };
  },
  created() {},
  mounted() {
    if (
      this.store.id === 108 ||
      this.store.id === 109 ||
      this.store.id === 110
    ) {
      this.setBagPickup(1);
    }
    if (this.storeModules.dailyOrderNumbers) {
      this.columns.splice(1, 0, "dailyOrderNumber");
    }

    let params = this.$route.params;

    if (params.autoPrintPackingSlip && params.orderId !== undefined) {
      this.printPackingSlip(params.orderId);
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

    this.clearBag();
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
      getStoreMeal: "storeMeal",
      reportSettings: "storeReportSettings",
      mealMixItems: "mealMixItems"
    }),
    orders: createInstance("orders", {
      page: 1,
      pageSize: 10,
      args() {
        const { filters } = this;
        const { query, delivery_dates, fulfilled, paid, has_notes } = filters;

        let args = {
          query,
          start: delivery_dates.start || null,
          end: delivery_dates.end || null,
          fulfilled,
          paid,
          has_notes
        };

        return args;
      }
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = this.orders.items;

      /*if (this.filters.delivery_dates.start === null) {
        orders = this.upcomingOrdersWithoutItems;
      } else {
        orders = this.ordersByDate;
      }*/

      orders.forEach(order => {
        if (order.balance && !this.columns.includes("balance")) {
          this.columns.splice(9, 0, "balance");
          return;
        }
      });

      //while(orders.length < this.orders.total) {
      //  orders.push({});
      //}

      return _.isArray(orders) ? orders : [];
    },
    mealColumns() {
      if (!this.order.isMultipleDelivery) {
        return this.columnsMeal;
      } else {
        return this.columnsMealMultipleDelivery;
      }
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
    ...mapActions("resources", ["refreshResource"], "printer/connect"),
    ...mapMutations([
      "clearBagDeliveryDate",
      "clearBagTransferTime",
      "clearBagStaffMember",
      "clearBagCustomerModel",
      "setBagPickup",
      "setBagCoupon"
    ]),
    refreshTable() {
      this.refreshResource("orders");
    },
    refreshTableWithFulfilled() {
      this.refreshOrdersWithFulfilled();
    },
    onChangePage(page) {
      this.orders.page = page;
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
    async savePublicNotes(id) {
      let data = { publicNotes: this.publicOrderNotes };
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
    printLabel(order_id, report, format, page = 1) {
      let params = { page };

      params.width = this.reportSettings.lab_width;
      params.height = this.reportSettings.lab_height;
      params.order_id = order_id;

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
        .then(response => {
          const { data } = response;

          if (format === "b64") {
            const size = new PrintSize(
              this.reportSettings.lab_width,
              this.reportSettings.lab_height
            );
            const margins = {
              top: this.reportSettings.lab_margin_top,
              right: this.reportSettings.lab_margin_right,
              bottom: this.reportSettings.lab_margin_bottom,
              left: this.reportSettings.lab_margin_left
            };
            const job = new PrintJob(data.url, size, margins);

            this.printerAddJob(job);
          } else if (!_.isEmpty(data.url)) {
            let win = window.open(data.url);
            if (win) {
              win.addEventListener(
                "load",
                () => {
                  win.print();

                  if (data.next_page && data.next_page !== page) {
                    this.print(report, format, data.next_page);
                  }
                },
                false
              );
            } else {
              this.$toastr.e(
                "Please add a popup exception to print this report.",
                "Failed to display PDF."
              );
            }
          }
        })
        .catch(err => {
          this.$toastr.e(
            "Please disable any popup blocker in our browser.",
            "Failed to print report."
          );
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
          this.publicOrderNotes = response.data.publicNotes;
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

          if (this.order.balance != 0) {
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
    async exportData(report, format = "pdf", print = false, page = 1) {
      // const warning = this.checkDateRange({ ...this.filters.delivery_dates });
      // if (warning) {
      //   try {
      //     let dialog = await this.$dialog.confirm(
      //       "You have selected a date range which includes delivery days which haven't passe" +
      //         "d their cutoff period. This means new orders can still come in for those days. Continue?"
      //     );
      //     dialog.close();
      //   } catch (e) {
      //     return;
      //   }
      // }

      let params = {
        has_notes: this.filters.has_notes ? 1 : 0,
        fulfilled: this.filters.fulfilled ? 1 : 0,
        page
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
          const { data } = response;
          if (!_.isEmpty(data.url)) {
            let win = window.open(data.url);
            if (win) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                  if (data.next_page && data.next_page !== page) {
                    this.exportData(report, format, true, data.next_page);
                  }
                },
                false
              );
            } else {
              this.$toastr.e(
                "Please add a popup exception to print this report.",
                "Failed to display PDF."
              );
            }
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    },
    onChangeDateFilter() {},
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
      if (this.chargeAmount === null || this.chargeAmount < 0.5) {
        this.$toastr.w("Please enter an amount in the box greater than .50");
        return;
      }
      axios
        .post("/api/me/charge", {
          orderId: this.orderId,
          chargeAmount: this.chargeAmount,
          applyToBalance: this.applyToBalanceCharge
        })
        .then(response => {
          this.viewOrderModal = false;
          this.$toastr.s(
            "Successfully charged " +
              format.money(this.chargeAmount, this.storeSettings.currency)
          );
          this.chargeAmount = 0;
          tthis.refreshResource("orders");
          this.applyToBalanceCharge = false;
          this.applyToBalanceRefund = false;
        });
    },
    refund(cooler = 0) {
      let isCoolerRefund = cooler;
      if (cooler == 1) {
        this.refundAmount = this.order
          ? this.order.coolerDeposit
          : coolerDepositAmount;
      }
      axios
        .post("/api/me/refundOrder", {
          orderId: this.order.id,
          refundAmount:
            this.refundAmount == null ? this.order.amount : this.refundAmount,
          applyToBalance: this.applyToBalanceRefund,
          cooler: isCoolerRefund
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
            this.$toastr.s(
              "Successfully refunded " +
                format.money(
                  this.refundAmount == null
                    ? this.order.amount
                    : this.refundAmount,
                  this.storeSettings.currency
                )
            );
            this.refundAmount = 0;
            this.refreshResource("orders");
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
          this.refreshResource("orders");
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
          this.refreshResource("orders");
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
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package.default_size_title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            ),
            meal_package: true
          });
        } else {
          data.push({
            delivery_date: moment(meal_package_item.delivery_date).format(
              "dddd, MMM Do"
            ),
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package_size.title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            ),
            meal_package: true
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
              false,
              item.customTitle,
              item.customSize
            );

            data.push({
              // delivery_date: item.delivery_date
              //   ? moment(item.delivery_date.date).format("dddd, MMM Do")
              //   : null,
              delivery_date: null,
              //meal: meal.title,
              size: size ? size.title : meal.default_size_title,
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

          const size = item.customSize
            ? { title: item.customSize }
            : meal.getSize(item.meal_size_id);
          const title = item.customTitle
            ? item.customTitle
            : meal.getTitle(
                true,
                size,
                item.components,
                item.addons,
                item.special_instructions,
                false,
                item.customTitle,
                item.customSize
              );

          data.push({
            delivery_date: item.delivery_date
              ? moment(item.delivery_date.date).format("dddd, MMM Do")
              : null,
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

      order.line_items_order.forEach(lineItem => {
        data.push({
          delivery_date: lineItem.delivery_date
            ? moment(item.delivery_date.date).format("dddd, MMM Do")
            : null,
          size: lineItem.size,
          meal: lineItem.title,
          quantity: lineItem.quantity,
          unit_price: format.money(lineItem.price, order.currency),
          subtotal: format.money(
            lineItem.price * lineItem.quantity,
            order.currency
          )
        });
      });

      order.purchased_gift_cards.forEach(purchasedGiftCard => {
        if (purchasedGiftCard.length === 5) {
          data.push({
            meal: "Gift Card Code: " + purchasedGiftCard.code,
            quantity: 1,
            unit_price: format.money(purchasedGiftCard.amount, order.currency),
            subtotal: format.money(purchasedGiftCard.amount, order.currency)
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
          this.refreshTable();
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
    },
    asYouType() {
      this.user_detail.phone = this.user_detail.phone.replace(/[^\d.-]/g, "");
      this.user_detail.phone = new AsYouType(this.store.details.country).input(
        this.user_detail.phone
      );
    },
    clearBag() {
      this.clearBagDeliveryDate();
      this.clearBagTransferTime();
      this.clearBagStaffMember();
      this.clearBagCustomerModel();
      this.setBagCoupon(null);
    },
    adjustOrder() {
      this.$router.push({
        name: "store-adjust-order",
        params: { order: this.order, orderId: this.orderId }
      });
    },
    coolerReturned() {
      axios.post("/api/me/coolerReturned", { id: this.orderId }).then(resp => {
        this.refreshTable();
        this.$toastr.s("Cooler marked as returned.");
      });
    }
  }
};
</script>

<style lang="scss">
.smsUL li {
  list-style-type: square;
}
</style>
