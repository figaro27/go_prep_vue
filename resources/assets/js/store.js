import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'
import createPersistedState from 'vuex-persistedstate';
import router from './routes';

Vue.use(Vuex)

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
  viewed_store: {
    meals: [],
    will_deliver: true,
    settings: {
      applyDeliveryFee: 0,
      deliveryFee: 0,
      allowPickup: true,
      pickupInstructions: ''
    }
  },
  stores: {},
  tags: [],
  bag: {
    items: {}
  },

  allergies: {},

  // State for logged in users (of any role)
  user: {
    weightUnit: 'oz',

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
    customers: {
      data: {},
      expires: 0
    },
    payments: {
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
  isLoading: true
}

// mutations are operations that actually mutates the state. each mutation
// handler gets the entire state tree as the first argument, followed by
// additional payload arguments. mutations must be synchronous and can be
// recorded by plugins for debugging purposes.
const mutations = {
  user(state, user) {
    state.user.data = user;
  },
  allergies(state, allergies) {
    state.allergies = _.keyBy(allergies, 'id');
  },
  setViewedStore(state, store) {
    state.viewed_store = {
      ...state.viewed_store,
      ...store
    };
  },
  tags(state, {tags}) {
    state.tags = tags;
  },
  setViewedStoreWillDeliver(state, willDeliver) {
    state.viewed_store.will_deliver = willDeliver;
  },
  setViewedStoreDistance(state, distance) {
    state.viewed_store.distance = distance;
  },
  addBagItems(state, items) {
    state.bag.items = _.keyBy(items, 'id');
  },
  updateBagTotal(state, total) {
    state.bag.total += total;
    if (state.bag.total < 0) {
      state.bag.total = 0;
    }
  },
  addToBag(state, {
    meal,
    quantity = 1
  }) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
    }

    if (!_.has(state.bag.items, mealId)) {
      Vue.set(state.bag.items, mealId, {
        quantity: 0,
        meal,
        added: moment().unix()
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
  removeFromBag(state, {
    meal,
    quantity = 1
  }) {
    let mealId = meal;
    if (!_.isNumber(mealId)) {
      mealId = meal.id;
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

  ingredients(state, {ingredients, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.ingredients.data = ingredients;
    state.store.ingredients.expires = expires;
  },

  ingredientUnits(state, {units, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.ingredient_units.data = units;
    state.store.ingredient_units.expires = expires;
  },

  ingredientUnit(state, {id, unit}) {
    Vue.set(state.store.ingredient_units.data, id, unit);
  },

  orderIngredients(state, {ingredients, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.order_ingredients.data = ingredients;
    state.store.order_ingredients.expires = expires;
  },

  storeDetail(state, {detail, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.detail.data = detail;
    state.store.detail.expires = expires;
  },

  storeSettings(state, {settings, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.settings.data = settings;
    state.store.settings.expires = expires;
  },

  storeMeals(state, {meals, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.meals.data = meals;
    state.store.meals.expires = expires;
  },

  storeCategories(state, {categories, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.categories.data = categories;
    state.store.categories.expires = expires;
  },

  storeCustomers(state, {customers, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.customers.data = customers;
    state.store.customers.expires = expires;
  },

  storeOrders(state, {orders, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.orders.data = orders;
    state.orders.expires = expires;
  },

  storeSubscriptions(state, {subscriptions, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.subscriptions.data = _.keyBy(subscriptions, 'id');
    state.subscriptions.expires = expires;
  },

  customerSubscriptions(state, {subscriptions, expires}) {
    state.customer.data.subscriptions = subscriptions;
  },

  customerOrders(state, {orders, expires}) {
    state.customer.data.orders = orders;
  },

  categories(state, {categories, expires}) {
    if (!expires) {
      expires = moment()
        .add(ttl, 'seconds')
        .unix();
    }

    state.store.categories.data = units;
    state.store.categories.expires = expires;
  }
}

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {

  async init({
    commit,
    state,
    dispatch
  }, args = {}) {
    const res = await axios.get('/api');
    const {data} = await res;

    const context = data.context;

    if (context === 'store') {
      dispatch('initStore', data)
    } else if (context === 'customer') {
      dispatch('initCustomer', data)
    } else {
      dispatch('initGuest', data);
    }

    try {
      if (!_.isEmpty(data.user) && _.isObject(data.user)) {
        let user = data.user;
        commit('user', user);
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.allergies) && _.isObject(data.allergies)) {
        let allergies = data.allergies;
        commit('allergies', allergies);
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.store_detail) && _.isObject(data.store.store_detail)) {
        let detail = data.store.store_detail;
        commit('storeDetail', {detail});
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.units)) {
        let units = {};

        _.forEach(data.store.units, unit => {
          units[unit.ingredient_id] = unit.unit;
        });

        if (!_.isEmpty(units)) {
          commit('ingredientUnits', {units});
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.categories)) {
        let categories = data.store.categories;

        if (!_.isEmpty(categories)) {
          commit('storeCategories', {categories});
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.tags)) {
        let tags = data.tags;

        if (_.isArray(tags)) {
          commit('tags', {tags});
        }
      }
    } catch (e) {}

    state.isLoading = false;

    // try {   if (!_.isEmpty(data.store.orders) && _.isObject(data.store.orders)) {
    //     let orders = data.store.orders;     commit('storeOrders', {orders});   }
    // } catch (e) {}

    /**
     * Extra actions
     */

  },

  async initStore({
    commit,
    state,
    dispatch
  }, data = {}) {

    try {
      if (!_.isEmpty(data.store.settings) && _.isObject(data.store.settings)) {
        let settings = data.store.settings;
        commit('storeSettings', {settings});
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.meals) && _.isObject(data.store.meals)) {
        let meals = data.store.meals;
        commit('storeMeals', {meals});
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.customers) && _.isObject(data.store.customers)) {
        let customers = data.store.customers;
        commit('storeCustomers', {customers});
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.orders) && _.isObject(data.orders)) {
        let orders = data.orders;
        commit('storeOrders', {orders});
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.subscriptions) && _.isObject(data.subscriptions)) {
        let subscriptions = data.subscriptions;
        commit('storeSubscriptions', {subscriptions});
      }
    } catch (e) {}

    dispatch('refreshMeals');
    dispatch('refreshStoreCustomers');
    dispatch('refreshOrders');
    dispatch('refreshIngredients');
    dispatch('refreshOrderIngredients');
    dispatch('refreshPayments');
    dispatch('refreshStoreSubscriptions');
  },

  async initCustomer({
    commit,
    state,
    dispatch
  }, data = {}) {

    if (data.store) {
      dispatch('refreshViewedStore');
    }

    dispatch('refreshStores');
    dispatch('refreshCards');
  },

  async initGuest({
    commit,
    state,
    dispatch
  }, data = {}) {

    if (data.store) {
      dispatch('refreshViewedStore');
    }

    dispatch('refreshStores');

    if(router.currentRoute.path === '/') {
      router.replace('customer/menu');
    }
  },

  async refreshViewedStore({commit, state}) {
    const res = await axios.get("/api/store/viewed");
    const {data} = await res;

    if (_.isObject(data.store) && !_.isEmpty(data.store)) {
      commit('setViewedStore', data.store);
    }

    try {
      if (data.store_distance) {
        commit('setViewedStoreDistance', data.store_distance);
      }
    } catch (e) {
      console.log(e);
    }

    try {
      if (_.isBoolean(data.will_deliver)) {
        commit('setViewedStoreWillDeliver', data.will_deliver);
      }
    } catch (e) {
      console.log(e);
    }

  },

  async refreshStores({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/stores");
    const {data} = await res;

    if (_.isArray(data)) {
      state.stores = data;
    }
  },

  async refreshUser({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me");
    const {data} = await res;

    if (_.isObject(data)) {
      commit('user', data);
    }
  },

  // Actions for logged in stores

  async refreshPayments({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/payments");
    const {data} = await res;

    if (_.isArray(data)) {
      state.store.categories.data = _.keyBy(data, 'id');
      state.store.categories.expires = moment()
        .add(ttl, 'seconds')
        .unix();
    } else {
      throw new Error('Failed to retrieve payments');
    }
  },

  async refreshStoreSettings({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/settings");
    const {data} = await res;

    if (_.isObject(data)) {
      commit('storeSettings', {settings: data});
    } else {
      throw new Error('Failed to retrieve settings');
    }
  },

  async refreshStoreSubscriptions({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/subscriptions");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('storeSubscriptions', {subscriptions: data});
    } else {
      throw new Error('Failed to retrieve subscriptions');
    }
  },

  async refreshCards({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/cards");
    const {data} = await res;

    if (_.isArray(data)) {
      state.cards = data;
    } else {
      throw new Error('Failed to retrieve cards');
    }
  },

  async refreshStoreCustomers({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/customers");
    const {data} = await res;
    const customers = data;

    if (_.isArray(customers)) {
      commit('storeCustomers', {customers});
    } else {
      throw new Error('Failed to retrieve customers');
    }
  },

  async refreshCategories({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/categories");
    const {data} = await res;

    if (_.isArray(data)) {
      state.store.categories.data = _.keyBy(data, 'id');
      state.store.categories.expires = moment()
        .add(ttl, 'seconds')
        .unix();
    } else {
      throw new Error('Failed to retrieve ingredients');
    }
  },

  async refreshIngredients({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/ingredients");
    const {data} = await res;

    if (_.isArray(data)) {
      state.store.ingredients.data = _.keyBy(data, 'id');
      state.store.ingredients.expires = moment()
        .add(ttl, 'seconds')
        .unix();
    } else {
      throw new Error('Failed to retrieve ingredients');
    }
  },

  async refreshOrderIngredients({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/orders/ingredients", {
      params: args
    });
    let {data} = await res;

    if (_.isObject(data)) {
      data = Object.values(data);
    }

    if (_.isArray(data)) {
      commit('orderIngredients', {
        ingredients: _.keyBy(data, 'id')
      })
    } else {
      throw new Error('Failed to retrieve order ingredients');
    }
  },

  async refreshIngredientUnits({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/units");
    let {data} = await res;

    if (_.isObject(data)) {
      data = Object.values(data);
    }

    if (_.isArray(data)) {
      let units = {};

      data = _.keyBy(data, 'ingredient_id');
      _.forEach(data, (unit, id) => {
        units[id] = unit.unit;
      });

      commit('ingredientUnits', {units})
    } else {
      throw new Error('Failed to retrieve ingredient units');
    }
  },

  async updateMeal({
    commit,
    state,
    getters,
    dispatch
  }, {id, data}) {

    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeMeals, ['id', id]);

    if (index === -1) {
      return;
    }

    Vue.set(state.store.meals.data, index, _.merge(state.store.meals.data[index], data));
    const resp = await axios.patch(`/api/me/meals/${id}`, data);
    Vue.set(state.store.meals.data, index, resp.data);
  },

  async refreshMeals({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/meals");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('storeMeals', {meals: data});
    } else {
      throw new Error('Failed to retrieve meals');
    }
  },

  async updateOrder({
    commit,
    state,
    getters,
    dispatch
  }, {id, data}) {

    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeOrders, {'id': id});

    if (index === -1) {
      return;
    }

    Vue.set(state.orders.data, index, _.merge(state.orders.data[index], data));
    const resp = await axios.patch(`/api/me/orders/${id}`, data);
    Vue.set(state.orders.data, index, resp.data);
  },

  async refreshOrders({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/orders");
    const {data} = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment
          .utc(order.created_at)
          .local(); //.format('ddd, MMMM Do')
        order.updated_at = moment
          .utc(order.updated_at)
          .local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment
          .utc(order.delivery_date)
          .local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit('storeOrders', {orders});
    } else {
      throw new Error('Failed to retrieve orders');
    }
  },

  async refreshSubscriptions({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/subscriptions");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('customerSubscriptions', {subscriptions: data});
    } else {
      throw new Error('Failed to retrieve orders');
    }
  },

  async refreshCustomerOrders({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/orders");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('customerOrders', {orders: data});
    } else {
      throw new Error('Failed to retrieve orders');
    }
  }
}

// getters are functions
const getters = {
  loggedIn(state) {
    return !_.isEmpty(state.user.data);
  },
  tags(state) {
    return state.tags || [];
  },
  user(state, getters) {
    return state.user.data;
  },
  userDetail(state, getters) {
    return state.user.data.user_detail;
  },
  allergies(state) {
    return state.allergies;
  },
  stores(state) {
    return state.stores;
  },
  store: (state, getters) => (id) => {
    return _.find(state.stores, ['id', id]);
  },
  viewedStore(state, getters) {
    return state.viewed_store;
  },
  viewedStoreSetting: (state, getters) => (key, defaultValue = '') => {
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
    return _.sortBy(state.viewed_store.categories, 'order');
  },
  viewedStoreLogo(state, getters) {
    try {
      return state.viewed_store.details.logo;
    } catch (e) {
      return null;
    }
  },

  //
  isLoading(state) {
    return state.isLoading;
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
    items = _.sortBy(items, 'added');
    return items;
  },
  bagQuantity(state) {
    return _.sumBy(_.compact(_.toArray(state.bag.items)), item => item.quantity);
  },
  bagHasMeal: (state) => (meal) => {
    if (!_.isNumber(meal)) {
      meal = meal.id;
    }

    return _.has(state.bag.items, meal);
  },
  bagItemQuantity: (state) => (meal) => {
    if (!_.isNumber(meal)) {
      meal = meal.id;
    }

    if (!_.has(state.bag.items, meal) || !_.isObject(state.bag.items[meal])) {
      return 0;
    }

    return state.bag.items[meal].quantity || 0;
  },
  totalBagPrice(state, getters) {
    let items = _.compact(_.toArray(state.bag.items));
    let totalBagPrice = 0;
    items.forEach(item => {
      totalBagPrice += (item.quantity * item.meal.price);
    })
    if (getters.viewedStoreSetting('applyDeliveryFee', false)) {
      totalBagPrice += getters.viewedStore.settings.deliveryFee;
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
  ingredient: (state) => id => {
    return _.find(state.store.ingredients.data, {'id': parseInt(id)});
  },
  ingredientUnit: (state) => id => {
    return _.has(state.store.ingredient_units.data, parseInt(id))
      ? state.store.ingredient_units.data[parseInt(id)]
      : '';
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
  storeSetting: (state, getters) => (key, defaultValue = '') => {
    try {
      return state.store.settings.data[key] || defaultValue;
    } catch (e) {
      return defaultValue;
    }
  },
  storeSettings: (state) => {
    try {
      return state.store.settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeMeals: (state) => {
    try {
      return state.store.meals.data || {};
    } catch (e) {
      return {};
    }
  },
  storeMeal: (state) => id => {
    try {
      return _.find(state.store.meals.data, ['id', parseInt(id)]) || null;
    } catch (e) {
      return {};
    }
  },
  storeCategories: (state) => {
    try {
      return _.orderBy(state.store.categories.data, 'order') || {};
    } catch (e) {
      return {};
    }
  },
  storeCategoryTitle: (state) => id => {
    try {
      return _
        .find(state.store.categories.data, {id})
        .category || {};
    } catch (e) {
      return '';
    }
  },
  storeAllergyTitle: (state) => id => {
    try {
      return _
        .find(state.allergies, {id})
        .title || {};
    } catch (e) {
      return '';
    }
  },
  storeCustomers: (state) => {
    try {
      return state.store.customers.data || {};
    } catch (e) {
      return {};
    }
  },
  storeCustomer: (state) => id => {
    try {
      return _.find(state.store.customers.data, {id}) || null;
    } catch (e) {
      return null;
    }
  },
  storeOrders: (state) => {
    try {
      return state.orders.data || [];
    } catch (e) {
      return {};
    }
  },
  storeOrdersByCustomer: (state) => (userId) => {
    try {
      return _.filter(state.orders.data, {'user_id': userId}) || [];
    } catch (e) {
      return {};
    }
  },
  storeSubscriptions: (state) => {
    try {
      return Object.values(state.subscriptions.data) || [];
    } catch (e) {
      return [];
    }
  },
  storeNextDeliveryDates: (state) => {
    try {
      return state.store.settings.data.next_delivery_dates || [];
    } catch (e) {
      return [];
    }
  },

  subscriptions: (state) => {
    return state.customer.data.subscriptions;
  },
  orders: (state) => {
    return state.customer.data.orders;
  },

  cards: (state) => {
    return state.cards;
  }
}

const plugins = [createPersistedState({
    paths: ['bag', 'cards']
  })];

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({state, getters, actions, mutations, plugins})