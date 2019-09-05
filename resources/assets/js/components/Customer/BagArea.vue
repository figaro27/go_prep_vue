<template>
  <div>
    <ul class="list-group">
      <li v-for="(item, mealId) in bag" :key="`bag-${mealId}`" class="bag-item">
        <div v-if="item && item.quantity > 0" class="d-flex align-items-center">
          <div class="bag-item-quantity mr-2">
            <div
              v-if="!item.meal_package"
              @click="
                addOne(
                  item.meal,
                  false,
                  item.size,
                  item.components,
                  item.addons
                )
              "
              class="bag-plus-minus brand-color white-text"
            >
              <i>+</i>
            </div>
            <div
              v-if="item.meal_package"
              @click="
                addOne(item.meal, true, item.size, item.components, item.addons)
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
                  item.addons
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
              width="80px"
            ></thumbnail>
          </div>
          <div class="flex-grow-1 mr-2">
            <span v-if="item.meal_package">{{ item.meal.title }}</span>
            <span v-else-if="item.size">
              {{ item.size.full_title }}
            </span>
            <span v-else>{{ item.meal.item_title }}</span>

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
                  item.addons
                )
              "
              class="clear-meal"
            />
          </div>
        </div>
      </li>
    </ul>

    <p
      class="align-right"
      v-if="minOption === 'meals' && total < minimumMeals && !manualOrder"
    >
      Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
    </p>

    <div
      v-if="
        minOption === 'meals' &&
          total >= minimumMeals &&
          !manualOrder &&
          !adjustOrder &&
          !adjustMealPlan
      "
      class="menu-btns-container"
    >
      <router-link to="/customer/bag" v-if="!subscriptionId && !manualOrder">
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>
      <router-link
        :to="{
          name: 'customer-bag',
          params: { subscriptionId: subscriptionId }
        }"
        v-if="subscriptionId"
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>
    </div>
    <div
      v-if="
        minOption === 'price' &&
          totalBagPricePreFees < minPrice &&
          !manualOrder &&
          !adjustOrder &&
          !adjustMealPlan
      "
      class="menu-btns-container"
    >
      <p class="align-right">
        Please add
        {{ format.money(remainingPrice, storeSettings.currency) }}
        more to continue.
      </p>
    </div>
    <div v-if="minOption === 'price' && totalBagPricePreFees >= minPrice">
      <router-link
        to="/customer/bag"
        v-if="
          !subscriptionId && !manualOrder && !adjustOrder && !adjustMealPlan
        "
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>

      <router-link
        :to="{
          name: 'customer-bag',
          params: { subscriptionId: subscriptionId }
        }"
        v-if="subscriptionId"
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>
    </div>
    <div v-if="adjustOrder">
      <p v-if="!order.pickup">Delivery Day</p>
      <p v-if="order.pickup">Pickup Day</p>
      <b-form-select
        v-if="adjustOrder"
        v-model="deliveryDay"
        :options="deliveryDaysOptions"
        class="w-100 mb-3"
      ></b-form-select>
      <b-btn class="menu-bag-btn" @click="adjust">ADJUST ORDER</b-btn>
    </div>
    <div>
      <router-link to="/store/bag" v-if="!subscriptionId && manualOrder">
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>
    </div>
    <div>
      <router-link
        :to="{
          name: 'store-bag',
          params: {
            subscriptionId: subscription.id,
            mealPlanAdjustment: true
          }
        }"
        v-if="adjustMealPlan"
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  props: {
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: null
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
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
      getMealPackage: "viewedStoreMealPackage"
    }),
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    }
  }
};
</script>
