import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from 'vuex-persistedstate';

Vue.use(Vuex)

// root state object. each Vuex instance is just a single state tree.
const state = {
  viewed_store: {},
  stores: {},
  cart: {
    items: {}
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
}

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {}

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
  }
}

const plugins = [createPersistedState({paths: []})]

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({state, getters, actions, mutations, plugins})