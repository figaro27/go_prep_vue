import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'
import createPersistedState from 'vuex-persistedstate';

Vue.use(Vuex)

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
  viewed_store: {},
  stores: {},
  cart: {
    items: {}
  },

  // State for logged in users (of any role)
  user: {
    weightUnit: 'oz'
  },

  // State for logged in customers State for logged in stores
  store: {
    ingredients: {
      data: {},
      expries: 0
    },
    order_ingredients: {
      data: {},
      expires: 0
    },
  }
}

// mutations are operations that actually mutates the state. each mutation
// handler gets the entire state tree as the first argument, followed by
// additional payload arguments. mutations must be synchronous and can be
// recorded by plugins for debugging purposes.
const mutations = {
  setViewedStore(state, store) {
    state.viewed_store = {
      ...store
    };
  },

  ingredients(state, { ingredients, expires }) {
    if(!expires) {
      expires = moment()
      .add(ttl, 'seconds')
      .unix();
    }

    state.store.ingredients.data = ingredients;
    state.store.ingredients.expires = expires;
  },

  orderIngredients(state, { ingredients, expires }) {
    if(!expires) {
      expires = moment()
      .add(ttl, 'seconds')
      .unix();
    }

    state.store.order_ingredients.data = ingredients;
    state.store.order_ingredients.expires = expires;
  },
}

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {
  
  async init({
    commit,
    state,
  }, args = {}) {
    const res = await axios.get('/api');
    const {data} = await res;


  },
  

  // Actions for logged in stores
  async refreshIngredients({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/ingredients");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('ingredients', {ingredients: _.keyBy(data, 'id')})
    } else {
      throw new Exception('Failed to retrieve ingredients');
    }
  },

  async refreshOrderIngredients({
    commit,
    state
  }, args = {}) {
    const res = await axios.get("/api/me/orders/ingredients");
    const {data} = await res;

    if (_.isArray(data)) {
      commit('orderIngredients', {ingredients: _.keyBy(data, 'id')})
    } else {
      throw new Exception('Failed to retrieve order ingredients');
    }
  }
}

// getters are functions
const getters = {

  stores(state) {
    return state.stores;
  },
  store: (state, getters) => (id) => {
    return _.find(state.stores, ['id', id]);
  },
  viewedStore(state, getters) {
    return state.viewed_store;
  },

  cart(state) {
    return state.cart;
  },
  cartItems(state) {
    return state.cart.items;
  },

  // Getters for logged in users (of any role)
  defaultWeightUnit(state) {
    return state.user.weightUnit;
  },

  // Getters for logged in customers
  customerOrders(state) {},

  //Getters for logged in stores
  ingredients(state) {
    return state.store.ingredients.data || {};
  },
  ingredient(state, id) {
    return _.find(state.store.ingredients.data, { id: 'id' }) || null;
  },
  orderIngredients(state) {
    return state.store.order_ingredients.data || {};
  }
}

const plugins = [createPersistedState({paths: []})]

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({state, getters, actions, mutations, plugins})