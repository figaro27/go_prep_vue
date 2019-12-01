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
      <ul class="list-group">
        <li
          v-for="(groupItem, indexGroup) in groupBag"
          :key="`bagGroup-${indexGroup}`"
          class="bag-item"
        >
          <div
            style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;"
            v-if="isMultipleDelivery && groupItem.delivery_day"
          >
            <h5>
              {{ moment(groupItem.delivery_day.day).format("MMM Do YYYY") }}
            </h5>

            <button
              v-if="$route.name != 'store-bag' && $route.name != 'customer-bag'"
              type="button"
              class="btn btn-primary btn-sm"
              @click="loadDeliveryDayMenu(groupItem.delivery_day)"
            >
              Pick Meals
            </button>
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
              <div class="flex-grow-1 mr-2">
                <span v-if="item.meal_package || item.meal.gift_card">
                  {{ item.meal.title }}
                  <span v-if="item.size && item.size.title !== 'Regular'">
                    - {{ item.size.title }}
                  </span>
                </span>
                <span v-else-if="item.size && item.size.title !== 'Regular'">{{
                  item.size.full_title
                }}</span>
                <span v-else>{{ item.meal.item_title }}</span>
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
                <div v-if="$route.params.storeView || storeView">
                  <input
                    type="checkbox"
                    :id="`checkox_${mealId}`"
                    v-model="item.free"
                    @change="e => makeFree(item, e)"
                  />
                  <label :for="`checkox_${mealId}`">Free</label>

                  <span v-if="editingPrice[item.guid] ? true : false">
                    <b-form-input
                      type="number"
                      v-model="item.price"
                      class="d-inline width-70 mb-1"
                      @input="v => changePrice(item, v)"
                    ></b-form-input>
                    <i
                      class="fas fa-check-circle text-primary"
                      @click="endEditPrice(item)"
                    ></i>
                  </span>
                  <span v-else>{{
                    format.money(
                      item.price * item.quantity,
                      storeSettings.currency
                    )
                  }}</span>
                  <i
                    v-if="
                      ($route.params.storeView || storeView) &&
                        !editingPrice[item.guid]
                    "
                    @click="enableEditPrice(item)"
                    class="fa fa-edit text-warning"
                  ></i>
                </div>
                <div v-if="!$route.params.storeView && !storeView">
                  <span>{{
                    format.money(
                      item.price * item.quantity,
                      storeSettings.currency
                    )
                  }}</span>
                </div>
              </div>
              <div class="flex-grow-0">
                <img
                  src="/images/customer/x.png"
                  @click="clearFromBag(item)"
                  class="clear-meal"
                />
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
        </li>
      </ul>
    </div>
    <div
      v-if="($route.params.storeView || storeView) && storeModules.lineItems"
    >
      <ul class="list-group">
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
                  {{ orderLineItem.title }} -
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
              <img
                src="/images/customer/x.png"
                @click="removeLineItem(index)"
                class="clear-meal"
              />
            </div>
          </div>
        </li>
      </ul>
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
        <h4>Order Notes</h4>
        <textarea
          type="text"
          id="form7"
          class="md-textarea form-control"
          rows="3"
          v-model="orderNotes"
          @input="passOrderNotes"
          placeholder="Optional"
        ></textarea>
      </div>
    </div>

    <b-modal
      size="lg"
      title="Add New Extra"
      v-model="showLineItemModal"
      v-if="showLineItemModal"
      hide-footer
    >
      <h3 class="center-text mt-3">Add New</h3>
      <div class="row">
        <b-input-group>
          <div class="col-md-7">
            <b-form-input
              v-model="lineItem.title"
              placeholder="Title"
              class="mr-3"
            ></b-form-input>
          </div>
          <div class="col-md-3">
            <b-form-input
              v-model="lineItem.price"
              placeholder="Price"
              class="mr-3"
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
          <b-form-select
            v-model="selectedLineItem"
            :options="lineItemOptions"
            class="mr-3 w-100"
          ></b-form-select>
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

export default {
  data() {
    return {
      orderNotes: null,
      editingPrice: {},
      showLineItemModal: false,
      lineItem: {
        title: "",
        price: null,
        quantity: 1,
        production_group_id: null
      },
      selectedLineItem: {},
      orderLineItems: []
    };
  },
  props: {
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: null,
    pickup: 0,
    storeView: false
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
      isLazy: "isLazy",
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
      storeProductionGroups: "storeProductionGroups"
    }),
    isMultipleDelivery() {
      return this.storeModules.multipleDeliveryDays == 1 ? true : false;
    },
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.id;
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

          grouped.sort((a, b) => {
            const ddA = a.delivery_day.day;
            const ddB = b.delivery_day.day;

            return moment(ddA).unix() - moment(ddB).unix();
          });
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
          text: lineItem.title,
          value: {
            price: lineItem.price,
            title: lineItem.title,
            quantity: 1
          }
        });
      });
      return options;
    },
    shoppingCartClass() {
      if (this.$route.name === "customer-menu")
        return "shopping-cart-meals area-scroll";
      else return "shopping-cart-meals";
    }
  },
  mounted() {
    if (this.bag) {
      this.bag.forEach(item => {
        this.editingPrice[item.guid] = false;
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
  },
  methods: {
    loadDeliveryDayMenu(delivery_day) {
      if (!this.isLazy) {
        store.dispatch("refreshLazyDD", {
          delivery_day
        });
      }
    },
    addToBag(item) {
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
      if (this.isAdjustOrder() || this.isManualOrder()) {
        this.removeFromAdjust(item);
      } else {
        this.clearMeal(
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
      if (this.isAdjustOrder() || this.isManualOrder()) {
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
            sizeId: pkgMeal.meal_size_id
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

          if (option.selectable) {
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
          } else {
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
          }
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
      let orderLineItems = this.orderLineItems;
      if (existing) {
        if (orderLineItems.includes(this.selectedLineItem)) {
          let index = _.findIndex(orderLineItems, orderLineItem => {
            return orderLineItem.title === this.selectedLineItem.title;
          });
          orderLineItems[index].quantity += 1;
        } else {
          orderLineItems.push(this.selectedLineItem);
        }
      } else {
        if (
          this.store.modules.productionGroups &&
          (this.lineItem.production_group_id === null ||
            this.lineItem.production_group_id === undefined)
        ) {
          this.$toastr.e(
            "Please select a production group or the extra won't show up in your reports."
          );
          return;
        }

        orderLineItems.push(this.lineItem);

        axios.post("/api/me/lineItems", this.lineItem).then(resp => {
          this.lineItem.production_group_id = null;
        });
      }

      this.showLineItemModal = false;
      this.lineItem = { title: "", price: null, quantity: 1 };
      this.selectedLineItem = { title: "", price: null, quantity: 1 };

      this.$emit("updateLineItems", this.orderLineItems);
    },
    updateLineItems(orderLineItem, increment) {
      orderLineItem.quantity += increment;
      this.$emit("updateLineItems", this.orderLineItems);
    },
    removeLineItem(index) {
      this.orderLineItems.splice(index, 1);
    },
    makeFree(item, event) {
      item.free = event.target.checked;
      this.updateBagItem(item);
    },
    changePrice(item, v) {
      if (!isNaN(v) && parseFloat(v) > 0) {
        item.price = parseFloat(parseFloat(v).toFixed(2));
        this.updateBagItem(item);
      }
    },
    enableEditPrice(item) {
      this.editingPrice[item.guid] = true;

      this.editingPrice = {
        ...this.editingPrice,
        updated: true
      };
    },
    endEditPrice(item) {
      this.editingPrice[item.guid] = false;

      this.editingPrice = {
        ...this.editingPrice,
        updated: true
      };
    },
    passOrderNotes() {
      this.$emit("passOrderNotes", this.orderNotes);
    },
    setOrderLineItems(lineItemOrders) {
      this.orderLineItems = lineItemOrders;
      this.$emit("updateLineItems", this.orderLineItems);
    }
  }
};
</script>
