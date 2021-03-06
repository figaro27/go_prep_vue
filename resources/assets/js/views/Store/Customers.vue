<template>
  <div class="store-customer-container mt-3">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading" />
        <add-customer-modal :addCustomerModal="addCustomerModal">
        </add-customer-modal>

        <add-customer-modal v-model="addCustomerModal" v-if="addCustomerModal">
        </add-customer-modal>

        <div class="card">
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
            >
              <div slot="beforeTable" class="mb-2">
                <button
                  v-if="storeModules.manualCustomers"
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="showAddCustomerModal"
                >
                  Add New Customer
                </button>
              </div>
              <span slot="beforeLimit">
                <b-btn
                  variant="primary"
                  @click="exportData('customers', 'pdf', true)"
                >
                  <i class="fa fa-print"></i>&nbsp; Print
                </b-btn>
                <b-dropdown class="mx-1" right text="Export as">
                  <b-dropdown-item @click="exportData('customers', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('customers', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('customers', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </span>

              <div slot="created_at" slot-scope="props">
                <p>
                  {{
                    moment(props.row.created_at).format("dddd, MMM Do, YYYY")
                  }}
                </p>
              </div>

              <div slot="total_paid" slot-scope="props">
                <p>{{ format.money(props.row.total_paid) }}</p>
              </div>

              <div slot="last_order" slot-scope="props">
                <p>
                  {{
                    moment(props.row.last_order).format("dddd, MMM Do, YYYY")
                  }}
                </p>
              </div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-primary btn-sm"
                  @click="viewCustomer(props.row.id)"
                >
                  View Customer
                </button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-basic">
      <b-modal
        size="xl"
        title="Customer Details"
        v-model="viewCustomerModal"
        v-if="viewCustomerModal"
        no-fade
      >
        <b-form @submit.prevent="updateCustomer">
          <div class="row light-background border-bottom mb-3">
            <div class="col-md-4 pt-4">
              <h4>Customer</h4>
              <span v-if="customer.companyname">
                <span v-if="editingCustomer">
                  <b-form-input
                    v-model="customer.companyname"
                    class="d-inline width-70 mb-3"
                  ></b-form-input>
                </span>
                <span v-else>
                  <p>{{ customer.companyname }}</p>
                </span>
              </span>

              <span v-if="editingCustomer">
                <b-form-input
                  v-model="customer.firstname"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
                <b-form-input
                  v-model="customer.lastname"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ customer.firstname }} {{ customer.lastname }}</p>
              </span>

              <h4>Phone</h4>
              <span v-if="editingCustomer">
                <b-form-input
                  v-model="customer.phone"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ customer.phone }}</p>
              </span>

              <h4>
                Email
              </h4>
              <span v-if="editingCustomer">
                <b-form-input
                  v-model="customer.email"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ customer.email }}</p>
              </span>

              <span v-if="editingCustomer">
                <h4>
                  Password
                  <img
                    v-b-popover.hover="
                      'Here you can reset your customers password. Leave this field blank to not affect the customer\'s current password.'
                    "
                    title="Password Reset"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </h4>
                <b-form-input
                  placeholder="Leave blank to not update password."
                  v-model="password"
                  class="d-inline mb-3"
                  style="width:80%"
                ></b-form-input>
              </span>
              <!-- No longer requiring the customer to be created by the store for the store to edit the customer -->
              <div>
                <!-- <div v-if="customer.added_by_store_id === store.id"> -->
                <b-btn
                  variant="warning"
                  class="d-inline mb-2"
                  @click="editCustomer"
                  >Edit Customer</b-btn
                >
                <b-btn variant="primary" class="d-inline mb-2" type="submit"
                  >Save</b-btn
                >
              </div>
            </div>
            <div class="col-md-4 pt-4">
              <h4>Address</h4>
              <span v-if="editingCustomer">
                <b-form-input
                  v-model="customer.address"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
                <b-form-input
                  v-model="customer.city"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
                <b-form-input
                  v-model="customer.state"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
                <b-form-input
                  v-model="customer.zip"
                  class="d-inline width-70 mb-3"
                  required
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ customer.address }}</p>
                <p>
                  {{ customer.city }}, {{ customer.state }} {{ customer.zip }}
                </p>
              </span>
            </div>
            <div class="col-md-4 pt-4">
              <h4>Delivery Instructions</h4>
              <span v-if="editingCustomer">
                <b-form-input
                  v-model="customer.delivery"
                  class="d-inline width-70 mb-3"
                ></b-form-input>
              </span>
              <span v-else>
                <p>{{ customer.delivery }}</p>
              </span>
              <span v-if="pointsName">
                <h4>{{ pointsName }}</h4>
                <span v-if="customer.points && customer.points > 0">{{
                  customer.points
                }}</span>
                <span v-else>0</span>
              </span>
            </div>
          </div>

          <div
            v-for="order in customer.orders"
            :key="`order-${order.order_number}`"
            class="mb-2"
          >
            <div v-b-toggle="'collapse' + order.order_number">
              <b-list-group-item>
                <div class="row">
                  <div class="col-md-4">
                    <h4>Order ID</h4>
                    <p>{{ order.order_number }}</p>
                    <i
                      v-if="order.cashOrder"
                      class="fas fa-money-bill text-success"
                      v-b-popover.hover.top="
                        'A credit card wasn\'t processed through GoPrep for this order.'
                      "
                    ></i>
                    <h4 v-if="storeModules.dailyOrderNumbers">Daily Order #</h4>
                    <p v-if="storeModules.dailyOrderNumbers">
                      {{ order.dailyOrderNumber }}
                    </p>
                    <div class="mt-3" v-if="order.staff_id">
                      <h4>Order Taken By</h4>
                      <p>{{ order.staff_member }}</p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <h4>Placed On</h4>
                    <p>
                      {{
                        moment
                          .utc(order.paid_at)
                          .local()
                          .format("dddd, MMM Do, Y")
                      }}
                    </p>
                  </div>
                  <div class="col-md-4">
                    <p v-if="order.prepaid">
                      (Prepaid Subscription Order)
                    </p>
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
                    <p v-if="order.salesTax > 0">
                      Sales Tax:
                      {{ format.money(order.salesTax, order.currency) }}
                    </p>
                    <p v-if="order.deliveryFee > 0">
                      {{ order.transfer_type }} Fee:
                      {{ format.money(order.deliveryFee, order.currency) }}
                    </p>
                    <p v-if="order.processingFee > 0">
                      Processing Fee:
                      {{ format.money(order.processingFee, order.currency) }}
                    </p>
                    <p
                      class="text-success"
                      v-if="order.purchasedGiftCardReduction > 0"
                    >
                      Gift Card
                      {{ order.purchased_gift_card_code }} ({{
                        format.money(
                          order.purchasedGiftCardReduction,
                          order.currency
                        )
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
                      Refunded:
                      {{ format.money(order.refundedAmount, order.currency) }}
                    </p>
                    <p>
                      Balance: {{ format.money(order.balance, order.currency) }}
                    </p>
                  </div>
                  <div class="col-md-1">
                    <img
                      class="pull-right mt-2"
                      src="/images/collapse-arrow.png"
                    />
                  </div>
                </div>

                <b-collapse :id="'collapse' + order.order_number" class="mt-2">
                  <v-client-table
                    v-if="!order.isMultipleDelivery"
                    striped
                    stacked="sm"
                    :columns="columnsMeal"
                    :options="optionsMeal"
                    :data="getMealTableData(order)"
                    foot-clone
                  >
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
                        {{ order.transfer_type }} Fee:
                        {{ format.money(order.deliveryFee, order.currency) }}
                      </p>
                      <p v-if="order.processingFee > 0">
                        Processing Fee:
                        {{ format.money(order.processingFee, order.currency) }}
                      </p>
                      <p>
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
                  <v-client-table
                    v-if="order.isMultipleDelivery"
                    striped
                    stacked="sm"
                    :columns="columnsMealMultipleDelivery"
                    :data="getMealTableData(order)"
                    ref="mealsTable"
                    foot-clone
                    :options="optionsMeal"
                  >
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
                        {{ order.transfer_type }} Fee:
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
              </b-list-group-item>
            </div>
          </div>
        </b-form>
      </b-modal>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
// import ViewCustomer from "./Modals/ViewCustomer";
import format from "../../lib/format";
import { mapGetters, mapActions, mapMutations } from "vuex";
import states from "../../data/states.js";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";
import store from "../../store";

export default {
  components: {
    Spinner,
    AddCustomerModal
    // ViewCustomer
  },
  data() {
    return {
      password: null,
      editingCustomer: false,
      form: {},
      addCustomerModal: false,
      viewCustomerModal: false,
      userId: "",
      editUserId: "",
      id: "",
      customer: [],
      orders: [],
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      columns: [
        "name",
        "phone",
        "address",
        "city",
        "zip",
        "created_at",
        "total_payments",
        "total_paid",
        "last_order",
        "actions"
      ],
      columnsMealMultipleDelivery: [
        "delivery_date",
        "size",
        "meal",
        "quantity",
        "unit_price",
        "subtotal"
      ],
      options: {
        headings: {
          last_order: "Last Order",
          total_payments: "Total Orders",
          total_paid: "Total Paid",
          Name: "Name",
          phone: "Phone",
          address: "Address",
          city: "City",
          zip: "Zip",
          created_at: "Customer Since",
          actions: "Actions"
        },
        customSorting: {
          TotalPaid: function(ascending) {
            return function(a, b) {
              var numA = parseInt(a.TotalPaid);
              var numB = parseInt(b.TotalPaid);
              if (ascending) return numA >= numB ? 1 : -1;
              return numA <= numB ? 1 : -1;
            };
          },
          LastOrder: function(ascending) {
            return function(a, b) {
              var numA = moment(a.LastOrder);
              var numB = moment(b.LastOrder);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          }
        },
        orderBy: {
          column: "name",
          ascending: true
        }
      },
      optionsMeal: {
        headings: {
          unit_price: "Unit Price",
          meal: "Item"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          classes += row.meal_package ? " strong" : "";
          return classes;
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      meal: "storeMeal",
      getMeal: "storeMeal",
      customers: "storeCustomers",
      //orders: "storeOrders",
      isLoading: "isLoading",
      _storeOrdersByCustomer: "storeOrdersByCustomer",
      storeModules: "storeModules",
      initialized: "initialized",
      getStoreMeal: "viewedStoreMeal"
    }),
    pointsName() {
      let name = null;
      this.store.promotions.forEach(promotion => {
        if (promotion.promotionType === "points") {
          name = promotion.pointsName;
        }
      });
      return name;
    },
    stateNames() {
      return states.selectOptions("US");
    },
    tableData() {
      return Object.values(
        _.uniqBy(this.customers, customer => {
          return customer.user_id;
        })
      );
    },
    customerOrders() {
      let orders = this.userId ? this._storeOrdersByCustomer(this.userId) : [];
    }
  },
  created() {},
  mounted() {
    this.refreshStoreCustomers();
    // if (this.customers.length <= 1) {
    //   this.refreshStoreCustomers();
    // }
  },
  methods: {
    ...mapActions({
      refreshStoreCustomers: "refreshStoreCustomers",
      refreshUpcomingOrdersWithoutItems: "refreshUpcomingOrdersWithoutItems"
    }),
    resetUserId() {
      this.userId = 0;
      this.editUserId = 0;
    },
    viewCustomer(id) {
      this.userId = id;
      this.viewCustomerModal = true;
      axios.get(`/api/me/customers/${id}`).then(response => {
        this.customer = response.data;
      });
    },
    exportData(report, format = "pdf", print = false) {
      axios
        .get(`/api/me/print/${report}/${format}`)
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
    getMealQuantities(order) {
      let data = order.items.map(item => {
        const meal = this.getMeal(item.meal_id);
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
          title: title,
          quantity: item.quantity,
          unit_price: format.money(item.unit_price, order.currency),
          subtotal: format.money(item.price, order.currency)
        };
      });
      // data = _.orderBy(data, "delivery_date");
      return _.filter(data);
    },
    getMealTableData(order) {
      if (!this.initialized || !order.items) return [];

      let data = [];

      let meal_package_items = _.orderBy(
        order.meal_package_items,
        "delivery_date"
      );
      let items = _.orderBy(order.items, "delivery_date");

      meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
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
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
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
        items.forEach(item => {
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
              delivery_date: item.delivery_date
                ? moment(item.delivery_date.date).format("dddd, MMM Do")
                : null,
              //meal: meal.title,
              size: size ? size.title : meal.default_size_title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal:
                item.added_price > 0
                  ? "In Package " +
                    "(" +
                    this.store.settings.currency_symbol +
                    item.added_price +
                    ")"
                  : "In Package"
            });
          }
        });
      });

      items.forEach(item => {
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

      if (order.purchased_gift_cards) {
        order.purchased_gift_cards.forEach(purchasedGiftCard => {
          data.push({
            meal: "Gift Card Code: " + purchasedGiftCard.code,
            quantity: 1,
            unit_price: format.money(purchasedGiftCard.amount, order.currency),
            subtotal: format.money(purchasedGiftCard.amount, order.currency)
          });
        });
      }
      // data = _.orderBy(data, "delivery_date");
      return _.filter(data);
    },
    showAddCustomerModal() {
      if (
        this.store.settings.payment_gateway === "stripe" &&
        !this.store.settings.stripe_id
      ) {
        this.$toastr.w(
          "You must connect to Stripe before being able to add customers & create orders. Visit the Settings page to connect to Stripe."
        );
        return;
      }
      this.addCustomerModal = true;
    },
    editCustomer() {
      this.editingCustomer = !this.editingCustomer;
    },
    updateCustomer() {
      let id = this.customer.id;
      this.customer.name =
        this.customer.firstname + " " + this.customer.lastname;

      if (this.password) {
        this.customer.password = this.password;
      }

      axios
        .post(`/api/me/updateCustomerUserDetails`, {
          id: id,
          details: this.customer,
          customers: true
        })
        .then(resp => {
          this.viewCustomer(id);
          this.refreshStoreCustomers();
          this.refreshUpcomingOrdersWithoutItems();
          this.$toastr.s("Customer updated.");
          this.editingCustomer = false;
          this.password = null;
        });
    }
  }
};
</script>
