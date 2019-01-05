import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'
import createPersistedState from 'vuex-persistedstate';

Vue.use(Vuex)

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
  viewed_store: {
    meals: []
  },
  stores: {},
  bag: {
    items: {}
  },

  allergies: {},

  // State for logged in users (of any role)
  user: {
    weightUnit: 'oz',

    data: {},
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
    settings: {
      data: {
        delivery_days: [],
        delivery_distance_zipcodes: [],
      },
      expires: 0
    },
    meals: {
      data: {},
      expires: 0
    }
  }
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
      ...store
    };
  },
  addBagItems(state, items) {
    state.bag.items = items;
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
        meal
      });
    }

    state.bag.items[mealId].quantity += quantity;
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
      delete state.bag.items[mealId];
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
  }
}

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {

  async init({
    commit,
    state
  }, args = {}) {
    const res = await axios.get('/api');
    const {data} = await res;

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

        if(!_.isEmpty(units)) {
          commit('ingredientUnits', {units});
        }
      }
    } catch (e) {}

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
  },

  // Actions for logged in stores

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
    const res = await axios.get("/api/me/orders/ingredients");
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

      commit('ingredientUnits', {
        units
      })
    } else {
      throw new Error('Failed to retrieve ingredient units');
    }
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
}

// getters are functions
const getters = {
  user(state) {
    return state.user.data;
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
    }
    catch(e) {
      return defaultValue;
    }
  },

  //
  bag(state) {
    return state.bag;
  },
  bagItems(state) {
    return state.bag.items;
  },
  bagQuantity(state) {
    return _.sumBy(_.toArray(state.bag.items), item => item.quantity);
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

    if (!_.has(state.bag.items, meal)) {
      return 0;
    }

    return state.bag.items[meal].quantity;
  },
  totalBagPrice(state, getters) {
    let items = _.toArray(state.bag.items);
    let totalBagPrice = 0;
    items.forEach(item => {
      totalBagPrice += (item.quantity * item.meal.price);
    })
    if (getters.viewedStoreSetting('applyDeliveryFee', false)) {
      totalBagPrice += getters.viewedStore.settings.deliveryFee;
    }

    return totalBagPrice;
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
    return _.has(state.store.ingredient_units.data, parseInt(id)) ?
      state.store.ingredient_units.data[parseInt(id)] :
      '';
  },
  orderIngredients(state) {
    return state.store.order_ingredients.data || {};
  },
  storeDetail: (state, getters) => {
    try {
      return state.store.detail.data || {};
    }
    catch(e) {
      return {};
    }
  },
  storeSetting: (state, getters) => (key, defaultValue = '') => {
    try {
      return state.store.settings.data[key] || defaultValue;
    }
    catch(e) {
      return defaultValue;
    }
  },
  storeSettings: (state) => {
    try {
      return state.store.settings.data || {};
    }
    catch(e) {
      return {};
    }
  },
  storeMeals: (state) => {
    try {
      return state.store.meals.data || {};
    }
    catch(e) {
      return {};
    }
  },
}

const plugins = [createPersistedState({paths: ['bag']})];

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({state, getters, actions, mutations, plugins})