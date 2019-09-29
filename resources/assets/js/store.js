import Vue from "vue";
import Vuex from "vuex";
import moment from "moment";
import createPersistedState from "vuex-persistedstate";
import router from "./routes";
import auth from "./lib/auth";
import uuid from "uuid";
import CryptoJS from "crypto-js";
import numeral from "numeral";
import getSymbolFromCurrency from "currency-symbol-map";

const Cookies = require("js-cookie");

Vue.use(Vuex);

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
  context: null,
  jobs: {},
  viewed_store: {
    distance: 0,
    meals: [],
    will_deliver: true,
    settings: {
      applyDeliveryFee: 0,
      deliveryFee: 0,
      applyProcessingFee: 0,
      processingFee: 0,
      allowPickup: true,
      pickupInstructions: "",
      minimumOption: "",
      minimumMeals: 0,
      minimumPrice: 0
    },
    modules: {
      data: {}
    },
    coupons: [],
    pickupLocations: [],
    lineItems: []
  },
  stores: {},
  tags: [],
  bag: {
    items: {},
    coupon: null,
    meal_plan: false
  },

  allergies: {},

  // State for logged in users (of any role)
  user: {
    weightUnit: "oz",

    data: {}
  },

  // State for logged in customers State for logged in stores
  store: {
    detail: {
      data: {}
    },
    ingredients: {
      data: {}
    },
    order_ingredients: {
      data: {}
    },
    ingredient_units: {
      data: {}
    },
    categories: {
      data: {}
    },
    settings: {
      data: {
        delivery_days: [],
        delivery_distance_zipcodes: [],
        transferType: [],
        notifications: {
          new_order: true,
          new_subscription: true,
          cancelled_subscription: true,
          ready_to_print: true
        }
      }
    },
    modules: {
      data: {}
    },
    module_settings: {
      data: {}
    },
    meals: {
      data: {}
    },
    meal_packages: {
      data: {}
    },
    customers: {
      data: {}
    },
    payments: {
      data: {}
    },
    coupons: {
      data: {}
    },
    pickupLocations: {
      data: {}
    },
    production_groups: {
      data: {}
    },
    lineItems: {
      data: {}
    }
  },
  orders: {
    data: []
  },
  upcomingOrders: {
    data: []
  },
  ordersToday: {
    data: []
  },
  subscriptions: {
    data: {}
  },
  cards: {},
  customer: {
    data: {
      subscriptions: null,
      orders: null
    }
  },
  isLoading: true,
  initialized: false
};

// mutations are operations that actually mutates the state. each mutation
// handler gets the entire state tree as the first argument, followed by
// additional payload arguments. mutations must be synchronous and can be
// recorded by plugins for debugging purposes.
const mutations = {
  user(state, user) {
    state.user.data = user;
  },
  allergies(state, allergies) {
    state.allergies = _.keyBy(allergies, "id");
  },
  setViewedStore(state, store) {
    state.viewed_store = {
      ...state.viewed_store,
      ...store
    };

    // Store in cookie for front-end site
    Cookies.set("last_viewed_store", state.viewed_store.id, {
      domain: window.app.domain
    });
  },
  tags(state, { tags }) {
    state.tags = tags;
  },
  setViewedStoreWillDeliver(state, willDeliver) {
    state.viewed_store.will_deliver = willDeliver;
  },
  setViewedStoreDistance(state, distance) {
    state.viewed_store.distance = distance;
  },
  setViewedStoreCoupons(state, { coupons }) {
    state.viewed_store.coupons = coupons;
  },
  setViewedStorePickupLocations(state, { pickupLocations }) {
    state.viewed_store.pickupLocations = pickupLocations;
  },
  setViewedStoreLineItems(state, { lineItems }) {
    state.viewed_store.lineItems = lineItems;
  },
  addBagItems(state, items) {
    state.bag.items = _.keyBy(items, "id");
  },
  updateBagTotal(state, total) {
    state.bag.total += total;
    if (state.bag.total < 0) {
      state.bag.total = 0;
    }
  },
  addToBag(
    state,
    {
      meal,
      quantity = 1,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    }
  ) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      //mealId = "package-" + mealId;
      mealPackage = true;
    }

    if (size) {
      //mealId = "size-" + mealId + "-" + size.id;
    }

    if (components) {
      //mealId += JSON.stringify(components);
    }

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      })
    ).toString();

    if (!_.has(state.bag.items, guid)) {
      Vue.set(state.bag.items, guid, {
        quantity: 0,
        meal,
        meal_package: mealPackage,
        added: moment().unix(),
        size,
        components,
        addons,
        special_instructions: special_instructions
      });
    }

    let item = {
      ...state.bag.items[guid]
    };
    item.quantity = (item.quantity || 0) + quantity;
    if (!item.added) {
      item.added = moment().unix();
    }

    Vue.set(state.bag.items, guid, item);
  },
  removeFromBag(
    state,
    {
      meal,
      quantity = 1,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    }
  ) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      //mealId = "package-" + mealId;
      mealPackage = true;
    }

    if (size) {
      //mealId = "size-" + mealId + "-" + size.id;
    }

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      })
    ).toString();

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    state.bag.items[guid].quantity -= quantity;

    if (state.bag.items[guid].quantity <= 0) {
      Vue.delete(state.bag.items, guid);
    }
  },
  emptyBag(state) {
    state.bag.items = {};
  },
  setBagCoupon(state, coupon) {
    state.bag.coupon = coupon;
  },
  setBagMealPlan(state, mealPlan) {
    state.bag.meal_plan = mealPlan;
  },

  ingredients(state, { ingredients }) {
    state.store.ingredients.data = ingredients;
  },

  ingredientUnits(state, { units }) {
    state.store.ingredient_units.data = units;
  },

  ingredientUnit(state, { id, unit }) {
    Vue.set(state.store.ingredient_units.data, id, unit);
  },

  orderIngredients(state, { ingredients }) {
    state.store.order_ingredients.data = ingredients;
  },

  storeDetail(state, { detail }) {
    state.store.detail.data = detail;
  },

  storeSettings(state, { settings }) {
    state.store.settings.data = settings;
  },

  storeModules(state, { modules }) {
    state.store.modules.data = modules;
  },

  storeModuleSettings(state, { moduleSettings }) {
    state.store.module_settings.data = moduleSettings;
  },

  storeCoupons(state, { coupons }) {
    state.store.coupons.data = coupons;
  },

  storePickupLocations(state, { pickupLocations }) {
    state.store.pickupLocations.data = pickupLocations;
  },

  storeProductionGroups(state, { productionGroups }) {
    state.store.production_groups.data = productionGroups;
  },

  storeLineItems(state, { lineItems }) {
    state.store.lineItems.data = lineItems;
  },

  storeMeals(state, { meals }) {
    state.store.meals.data = meals;
  },

  storeCategories(state, { categories }) {
    state.store.categories.data = categories;
  },

  storeCustomers(state, { customers }) {
    state.store.customers.data = customers;
  },

  storeOrders(state, { orders }) {
    state.orders.data = orders;
  },

  storeUpcomingOrders(state, { orders }) {
    state.upcomingOrders.data = orders;
  },

  storeOrdersToday(state, { orders }) {
    state.ordersToday.data = orders;
  },

  storeSubscriptions(state, { subscriptions }) {
    state.subscriptions.data = _.keyBy(subscriptions, "id");
  },

  customerSubscriptions(state, { subscriptions }) {
    state.customer.data.subscriptions = subscriptions;
  },

  customerOrders(state, { orders }) {
    state.customer.data.orders = orders;
  },

  categories(state, { categories }) {
    state.store.categories.data = units;
  }
};

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {
  async init({ commit, state, dispatch }, args = {}) {
    state.isLoading = true;
    state.initialized = false;

    const res = await axios.get("/api");
    const { data } = await res;

    try {
      if (!_.isEmpty(data.user) && _.isObject(data.user)) {
        let user = data.user;
        commit("user", user);
      }
    } catch (e) {}

    const context = data.context;
    state.context = context;

    if (context === "store") {
      await dispatch("initStore", data);
    } else if (context === "customer") {
      await dispatch("initCustomer", data);
    } else {
      await dispatch("initGuest", data);
    }

    try {
      if (!_.isEmpty(data.allergies) && _.isObject(data.allergies)) {
        let allergies = data.allergies;
        commit("allergies", allergies);
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.store_detail) &&
        _.isObject(data.store.store_detail)
      ) {
        let detail = data.store.store_detail;
        commit("storeDetail", { detail });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.units)) {
        let units = {};

        _.forEach(data.store.units, unit => {
          units[unit.ingredient_id] = unit.unit;
        });

        if (!_.isEmpty(units)) {
          commit("ingredientUnits", { units });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.categories)) {
        let categories = data.store.categories;

        if (!_.isEmpty(categories)) {
          commit("storeCategories", { categories });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.coupons)) {
        let coupons = data.store.coupons;

        if (!_.isEmpty(coupons)) {
          commit("storeCoupons", { coupons });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.pickupLocations)) {
        let pickupLocations = data.store.pickupLocations;

        if (!_.isEmpty(pickupLocations)) {
          commit("storePickupLocations", { pickupLocations });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.production_groups)) {
        let productionGroups = data.store.production_groups;

        if (!_.isEmpty(productionGroups)) {
          commit("storeProductionGroups", { productionGroups });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.lineItems)) {
        let lineItems = data.store.lineItems;

        if (!_.isEmpty(lineItems)) {
          commit("storeLineItems", { lineItems });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.tags)) {
        let tags = data.tags;

        if (_.isArray(tags)) {
          commit("tags", { tags });
        }
      }
    } catch (e) {}

    state.isLoading = false;
    state.initialized = true;

    // try {   if (!_.isEmpty(data.store.orders) && _.isObject(data.store.orders)) {
    //     let orders = data.store.orders;     commit('storeOrders', {orders});   }
    // } catch (e) {}

    /**
     * Extra actions
     */
  },

  async ensureInitialized({ commit, state, dispatch }) {
    return new Promise((resolve, reject) => {
      if (state.initialized) resolve();
      let interval = setInterval(() => {
        if (state.initialized) {
          clearInterval(interval);
          resolve();
        }
      }, 100);
    });
  },

  async initStore({ commit, state, dispatch }, data = {}) {
    try {
      if (!_.isEmpty(data.store.settings) && _.isObject(data.store.settings)) {
        let settings = data.store.settings;
        commit("storeSettings", { settings });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.modules) && _.isObject(data.store.modules)) {
        let modules = data.store.modules;
        commit("storeModules", { modules });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.module_settings) &&
        _.isObject(data.store.module_settings)
      ) {
        let moduleSettings = data.store.module_settings;
        commit("storeModuleSettings", { moduleSettings });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.coupons) && _.isObject(data.store.coupons)) {
        let coupons = data.store.coupons;
        commit("storeCoupons", { coupons });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.pickupLocations) &&
        _.isObject(data.store.pickupLocations)
      ) {
        let pickupLocations = data.store.pickupLocations;
        commit("storePickupLocations", { pickupLocations });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.production_groups) &&
        _.isObject(data.store.production_groups)
      ) {
        let productionGroups = data.store.production_groups;
        commit("storeProductionGroups", { productionGroups });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.lineItems) &&
        _.isObject(data.store.lineItems)
      ) {
        let lineItems = data.store.lineItems;
        commit("storeLineItems", { lineItems });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.meals) && _.isObject(data.store.meals)) {
        let meals = data.store.meals;
        commit("storeMeals", { meals });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.customers) &&
        _.isObject(data.store.customers)
      ) {
        let customers = data.store.customers;
        commit("storeCustomers", { customers });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.orders) && _.isObject(data.orders)) {
        let orders = data.orders;
        commit("storeOrders", { orders });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.upcomingOrders) && _.isObject(data.upcomingOrders)) {
        let orders = data.upcomingOrders;
        commit("storeUpcomingOrders", { orders });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.ordersToday) && _.isObject(data.ordersToday)) {
        let orders = data.ordersToday;
        commit("storeOrdersToday", { orders });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.subscriptions) && _.isObject(data.subscriptions)) {
        let subscriptions = data.subscriptions;
        commit("storeSubscriptions", { subscriptions });
      }
    } catch (e) {}

    // Required actions
    await Promise.all([
      dispatch("refreshMeals"),
      dispatch("refreshMealPackages"),
      // dispatch("refreshOrders"),
      dispatch("refreshUpcomingOrders"),
      dispatch("refreshOrdersToday"),
      dispatch("refreshViewedStore")
    ]);

    dispatch("refreshStoreCustomers");
    dispatch("refreshOrderIngredients");
    dispatch("refreshIngredients");
    dispatch("refreshStoreSubscriptions");
  },

  async initCustomer({ commit, state, dispatch }, data = {}) {
    if (data.store) {
      await dispatch("refreshViewedStore");
    }

    dispatch("refreshStores");
    dispatch("refreshCards");
    dispatch("refreshCustomerOrders");
    dispatch("refreshSubscriptions");
  },

  async initGuest({ commit, state, dispatch }, data = {}) {
    auth.deleteToken();

    if (data.store) {
      await dispatch("refreshViewedStore");
    }

    dispatch("refreshStores");
  },

  async logout({ commit, state }) {
    const res = await axios.post("/api/auth/logout");
    auth.deleteToken();
    const { data } = await res;
    window.location = window.app.url + "/login";
  },

  addJob({ state, dispatch }, args = {}) {
    if (!("id" in args)) {
      args.id = uuid.v1();
    }
    if (!("expires" in args)) {
      args.expires = 10000;
    }
    Vue.set(
      state.jobs,
      args.id,
      moment()
        .add(args.expires, "seconds")
        .unix()
    );

    // Automatically remove after 10s
    setTimeout(() => {
      dispatch("removeJob", args.id);
    }, args.expires);

    return args.id;
  },

  removeJob({ state }, id) {
    Vue.delete(state.jobs, id);
  },

  async refreshViewedStore({ commit, state }) {
    const res = await axios.get("/api/store/viewed");
    const { data } = await res;

    if (_.isObject(data.store) && !_.isEmpty(data.store)) {
      commit("setViewedStore", data.store);
    }

    try {
      if (data.store_distance) {
        commit("setViewedStoreDistance", data.store_distance);
      }
    } catch (e) {
      console.log(e);
    }

    try {
      if (data.distance) {
        commit("setViewedStoreDistance", data.distance);
      }
    } catch (e) {
      console.log(e);
    }

    try {
      if (_.isBoolean(data.will_deliver)) {
        commit("setViewedStoreWillDeliver", data.will_deliver);
      }
    } catch (e) {
      console.log(e);
    }

    try {
      if (!_.isEmpty(data.coupons)) {
        let coupons = data.coupons;

        if (!_.isEmpty(coupons)) {
          commit("setViewedStoreCoupons", { coupons });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.pickup_locations)) {
        let pickupLocations = data.store.pickup_locations;

        if (!_.isEmpty(pickupLocations)) {
          commit("setViewedStorePickupLocations", { pickupLocations });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.line_items)) {
        let lineItems = data.store.line_items;

        if (!_.isEmpty(lineItems)) {
          commit("setViewedStoreLineItems", { lineItems });
        }
      }
    } catch (e) {}
  },

  async refreshStores({ commit, state }, args = {}) {
    const res = await axios.get("/api/stores");
    const { data } = await res;

    if (_.isArray(data)) {
      state.stores = data;
    }
  },

  async refreshUser({ commit, state }, args = {}) {
    const res = await axios.get("/api/me");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("user", data);
    }
  },

  // Actions for logged in stores

  async refreshStoreSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/settings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeSettings", { settings: data });
    } else {
      throw new Error("Failed to retrieve settings");
    }
  },

  async refreshStoreModules({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/modules");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeModules", { modules: data });
    } else {
      throw new Error("Failed to retrieve modules");
    }
  },

  async refreshStoreModuleSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/moduleSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeModuleSettings", { modules: data });
    } else {
      throw new Error("Failed to retrieve modules");
    }
  },

  async refreshStoreCoupons({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/coupons");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeCoupons", { coupons: data });
    } else {
      throw new Error("Failed to retrieve coupons");
    }
  },

  async refreshStorePickupLocations({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/pickupLocations");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storePickupLocations", { pickupLocations: data });
    } else {
      throw new Error("Failed to retrieve pickupLocations");
    }
  },

  async refreshStoreProductionGroups({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/productionGroups");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeProductionGroups", { productionGroups: data });
    } else {
      throw new Error("Failed to retrieve productionGroups");
    }
  },

  async refreshStoreLineItems({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/lineItems");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeLineItems", { lineItems: data });
    } else {
      throw new Error("Failed to retrieve lineItems");
    }
  },

  async refreshStoreSubscriptions({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/subscriptions");
    const { data } = await res;

    if (_.isArray(data)) {
      const subscriptions = _.map(data, subscription => {
        subscription.created_at = moment.utc(subscription.created_at).local(); //.format('ddd, MMMM Do')
        subscription.updated_at = moment.utc(subscription.updated_at).local();
        subscription.next_delivery_date = moment.utc(
          subscription.next_delivery_date.date
        );
        subscription.next_renewal_at = moment
          .utc(subscription.next_renewal_at)
          .local();
        //.local(); //.format('ddd, MMMM Do')
        return subscription;
      });

      commit("storeSubscriptions", { subscriptions });
    } else {
      throw new Error("Failed to retrieve subscriptions");
    }
  },

  async refreshCards({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/cards");
    const { data } = await res;

    if (_.isArray(data)) {
      state.cards = data;
    } else {
      throw new Error("Failed to retrieve cards");
    }
  },

  async refreshStoreCustomers({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/customers");
    const { data } = await res;
    const customers = data;

    if (_.isArray(customers)) {
      commit("storeCustomers", { customers });
    } else {
      throw new Error("Failed to retrieve customers");
    }
  },

  async refreshCategories({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/categories");
    const { data } = await res;

    if (_.isArray(data)) {
      state.store.categories.data = _.keyBy(data, "id");
    } else {
      throw new Error("Failed to retrieve ingredients");
    }
  },

  async refreshIngredients({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/ingredients");
    const { data } = await res;

    if (_.isArray(data)) {
      state.store.ingredients.data = _.keyBy(data, "id");
    } else {
      throw new Error("Failed to retrieve ingredients");
    }
  },

  async refreshOrderIngredients({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/orders/ingredients", {
      params: args
    });
    let { data } = await res;

    if (_.isObject(data)) {
      data = Object.values(data);
    }

    if (_.isArray(data)) {
      commit("orderIngredients", {
        ingredients: _.keyBy(data, "id")
      });
    } else {
      throw new Error("Failed to retrieve order ingredients");
    }
  },

  async refreshIngredientUnits({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/units");
    let { data } = await res;

    if (_.isObject(data)) {
      data = Object.values(data);
    }

    if (_.isArray(data)) {
      let units = {};

      data = _.keyBy(data, "ingredient_id");
      _.forEach(data, (unit, id) => {
        units[id] = unit.unit;
      });

      commit("ingredientUnits", { units });
    } else {
      throw new Error("Failed to retrieve ingredient units");
    }
  },

  async updateMeal(
    { commit, state, getters, dispatch },
    { id, data, updateLocal = true }
  ) {
    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeMeals, ["id", id]);

    if (index === -1) {
      return;
    }

    if (updateLocal || updateLocal === undefined) {
      Vue.set(
        state.store.meals.data,
        index,
        _.merge(state.store.meals.data[index], data)
      );
    }
    const resp = await axios.patch(`/api/me/meals/${id}`, data);
    Vue.set(state.store.meals.data, index, resp.data);
    return resp.data;
  },

  async updateMealPackage({ commit, state, getters, dispatch }, { id, data }) {
    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.mealPackages, ["id", id]);

    if (index === -1) {
      return;
    }

    Vue.set(
      state.store.meal_packages.data,
      index,
      _.merge(state.store.meal_packages.data[index], data)
    );
    const resp = await axios.patch(`/api/me/packages/${id}`, data);
    Vue.set(state.store.meal_packages.data, index, resp.data);
  },

  async refreshMeals({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/meals");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeMeals", { meals: data });
    } else {
      throw new Error("Failed to retrieve meals");
    }
  },
  async refreshMealPackages({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/packages");
    const { data } = await res;

    if (_.isArray(data)) {
      state.store.meal_packages.data = data;
    } else {
      throw new Error("Failed to retrieve meal packages");
    }
  },

  async updateOrder({ commit, state, getters, dispatch }, { id, data }) {
    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeOrders, { id: id });

    if (index === -1) {
      return;
    }

    Vue.set(state.orders.data, index, _.merge(state.orders.data[index], data));
    const resp = await axios.patch(`/api/me/orders/${id}`, data);
    Vue.set(state.orders.data, index, resp.data);
  },

  async refreshOrders({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/orders");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeOrders", { orders });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshUpcomingOrders({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getUpcomingOrders");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeUpcomingOrders", { orders });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshOrdersToday({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getOrdersToday");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeOrdersToday", { orders });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshOrdersWithFulfilled({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getFulfilledOrders");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeOrders", { orders });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshSubscriptions({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/subscriptions");
    const { data } = await res;

    if (_.isArray(data)) {
      const subscriptions = _.map(data, subscription => {
        subscription.created_at = moment.utc(subscription.created_at).local(); //.format('ddd, MMMM Do')
        subscription.updated_at = moment.utc(subscription.updated_at).local(); //.format('ddd, MMMM Do')
        subscription.next_delivery_date = moment.utc(
          subscription.next_delivery_date.date
        );
        //.local(); //.format('ddd, MMMM Do')
        return subscription;
      });

      commit("customerSubscriptions", { subscriptions });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshCustomerOrders({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/orders");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("customerOrders", { orders: data });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  }
};

// getters are functions
const getters = {
  loggedIn(state) {
    return !_.isEmpty(state.user.data) && state.user.data.id && auth.hasToken();
  },
  tags(state) {
    return state.tags || [];
  },
  user(state, getters) {
    return state.user.data;
  },
  userDetail(state, getters) {
    let userDetail = state.user.data.user_detail;

    if (!userDetail) {
      return null;
    }

    if (!userDetail.notifications) {
      userDetail.notifications = {};
    }
    return userDetail;
  },
  allergies(state) {
    return state.allergies;
  },
  stores(state) {
    return state.stores;
  },
  store: (state, getters) => id => {
    return _.find(state.stores, ["id", id]);
  },
  viewedStore(state, getters) {
    return state.viewed_store;
  },
  viewedStoreSetting: (state, getters) => (key, defaultValue = "") => {
    try {
      return state.viewed_store.settings[key] || defaultValue;
    } catch (e) {
      return defaultValue;
    }
  },
  viewedStoreWillDeliver(state, getters) {
    return state.viewed_store.will_deliver;
    // state.viewed_store.distance <=
    // getters.viewedStoreSetting('delivery_distance_radius' - 1)
  },
  viewedStoreCategories(state, getters) {
    return _.sortBy(state.viewed_store.categories, "order");
  },
  viewedStoreLogo(state, getters) {
    try {
      return state.viewed_store.details.logo;
    } catch (e) {
      return null;
    }
  },
  viewedStoreCoupons(state, getters) {
    try {
      return state.viewed_store.coupons;
    } catch (e) {
      return null;
    }
  },
  viewedStorePickupLocations(state, getters) {
    try {
      return state.viewed_store.pickupLocations;
    } catch (e) {
      return null;
    }
  },
  viewedStoreLineItems(state, getters) {
    try {
      return state.viewed_store.lineItems;
    } catch (e) {
      return null;
    }
  },
  viewedStoreMeal: state => id => {
    try {
      let meal = _.find(state.viewed_store.meals, ["id", parseInt(id)]) || null;
      if (!meal) {
        return null;
      }

      meal.getSize = sizeId => {
        return _.find(meal.sizes, ["id", parseInt(sizeId)]);
      };

      meal.getTitle = (
        html = false,
        size = null,
        components = null,
        addons = null,
        special_instructions = null
      ) => {
        let title = meal.title;
        if (_.isObject(size)) {
          title = size.full_title;
        }

        let hasComponents = _.isArray(components) && components.length;
        let hasAddons = _.isArray(addons) && addons.length;

        if (!html) {
          if (hasComponents) {
            title += " - " + _.map(components, "option").join(", ");
          }

          if (hasAddons) {
            title += " - " + _.map(addons, "addon").join(", ");
          }
        } else if (hasComponents || hasAddons) {
          title += '<ul class="meal-components plain mb-0">';
          if (hasComponents) {
            _.forEach(components, component => {
              title += `<li class="plain">${component.option}</li>`;
            });
          }
          if (hasAddons) {
            _.forEach(addons, addon => {
              title += `<li class="plus">${addon.addon}</li>`;
            });
          }
          title += "</ul>";
        }

        if (special_instructions) {
          title += `<p class="small">${special_instructions}</p>`;
        }

        return title;
      };

      meal.getComponent = componentId => {
        return _.find(meal.components, { id: parseInt(componentId) }) || null;
      };
      meal.getComponentOption = (component, optionId) => {
        if (!component) return null;
        return _.find(component.options, { id: parseInt(optionId) }) || null;
      };

      meal.getComponentTitle = componentId => {
        const component = meal.getComponent(componentId);
        if (component) {
          return component.title;
        } else {
          return null;
        }
      };

      meal.getAddon = addonId => {
        return _.find(meal.addons, { id: parseInt(addonId) });
      };

      return meal;
    } catch (e) {
      return null;
    }
  },
  viewedStoreMealPackage: state => id => {
    try {
      let meal =
        _.find(state.viewed_store.packages, ["id", parseInt(id)]) || null;
      if (!meal) {
        return null;
      }

      meal.getSize = sizeId => {
        return _.find(meal.sizes, ["id", parseInt(sizeId)]) || null;
      };

      meal.getTitle = (
        html = false,
        size = null,
        components = null,
        addons = null,
        special_instructions = null
      ) => {
        let title = meal.title;

        if (_.isObject(size)) {
          title = size.full_title;
        }

        let hasComponents = _.isArray(components) && components.length;
        let hasAddons = _.isArray(addons) && addons.length;

        if (!html) {
          if (hasComponents) {
            title += " - " + _.map(components, "option").join(", ");
          }

          if (hasAddons) {
            title += " - " + _.map(addons, "addon").join(", ");
          }
        } else if (hasComponents || hasAddons) {
          title += '<ul class="meal-components plain mb-0">';
          if (hasComponents) {
            _.forEach(components, component => {
              title += `<li class="plain">${component.option}</li>`;
            });
          }
          if (hasAddons) {
            _.forEach(addons, addon => {
              title += `<li class="plus">${addon.addon}</li>`;
            });
          }
          title += "</ul>";
        }

        if (special_instructions) {
          title += `<p class="small">${special_instructions}</p>`;
        }

        return title;
      };

      meal.getComponent = componentId => {
        return _.find(meal.components, { id: parseInt(componentId) });
      };
      meal.getComponentOption = (component, optionId) => {
        return _.find(component.options, { id: parseInt(optionId) });
      };

      meal.getComponentTitle = componentId => {
        const component = meal.getComponent(componentId);
        if (component) {
          return component.title;
        } else {
          return null;
        }
      };

      meal.getAddon = addonId => {
        return _.find(meal.addons, { id: parseInt(addonId) });
      };

      return meal;
    } catch (e) {
      return null;
    }
  },
  viewedStoreModules: state => {
    try {
      return state.viewed_store.modules || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreModuleSettings: state => {
    try {
      return state.viewed_store.module_settings || {};
    } catch (e) {
      return {};
    }
  },
  isLoading(state) {
    return state.isLoading || !_.isEmpty(state.jobs);
  },
  initialized(state) {
    return state.initialized;
  },
  mealPackages(state) {
    return (
      _.map(state.store.meal_packages.data, mealPackage => {
        mealPackage.meal_package = true;
        return mealPackage;
      }) || []
    );
  },
  bag(state) {
    let bag = {
      ...state.bag
    };
    bag.items = _.filter(bag.items);
    //bag.items = _.sortBy(bag.items, 'added');
    return bag;
  },
  bagItems(state) {
    let items = _.filter(state.bag.items);
    items = _.sortBy(items, "added");
    return items;
  },
  bagQuantity(state) {
    return _.sumBy(_.compact(_.toArray(state.bag.items)), item => {
      if (!item.meal_package) {
        return item.quantity;
      } else {
        return (
          item.quantity *
          _.sumBy(
            _.compact(_.toArray(item.meal.meals)),
            item => item.pivot.quantity
          )
        );
      }
    });
  },
  bagCoupon(state) {
    return state.bag.coupon;
  },
  bagMealPlan(state) {
    return state.bag.meal_plan;
  },
  bagHasMeal: state => meal => {
    if (!_.isNumber(meal)) {
      meal = meal.id;
    }

    return _.has(state.bag.items, meal);
  },
  bagItemQuantity: state => (
    meal,
    mealPackage = false,
    size = null,
    components = null,
    addons = null,
    special_instructions = null
  ) => {
    if (!meal) {
      return 0;
    }

    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      mealPackage = true;
    }

    if (_.isObject(size)) {
    }

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      })
    ).toString();

    if (!_.has(state.bag.items, guid) || !_.isObject(state.bag.items[guid])) {
      return 0;
    }

    return state.bag.items[guid].quantity || 0;
  },
  bagMealQuantity: state => meal => {
    if (!meal) {
      return 0;
    }

    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    return _.sumBy(Object.values(state.bag.items), item => {
      if (item.meal.id === mealId) {
        return item.quantity;
      } else return 0;
    });
  },
  totalBagPricePreFees(state, getters) {
    let items = _.compact(_.toArray(state.bag.items));
    let totalBagPricePreFees = 0;
    items.forEach(item => {
      let price = item.size ? item.size.price : item.meal.price;
      let meal = getters.viewedStoreMeal(item.meal.id);
      if (item.components) {
        _.forEach(item.components, (choices, componentId) => {
          let component = _.find(item.meal.components, {
            id: parseInt(componentId)
          });
          if (!item.meal_package) {
            _.forEach(choices, optionId => {
              let option = _.find(component.options, {
                id: parseInt(optionId)
              });
              price += option.price;
            });
          } else {
            if (component.price) {
              price += component.price;
            }
            _.forEach(choices, (choices, optionId) => {
              let option = _.find(component.options, {
                id: parseInt(optionId)
              });
              price += option.price;

              _.forEach(choices, choice => {
                if (choice.price) {
                  price += choice.price;
                }
              });
            });
          }
        });
      }
      if (item.addons) {
        if (!item.meal_package) {
          _.forEach(item.addons, addonId => {
            let addon = _.find(item.meal.addons, { id: parseInt(addonId) });
            price += addon.price;
          });
        } else {
          _.forEach(item.addons, (choices, addonId) => {
            let addon = _.find(item.meal.addons, { id: parseInt(addonId) });

            // Add base addon price * choices selected
            if (addon.price) {
              price += addon.price * Math.max(1, choices.length);
            }

            // Add addon choice prices
            _.forEach(choices, choice => {
              if (choice.price) {
                price += choice.price;
              }
            });
          });
        }
      }
      totalBagPricePreFees += item.quantity * price;
    });

    return totalBagPricePreFees;
  },
  totalBagPrice(state, getters) {
    let items = _.compact(_.toArray(state.bag.items));
    let totalBagPrice = 0;
    items.forEach(item => {
      totalBagPrice += item.quantity * item.meal.price;
    });
    if (getters.viewedStoreSetting("applyDeliveryFee", false)) {
      totalBagPrice += getters.viewedStore.settings.deliveryFee;
    }
    if (getters.viewedStoreSetting("applyProcessingFee", false)) {
      totalBagPrice += getters.viewedStore.settings.processingFee;
    }

    return totalBagPrice.toFixed(2);
  },

  // Getters for logged in users (of any role)
  defaultWeightUnit(state) {
    return state.user.weightUnit;
  },

  // Getters for logged in customers Getters for logged in stores
  ingredients(state) {
    return state.store.ingredients.data || {};
  },
  ingredient: state => id => {
    return _.find(state.store.ingredients.data, { id: parseInt(id) });
  },
  ingredientUnit: state => id => {
    return _.has(state.store.ingredient_units.data, parseInt(id))
      ? state.store.ingredient_units.data[parseInt(id)]
      : "";
  },
  orderIngredients(state) {
    return state.store.order_ingredients.data || {};
  },
  storeDetail: (state, getters) => {
    try {
      return state.store.detail.data || {};
    } catch (e) {
      return {};
    }
  },
  storeSetting: (state, getters) => (key, defaultValue = "") => {
    try {
      return state.store.settings.data[key] || defaultValue;
    } catch (e) {
      return defaultValue;
    }
  },
  storeSettings: state => {
    try {
      return state.store.settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeModules: state => {
    try {
      return state.store.modules.data || {};
    } catch (e) {
      return {};
    }
  },
  storeModuleSettings: state => {
    try {
      return state.store.module_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeCoupons: state => {
    try {
      return state.store.coupons.data || {};
    } catch (e) {
      return {};
    }
  },
  storePickupLocations: state => {
    try {
      return state.store.pickupLocations.data || {};
    } catch (e) {
      return {};
    }
  },
  storeProductionGroups: state => {
    try {
      return state.store.production_groups.data || {};
    } catch (e) {
      return {};
    }
  },
  storeLineItems: state => {
    try {
      return state.store.lineItems.data || {};
    } catch (e) {
      return {};
    }
  },
  storeMeals: state => {
    try {
      return state.store.meals.data || {};
    } catch (e) {
      return {};
    }
  },
  storeMeal: state => id => {
    try {
      let meal = _.find(state.store.meals.data, ["id", parseInt(id)]) || null;
      if (!meal) {
        return null;
      }

      meal.getSize = sizeId => {
        return _.find(meal.sizes, ["id", parseInt(sizeId)]);
      };

      meal.getTitle = (
        html = false,
        size = null,
        components = null,
        addons = null,
        special_instructions = null
      ) => {
        let title = meal.title;

        if (_.isObject(size)) {
          title = size.full_title;
        }

        let hasComponents = _.isArray(components) && components.length;
        let hasAddons = _.isArray(addons) && addons.length;

        if (!html) {
          if (hasComponents) {
            title += " - " + _.map(components, "option").join(", ");
          }

          if (hasAddons) {
            title += " - " + _.map(addons, "addon").join(", ");
          }
        } else if (hasComponents || hasAddons) {
          title += '<ul class="plain mb-0">';
          if (hasComponents) {
            _.forEach(components, component => {
              title += `<li class="plain">${component.option}</li>`;
            });
          }
          if (hasAddons) {
            _.forEach(addons, addon => {
              title += `<li class="plus">${addon.addon}</li>`;
            });
          }
          title += "</ul>";
        }

        if (special_instructions) {
          title += `<p class="small">${special_instructions}</p>`;
        }

        return title;
      };

      return meal;
    } catch (e) {
      return null;
    }
  },
  storeCategories: state => {
    try {
      return _.orderBy(state.store.categories.data, "order") || {};
    } catch (e) {
      return {};
    }
  },
  storeCategoryTitle: state => id => {
    try {
      return _.find(state.store.categories.data, { id }).category || {};
    } catch (e) {
      return "";
    }
  },
  storeAllergyTitle: state => id => {
    try {
      return _.find(state.allergies, { id }).title || {};
    } catch (e) {
      return "";
    }
  },
  storeCustomers: state => {
    try {
      return state.store.customers.data || {};
    } catch (e) {
      return {};
    }
  },
  storeCustomer: state => id => {
    try {
      return _.find(state.store.customers.data, { id }) || null;
    } catch (e) {
      return null;
    }
  },
  storeOrders: state => {
    try {
      return state.orders.data || [];
    } catch (e) {
      return {};
    }
  },
  storeUpcomingOrders: state => {
    try {
      return state.upcomingOrders.data || [];
    } catch (e) {
      return {};
    }
  },
  storeOrdersToday: state => {
    try {
      return state.ordersToday.data || [];
    } catch (e) {
      return {};
    }
  },
  storeOrdersByCustomer: state => userId => {
    try {
      return _.filter(state.orders.data, { user_id: userId }) || [];
    } catch (e) {
      return {};
    }
  },
  storeSubscriptions: state => {
    try {
      return Object.values(state.subscriptions.data) || [];
    } catch (e) {
      return [];
    }
  },
  storeNextDeliveryDates: state => {
    try {
      return state.store.settings.data.next_delivery_dates || [];
    } catch (e) {
      return [];
    }
  },
  storeCurrencySymbol: state => {
    let currency = state.store.settings.data.currency;
    let symbol = getSymbolFromCurrency(currency);
    return symbol;
  },
  subscriptions: state => {
    if (_.isNull(state.customer.data.subscriptions)) return null;
    return _.orderBy(state.customer.data.subscriptions, "id", "desc");
  },
  orders: state => {
    if (_.isNull(state.customer.data.orders)) return null;
    return _.orderBy(state.customer.data.orders, "id", "desc");
  },

  cards: state => {
    return state.cards;
  },

  minimumOption: state => {
    return state.viewed_store.settings.minimumOption;
  },

  minimumMeals: state => {
    return state.viewed_store.settings.minimumMeals;
  },

  minimumPrice: state => {
    return state.viewed_store.settings.minimumPrice;
  }
};

const plugins = [
  createPersistedState({
    paths: ["bag", "cards"]
  })
];

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({ state, getters, actions, mutations, plugins });
