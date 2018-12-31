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

  // State for logged in users (of any role)
  user: {
    weightUnit: 'oz'
  },

  // State for logged in customers State for logged in stores
  store: {
    ingredients: {
      data: {},
      expries: 0
    }
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
}

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {

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
    } else 
      throw new Exception('Failed to retrieve ingredients');
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
  totalBagPrice(state){
    let items = _.toArray(state.bag.items);
    let totalBagPrice = 0;
    items.forEach(item => {
      totalBagPrice += (item.quantity * item.meal.price);
    })
    return totalBagPrice.toFixed(2);
  },
}

const plugins = [
  createPersistedState({paths: [
    'bag',
  ]})
];

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({state, getters, actions, mutations, plugins})