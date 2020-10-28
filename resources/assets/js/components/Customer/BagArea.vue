<template>
  <div>
    <zip-code-modal
      v-if="showZipCodeModal"
      :deliverySelected="true"
      @setAutoPickUpcomingMultDD="
        (showZipCodeModal = false), $parent.autoPickUpcomingMultDD(null)
      "
    ></zip-code-modal>
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
              hasBothTranserTypes
          "
          buttons
          class="filters mb-3"
          v-model="isPickup"
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
            <h5 @click="loadDeliveryDayMenu(groupItem.delivery_day)">
              {{
                moment(groupItem.delivery_day.day_friendly).format(
                  "ddd, MMM Do YYYY"
                )
              }}
            </h5>
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

                <p class="small">{{ item.special_instructions }}</p>
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

    <div
      class="row mt-5"
      v-if="
        store.modules.orderNotes &&
          $route.params.manualOrder &&
          $route.name != 'store-manual-order'
      "
    >
      <div class="col-md-12">
        <h4>Private Order Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          v-model="orderNotes"
          @input="passOrderNotes"
          placeholder="Private notes found on your orders page and Order Summary report."
        ></textarea>
      </div>
      <div class="col-md-12 pt-2">
        <h4>Public Order Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          v-model="publicOrderNotes"
          @input="passPublicOrderNotes"
          placeholder="Public notes sent to the customer in their emails and shown on your packing slips."
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
import ZipCodeModal from "../../views/Customer/Modals/ZipCodeModal";
import format from "../../lib/format";

export default {
  components: {
    ZipCodeModal
  },
  data() {
    return {
      enablingEdit: {},
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
      showZipCodeModal: false,
      isPickup: 0
    };
  },
  props: {
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: null,
    pickup: 0,
    storeView: false,
    selectedDeliveryDay: null
  },
  watch: {
    bagPickup(val) {
      this.changePickup(val);
    }
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
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      lineItems: "viewedStoreLineItems",
      storeProductionGroups: "storeProductionGroups",
      bagPickup: "bagPickup",
      bagZipCode: "bagZipCode",
      multDDZipCode: "bagMultDDZipCode"
    }),
    hasBothTranserTypes() {
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
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.day_friendly;
              if (!groupedDD[key]) {
                groupedDD[key] = {
                  items: [],
                  delivery_day: bagItem.delivery_day
                };
              }

              groupedDD[key].items.push(bagItem);
            }
          });

          if (JSON.stringify(groupedDD) != "{}") {
            for (let i in groupedDD) {
              grouped.push(groupedDD[i]);
            }
          }

          // Add all delivery days
          if (this.selectedDeliveryDay) {
            let included = false;
            grouped.forEach(group => {
              if (group.delivery_day.id === this.selectedDeliveryDay.id) {
                included = true;
              }
            });
            if (!included) {
              grouped.push({
                items: [],
                delivery_day: this.selectedDeliveryDay
              });
            }
          }
        } else {
          grouped.push({
            items: this.bag
          });
        }
      }

      return grouped;
    },
    productionGroupOptions() {
      let prodGroups = this.storeProductionGroups;
      let prodGroupOptions = [];

      prodGroups.forEach(prodGroup => {
        prodGroupOptions.push({ text: prodGroup.title, value: prodGroup.id });
      });
      return prodGroupOptions;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
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
    this.isPickup = this.bagPickup;
    if (this.bag) {
      this.bag.forEach(item => {
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
  },
  methods: {
    loadDeliveryDayMenu(deliveryDay) {
      this.$emit("changeDeliveryDay", deliveryDay);
    },
    ...mapMutations({
      setBagPickup: "setBagPickup",
      setMultDDZipCode: "setMultDDZipCode",
      setBagZipCode: "setBagZipCode"
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
        this.minusOne(
          item.meal,
          item.meal_package,
          item.size,
          item.components,
          item.addons,
          item.special_instructions
        );
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
              mealQuantities[guid] += addonItem.quantity;
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

    passOrderNotes() {
      this.$emit("passOrderNotes", this.orderNotes);
    },
    passPublicOrderNotes() {
      this.$emit("passPublicOrderNotes", this.publicOrderNotes);
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
      this.isPickup = val;
      this.setBagPickup(val);
      this.setBagZipCode(null);
      if (
        !this.bagZipCode &&
        val == 0 &&
        this.store.delivery_day_zip_codes.length > 0
      ) {
        this.setMultDDZipCode(0);
        this.showZipCodeModal = true;
      }
      this.$parent.autoPickUpcomingMultDD(null);
    },
    changePickup(val) {
      this.isPickup = val;
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
    }
  }
};
</script>
