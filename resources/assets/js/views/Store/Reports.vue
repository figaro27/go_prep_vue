<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Production</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <Spinner v-if="isLoading" />
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Production</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.meal_orders"
                ref="mealOrdersDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('meal_orders')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">
              Shows how many of each items to make based on your orders.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('meal_orders', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item @click="exportData('meal_orders', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meal_orders', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meal_orders', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Ingredients</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.ingredient_quantities"
                :rtl="true"
                ref="ingredientQuantitiesDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('ingredient_quantities')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">
              Shows how much of each ingredient is needed based on your orders.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('ingredient_quantities', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Bagging & Delivery</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Orders Summary</h4>
            <div class="report-date-picker">
              <p v-if="pickupLocations.length > 0">Pickup Location</p>
              <b-select
                v-if="pickupLocations.length > 0"
                v-model="selectedPickupLocation"
                :options="pickupLocationOptions"
                class="delivery-select ml-2"
              ></b-select>
            </div>
            <div class="report-date-picker mt-2">
              <delivery-date-picker
                v-model="delivery_dates.order_summary"
                ref="ordersByCustomerDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('order_summary')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">
              Shows how to bag up your items for each customer.
            </p>
            <p class="center-text">
              <b-form-checkbox
                v-model="orderPackageSummary"
                unchecked-value="order_summary"
                value="package_order_summary"
                class="pt-1 pb-2"
                >Package Summary</b-form-checkbox
              >
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print(orderPackageSummary, 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item
                    @click="exportData(orderPackageSummary, 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData(orderPackageSummary, 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData(orderPackageSummary, 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Packing Slips</h4>
            <div class="report-date-picker">
              <p v-if="pickupLocations.length > 0">Pickup Location</p>
              <b-select
                v-if="pickupLocations.length > 0"
                v-model="selectedPickupLocation"
                :options="pickupLocationOptions"
                class="delivery-select ml-2"
              ></b-select>
            </div>
            <div class="report-date-picker mt-2">
              <delivery-date-picker
                v-model="delivery_dates.packing_slips"
                :rtl="true"
                ref="packingSlipsDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('packing_slips')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">
              Packing slips will print 15 orders per browser tab.
            </p>
            <div class="row">
              <div class="col-md-12">
                <button
                  @click="print('packing_slips', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 center"
                >
                  Print
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Delivery Summary</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.delivery_routes"
                ref="deliveryRoutesDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('delivery_routes')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="center-text pt-2">
              Summarizes all of your order deliveries.
            </p>
            <p class="center-text">
              <b-form-checkbox v-model="orderByRoutes" class="pt-1 pb-2"
                >Order By Routes</b-form-checkbox
              >
            </p>
            <p
              class="center-text font-14"
              style="color:#20A8D8"
              v-if="orderByRoutes"
              @click="showStartEndAddressModal = true"
            >
              Set Start & End Addresses (Optional)
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('delivery_routes', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item @click="exportData('delivery_routes', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('delivery_routes', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('delivery_routes', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">
              Item Labels <span class="text-danger">(Beta)</span>
            </h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.labels"
                ref="labelsDates"
                :rtl="true"
              ></delivery-date-picker>
              <b-btn @click="clearDates('labels')" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">Labels for your items.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  class="btn btn-warning btn-md center mt-2 pull-right"
                  @click="showSettingsModal.labels = true"
                >
                  Settings
                </button>
              </div>
              <div class="col-md-6">
                <button
                  @click="print('labels', 'pdf')"
                  class="btn btn-primary btn-md mt-2"
                >
                  Print
                </button>
                <!-- <button
                  @click="print('labels', 'b64')"
                  class="btn btn-primary btn-md mt-2"
                >
                  Print
                </button> -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">
              Order Labels <span class="text-danger">(Beta)</span>
            </h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.order_labels"
                ref="orderLabelsDates"
              ></delivery-date-picker>
              <b-btn @click="clearDates('order_labels')" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">Labels for your orders.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  class="btn btn-warning btn-md center mt-2 pull-right"
                  @click="showSettingsModal.order_labels = true"
                >
                  Settings
                </button>
              </div>
              <div class="col-md-6">
                <button
                  @click="print('order_labels', 'pdf')"
                  class="btn btn-primary btn-md mt-2"
                >
                  Print
                </button>
                <!-- <button
                  @click="print('order_labels', 'b64')"
                  class="btn btn-primary btn-md mt-2"
                >
                  Print
                </button> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Payments</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Payments</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.payments"
                ref="paymentsDates"
                :orderDate="true"
              ></delivery-date-picker>
              <b-btn @click="clearDates('payments')" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Gives you breakdowns of all your payments as well as totals for
              the date range you pick.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('payments', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item @click="exportData('payments', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('payments', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('payments', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <b-modal
      size="lg"
      hide-header
      v-model="showStartEndAddressModal"
      v-if="showStartEndAddressModal"
      no-fade
    >
      <div class="d-flex mt-3">
        <div style="width:200px">
          <p class="strong mr-2 pt-1">Start:</p>
        </div>
        <b-form-input
          v-model="startingAddress.address"
          placeholder="Starting Address"
          class="mr-2"
        ></b-form-input>
        <b-form-input
          v-model="startingAddress.city"
          placeholder="Starting City"
          class="mr-2"
        ></b-form-input>
        <b-form-input
          v-model="startingAddress.zip"
          placeholder="Starting Postal"
        ></b-form-input>
      </div>
      <div class="d-flex mt-1">
        <div style="width:200px">
          <p class="strong mr-2 pt-1">End:</p>
        </div>
        <b-form-input
          v-model="endingAddress.address"
          placeholder="Ending Address"
          class="mr-2"
        ></b-form-input>
        <b-form-input
          v-model="endingAddress.city"
          placeholder="Ending City"
          class="mr-2"
        ></b-form-input>
        <b-form-input
          v-model="endingAddress.zip"
          placeholder="Ending Postal"
        ></b-form-input>
      </div>
    </b-modal>
    <b-modal
      size="xl"
      title="Labels Settings"
      v-model="showSettingsModal.labels"
      v-if="showSettingsModal.labels"
      no-fade
      no-close-on-backdrop
      @ok="updateLabelSettings"
    >
      <b-alert variant="warning" show>
        <h6 class="center-text text-danger">Beta</h6>
        <p>
          This feature is currently in beta. It has only been tested on Dymo
          printers using a common label size of 4" by 2.33" inches. Please
          adjust the dimensions & margins below to see if it prints correctly
          for you. You can also try adjusting Printer Preferences on your
          computer. Try not to check all the boxes below as not everything can
          fit on the label.
        </p>
      </b-alert>
      <!-- <b-alert variant="info" show>
        <h6 class="center-text">Setup Instructions</h6>
        <p>
          #1
          <a
            href="https://github.com/qzind/tray/releases/download/v2.1.1/qz-tray-2.1.1%2B1.exe"
            target="_blank"
            class="strong"
            >Download QZ tray (Windows)</a
          >
          <a
            href="https://github.com/qzind/tray/releases/download/v2.1.1/qz-tray-2.1.1%2B1.pkg"
            target="_blank"
            class="strong"
            >Download QZ tray (Mac)</a
          >
        </p>
        <p>
          #2 Run the installer on your computer and make sure the QZ Tray
          application is running. Refresh GoPrep and the red light at the top
          should show as green.
        </p>
        <p>
          #3 Click the printer icon at the top of GoPrep and choose your label
          printer.
        </p>
        <p>
          #4 Adjust and save the settings below. The label size is required but
          the margins can and should be left blank unless adjustment is needed.
        </p>
        <p>
          #5 Click the "Preview" button to see if the labels are coming out
          correctly. Test on a single order first on the orders page.
        </p>
      </b-alert> -->
      <div class="row">
        <div class="col-md-4">
          <p class="strong">Show the Following on the Label</p>
          <p v-if="store.modules.dailyOrderNumbers">
            <b-form-checkbox v-model="labelSettings.daily_order_number"
              >Daily Order Numbers</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.index"
              >Indexes (E.G. #1 of #5)</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.nutrition"
              >Nutrition</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.macros"
              >Macros</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.logo">Logo</b-form-checkbox>
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.website"
              >Website</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.social"
              >Social Media Handle</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.customer"
              >Customer Name</b-form-checkbox
            >
          </p>
          <!-- <p>
          <b-form-checkbox v-model="labelSettings.description"
            >Item Description</b-form-checkbox
          >
        </p> -->
          <p>
            <b-form-checkbox v-model="labelSettings.instructions"
              >Item Instructions</b-form-checkbox
            >
          </p>
          <p v-if="store.modules.mealExpiration">
            <b-form-checkbox v-model="labelSettings.expiration"
              >Item Expiration</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.ingredients"
              >Ingredients</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.allergies"
              >Allergens</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.packaged_by"
              >Packaged By (Your address)</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="labelSettings.packaged_on"
              >Packaged On (Today's date)</b-form-checkbox
            >
          </p>
        </div>
        <div class="col-md-4">
          <p class="strong">Label Dimensions</p>
          <b-form-group class="mt-3">
            <b-input-group size="md" append="width">
              <b-input v-model="labelSettings.width" type="number" />
            </b-input-group>
            <b-input-group size="md" append="height">
              <b-input v-model="labelSettings.height" type="number" />
            </b-input-group>
          </b-form-group>
        </div>
        <!--         <div class="col-md-4">
          <p class="strong">Label Margins</p>
          <b-form-group class="mt-3">
            <b-input-group size="md" append="top">
              <b-input
                v-model="labelSettings.lab_margin_top"
                class="d-inline-block"
                type="number"
              />
            </b-input-group>
            <b-input-group size="md" append="right">
              <b-input
                v-model="labelSettings.lab_margin_right"
                type="number"
              />
            </b-input-group>
            <b-input-group size="md" append="bottom">
              <b-input
                v-model="labelSettings.lab_margin_bottom"
                class="d-inline-block"
                type="number"
              />
            </b-input-group>
            <b-input-group size="md" append="left">
              <b-input
                v-model="labelSettings.lab_margin_left"
                class="d-inline-block"
                type="number"
              />
            </b-input-group>
          </b-form-group>
        </div> -->
      </div>

      <b-btn variant="primary" @click="updateLabelSettings">Save</b-btn>
    </b-modal>

    <b-modal
      size="xl"
      title="Order Labels Settings"
      v-model="showSettingsModal.order_labels"
      v-if="showSettingsModal.order_labels"
      no-fade
      no-close-on-backdrop
      @ok="updateOrderLabelSettings"
    >
      <div class="row">
        <div class="col-md-5 offset-2">
          <p class="strong">Show the Following on the Label</p>
          <p v-if="store.modules.dailyOrderNumbers">
            <b-form-checkbox v-model="orderLabelSettings.daily_order_number"
              >Daily Order Numbers</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.customer"
              >Client Name</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.address"
              >Client Address</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.phone"
              >Client Phone Number</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.delivery"
              >Client Delivery Instructions</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.order_number"
              >Order ID</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.order_date"
              >Order Date</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.delivery_date"
              >Delivery Date</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.amount"
              >Amount</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.balance"
              >Balance</b-form-checkbox
            >
          </p>
          <p v-if="store.modules.pickupLocations">
            <b-form-checkbox v-model="orderLabelSettings.pickup_location"
              >Pickup Location</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.website"
              >Website</b-form-checkbox
            >
          </p>
          <p>
            <b-form-checkbox v-model="orderLabelSettings.social"
              >Social Media Handle</b-form-checkbox
            >
          </p>
        </div>
        <!-- <div class="col-md-5">
          <p class="strong">Label Dimensions</p>
          <b-form-group class="mt-3">
            <b-input-group size="md" append="width">
              <b-input v-model="orderLabelSettings.o_lab_width" type="number" />
            </b-input-group>
            <b-input-group size="md" append="height">
              <b-input v-model="orderLabelSettings.o_lab_height" type="number" />
            </b-input-group>
          </b-form-group>
        </div> -->
      </div>

      <b-btn
        variant="primary"
        class="float-right"
        @click="updateOrderLabelSettings"
        >Save</b-btn
      >
    </b-modal>
  </div>
</template>

<style lang="scss" scoped>
.report-date-picker {
  display: flex;
  justify-content: center;
}
</style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import vSelect from "vue-select";
import Spinner from "../../components/Spinner";
import checkDateRange from "../../mixins/deliveryDates";
import printer from "../../mixins/printer";
import { sleep } from "../../lib/utils";
import { PrintJob, PrintSize } from "../../store/printer";

export default {
  components: {
    vSelect,
    Spinner
  },
  data() {
    return {
      startingAddress: {},
      endingAddress: {},
      showStartEndAddressModal: false,
      orderPackageSummary: "order_summary",
      orderByRoutes: false,
      delivery_dates: {
        meal_quantities: [],
        meal_orders: [],
        ingredient_quantities: [],
        order_summary: [],
        package_order_summary: [],
        packing_slips: [],
        delivery_routes: [],
        payments: [],
        labels: [],
        order_labels: []
      },
      showSettingsModal: {
        labels: false,
        order_labels: false
      },
      labelSize: {
        width: 4,
        height: 6
      },
      labelSizeOptions: [
        {
          text: "A4",
          value: {
            width: 8.3,
            height: 11.7
          }
        },
        {
          text: "Letter",
          value: {
            width: 8.5,
            height: 11
          }
        },
        {
          text: '4" x 6"',
          value: {
            width: 4,
            height: 6
          }
        },
        {
          text: '4" x 8"',
          value: {
            width: 4,
            height: 8
          }
        },
        {
          text: '6" x 4"',
          value: {
            width: 6,
            height: 4
          }
        },
        {
          text: '8" x 11"',
          value: {
            width: 8,
            height: 11
          }
        }
      ],
      selectedPickupLocation: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading",
      pickupLocations: "viewedStorePickupLocations",
      labelSettings: "storeLabelSettings",
      orderLabelSettings: "storeOrderLabelSettings"
    }),
    pickupLocationOptions() {
      return this.pickupLocations.map(loc => {
        return {
          value: loc.id,
          text: loc.name,
          instructions: loc.instructions
        };
      });
    },
    selected() {
      return this.deliveryDates;
    },
    deliveryDates() {
      let grouped = [];
      this.orders.forEach(order => {
        if (!_.includes(grouped, order.delivery_date)) {
          grouped.push(order.delivery_date);
        }
      });
      this.deliveryDate = grouped[0];
      return grouped;
    }
  },
  mixins: [checkDateRange, printer],
  async mounted() {
    this.setDeliveryAddresses();
  },
  methods: {
    ...mapActions(["printer/connect", "refreshStoreReportSettings"]),
    setDeliveryAddresses() {
      let details = this.store.details;
      this.startingAddress.address = details.address;
      this.startingAddress.city = details.city;
      this.startingAddress.zip = details.zip;
      this.endingAddress.address = details.address;
      this.endingAddress.city = details.city;
      this.endingAddress.zip = details.zip;
    },

    async print(report, format = "pdf", page = 1) {
      let params = { page };

      this.delivery_dates["package_order_summary"] = this.delivery_dates[
        "order_summary"
      ];

      let dates = this.delivery_dates[report];

      if (dates.start && dates.end) {
        params.delivery_dates = {
          from: dates.start,
          to: dates.end
        };
        // const warning = this.checkDateRange({ ...dates });
        // if (report != "payments") {
        //   if (warning) {
        //     try {
        //       let dialog = await this.$dialog.confirm(
        //         "You have selected a date range which includes delivery days which haven't passe" +
        //           "d their cutoff period. This means new orders can still come in for those days. Continue?"
        //       );
        //       dialog.close();
        //     } catch (e) {
        //       return;
        //     }
        //   }
        // }
      }
      if (dates.start && !dates.end) {
        params.delivery_dates = {
          from: dates.start,
          to: dates.start
        };

        // const warning = this.checkDateRange({ ...dates });
        // if (report != "payments") {
        //   if (warning) {
        //     try {
        //       let dialog = await this.$dialog.confirm(
        //         "You have selected a date range which includes delivery days which haven't passe" +
        //           "d their cutoff period. This means new orders can still come in for those days. Continue?"
        //       );
        //       dialog.close();
        //     } catch (e) {
        //       return;
        //     }
        //   }
        // }
      }

      params.dailySummary = 0;

      params.pickupLocationId = this.selectedPickupLocation;

      params.byOrderDate = 0;

      params.labelsNutrition = this.labelsNutrition;

      params.width = this.labelSettings.width;
      params.height = this.labelSettings.height;

      params.orderByRoutes = this.orderByRoutes;
      params.startingAddress = this.startingAddress;
      params.endingAddress = this.endingAddress;

      if (report == "delivery_routes") {
        if (
          this.startingAddress.address === "" ||
          this.startingAddress.city === "" ||
          this.startingAddress.zip === ""
        ) {
          this.$toastr.w("Please add a full starting address");
          return;
        }
        if (
          this.endingAddress.address === "" ||
          this.endingAddress.city === "" ||
          this.endingAddress.zip === ""
        ) {
          this.$toastr.w("Please add a full ending address");
          return;
        }
      }

      if (
        report == "delivery_routes" &&
        (this.store.id == 108 ||
          this.store.id == 109 ||
          this.store.id == 110 ||
          this.store.id == 278)
      ) {
        report = "delivery_routes_livotis";
      }

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
        .then(response => {
          const { data } = response;

          if (format === "b64") {
            const size = new PrintSize(
              this.labelSettings.width,
              this.labelSettings.height
            );
            const margins = {
              top: 0,
              right: 0,
              bottom: 0,
              left: 0
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
              this.$toastr.w(
                "Please add a popup exception to print this report.",
                "Failed to display PDF."
              );
            }
          }
        })
        .catch(err => {
          this.$toastr.w(
            "Please confirm that orders exist for the selected date range and disable any popup blocker in our browser.",
            "Failed to print report."
          );
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async exportData(report, format = "pdf", print = false, page = 1) {
      let params = { page };

      params.orderByRoutes = this.orderByRoutes;
      params.startingAddress = this.startingAddress;
      params.endingAddress = this.endingAddress;

      this.delivery_dates["package_order_summary"] = this.delivery_dates[
        "order_summary"
      ];

      let dates = this.delivery_dates[report];
      if (dates.start && dates.end) {
        params.delivery_dates = {
          from: dates.start,
          to: dates.end
        };

        const warning = this.checkDateRange({ ...dates });
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
      }

      params.dailySummary = 0;

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
        .then(response => {
          const { data } = response;
          if (!_.isEmpty(data.url)) {
            let win = window.open(data.url);

            if (win) {
              if (print) {
                win.addEventListener(
                  "load",
                  () => {
                    win.print();
                  },
                  false
                );
              }

              if (data.next_page && data.next_page !== page) {
                this.exportData(report, format, print, data.next_page);
              }
            } else {
              this.$toastr.w(
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
    clearDates(report) {
      switch (report) {
        case "meal_orders":
          this.delivery_dates.meal_orders.start = null;
          this.delivery_dates.meal_orders.end = null;
          this.$refs.mealOrdersDates.clearDates();
          break;
        case "ingredient_quantities":
          this.delivery_dates.ingredient_quantities.start = null;
          this.delivery_dates.ingredient_quantities.end = null;
          this.$refs.ingredientQuantitiesDates.clearDates();
          break;
        case "order_summary":
          this.delivery_dates.order_summary.start = null;
          this.delivery_dates.order_summary.end = null;
          this.$refs.ordersByCustomerDates.clearDates();
          break;
        case "packing_slips":
          this.delivery_dates.packing_slips.start = null;
          this.delivery_dates.packing_slips.end = null;
          this.$refs.packingSlipsDates.clearDates();
          break;
        case "delivery_routes":
          this.delivery_dates.delivery_routes.start = null;
          this.delivery_dates.delivery_routes.end = null;
          this.$refs.deliveryRoutesDates.clearDates();
          break;
        case "payments":
          this.delivery_dates.payments.start = null;
          this.delivery_dates.payments.end = null;
          this.$refs.paymentsDates.clearDates();
          break;
        case "labels":
          this.delivery_dates.labels.start = null;
          this.delivery_dates.labels.end = null;
          this.$refs.labelsDates.clearDates();
          break;
        case "order_labels":
          this.delivery_dates.order_labels.start = null;
          this.delivery_dates.order_labels.end = null;
          this.$refs.orderLabelsDates.clearDates();
          break;
      }
    },
    updateLabelSettings() {
      let settings = { ...this.labelSettings };
      axios
        .post("/api/me/updateLabelSettings", settings)
        .then(response => {
          this.$toastr.s("Your settings have been saved.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    },
    updateOrderLabelSettings() {
      let settings = { ...this.orderLabelSettings };
      axios
        .post("/api/me/updateOrderLabelSettings", settings)
        .then(response => {
          this.$toastr.s("Your settings have been saved.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    }
  }
};
</script>
