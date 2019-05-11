import Vue from "vue";
import Vuex from "vuex";
import moment from "moment";
import createPersistedState from "vuex-persistedstate";
import router from "./routes";
import auth from "./lib/auth";
import uuid from "uuid";

const Cookies = require("js-cookie");

Vue.use(Vuex);

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
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
    coupons: [],
    pickupLocations: []
  },
  stores: {},
  tags: [],
  bag: {
    items: {}
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
      data: {},
      expries: 0
    },
    ingredients: {
      data: {},
      expries: 0
    },
    order_ingredients: {
      data: {},
      expries: 0
    },
    ingredient_units: {
      data: {},
      expires: 0
    },
    categories: {
      data: {},
      expries: 0
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
      },
      expires: 0
    },
    meals: {
      data: {},
      expires: 0
    },
    meal_packages: {
      data: {},
      expires: 0
    },
    customers: {
      data: {},
      expires: 0
    },
    payments: {
      data: {},
      expires: 0
    },
    coupons: {
      data: {},
      expires: 0
    },
    pickupLocations: {
      data: {},
      expires: 0
    }
  },
  orders: {
    data: [],
    expires: 0
  },
  subscriptions: {
    data: {},
    expires: 0
  },
  cards: {},
  customer: {
    data: {
      subscriptions: [],
      orders: []
    },
    expires: 0
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
  addBagItems(state, items) {
    state.bag.items = _.keyBy(items, "id");
  },
  updateBagTotal(state, total) {
    state.bag.total += total;
    if (state.bag.total < 0) {
      state.bag.total = 0;
    }
  },
  addToBag(state, { meal, quantity = 1, mealPackage = false, size = null }) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      mealId = "package-" + mealId;
      mealPackage = true;
    }

    if (size) {
      mealId = "size-" + mealId + "-" + size.id;
    }

    if (!_.has(state.bag.items, mealId)) {
      Vue.set(state.bag.items, mealId, {
        quantity: 0,
        meal,
        meal_package: mealPackage,
        added: moment().unix(),
        size
      });
    }

    let item = {
      ...state.bag.items[mealId]
    };
    item.quantity = (item.quantity || 0) + quantity;
    if (!item.added) {
      item.added = moment().unix();
    }

    Vue.set(state.bag.items, mealId, item);
  },
  removeFromBag(
    state,
    { meal, quantity = 1, mealPackage = false, size = null }
  ) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      mealId = "package-" + mealId;
      mealPackage = true;
    }

    if (size) {
      mealId = "size-" + mealId + "-" + size.id;
    }

    if (!_.has(state.bag.items, mealId)) {
      return;
    }

    state.bag.items[mealId].quantity -= quantity;

    if (state.bag.items[mealId].quantity <= 0) {
      Vue.delete(state.bag.items, mealId);
    }
  },
  emptyBag(state) {
    state.bag.items = {};
  },

  ingredients(state, { ingredients, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.ingredients.data = ingredients;
    state.store.ingredients.expires = expires;
  },

  ingredientUnits(state, { units, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.ingredient_units.data = units;
    state.store.ingredient_units.expires = expires;
  },

  ingredientUnit(state, { id, unit }) {
    Vue.set(state.store.ingredient_units.data, id, unit);
  },

  orderIngredients(state, { ingredients, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.order_ingredients.data = ingredients;
    state.store.order_ingredients.expires = expires;
  },

  storeDetail(state, { detail, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.detail.data = detail;
    state.store.detail.expires = expires;
  },

  storeSettings(state, { settings, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.settings.data = settings;
    state.store.settings.expires = expires;
  },

  storeCoupons(state, { coupons, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.coupons.data = coupons;
    state.store.coupons.expires = expires;
  },

  storePickupLocations(state, { pickupLocations, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.pickupLocations.data = pickupLocations;
    state.store.pickupLocations.expires = expires;
  },

  storeMeals(state, { meals, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.meals.data = meals;
    state.store.meals.expires = expires;
  },

  storeCategories(state, { categories, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.categories.data = categories;
    state.store.categories.expires = expires;
  },

  storeCustomers(state, { customers, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.customers.data = customers;
    state.store.customers.expires = expires;
  },

  storeOrders(state, { orders, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.orders.data = orders;
    state.orders.expires = expires;
  },

  storeSubscriptions(state, { subscriptions, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.subscriptions.data = _.keyBy(subscriptions, "id");
    state.subscriptions.expires = expires;
  },

  customerSubscriptions(state, { subscriptions, expires }) {
    state.customer.data.subscriptions = subscriptions;
  },

  customerOrders(state, { orders, expires }) {
    state.customer.data.orders = orders;
  },

  categories(state, { categories, expires }) {
    if (!expires) {
      expires = moment()
        .add(ttl, "seconds")
        .unix();
    }

    state.store.categories.data = units;
    state.store.categories.expires = expires;
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

    if (context === "store") {
      await dispatch("initStore", data);

      if (router.currentRoute.fullPath === "/") {
        router.replace("/store/orders");
      }
    } else if (context === "customer") {
      await dispatch("initCustomer", data);

      if (router.currentRoute.fullPath === "/") {
        if (_.isNull(data.store)) {
          router.replace("/customer/home");
        } else {
          router.replace("/customer/menu");
        }
      }
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

  async initStore({ commit, state, dispatch }, data = {}) {
    try {
      if (!_.isEmpty(data.store.settings) && _.isObject(data.store.settings)) {
        let settings = data.store.settings;
        commit("storeSettings", { settings });
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
      if (!_.isEmpty(data.subscriptions) && _.isObject(data.subscriptions)) {
        let subscriptions = data.subscriptions;
        commit("storeSubscriptions", { subscriptions });
      }
    } catch (e) {}

    // Required actions
    await Promise.all([
      dispatch("refreshMeals"),
      dispatch("refreshMealPackages"),
      dispatch("refreshOrders")
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

    if (router.currentRoute.path === "/") {
      if (_.isNull(data.store)) {
        router.replace("/customer/home");
      } else {
        router.replace("/customer/menu");
      }
    }
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
      if (!_.isEmpty(data.pickupLocations)) {
        let pickupLocations = data.pickupLocations;

        if (!_.isEmpty(pickupLocations)) {
          commit("setViewedStorePickupLocations", { pickupLocations });
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
      state.store.categories.expires = moment()
        .add(ttl, "seconds")
        .unix();
    } else {
      throw new Error("Failed to retrieve ingredients");
    }
  },

  async refreshIngredients({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/ingredients");
    const { data } = await res;

    if (_.isArray(data)) {
      state.store.ingredients.data = _.keyBy(data, "id");
      state.store.ingredients.expires = moment()
        .add(ttl, "seconds")
        .unix();
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

  async updateMeal({ commit, state, getters, dispatch }, { id, data }) {
    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeMeals, ["id", id]);

    if (index === -1) {
      return;
    }

    Vue.set(
      state.store.meals.data,
      index,
      _.merge(state.store.meals.data[index], data)
    );
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
    const res = await axios.post("/api/me/getOrders");
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
  bagHasMeal: state => meal => {
    if (!_.isNumber(meal)) {
      meal = meal.id;
    }

    return _.has(state.bag.items, meal);
  },
  bagItemQuantity: state => (meal, mealPackage = false, size = null) => {
    if (!meal) {
      return 0;
    }

    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (mealPackage || meal.meal_package) {
      mealId = "package-" + mealId;
      mealPackage = true;
    }

    if (_.isObject(size)) {
      mealId = "size-" + mealId + "-" + size.id;
    }

    if (
      !_.has(state.bag.items, mealId) ||
      !_.isObject(state.bag.items[mealId])
    ) {
      return 0;
    }

    return state.bag.items[mealId].quantity || 0;
  },
  totalBagPricePreFees(state, getters) {
    let items = _.compact(_.toArray(state.bag.items));
    let totalBagPricePreFees = 0;
    items.forEach(item => {
      const price = item.size ? item.size.price : item.meal.price;
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
      meal.getSize = sizeId => {
        return _.find(meal.sizes, ["id", parseInt(sizeId)]);
      };
      return meal;
    } catch (e) {
      return {};
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

  subscriptions: state => {
    return _.orderBy(state.customer.data.subscriptions, "id", "desc");
  },
  orders: state => {
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
