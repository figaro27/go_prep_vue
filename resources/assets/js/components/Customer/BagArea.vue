<template>
  <div>
    <div
      class="bag-header center-text pt-3"
      v-if="
        $route.name === 'customer-menu' ||
          (($route.params.storeView || storeView) &&
            $route.name != 'store-bag') ||
          subscriptionId != null
      "
    >
      <h3 class="d-inline ml-3 float-left">
        <i
          v-if="!$route.params.storeView && !storeView"
          class="fa fa-angle-right white-text"
          @click="$parent.showBag()"
        ></i>
      </h3>
      <h3 class="white-text d-inline">My Bag</h3>
      <p class="white-text d-inline">({{ total }} Items)</p>
      <i
        class="fas fa-trash white-text d-inline bag-icon float-right pt-2 pr-3"
        @click="clearAll"
      ></i>
    </div>
    <div :class="shoppingCartClass" style="overflow-y: auto">
      <!-- <b-alert
        v-if="isMultipleDelivery"
        variant="secondary"
        show
      >
        <h6>Ordering for {{selectedDeliveryDay ? moment(selectedDeliveryDay.day_friendly).format("ddd, MMM Do YYYY") : null}}</h6>
        <div style="text-align:center">
        <button
            v-if="
              isMultipleDelivery &&
                $route.name != 'store-bag' &&
                $route.name != 'customer-bag'
            "
            :style="brandColor"
            type="button"
            class="mt-1 btn btn-md white-text"
            @click="addDeliveryDay()"
          >
            Add/Change Day
          </button>
        </div>
      </b-alert> -->

      <center>
        <b-form-radio-group
          v-if="
            isMultipleDelivery &&
              $route.name != 'store-bag' &&
              $route.name != 'customer-bag' &&
              hasBothTransferTypes
          "
          buttons
          class="filters mb-3"
          v-model="transferDayType"
          :options="[
            { value: 1, text: 'Pickup' },
            { value: 0, text: 'Delivery' }
          ]"
          @change="val => changeTransferType(val)"
        ></b-form-radio-group>
      </center>
      <button
        v-if="
          isMultipleDelivery &&
            $route.name != 'store-bag' &&
            $route.name != 'customer-bag'
        "
        style="background-color:#28A745"
        type="button"
        class="mb-3 btn btn-md white-text w-100"
        @click="addDeliveryDay()"
      >
        Add Day
      </button>
      <b-alert
        v-if="bag.length == 0 && !isMultipleDelivery"
        variant="secondary"
        show
        class="d-flex d-center"
      >
        <span class="small strong">Please add items to your bag.</span>
      </b-alert>

      <ul class="list-group">
        <li
          v-for="(groupItem, indexGroup) in groupBag"
          :key="`bagGroup-${indexGroup}`"
          class="bag-item"
        >
          <div
            style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;padding-top:5px;border-radius:10px"
            v-if="isMultipleDelivery && groupItem.delivery_day"
            :style="activeDD(groupItem.delivery_day)"
          >
            <div
              v-if="
                groupItem &&
                  !enablingDeliveryDayEdit[groupItem.delivery_day.day_friendly]
              "
            >
              <h5 @click="loadDeliveryDayMenu(groupItem.delivery_day)">
                ({{
                  groupItem.delivery_day.type.charAt(0).toUpperCase() +
                    groupItem.delivery_day.type.slice(1)
                }})
                {{
                  moment(groupItem.delivery_day.day_friendly).format(
                    "ddd, MMM Do"
                  )
                }}
              </h5>
            </div>
            <div
              v-if="
                enablingDeliveryDayEdit[groupItem.delivery_day.day_friendly]
              "
            >
              <b-form-select
                :options="allDeliveryDays"
                class="ml-2 mb-1 w-180"
                style="height:25px"
                @input="
                  val => updateSelectedDeliveryDay(val, groupItem.delivery_day)
                "
              ></b-form-select>
            </div>
            <i
              v-if="
                ($route.params.storeView ||
                  storeView !== undefined ||
                  context == 'store') &&
                  (enablingDeliveryDayEdit[groupItem.delivery_day.day_friendly]
                    ? enablingDeliveryDayEdit[
                        groupItem.delivery_day.day_friendly
                      ] === false
                    : true)
              "
              @click="
                enableDeliveryDayEdit(groupItem.delivery_day.day_friendly)
              "
              class="fa fa-edit text-warning ml-2 mb-2 white-text"
              :style="checkMarkStyle"
            ></i>
            <i
              class="fas fa-arrow-circle-left ml-1 mb-2"
              :style="checkMarkStyle"
              v-if="
                isMultipleDelivery &&
                  $route.name != 'store-bag' &&
                  $route.name != 'customer-bag' &&
                  selectedDeliveryDay &&
                  groupItem.delivery_day.id !== selectedDeliveryDay.id
              "
              @click="loadDeliveryDayMenu(groupItem.delivery_day)"
            >
            </i>
            <i
              v-if="activeDD(groupItem.delivery_day)"
              style="font-size:16px"
              class="fas fa-times-circle white-text d-inline float-right pb-2 pl-2"
              @click="removeDeliveryDayItems(groupItem.delivery_day)"
            ></i>
            <i
              v-if="!activeDD(groupItem.delivery_day)"
              style="font-size:16px"
              class="fas fa-times-circle dark-gray d-inline float-right pb-2 pl-1"
              @click="removeDeliveryDayItems(groupItem.delivery_day)"
            ></i>

            <!-- <button
              v-if="$route.name != 'store-bag' && $route.name != 'customer-bag' && selectedDeliveryDay && groupItem.delivery_day.id !== selectedDeliveryDay.id"
              type="button"
              class="btn btn-sm white-text"
              :style="brandColor"
              @click="loadDeliveryDayMenu(groupItem.delivery_day)"
            >
            Select Day
            </button> -->
          </div>
          <div
            v-for="(item, mealId) in groupItem.items"
            :key="`bag-${mealId}`"
            style="margin-bottom: 12px;"
          >
            <div
              v-if="item && item.quantity > 0"
              class="d-flex align-items-center"
            >
              <div class="bag-item-quantity mr-2">
                <div
                  @click="addToBag(item)"
                  class="bag-plus-minus brand-color white-text"
                >
                  <i>+</i>
                </div>
                <p class="bag-quantity">{{ item.quantity }}</p>
                <div
                  @click="removeFromBag(item)"
                  class="bag-plus-minus gray white-text"
                >
                  <i>-</i>
                </div>
              </div>
              <div class="bag-item-image mr-2">
                <thumbnail
                  v-if="item.meal.image != null"
                  :src="item.meal.image.url_thumb"
                  :spinner="false"
                  class="cart-item-img"
                  width="80px"
                ></thumbnail>
              </div>
              <div class="mr-2">
                <span v-if="enablingEdit[item.guid] ? true : false">
                  <b-form-input
                    placeholder="Title"
                    v-model="customTitle[item.guid]"
                  ></b-form-input>
                  <b-form-input
                    v-if="item.size"
                    placeholder="Size"
                    v-model="customSize[item.guid]"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customPrice[item.guid]"
                  ></b-form-input>
                </span>

                <span v-else>
                  <span v-if="item.meal_package || item.meal.gift_card">
                    {{ item.meal.title }}
                    <span v-if="item.size && item.size.title !== 'Regular'">
                      - {{ item.size.title }}
                    </span>

                    <b-form-input
                      v-if="
                        item.meal.gift_card &&
                          ($route.name === 'customer-bag' ||
                            $route.name === 'store-bag')
                      "
                      placeholder="Optional Email Recipient"
                      @input="
                        val => {
                          item.emailRecipient = val;
                        }
                      "
                    >
                    </b-form-input>
                  </span>

                  <span
                    v-else-if="item.size && item.size.title !== 'Regular'"
                    >{{ item.size.full_title }}</span
                  >
                  <span v-else>{{ item.meal.item_title }}</span>
                  <div
                    class="w-100 pt-1"
                    v-if="$route.params.storeView || storeView"
                  >
                    <input
                      v-if="enablingEdit[item.guid] ? false : true"
                      type="checkbox"
                      :id="`checkox_${mealId}`"
                      v-model="item.free"
                      @change="e => makeFree(item, e)"
                    />
                    <label
                      :for="`checkox_${mealId}`"
                      v-if="enablingEdit[item.guid] ? false : true"
                      >Free</label
                    >
                    <span>{{
                      format.money(
                        item.price * item.quantity,
                        storeSettings.currency
                      )
                    }}</span>
                  </div>
                  <div v-else>
                    <span>{{
                      format.money(
                        item.price * item.quantity,
                        storeSettings.currency
                      )
                    }}</span>
                  </div>
                </span>

                <p v-if="item.special_instructions" class="small">
                  {{ item.special_instructions }}
                </p>
                <ul
                  v-if="!item.meal_package && (item.components || item.addons)"
                  class="plain"
                >
                  <li
                    v-for="(component, index) in itemComponents(item)"
                    v-bind:key="'itemComponent' + index"
                    class="plain"
                  >
                    {{ component }}
                  </li>
                  <li
                    v-for="(addon, index) in itemAddons(item)"
                    v-bind:key="'itemAddon' + index"
                    class="plus"
                  >
                    {{ addon }}
                  </li>
                </ul>

                <!-- <div v-if="!$route.params.storeView && !storeView">
                  <span>{{format.money(item.price * item.quantity, storeSettings.currency ) }}</span>
                </div> -->
                <span
                  v-if="
                    store.modules.frequencyItems &&
                      item.meal &&
                      !adjustingSubscription &&
                      !adjustingOrder
                  "
                >
                  <span
                    v-if="
                      item.meal &&
                        item.meal.frequencyType &&
                        item.meal.frequencyType === 'sub' &&
                        !item.meal.toggled
                    "
                  >
                    <i
                      class="fas fa-check-circle text-primary pt-1 font-15"
                      @click="showSubOnlyToast(true)"
                    ></i>
                    Subscription
                  </span>
                  <span
                    v-if="
                      item.meal &&
                        item.meal.frequencyType &&
                        item.meal.frequencyType === 'order' &&
                        !item.meal.toggled
                    "
                  >
                    <i
                      class="fas fa-times-circle pt-1 font-15"
                      @click="showSubOnlyToast(false)"
                    ></i>
                    Subscription
                  </span>
                  <span
                    v-if="
                      (item.meal &&
                        item.meal.frequencyType &&
                        item.meal.frequencyType === 'none') ||
                        item.meal.toggled
                    "
                    class="d-flex d-inline"
                  >
                    <b-form-checkbox
                      v-model="item.meal.subItem"
                      @input="val => changeFrequencyType(item, val)"
                      readonly
                    ></b-form-checkbox
                    >Subscription
                  </span>
                </span>
              </div>

              <div class="flex-grow-0">
                <i
                  v-if="
                    ($route.params.storeView || storeView !== undefined) &&
                      (enablingEdit[item.guid]
                        ? enablingEdit[item.guid] === false
                        : true)
                  "
                  @click="enableEdit(item)"
                  class="fa fa-edit text-warning font-15 pt-1"
                ></i>
                <i
                  v-if="
                    ($route.params.storeView || storeView !== undefined) &&
                      enablingEdit[item.guid] === true
                  "
                  class="fas fa-check-circle text-primary pt-1 font-15"
                  @click="endEdit(item)"
                ></i>

                <i
                  class="fas fa-times-circle clear-meal dark-gray font-15"
                  @click="clearFromBag(item)"
                ></i>
              </div>
            </div>
            <ul>
              <li v-for="(mealItem, i) in getItemMeals(item)" :key="i">
                <div
                  class="medium"
                  style="display: flex; align-items: center; margin-bottom: 5px;"
                >
                  <div
                    v-if="$route.params.storeView || storeView"
                    style="display: flex; align-items: center;"
                  >
                    <div
                      @click="adjustMinus(mealItem, item)"
                      class="bag-plus-minus gray white-text"
                      style="margin-right: 3px;"
                    >
                      <i>-</i>
                    </div>
                    <div
                      @click="adjustPlus(mealItem, item)"
                      class="bag-plus-minus brand-color white-text"
                      style="margin-right: 5px;"
                    >
                      <i>+</i>
                    </div>
                  </div>

                  {{ mealItem.quantity }} x
                  {{ mealItem.title ? mealItem.title : mealItem.meal.title }}
                </div>
                <div class="small" v-if="mealItem.special_instructions != null">
                  {{ mealItem.special_instructions }}
                </div>
              </li>
            </ul>
          </div>
          <h5
            class="align-right"
            v-if="isMultipleDelivery && groupItem.delivery_day"
          >
            {{ getDeliveryDayTotal(groupItem) }}
          </h5>
        </li>
      </ul>
      <!-- <center>
      <button
        v-if="
          isMultipleDelivery &&
            $route.name != 'store-bag' &&
            $route.name != 'customer-bag'
        "
        :style="brandColor"
        type="button"
        class="mt-3 btn btn-md white-text"
        @click="addDeliveryDay()"
      >
        Add/Change Day
      </button>
    </center> -->
    </div>
    <div
      v-if="
        ($route.params.storeView || storeView) &&
          storeModules.lineItems &&
          !storeModules.multipleDeliveryDays
      "
    >
      <b-button
        size="lg"
        variant="success"
        class="d-inline"
        @click="showLineItemModal = true"
        v-if="
          ($route.params.manualOrder && $route.name != 'store-manual-order') ||
            ($route.params.adjustOrder && $route.name != 'store-adjust-order')
        "
      >
        <span class="d-sm-inline">Add Extra</span>
      </b-button>
      <span
        class="d-inline"
        v-if="
          ($route.params.manualOrder && $route.name != 'store-manual-order') ||
            ($route.params.adjustOrder && $route.name != 'store-adjust-order')
        "
        ><img
          v-b-popover.hover="
            'Here you can add any extra line item to the order for your customer. An example may include a customized meal that you don\'t want to have available to all on your main menu.'
          "
          title="Extras"
          src="/images/store/popover.png"
          class="popover-size"
      /></span>
      <ul class="list-group mt-2">
        <li
          v-for="(orderLineItem, index) in orderLineItems"
          v-bind:key="'orderLineItem' + index"
          class="bag-item"
        >
          <div
            v-if="orderLineItem.quantity > 0"
            class="d-flex align-items-center"
          >
            <div class="bag-item-quantity mr-2">
              <div
                class="bag-plus-minus brand-color white-text"
                @click="updateLineItems(orderLineItem, 1)"
              >
                <i>+</i>
              </div>
              <p class="bag-quantity">{{ orderLineItem.quantity }}</p>
              <div
                class="bag-plus-minus gray white-text"
                @click="updateLineItems(orderLineItem, -1)"
              >
                <i>-</i>
              </div>
            </div>
            <div class="bag-item-image mr-2">
              <span class="cart-item-img" width="80px"></span>
            </div>
            <div class="flex-grow-1">
              <span>
                <p>
                  {{
                    orderLineItem.size
                      ? orderLineItem.size + " - " + orderLineItem.title
                      : orderLineItem.title
                  }}
                  -
                  {{
                    format.money(
                      orderLineItem.price * orderLineItem.quantity,
                      storeSettings.currency
                    )
                  }}
                </p>
              </span>
            </div>
            <div class="flex-grow-0">
              <i
                class="fas fa-times-circle clear-meal dark-gray pt-2"
                @click="removeLineItem(index)"
              ></i>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <div class="row mt-5" v-if="store.modules.orderNotes && bagView">
      <div class="col-md-12" v-if="context === 'store'">
        <h4>Private Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          :value="bagNotes"
          @input="setNotes($event.target.value, 'private')"
          placeholder="Private notes found on your orders page and Order Summary report."
        ></textarea>
      </div>
      <div class="col-md-12 pt-2" v-if="context === 'store'">
        <h4 v-if="context === 'store'">Public Notes</h4>
        <h4 v-else>Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          :value="bagPublicNotes"
          @input="setNotes($event.target.value, 'public')"
          placeholder="Public notes sent to the customer in their emails and shown on your packing slips."
        ></textarea>
      </div>
    </div>

    <div
      v-if="
        store.module_settings.orderNotesForCustomer &&
          context !== 'store' &&
          $route.path === '/customer/bag' &&
          loggedIn
      "
    >
      <div class="col-md-12 pt-2 pb-4">
        <h4>Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          :value="bagPublicNotes"
          @input="setNotes($event.target.value, 'public')"
          placeholder="Add any special notes about your order here such as if you want your order to be delivered to a different address."
        ></textarea>
      </div>
    </div>

    <b-modal
      size="xl"
      title="Add New Extra"
      v-model="showLineItemModal"
      v-if="showLineItemModal"
      hide-footer
    >
      <h3 class="center-text mt-3">Add New</h3>
      <div class="row">
        <b-input-group>
          <div class="col-md-4">
            <b-form-input
              v-model="lineItem.size"
              placeholder="Size"
              class="mr-3"
            ></b-form-input>
          </div>
          <div class="col-md-4">
            <b-form-input
              v-model="lineItem.title"
              placeholder="Title"
              class="mr-3"
              required
            ></b-form-input>
          </div>
          <div class="col-md-2">
            <b-form-input
              v-model="lineItem.price"
              placeholder="Price"
              class="mr-3"
              required
              type="number"
              min="0"
            ></b-form-input>
          </div>
          <div class="col-md-2">
            <b-btn variant="success" @click="addLineItem(0)">Add</b-btn>
          </div>
        </b-input-group>
      </div>

      <div v-if="storeModules.productionGroups" class="width-70 row mt-2">
        <p class="col-md-2 pt-1">Production:</p>
        <v-select
          class="col-md-6"
          v-model="lineItem.production_group_id"
          label="text"
          :options="productionGroupOptions"
          :reduce="group => group.value"
        >
        </v-select>
      </div>

      <hr />

      <h3 class="center-text mt-5">Or Select From Existing</h3>
      <div class="row mt-5">
        <div class="col-md-7">
          <v-select
            label="text"
            :options="lineItemOptions"
            v-model="selectedLineItem"
            class="mr-3 w-100"
          >
          </v-select>
        </div>
        <div class="col-md-3">
          <p class="pt-1 mr-3 center-text">
            {{ format.money(selectedLineItem.price, storeSettings.currency) }}
          </p>
        </div>
        <div class="col-md-2">
          <b-btn class="mb-5" variant="success" @click="addLineItem(1)"
            >Add</b-btn
          >
        </div>
      </div>
    </b-modal>

    <v-style>
      .bag-header{ height:70px !important; background-color:
      {{ store.settings.color }}; margin-bottom: 15px; padding-top: 10px }
    </v-style>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import store from "../../store";
import format from "../../lib/format";
import DeliveryDayModal from "../../views/Customer/Modals/DeliveryDayModal";

export default {
  components: {
    DeliveryDayModal
  },
  data() {
    return {
      notes: null,
      publicNotes: null,
      enablingEdit: {},
      enablingDeliveryDayEdit: {},
      customTitle: {},
      customSize: {},
      customPrice: {},
      orderNotes: null,
      publicOrderNotes: null,
      showLineItemModal: false,
      lineItem: {
        id: null,
        title: "",
        price: null,
        quantity: 1,
        production_group_id: null
      },
      selectedLineItem: {},
      orderLineItems: [],
      showZipCodeModal: false
    };
  },
  props: {
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: null,
    storeView: false,
    selectedDeliveryDay: null
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      context: "context",
      storeCustomers: "storeCustomers",
      storeModules: "viewedStoreModules",
      storeSettings: "viewedStoreSetting",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      lineItems: "viewedStoreLineItems",
      storeProductionGroups: "storeProductionGroups",
      bagPickup: "bagPickup",
      bagZipCode: "bagZipCode",
      multDDZipCode: "bagMultDDZipCode",
      bagNotes: "bagNotes",
      bagPublicNotes: "bagPublicNotes"
    }),
    allDeliveryDays() {
      let allDays = [];

      for (let i = 0; i <= 60; i++) {
        let now = moment();
        let date = now.add(i, "days");
        allDays.push({
          value: date,
          text: date.format("ddd, MMM Do")
        });
      }

      return allDays;
    },
    adjustingOrder() {
      if (this.$route.params.adjustOrder || this.$route.params.orderId) {
        return true;
      }
    },
    hasBothTransferTypes() {
      let hasPickup = false;
      let hasDelivery = false;
      this.store.delivery_days.forEach(day => {
        if (day.type === "delivery") {
          hasDelivery = true;
        }
        if (day.type === "pickup") {
          hasPickup = true;
        }
      });
      if (hasPickup && hasDelivery) {
        return true;
      } else {
        return false;
      }
    },
    isMultipleDelivery() {
      return this.storeModules.multipleDeliveryDays == 1 ? true : false;
    },
    brandColor() {
      let style = "background-color:";
      style += this.store.settings.color;
      return style;
    },
    checkMarkStyle() {
      let style = "font-size:16px;color:";
      style += this.store.settings.color;
      return style;
    },
    productionGroupOptions() {
      let prodGroups = this.storeProductionGroups;
      let prodGroupOptions = [];

      prodGroups.forEach(prodGroup => {
        prodGroupOptions.push({ text: prodGroup.title, value: prodGroup.id });
      });
      return prodGroupOptions;
    },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheckDelivery() {
      if (_.includes(this.transferType, "delivery")) return true;
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferType, "pickup")) return true;
    },
    storeSettings() {
      return this.store.settings;
    },
    lineItemTotal() {
      let totalLineItemsPrice = 0;
      this.orderLineItems.forEach(orderLineItem => {
        totalLineItemsPrice += orderLineItem.price * orderLineItem.quantity;
      });
      return totalLineItemsPrice;
    },
    lineItemOptions() {
      let options = [];
      this.lineItems.forEach(lineItem => {
        options.push({
          id: lineItem.id,
          text: lineItem.full_title,
          price: lineItem.price,
          title: lineItem.title,
          full_title: lineItem.full_title,
          quantity: 1
        });
      });
      return options;
    },
    shoppingCartClass() {
      if (this.$route.name === "store-bag")
        return "shopping-cart-meals-bag area-scroll";
      else return "shopping-cart-meals-menu";
    }
  },
  mounted() {
    if (this.bag) {
      this.bag.forEach(item => {
        // Remove item if MDD and not in a delivery day
        if (this.store.modules.multipleDeliveryDays && !item.delivery_day) {
          this.clearMealFullQuantity(
            item.meal,
            item.meal_package,
            item.size,
            item.components,
            item.addons,
            item.special_instructions
          );
        }

        // Disable editing
        this.$set(this.enablingEdit, item.guid, false);
      });
    }

    let lineItemsOrder = [];

    if (this.$route.params && this.$route.params.line_items_order) {
      lineItemsOrder = this.$route.params.order.line_items_order;
    }

    lineItemsOrder.forEach(lineItemOrder => {
      this.orderLineItems.push(lineItemOrder);
    });
    this.$emit("updateLineItems", this.orderLineItems);

    // If adjusting
    if (
      this.store.modules.multipleDeliveryDays &&
      (this.$route.params.order || this.$parent.subscription)
    ) {
      this.autoPickAdjustDD();
    }

    if (this.$route.params.lineItemOrders) {
      this.orderLineItems = this.$route.params.lineItemOrders;
    }
  },
  methods: {
    loadDeliveryDayMenu(deliveryDay) {
      this.$emit("changeDeliveryDay", deliveryDay);
    },
    ...mapMutations({
      setBagPickup: "setBagPickup",
      setMultDDZipCode: "setMultDDZipCode",
      setBagZipCode: "setBagZipCode",
      updateBagItemFrequency: "updateBagItemFrequency",
      setBagNotes: "setBagNotes",
      setBagPublicNotes: "setBagPublicNotes"
    }),
    addToBag(item) {
      item.meal.quantity = 1;
      if (this.isAdjustOrder() || this.isManualOrder()) {
        this.addOneFromAdjust({
          ...item,
          quantity: 1
        });
      } else {
        this.addOne(
          item.meal,
          item.meal_package,
          item.size,
          item.components,
          item.addons,
          item.special_instructions
        );
      }
    },
    clearFromBag(item) {
      if (this.isAdjustOrder() || this.isManualOrder() || this.subscriptionId) {
        this.removeFromAdjust(item);
      } else {
        this.clearMealFullQuantity(
          item.meal,
          item.meal_package,
          item.size,
          item.components,
          item.addons,
          item.special_instructions
        );
      }
    },
    removeFromBag(item) {
      if (this.isAdjustOrder() || this.isManualOrder() || this.subscriptionId) {
        this.removeOneFromAdjust({
          ...item,
          quantity: 1
        });
      } else {
        if (
          !item.meal.force_quantity ||
          item.quantity > item.meal.force_quantity
        ) {
          this.minusOne(
            item.meal,
            item.meal_package,
            item.size,
            item.components,
            item.addons,
            item.special_instructions
          );
        } else {
          this.$toastr.w(
            "You must order at least " +
              item.meal.force_quantity +
              " of this item."
          );
        }
      }
    },
    adjustMinus(mealItem, item) {
      this.updateOneSubItemFromAdjust(mealItem, item, false);
    },
    adjustPlus(mealItem, item) {
      this.updateOneSubItemFromAdjust(mealItem, item, true);
    },
    isManualOrder() {
      if (
        this.manualOrder ||
        this.$route.params.manualOrder ||
        this.$route.name == "store-manual-order"
      ) {
        return true;
      }
      return false;
    },
    isAdjustOrder() {
      if (
        this.adjustOrder ||
        this.$route.params.adjustOrder ||
        this.$route.name == "store-adjust-order"
      ) {
        return true;
      }
      return false;
    },
    getItemMeals(item) {
      const mealPackage = !!item.meal_package;

      if (!mealPackage || !item.meal) {
        return [];
      }

      const pkg = this.getMealPackage(item.meal.id, item.meal);
      //const size = pkg && item.size ? pkg.getSize(item.size.id) : null;
      const size = pkg && item.size ? item.size : null;

      const packageMeals = size ? size.meals : pkg ? pkg.meals : null;

      let mealQuantities = _.mapValues(
        _.keyBy(packageMeals, pkgMeal => {
          return JSON.stringify({
            mealId: pkgMeal.id,
            sizeId: pkgMeal.meal_size_id,
            itemId: pkgMeal.item_id
          });
        }),
        mealItem => {
          return {
            quantity: mealItem.quantity,
            meal: mealItem,
            special_instructions: mealItem.special_instructions
          };
        }
      );

      // Add on component option selections
      _(item.components).forEach((options, componentId) => {
        const component = pkg.getComponent(componentId);
        const optionIds = mealPackage ? Object.keys(options) : options;

        _.forEach(optionIds, optionId => {
          const option = pkg.getComponentOption(component, optionId);
          if (!option) {
            return null;
          }

          //if (option.selectable) {
          _.forEach(options[option.id], item => {
            const mealId = item.meal_id;
            const sizeId = item.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += item.quantity;
            } else if (item.meal) {
              mealQuantities[guid] = {
                quantity: item.quantity,
                meal: item.meal,
                special_instructions: item.special_instructions
              };
            }
          });
          /*} else {
            _.forEach(option.meals, mealItem => {
              const mealId = mealItem.meal_id;
              const sizeId = mealItem.meal_size_id;
              const guid = JSON.stringify({ mealId, sizeId });

              if (mealQuantities[guid]) {
                mealQuantities[guid].quantity += mealItem.quantity;
              } else if (item.meal) {
                mealQuantities[guid] = {
                  quantity: item.quantity,
                  meal: item.meal,
                  special_instructions: item.special_instructions
                };
              }
            });
          }*/
        });
      });

      _(item.addons).forEach((addonItems, addonId) => {
        const addon = pkg.getAddon(addonId);

        if (addon.selectable) {
          _.forEach(addonItems, addonItem => {
            const mealId = addonItem.meal_id;
            const sizeId = addonItem.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += addonItem.quantity;
            } else if (addonItem.meal) {
              mealQuantities[guid] = {
                quantity: addonItem.quantity,
                meal: addonItem.meal,
                special_instructions: addonItem.special_instructions
              };
            }
          });
        } else {
          _.forEach(addonItems, addonItem => {
            const mealId = addonItem.meal_id;
            const sizeId = addonItem.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += addonItem.quantity;
            } else if (addonItem.meal) {
              mealQuantities[guid] = {
                quantity: addonItem.quantity,
                meal: addonItem.meal,
                special_instructions: addonItem.special_instructions
              };
            }
          });
        }
      });

      const meals = _(mealQuantities)
        .map((item, guid) => {
          if (
            !item.hasOwnProperty("quantity") ||
            !item.hasOwnProperty("meal")
          ) {
            return null;
          }

          const { mealId, sizeId } = JSON.parse(guid);
          const meal = this.getMeal(mealId, item.meal);

          if (!meal) return null;

          //const size = meal && sizeId ? meal.getSize(sizeId) : null;
          const size =
            meal && meal.meal_size
              ? meal.meal_size
              : meal && sizeId
              ? meal.getSize(sizeId)
              : null;
          const title = size ? size.full_title : meal.full_title;

          const special_instructions = item.special_instructions;

          return {
            meal,
            size,
            quantity: item.quantity,
            title,
            special_instructions
          };
        })
        .filter()
        .value();

      return meals;
    },
    addLineItem(existing) {
      if (
        !existing &&
        (this.lineItem.title === null || this.lineItem.title === "")
      ) {
        this.$toastr.w("Please add a title for the extra item.");
        return;
      }
      if (
        !existing &&
        (this.lineItem.price === null || this.lineItem.price === "")
      ) {
        this.$toastr.w("Please add a price for the extra item.");
        return;
      }

      if (existing) {
        if (Object.keys(this.selectedLineItem).length === 0) {
          this.$toastr.w("Please select an extra from the dropdown.");
          return;
        }
        if (this.orderLineItems.includes(this.selectedLineItem)) {
          let index = _.findIndex(this.orderLineItems, orderLineItem => {
            return orderLineItem.title === this.selectedLineItem.title;
          });
          this.orderLineItems[index].quantity += 1;
        } else {
          this.orderLineItems.push(this.selectedLineItem);
        }
        this.showLineItemModal = false;
        this.lineItem = { title: "", price: null, quantity: 1 };
        this.selectedLineItem = { title: "", price: null, quantity: 1 };
      } else {
        if (
          this.store.modules.productionGroups &&
          (this.lineItem.production_group_id === null ||
            this.lineItem.production_group_id === undefined)
        ) {
          this.$toastr.w(
            "Please select a production group or the extra won't show up in your reports."
          );
          return;
        }

        axios.post("/api/me/lineItems", this.lineItem).then(resp => {
          this.lineItem.id = resp.data.id;
          this.lineItem.production_group_id = null;
          this.orderLineItems.push(this.lineItem);
          this.showLineItemModal = false;
          this.lineItem = { title: "", price: null, quantity: 1 };
          this.selectedLineItem = { title: "", price: null, quantity: 1 };
        });
      }
      this.$parent.checkoutData.lineItemOrders = this.orderLineItems;
    },
    updateLineItems(orderLineItem, increment) {
      orderLineItem.quantity += increment;
      if (orderLineItem.quantity === 0) {
        this.orderLineItems.pop(orderLineItem);
      }

      this.$emit("updateData", {
        lineItemOrders: this.orderLineItems
      });
    },
    removeLineItem(index) {
      this.orderLineItems.splice(index, 1);
    },
    makeFree(item, event) {
      item.free = event.target.checked;
      this.updateBagItem(item);
    },
    enableEdit(item) {
      this.$nextTick(() => {
        this.$set(this.enablingEdit, item.guid, true);
      });
      this.customTitle[item.guid] = item.meal.title;
      this.customSize[item.guid] = item.size ? item.size.title : null;
      this.customPrice[item.guid] = item.price;
    },
    endEdit(item) {
      item.meal.title = this.customTitle[item.guid];
      item.customTitle = this.customTitle[item.guid];
      item.size ? (item.size.title = this.customSize[item.guid]) : null;
      item.customSize = this.customSize[item.guid];

      if (this.customSize[item.guid]) {
        item.size.title = this.customSize[item.guid];
        item.size.full_title =
          this.customTitle[item.guid] + " - " + this.customSize[item.guid];
      } else {
        item.meal.item_title = this.customTitle[item.guid];
      }

      item.price = this.customPrice[item.guid];
      this.updateBagItem(item);

      this.$nextTick(() => {
        this.$set(this.enablingEdit, item.guid, false);
      });
    },
    enableDeliveryDayEdit(dayFriendly) {
      this.$nextTick(() => {
        this.$set(this.enablingDeliveryDayEdit, dayFriendly, true);
      });
    },
    setOrderLineItems(lineItemOrders) {
      let extras = lineItemOrders;
      this.orderLineItems = this.$route.params.adjustOrder
        ? this.$parent.order.line_items_order
        : extras;
      this.$emit("updateLineItems", this.orderLineItems);
    },
    addDeliveryDay() {
      store.dispatch("refreshDeliveryDay");
      this.$parent.showDeliveryDayModal = true;
    },
    activeDD(deliveryDay) {
      if (
        this.selectedDeliveryDay &&
        deliveryDay.day_friendly == this.selectedDeliveryDay.day_friendly
      ) {
        if (this.store.settings) {
          let style = "color:#ffffff;background-color:";
          style += this.store.settings.color;
          return style;
        }
      }
    },
    removeDeliveryDayItems(deliveryDay) {
      this.bag.forEach(item => {
        if (item.delivery_day.day_friendly == deliveryDay.day_friendly) {
          this.clearMealFullQuantity(
            item.meal,
            item.meal_package,
            item.size,
            item.components,
            item.addons,
            item.special_instructions
          );
        }
      });

      // Switch to the first delivery day in the bag if exists
      this.loadDeliveryDayMenu(this.bag[0].delivery_day);
    },
    changeTransferType(val) {
      this.$store.commit("emptyBag");
      this.setBagPickup(val);
      this.$parent.autoPickUpcomingMultDD(null);
      if (
        this.bagPickup == 0 &&
        !this.bagZipCode &&
        this.hasDeliveryDayZipCodes &&
        this.context !== "store"
      ) {
        this.$parent.showDeliveryDayModal = true;
      }
    },
    autoPickAdjustDD() {
      let firstDate = "";
      if (this.$route.params.order) {
        firstDate = moment(
          this.$route.params.order.items[0].delivery_date.date
        ).format("YYYY-MM-DD");
      } else {
        // Subscription items / bag items don't load fast enough. Rework this. Selecting the first listed store delivery date for now.
        // firstDate = moment(this.$route.params.subscription.items[0].delivery_date.date).format("YYYY-MM-DD");
        firstDate = this.store.delivery_days[0].day_friendly;
      }

      let deliveryDay = this.store.delivery_days.find(day => {
        return day.day_friendly == firstDate;
      });

      this.loadDeliveryDayMenu(deliveryDay);
    },
    getDeliveryDayTotal(groupItem) {
      return format.money(
        groupItem.items.reduce((acc, item) => {
          return (acc = acc + item.price * item.quantity);
        }, 0),
        this.store.settings.currency
      );
    },
    showSubOnlyToast(val) {
      if (val) {
        this.$toastr.w("This is subscription only item.");
      } else {
        this.$toastr.w("This item is not subscribable.");
      }
    },
    changeFrequencyType(item, val) {
      let sub = val == true ? "sub" : null;
      item.meal.frequencyType = sub;
      item.meal.toggled = true;
      if (sub == null) {
        sub = false;
      } else {
        sub = true;
      }
      item.meal.subItem = sub;

      this.updateBagItemFrequency(item);
    },
    setNotes(val, type) {
      if (type === "private") {
        this.notes = val;
        this.debounceNotes();
      } else {
        this.publicNotes = val;
        this.debouncePublicNotes();
      }
    },
    updateSelectedDeliveryDay(newDay, oldDay) {
      oldDay.day_friendly = moment(newDay).format("YYYY-MM-DD");
      this.$nextTick(() => {
        this.$set(this.enablingDeliveryDayEdit, oldDay.day_friendly, false);
      });
    },
    debounceNotes: _.debounce(function() {
      this.setBagNotes(this.notes);
    }, 250),
    debouncePublicNotes: _.debounce(function() {
      this.setBagPublicNotes(this.publicNotes);
    }, 250)
  }
};
</script>
