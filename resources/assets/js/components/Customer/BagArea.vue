<template>
  <div>
    <div
      class="bag-header center-text pt-3"
      v-if="$route.name === 'customer-menu'"
    >
      <h3 class="d-inline ml-3 float-left">
        <i class="fa fa-angle-right white-text" @click="$parent.showBag()"></i>
      </h3>
      <h3 class="white-text d-inline">My Bag</h3>
      <p class="white-text d-inline">({{ total }} Items)</p>
      <i
        class="fas fa-trash white-text d-inline bag-icon float-right pt-2 pr-3"
        @click="clearAll"
      ></i>
    </div>
    <div class="shopping-cart-meals">
      <ul class="list-group">
        <li
          v-for="(item, mealId) in bag"
          :key="`bag-${mealId}`"
          class="bag-item"
        >
          <div
            v-if="item && item.quantity > 0"
            class="d-flex align-items-center"
          >
            <div class="bag-item-quantity mr-2">
              <div
                v-if="!item.meal_package"
                @click="
                  addOne(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons,
                    item.special_instructions
                  )
                "
                class="bag-plus-minus brand-color white-text"
              >
                <i>+</i>
              </div>
              <div
                v-if="item.meal_package"
                @click="
                  addOne(
                    item.meal,
                    true,
                    item.size,
                    item.components,
                    item.addons,
                    item.special_instructions
                  )
                "
                class="bag-plus-minus brand-color white-text"
              >
                <i>+</i>
              </div>
              <p class="bag-quantity">{{ item.quantity }}</p>
              <div
                @click="
                  minusOne(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons,
                    item.special_instructions
                  )
                "
                class="bag-plus-minus gray white-text"
              >
                <i>-</i>
              </div>
            </div>
            <div class="bag-item-image mr-2">
              <thumbnail
                :src="item.meal.image.url_thumb"
                :spinner="false"
                class="cart-item-img"
                width="80px"
              ></thumbnail>
            </div>
            <div class="flex-grow-1 mr-2">
              <span v-if="item.meal_package">{{ item.meal.title }}</span>
              <span v-else-if="item.size">{{ item.size.full_title }}</span>
              <span v-else>{{ item.meal.item_title }}</span>
              <p class="small">{{ item.special_instructions }}</p>

              <ul v-if="item.components || item.addons" class="plain">
                <li v-for="component in itemComponents(item)" class="plain">
                  {{ component }}
                </li>
                <li v-for="addon in itemAddons(item)" class="plus">
                  {{ addon }}
                </li>
              </ul>
            </div>
            <div class="flex-grow-0">
              <img
                src="/images/customer/x.png"
                @click="
                  clearMeal(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons,
                    item.special_instructions
                  )
                "
                class="clear-meal"
              />
            </div>
          </div>
          <ul>
            <li v-for="(mealItem, i) in getItemMeals(item)" :key="i">
              <span class="small"
                >{{ mealItem.quantity }} x {{ mealItem.meal.item_title }}</span
              >
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <div v-if="$route.params.storeView && storeModules.lineItems">
      <ul class="list-group">
        <li
          v-for="(orderLineItem, index) in orderLineItems"
          class="bag-item"
          v-if="orderLineItem.quantity > 0"
        >
          <div class="d-flex align-items-center">
            <div class="bag-item-quantity mr-2">
              <div
                class="bag-plus-minus brand-color white-text"
                @click="orderLineItem.quantity += 1"
              >
                <i>+</i>
              </div>
              <p class="bag-quantity">{{ orderLineItem.quantity }}</p>
              <div
                class="bag-plus-minus gray white-text"
                @click="orderLineItem.quantity -= 1"
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
        size="md"
        variant="success"
        @click="showLineItemModal = true"
        v-if="$route.params.manualOrder"
      >
        <span class="d-sm-inline">Add Extra</span>
      </b-button>
    </div>

    <b-modal
      size="lg"
      title="Add New Extra"
      v-model="showLineItemModal"
      v-if="showLineItemModal"
      hide-footer
    >
      <h3 class="center-text mt-3">Add New</h3>
      <b-input-group>
        <b-form-input
          v-model="lineItem.title"
          placeholder="Title"
          class="mr-3"
        ></b-form-input>
        <b-form-input
          v-model="lineItem.price"
          placeholder="Price"
          class="mr-3"
        ></b-form-input>
        <b-btn variant="success" @click="addLineItem(0)">Add</b-btn>
      </b-input-group>
      <h3 class="center-text mt-5">Or Select From Existing</h3>
      <b-input-group>
        <b-form-select
          v-model="selectedLineItem"
          :options="lineItemOptions"
          class="mr-3"
        ></b-form-select>
        <p class="pt-1 mr-3">
          {{ format.money(selectedLineItem.price, storeSettings.currency) }}
        </p>
        <b-btn class="mb-5" variant="success" @click="addLineItem(1)"
          >Add</b-btn
        >
      </b-input-group>
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

export default {
  data() {
    return {
      orderLineItems: [],
      showLineItemModal: false,
      lineItem: {
        title: "",
        price: null,
        quantity: 1
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
      lineItems: "viewedStoreLineItems"
    }),
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
    }
  },
  methods: {
    getItemMeals(item) {
      const mealPackage = !!item.meal_package;
      const meal = !mealPackage
        ? this.getMeal(item.meal.id)
        : this.getMealPackage(item.meal.id);

      if (!mealPackage) {
        return [];
      }

      let mealQuantities = _.mapValues(item.meal.meals, mealItem => {
        return mealItem.quantity;
      });

      // Add on component option selections
      _(item.components).forEach((options, componentId) => {
        const component = meal.getComponent(componentId);
        const optionIds = mealPackage ? Object.keys(options) : options;

        _.forEach(optionIds, optionId => {
          const option = meal.getComponentOption(component, optionId);
          if (!option) {
            return null;
          }

          if (option.selectable) {
            _.forEach(options[option.id], item => {
              const mealId = item.meal.id;
              if (!mealQuantities[mealId]) {
                mealQuantities[mealId] = 0;
              }

              mealQuantities[mealId] += item.quantity;
            });
          } else {
            _.forEach(option.meals, mealItem => {
              const mealId = mealItem.meal_id;
              if (!mealQuantities[mealId]) {
                mealQuantities[mealId] = 0;
              }
              mealQuantities[mealId] += mealItem.quantity;
            });
          }
        });
      });

      const meals = _.map(mealQuantities, (quantity, mealId) => {
        const meal = this.getMeal(mealId);
        return {
          meal,
          quantity
        };
      });

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
        axios.post("/api/me/lineItems", this.lineItem);
        orderLineItems.push(this.lineItem);
      }

      this.showLineItemModal = false;
      this.lineItem = { title: "", price: null, quantity: 1 };
      this.selectedLineItem = { title: "", price: null, quantity: 1 };

      this.$emit("updateLineItems", this.orderLineItems);
    },
    removeLineItem(index) {
      this.orderLineItems.splice(index, 1);
    }
  }
};
</script>
