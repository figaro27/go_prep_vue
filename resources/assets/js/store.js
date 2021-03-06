import Vue from "vue";
import Vuex from "vuex";
import moment from "moment";
import createPersistedState from "vuex-persistedstate";
import auth from "./lib/auth";
import uuid from "uuid";
import CryptoJS from "crypto-js";
import getSymbolFromCurrency from "currency-symbol-map";
// Paginated resources
import ResourceStore from "./store/resources";
import PayoutResourceStore from "./store/payoutsResources";
import PrinterStore from "./store/printer";
import allergies from "./data/allergies";
import tags from "./data/tags";

const Cookies = require("js-cookie");

Vue.use(Vuex);

const ttl = 60; // 60 seconds

// root state object. each Vuex instance is just a single state tree.
const state = {
  context: null,
  isLazy: false,
  isLazyStore: false,
  isLazyLoading: false,
  isLazyDD: {},
  isLazyDDLoading: {},
  jobs: {},
  hideSpinner: false,
  multDDZipCode: false,
  viewed_store: {
    delivery_days: [],
    delivery_day: null,
    dataDD: {},
    meals: [],
    packages: [],
    items: [],
    finalCategories: [],
    refreshed_package_ids: [],
    distance: null,
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
    gift_cards: [],
    purchased_gift_cards: [],
    pickupLocations: [],
    lineItems: []
  },
  stores: {},
  tags: [],
  bag: {
    items: {},
    coupon: null,
    purchased_gift_card: null,
    referral: null,
    meal_plan: false,
    pickup: null,
    pickupSet: false,
    transferTime: null,
    staffMember: null,
    customerModel: null,
    deliveryFee: null,
    frequencyType: null,
    pickupLocation: null,
    gratuityPercent: null,
    customGratuity: null,
    subscriptionInterval: null,
    subscription: null,
    notes: null,
    publicNotes: null,
    lineItems: [],
    referralUrl: null
  },
  delivery_date: null,
  zip_code: null,

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
    order_ingredients_special: {
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
    delivery_fee_zip_codes: {
      data: {}
    },
    delivery_fee_ranges: {
      data: {}
    },
    holiday_transfer_times: {
      data: {}
    },
    report_settings: {
      data: {}
    },
    label_settings: {
      data: {}
    },
    order_label_settings: {
      data: {}
    },
    menu_settings: {
      data: {}
    },
    sms_settings: {
      data: {}
    },
    referrals: {
      data: {}
    },
    referral_settings: {
      data: {}
    },
    promotions: {
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
    leads: {
      data: {}
    },
    payments: {
      data: {}
    },
    coupons: {
      data: {}
    },
    gift_cards: {
      data: {}
    },
    purchased_gift_cards: {
      data: {}
    },
    survey_questions: {
      data: {}
    },
    survey_responses: {
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
    },
    staff: {
      data: {}
    },
    sms: {
      messages: {
        data: {}
      },
      templates: {
        data: {}
      },
      lists: {
        data: {}
      },
      contacts: {
        data: {}
      },
      chats: {
        data: {}
      }
    }
  },
  orders: {
    data: []
  },
  upcomingOrders: {
    data: []
  },
  upcomingOrdersWithoutItems: {
    data: []
  },
  ordersToday: {
    data: []
  },
  payments: {
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
  tags(state, tags) {
    state.tags = tags;
  },
  setBagLineItem(state, data) {
    if (state.bag.lineItems.includes(data)) {
      let existingLineItem = state.bag.lineItems.find(lineItem => {
        return lineItem === data;
      });
      let increment = data.increment;
      existingLineItem.quantity += increment;
    } else {
      state.bag.lineItems.push(data);
    }
  },
  setBagLineItems(state, data) {
    state.bag.lineItems = data;
  },
  setBagReferralUrl(state, data) {
    state.bag.referralUrl = data;
  },
  setCards(state, data) {
    state.cards = data;
  },
  setSMSMessages(state, data) {
    state.store.sms.messages.data = data.messages;
  },
  setSMSTemplates(state, data) {
    state.store.sms.templates.data = data.templates;
  },
  setSMSLists(state, data) {
    state.store.sms.lists.data = data.lists;
  },
  setSMSContacts(state, data) {
    state.store.sms.contacts.data = data.contacts;
  },
  setSMSChats(state, data) {
    state.store.sms.chats.data = data.chats;
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
  setViewedStoreGiftCards(state, { gift_cards }) {
    state.viewed_store.gift_cards = gift_cards;
  },
  setViewedStorePurchasedGiftCards(state, { purchased_gift_cards }) {
    state.viewed_store.purchased_gift_cards = purchased_gift_cards;
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
  updateBagItemFrequency(state, item) {
    state.bag.items[item.guid].frequencyType = item.meal.frequencyType;
    state.bag.items[item.guid].subItem = item.meal.subItem;
  },
  updateBagTotal(state, total) {
    state.bag.total += total;
    if (state.bag.total < 0) {
      state.bag.total = 0;
    }
  },
  updateOneSubItemFromAdjust(state, { item, order_bag, plus }) {
    let guid = order_bag.guid.toString();
    /*if (!guid.includes("_adjust")) {
      guid += "_adjust"
    }*/

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    let bag_item = state.bag.items[guid];
    const mealPackage = !!bag_item.meal_package;
    const size_id = item.size ? item.size.id : null;

    if (!mealPackage || !bag_item.meal) {
      return;
    }

    const pkg = bag_item.meal;
    const size = pkg && bag_item.size ? bag_item.size : null;

    const packageMeals = size ? size.meals : pkg ? pkg.meals : null;
    let found = false;

    _.forEach(packageMeals, (pkgMeal, index) => {
      if (
        pkgMeal &&
        pkgMeal.id == item.meal.id &&
        pkgMeal.meal_size_id == size_id &&
        !found
      ) {
        found = true;

        if (plus) {
          pkgMeal.quantity += 1;
        } else {
          pkgMeal.quantity -= 1;
        }

        if (pkgMeal.quantity <= 0) {
          packageMeals.splice(index, 1);
        }
      }
    });

    if (!found) {
      // Not Found
      _(bag_item.components).forEach((options, componentId) => {
        const component = pkg.getComponent(componentId);
        const optionIds = mealPackage ? Object.keys(options) : options;

        _.forEach(optionIds, optionId => {
          const option = pkg.getComponentOption(component, optionId);
          if (!option) {
            return null;
          }

          if (option.selectable) {
            _.forEach(options[option.id], (optionItem, index) => {
              if (
                optionItem &&
                optionItem.meal_id == item.meal.id &&
                optionItem.meal_size_id == size_id &&
                !found
              ) {
                found = true;

                if (plus) {
                  optionItem.quantity += 1;
                } else {
                  optionItem.quantity -= 1;
                }

                if (optionItem.quantity <= 0) {
                  options[option.id].splice(index, 1);
                }
              }
            });
          } else {
            _.forEach(option.meals, (mealItem, index) => {
              if (
                mealItem &&
                mealItem.meal_id == item.meal.id &&
                mealItem.meal_size_id == size_id &&
                !found
              ) {
                found = true;

                if (plus) {
                  mealItem.quantity += 1;
                } else {
                  mealItem.quantity -= 1;
                }

                if (mealItem.quantity <= 0) {
                  option.meals.splice(index, 1);
                }
              }
            });
          }
        });
      });
    }

    if (!found) {
      _(bag_item.addons).forEach((addonItems, addonId) => {
        const addon = pkg.getAddon(addonId);
        if (addon.selectable) {
          _.forEach(addonItems, (addonItem, index) => {
            if (
              addonItem &&
              addonItem.meal_id == item.meal.id &&
              addonItem.meal_size_id == size_id &&
              !found
            ) {
              found = true;

              if (plus) {
                addonItem.quantity += 1;
              } else {
                addonItem.quantity -= 1;
              }

              if (addonItem.quantity <= 0) {
                addonItems.splice(index, 1);
              }
            }
          });
        } else {
          _.forEach(addonItems, (addonItem, index) => {
            if (
              addonItem &&
              addonItem.meal_id == item.meal.id &&
              addonItem.meal_size_id == size_id &&
              !found
            ) {
              found = true;

              if (plus) {
                addonItem.quantity += 1;
              } else {
                addonItem.quantity -= 1;
              }

              if (addonItem.quantity <= 0) {
                addonItems.splice(index, 1);
              }
            }
          });
        }
      });
    }

    if (!found && plus) {
      // New
      const addingMeal = {
        ...item.meal,
        quantity: 1,
        special_instructions: item.special_instructions
      };

      if (item.size) {
        addingMeal.meal_size = item.size;
        addingMeal.meal_size_id = item.size.id;
      } else {
        addingMeal.meal_size = null;
        addingMeal.meal_size_id = null;
      }

      packageMeals.push(addingMeal);
    }

    Vue.set(state.bag.items, guid, bag_item);
  },
  addToBagFromAdjust(state, order_bag) {
    let guid = order_bag.guid.toString();
    /*if (!guid.includes("_adjust")) {
      guid += "_adjust"
    }*/

    if (!_.has(state.bag.items, guid)) {
      Vue.set(state.bag.items, guid, {
        quantity: 0,
        meal: order_bag.meal,
        meal_package: order_bag.meal_package,
        added: moment().unix(),
        size: order_bag.size,
        components: order_bag.components,
        addons: order_bag.addons,
        special_instructions: order_bag.special_instructions,
        free: order_bag.free,
        adjust: true,
        price: order_bag.price,
        original_price: order_bag.original_price,
        delivery_day: order_bag.delivery_day ? order_bag.delivery_day : null
      });
    }

    let item = {
      ...state.bag.items[guid]
    };

    item.quantity = (item.quantity || 0) + order_bag.quantity;
    item.guid = guid;

    Vue.set(state.bag.items, guid, item);
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
      special_instructions = null,
      free = null
    }
  ) {
    /* Remove Mutation - This is temporary solution. Not professional code. */
    meal = JSON.parse(JSON.stringify(meal));
    size = JSON.parse(JSON.stringify(size));
    components = JSON.parse(JSON.stringify(components));
    addons = JSON.parse(JSON.stringify(addons));
    /* Remove Mutation End */

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

    // if (!meal.delivery_day && state.viewed_store.delivery_day) {
    //   meal.delivery_day = state.viewed_store.delivery_day;
    // }

    const delivery_day = meal.delivery_day ? meal.delivery_day : null;

    if (components == null) {
      components = {};
    }

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        delivery_day,
        title: meal.customTitle ? meal.customTitle : meal.title,
        mappingId: meal.mappingId ? meal.mappingId : null
      })
    ).toString();

    let isNew = false;
    if (!_.has(state.bag.items, guid)) {
      isNew = true;
      Vue.set(state.bag.items, guid, {
        quantity: 0,
        meal,
        meal_package: mealPackage,
        added: moment().unix(),
        size,
        components,
        addons,
        special_instructions: special_instructions,
        free
      });
    }

    let item = {
      ...state.bag.items[guid]
    };

    item.quantity = (item.quantity || 0) + quantity;

    if (!item.added) {
      item.added = moment().unix();
    }

    /* Adjustments */
    let price = item.size
      ? item.size.price
      : item.meal_package
      ? item.meal.price
      : item.meal.item_price;

    if (item.meal.gift_card) {
      price = item.meal.price;
    }

    if (item.meal.ignoreBasePrice && !item.meal.adjustOrder) {
      price = 0;
    }

    if (item.components) {
      _.forEach(item.components, (choices, componentId) => {
        let component = _.find(item.meal.components, {
          id: parseInt(componentId)
        });

        if (!item.meal_package) {
          _.forEach(choices, optionId => {
            let option = component
              ? _.find(component.options, {
                  id: parseInt(optionId)
                })
              : null;
            price += option ? option.price : 0;
          });
        } else {
          if (component.price) {
            price += parseInt(component.price);
          }
          _.forEach(choices, (choices, optionId) => {
            let option = _.find(component.options, {
              id: parseInt(optionId)
            });
            price += option ? option.price : 0;

            _.forEach(choices, choice => {
              if (choice.price) {
                price += choice.price;
              }
            });
          });
        }
      });
    } // End If

    if (item.addons) {
      if (!item.meal_package) {
        _.forEach(item.addons, addonId => {
          let addon = _.find(item.meal.addons, { id: parseInt(addonId) });
          price += addon ? addon.price : 0;
        });
      } else {
        _.forEach(item.addons, (choices, addonId) => {
          let addon = _.find(item.meal.addons, { id: parseInt(addonId) });
          if (addon.selectable === 0) {
            price += addon ? addon.price : 0;
          } else {
            // Add base addon price * choices selected
            if (addon.price) {
              price += addon ? addon.price * Math.max(1, choices.length) : 0;
            }

            // Add addon choice prices
            _.forEach(choices, choice => {
              if (choice.price) {
                price += choice ? choice.price : 0;
              }
            });
          }
        });
      }
    } // End IF

    item.original_price = parseFloat(parseFloat(price).toFixed(2));
    item.price = item.original_price + 0;
    item.delivery_day = delivery_day;

    if (isNew) {
      // item.free = false;
      item.guid = guid;
    }
    /* Adjustments End */
    item.customTitle = meal.customTitle;
    item.customSize = meal.customSize;

    // If the price was customized (which also saves customTitle or customSize)
    if (item.customTitle || item.customSize) {
      item.price = meal.price;
    }

    Vue.set(state.bag.items, guid, item);
  },
  removeFromBagFromAdjust(state, order_bag) {
    let guid = order_bag.guid.toString();
    /*if (!guid.includes("_adjust")) {
      guid += "_adjust"
    }*/

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    Vue.delete(state.bag.items, guid);
  },
  removeOneFromBagFromAdjust(state, order_bag) {
    let guid = order_bag.guid.toString();
    /*if (!guid.includes("_adjust")) {
      guid += "_adjust"
    }*/

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    state.bag.items[guid].quantity -= order_bag.quantity;
    if (state.bag.items[guid].quantity <= 0) {
      Vue.delete(state.bag.items, guid);
    }
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

    // if (!meal.delivery_day && state.viewed_store.delivery_day) {
    //   meal.delivery_day = state.viewed_store.delivery_day;
    // }

    const delivery_day = meal.delivery_day ? meal.delivery_day : null;

    if (components == null) {
      components = {};
    }

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        delivery_day,
        title: meal.customTitle ? meal.customTitle : meal.title,
        mappingId: meal.mappingId ? meal.mappingId : null
      })
    ).toString();

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    // Vue.delete(state.bag.items, guid);

    state.bag.items[guid].quantity -= quantity;

    if (state.bag.items[guid].quantity <= 0) {
      state.bag.items[guid].meal.price = state.bag.items[guid].meal.item_price;
      Vue.delete(state.bag.items, guid);
    }
  },
  removeFullQuantityFromBag(
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

    const delivery_day = meal.delivery_day ? meal.delivery_day : null;

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        delivery_day,
        title: meal.customTitle ? meal.customTitle : meal.title,
        mappingId: meal.mappingId ? meal.mappingId : null
      })
    ).toString();

    if (!_.has(state.bag.items, guid)) {
      return;
    }

    Vue.delete(state.bag.items, guid);
  },
  setBagSubscription({ state, dispatch }, subscription) {
    this.state.bag.subscription = subscription;
  },
  setBagPickupLocation({ state, dispatch }, pickupLocation) {
    this.state.bag.pickupLocation = pickupLocation;
  },
  setBagNotes({ state, dispatch }, notes) {
    this.state.bag.notes = notes;
  },
  setBagPublicNotes({ state, dispatch }, publicNotes) {
    this.state.bag.publicNotes = publicNotes;
  },
  setBagGratuityPercent({ state, dispatch }, gratuityPercent) {
    this.state.bag.gratuityPercent = gratuityPercent;
  },
  setBagCustomGratuity({ state, dispatch }, customGratuity) {
    if (
      !customGratuity ||
      customGratuity === "" ||
      customGratuity === undefined
    ) {
      customGratuity = 0;
    }
    this.state.bag.customGratuity = customGratuity;
  },
  setBagSubscriptionInterval({ state, dispatch }, subscriptionInterval) {
    this.state.bag.subscriptionInterval = subscriptionInterval;
  },
  setBagZipCode({ state, dispatch }, zipCode) {
    this.state.zip_code = zipCode;
  },
  setBagDeliveryDate({ state, dispatch }, date) {
    this.state.delivery_date = date;
  },
  clearBagDeliveryDate(state, date) {
    this.state.delivery_date = null;
  },
  clearBagTransferTime(state, date) {
    this.state.bag.transferTime = null;
  },
  clearBagStaffMember(state, date) {
    this.state.bag.staffMember = null;
  },
  clearBagCustomerModel(state, date) {
    this.state.bag.customerModel = null;
  },
  clearBagDeliveryFee(state, deliveryFee) {
    this.state.bag.deliveryFee = null;
  },
  clearBagLineItems(state, lineItem) {
    this.state.bag.lineItems = [];
  },
  setBagPickup({ state, dispatch }, pickup) {
    this.state.bag.pickup = pickup;
    this.state.bag.pickupSet = true;
  },
  setMultDDZipCode({ state, dispatch }, multDDZipCode) {
    this.state.multDDZipCode = multDDZipCode;
  },
  setBagTransferTime({ state, dispatch }, transferTime) {
    this.state.bag.transferTime = transferTime;
    this.state.bag.transferTimeSet = true;
  },
  setBagStaffMember({ state, dispatch }, staffMember) {
    this.state.bag.staffMember = staffMember;
  },
  setBagCustomerModel({ state, dispatch }, customerModel) {
    this.state.bag.customerModel = customerModel;
  },
  setBagDeliveryFee({ state, dispatch }, deliveryFee) {
    this.state.bag.deliveryFee = deliveryFee;
  },
  setBagFrequencyType({ state, dispatch }, frequencyType) {
    this.state.bag.frequencyType = frequencyType;
  },
  updateBagItem(state, item) {
    if (item.guid) {
      state.bag.items[item.guid] = item;
    }
  },
  emptyBag(state) {
    state.bag.items = {};
  },
  setBagCoupon(state, coupon) {
    state.bag.coupon = coupon;
  },
  setBagPurchasedGiftCard(state, purchased_gift_card) {
    state.bag.purchased_gift_card = purchased_gift_card;
  },
  setBagReferral(state, referral) {
    state.bag.referral = referral;
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

  orderIngredientsSpecial(state, { ingredients }) {
    state.store.order_ingredients_special.data = ingredients;
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

  storeDeliveryFeeZipCodes(state, { deliveryFeeZipCodes }) {
    state.store.delivery_fee_zip_codes.data = deliveryFeeZipCodes;
  },

  storeDeliveryFeeRanges(state, { deliveryFeeRanges }) {
    state.store.delivery_fee_ranges.data = deliveryFeeRanges;
  },

  storeHolidayTransferTimes(state, { holidayTransferTimes }) {
    state.store.holiday_transfer_times.data = holidayTransferTimes;
  },

  storeReportSettings(state, { reportSettings }) {
    state.store.report_settings.data = reportSettings;
  },

  storeLabelSettings(state, { labelSettings }) {
    state.store.label_settings.data = labelSettings;
  },

  storeOrderLabelSettings(state, { orderLabelSettings }) {
    state.store.order_label_settings.data = orderLabelSettings;
  },

  storemMenuSettings(state, { menuSettings }) {
    state.store.menu_settings.data = menuSettings;
  },

  storeSMSSettings(state, { smsSettings }) {
    state.store.sms_settings.data = smsSettings;
  },

  storeReferrals(state, { referrals }) {
    state.store.referrals.data = referrals;
  },

  storeReferralSettings(state, { referralSettings }) {
    state.store.referral_settings.data = referralSettings;
  },

  storePromotions(state, { promotions }) {
    state.store.promotions.data = promotions;
  },

  storeCoupons(state, { coupons }) {
    state.store.coupons.data = coupons;
  },

  storeGiftCards(state, { gift_cards }) {
    state.store.gift_cards.data = gift_cards;
  },

  storePurchasedGiftCards(state, { purchased_gift_cards }) {
    state.store.purchased_gift_cards.data = purchased_gift_cards;
  },

  storeSurveyQuestions(state, { survey_questions }) {
    state.store.survey_questions.data = survey_questions;
  },

  storeSurveyResponses(state, { survey_responses }) {
    state.store.survey_responses.data = survey_responses;
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

  storeLeads(state, { leads }) {
    state.store.leads.data = leads;
  },

  storeStaff(state, { staff }) {
    state.store.staff.data = staff;
  },

  storeOrders(state, { orders }) {
    state.orders.data = orders;
  },

  storePayments(state, { payments }) {
    state.payments.data = payments;
  },

  storeUpcomingOrders(state, { orders }) {
    state.upcomingOrders.data = orders;
  },

  storeUpcomingOrdersWithoutItems(state, { orders }) {
    state.upcomingOrdersWithoutItems.data = orders;
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
  },

  showSpinner(state, {}) {
    state.isLoading = true;
  },

  hideSpinner(state, {}) {
    state.isLoading = false;
  },

  enableSpinner(state, {}) {
    state.hideSpinner = false;
  },

  disableSpinner(state, {}) {
    state.hideSpinner = true;
  }
};

const callLazyStore = (offset_meal, offset_package, bypass_meal) => {
  return new Promise((resolve, reject) => {
    const url =
      "/api/refresh_lazy_store?offset_meal=" +
      offset_meal +
      "&offset_package=" +
      offset_package +
      "&bypass_meal=" +
      bypass_meal;
    axios
      .get(url)
      .then(res => {
        if (res.data) {
          resolve(res.data);
        } else {
          reject("");
        }
      })
      .catch(error => {
        reject("");
      });
  });
};

const callLazy = (
  offset_meal,
  offset_package,
  category_id,
  category_ids_str,
  bypass_meal,
  delivery_day = null
) => {
  const delivery_day_id = delivery_day ? delivery_day.id : 0;
  return new Promise((resolve, reject) => {
    const url =
      "/api/refresh_lazy?offset_meal=" +
      offset_meal +
      "&offset_package=" +
      offset_package +
      "&category_id=" +
      category_id +
      "&category_ids_str=" +
      category_ids_str +
      "&bypass_meal=" +
      bypass_meal +
      "&delivery_day_id=" +
      delivery_day_id;
    axios
      .get(url)
      .then(res => {
        if (res.data) {
          resolve(res.data);
        } else {
          reject("");
        }
      })
      .catch(error => {
        reject("");
      });
  });
};

const triggerLazyStore = (state, offset_meal, offset_package, bypass_meal) => {
  callLazyStore(offset_meal, offset_package, bypass_meal).then(data => {
    if (data && (data.meals.length > 0 || data.packages.length > 0)) {
      let store_meals = state.store.meals.data;
      let store_packages = state.store.meal_packages.data;

      if (data.meals.length > 0) {
        if (store_meals && store_meals.length > 0) {
          store_meals = store_meals.concat(data.meals);
        } else {
          store_meals = data.meals;
        }
      }

      if (data.packages.length > 0) {
        if (store_packages && store_packages.length > 0) {
          store_packages = store_packages.concat(data.packages);
        } else {
          store_packages = data.packages;
        }
      }

      state.store = {
        ...state.store,
        meals: {
          data: store_meals
        },
        meal_packages: {
          data: store_packages
        }
      };
    }

    if (data.end == 0) {
      triggerLazyStore(
        state,
        data.offset_meal,
        data.offset_package,
        data.bypass_meal
      );
    } else {
      // Finished
    }
  });
};

const triggerLazyDD = (
  state,
  offset_meal,
  offset_package,
  category_id,
  category_ids_str,
  bypass_meal,
  delivery_day
) => {
  callLazy(
    offset_meal,
    offset_package,
    category_id,
    category_ids_str,
    bypass_meal,
    delivery_day
  )
    .then(data => {
      const key = "dd_" + delivery_day.id;

      if (data.items && data.items.length > 0) {
        if (!state.viewed_store.dataDD[key]) {
          Vue.set(state.viewed_store.dataDD, key, {
            items: [],
            meals: [],
            packages: [],
            finalCategories: []
          });
        }

        let items = state.viewed_store.dataDD[key].items;
        let meals = state.viewed_store.dataDD[key].meals;
        let packages = state.viewed_store.dataDD[key].packages;
        let finalCategories = state.viewed_store.dataDD[key].finalCategories;

        if (data.meals && data.meals.length > 0) {
          if (meals.length > 0) {
            for (let i in data.meals) {
              let meal = data.meals[i];

              let found = _.find(meals, ["id", parseInt(meal.id)]) || null;
              if (!found) {
                meals.push(meal);
              }
            }
          } else {
            meals = data.meals;
          }
        }

        if (data.packages && data.packages.length > 0) {
          if (packages.length > 0) {
            for (let i in data.packages) {
              let pack = data.packages[i];

              let found = _.find(packages, ["id", parseInt(pack.id)]) || null;
              if (!found) {
                packages.push(pack);
              }
            }
          } else {
            packages = data.packages;
          }
        }

        if (data.category_data && data.category_data.length > 0) {
          finalCategories = [];
          finalCategories = data.category_data.map(item => {
            return {
              ...item,
              visible: false
            };
          });

          for (let i in finalCategories) {
            const {
              category,
              subtitle,
              id,
              order,
              date_range,
              date_range_exclusive,
              date_range_from,
              date_range_to,
              date_range_exclusive_from,
              date_range_exclusive_to
            } = finalCategories[i];

            const itemData = {
              category: category,
              subtitle: subtitle,
              category_id: id,
              meals: [],
              order,
              date_range,
              date_range_exclusive,
              date_range_from,
              date_range_to,
              date_range_exclusive_from,
              date_range_exclusive_to
            };
            items.push(itemData);
          }

          category_id = finalCategories[0].id;
        }

        const currentCatIndex = finalCategories.findIndex(
          item => item.id == category_id
        );
        if (currentCatIndex > -1)
          finalCategories[currentCatIndex].visible = true;

        for (let i in items) {
          if (items[i].category_id == category_id) {
            items[i].meals = items[i].meals.concat(data.items);

            break;
          }
        }

        Vue.set(state.viewed_store.dataDD, key, {
          items,
          meals,
          packages,
          finalCategories
        });
      }

      if (data.end == 0) {
        triggerLazyDD(
          state,
          data.offset_meal,
          data.offset_package,
          data.category_id,
          data.category_ids_str,
          data.bypass_meal,
          delivery_day
        );
      } else {
        // Finished
        Vue.set(state.isLazyDDLoading, key, false);
      }
    })
    .catch(error => {
      // Finished
      console.error(error);
    });
};

const triggerLazy = (
  state,
  offset_meal,
  offset_package,
  category_id,
  category_ids_str,
  bypass_meal
) => {
  callLazy(
    offset_meal,
    offset_package,
    category_id,
    category_ids_str,
    bypass_meal
  )
    .then(data => {
      if (
        (data.items && data.items.length > 0) ||
        data.category_data !== null
      ) {
        let items = state.viewed_store.items;
        let meals = state.viewed_store.meals;
        let packages = state.viewed_store.packages;
        let finalCategories = state.viewed_store.finalCategories;

        if (data.meals && data.meals.length > 0) {
          if (meals.length > 0) {
            for (let i in data.meals) {
              let meal = data.meals[i];

              let found = _.find(meals, ["id", parseInt(meal.id)]) || null;
              if (!found) {
                meals.push(meal);
              }
            }
          } else {
            meals = data.meals;
          }
        }

        if (data.packages && data.packages.length > 0) {
          if (packages.length > 0) {
            for (let i in data.packages) {
              let pack = data.packages[i];

              let found = _.find(packages, ["id", parseInt(pack.id)]) || null;
              if (!found) {
                packages.push(pack);
              }
            }
          } else {
            packages = data.packages;
          }
        }

        if (data.category_data && data.category_data.length > 0) {
          finalCategories = [];
          finalCategories = data.category_data.map(item => {
            return {
              ...item,
              visible: false
            };
          });

          for (let i in finalCategories) {
            const {
              category,
              subtitle,
              id,
              order,
              date_range,
              date_range_exclusive,
              date_range_from,
              date_range_to,
              date_range_exclusive_from,
              date_range_exclusive_to,
              minimumType,
              minimum,
              minimumIfAdded
            } = finalCategories[i];
            const itemData = {
              category: category,
              subtitle: subtitle,
              category_id: id,
              meals: [],
              order,
              date_range,
              date_range_exclusive,
              date_range_from,
              date_range_to,
              date_range_exclusive_from,
              date_range_exclusive_to,
              minimumType,
              minimum,
              minimumIfAdded
            };
            items.push(itemData);
          }

          category_id = finalCategories[0].id;
        }

        const currentCatIndex = finalCategories.findIndex(
          item => item.id == category_id
        );
        if (currentCatIndex > -1)
          finalCategories[currentCatIndex].visible = true;

        for (let i in items) {
          if (items[i].category_id == category_id) {
            items[i].meals = items[i].meals.concat(data.items);

            break;
          }
        }

        state.viewed_store = {
          ...state.viewed_store,
          items,
          finalCategories,
          meals,
          packages
        };
      }

      if (data.end == 0) {
        triggerLazy(
          state,
          data.offset_meal,
          data.offset_package,
          data.category_id,
          data.category_ids_str,
          data.bypass_meal
        );
      } else {
        // Finished
        state.isLazyLoading = false;
      }
    })
    .catch(error => {
      // Finished
    });
};

// actions are functions that cause side effects and can involve asynchronous
// operations.
const actions = {
  async init({ commit, state, dispatch }, args = {}) {
    state.initialized = false;

    /*const resContext = await axios.get("/api/context");
    let dataContext =
      resContext.data && resContext.data.context
        ? resContext.data.context
        : "guest";*/

    //let apiEndpoint = "/api";
    let apiEndpoint = "/api/optimized";

    const res = await axios.get(apiEndpoint);
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
      if (
        !_.isEmpty(data.upcomingOrdersWithoutItems) &&
        _.isObject(data.upcomingOrdersWithoutItems)
      ) {
        let orders = data.upcomingOrdersWithoutItems;
        commit("storeUpcomingOrdersWithoutItems", { orders });
      }
    } catch (e) {}

    try {
      let allergyList = allergies;
      commit("allergies", allergyList);
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
      if (!_.isEmpty(data.store.settings) && _.isObject(data.store.settings)) {
        let settings = data.store.settings;
        commit("storeSettings", { settings });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.report_settings) &&
        _.isObject(data.store.report_settings)
      ) {
        let reportSettings = data.store.report_settings;
        commit("storeReportSettings", { reportSettings });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.label_settings) &&
        _.isObject(data.store.label_settings)
      ) {
        let labelSettings = data.store.label_settings;
        commit("storeLabelSettings", { labelSettings });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.order_label_settings) &&
        _.isObject(data.store.order_label_settings)
      ) {
        let orderLabelSettings = data.store.order_label_settings;
        commit("storeOrderLabelSettings", { orderLabelSettings });
      }
    } catch (e) {}

    // try {
    //   if (
    //     !_.isEmpty(data.store.menu_settings) &&
    //     _.isObject(data.store.menu_settings)
    //   ) {
    //     let menuSettings = data.store.menu_settings;
    //     commit("storeMenuSettings", { menuSettings });
    //   }
    // } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.sms_settings) &&
        _.isObject(data.store.sms_settings)
      ) {
        let smsSettings = data.store.sms_settings;
        commit("storeSMSSettings", { smsSettings });
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
      if (
        !_.isEmpty(data.store.delivery_fee_zip_codes) &&
        _.isObject(data.store.delivery_fee_zip_codes)
      ) {
        let deliveryFeeZipCodes = data.store.delivery_fee_zip_codes;
        commit("storeDeliveryFeeZipCodes", { deliveryFeeZipCodes });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.delivery_fee_ranges) &&
        _.isObject(data.store.delivery_fee_ranges)
      ) {
        let deliveryFeeRanges = data.store.delivery_fee_ranges;
        commit("storeDeliveryFeeRanges", { deliveryFeeRanges });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.holiday_transfer_times) &&
        _.isObject(data.store.holiday_transfer_times)
      ) {
        let holidayTransferTimes = data.store.holiday_transfer_times;
        commit("storeHolidayTransferTimes", { holidayTransferTimes });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.referrals) &&
        _.isObject(data.store.referrals)
      ) {
        let referrals = data.store.referrals;
        commit("storeReferrals", { referrals });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.referral_settings) &&
        _.isObject(data.store.referral_settings)
      ) {
        let referralSettings = data.store.referral_settings;
        commit("storeReferralSettings", { referralSettings });
      }
    } catch (e) {}

    try {
      if (
        !_.isEmpty(data.store.promotions) &&
        _.isObject(data.store.promotions)
      ) {
        let promotions = data.store.promotions;
        commit("storePromotions", { promotions });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.ordersToday) && _.isObject(data.ordersToday)) {
        let orders = data.ordersToday;
        commit("storeOrdersToday", { orders });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.modules) && _.isObject(data.store.modules)) {
        let modules = data.store.modules;
        commit("storeModules", { modules });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.subscriptions) && _.isObject(data.subscriptions)) {
        let subscriptions = data.subscriptions;
        commit("storeSubscriptions", { subscriptions });
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
      if (!_.isEmpty(data.upcomingOrders) && _.isObject(data.upcomingOrders)) {
        let orders = data.upcomingOrders;
        commit("storeUpcomingOrders", { orders });
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
      if (!_.isEmpty(data.store.gift_cards)) {
        let gift_cards = data.store.gift_cards;

        if (!_.isEmpty(gift_cards)) {
          commit("storeGiftCards", { gift_cards });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.purchased_gift_cards)) {
        let purchased_gift_cards = data.store.purchased_gift_cards;

        if (!_.isEmpty(purchased_gift_cards)) {
          commit("storePurchasedGiftCards", { purchased_gift_cards });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.survey_questions)) {
        let survey_questions = data.store.survey_questions;

        if (!_.isEmpty(survey_questions)) {
          commit("storeSurveyQuestions", { survey_questions });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.survey_responses)) {
        let survey_responses = data.store.survey_responses;

        if (!_.isEmpty(survey_responses)) {
          commit("storeSurveyResponses", { survey_responses });
        }
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
      if (!_.isEmpty(data.store.leads) && _.isObject(data.store.leads)) {
        let leads = data.store.leads;
        commit("storeLeads", { leads });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.staff) && _.isObject(data.store.staff)) {
        let staff = data.store.staff;
        commit("storeStaff", { staff });
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.store.meals) && _.isObject(data.store.meals)) {
        let meals = data.store.meals;
        commit("storeMeals", { meals });
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
      if (!_.isEmpty(data.orders) && _.isObject(data.orders)) {
        let orders = data.orders;
        commit("storeOrders", { orders });
      }
    } catch (e) {}

    try {
      let tagList = tags;
      commit("tags", tagList);
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

  async refreshSMS({ commit, state, dispatch }, data = {}) {
    dispatch("refreshSMSChats");
    dispatch("refreshSMSMessages");
    dispatch("refreshSMSTemplates");
    dispatch("refreshSMSLists");
    dispatch("refreshSMSContacts");
  },

  async refreshSMSMessages({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/SMSMessages");
    const { data } = await res;
    commit("setSMSMessages", { messages: data });
    state.isLoading = false;
  },
  async refreshSMSTemplates({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/SMSTemplates");
    const { data } = await res;
    commit("setSMSTemplates", { templates: data });
    state.isLoading = false;
  },
  async refreshSMSLists({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/SMSLists");
    const { data } = await res;
    commit("setSMSLists", { lists: data });
    state.isLoading = false;
  },
  async refreshSMSContacts({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/SMSContacts");
    const { data } = await res;
    commit("setSMSContacts", { contacts: data });
    state.isLoading = false;
  },
  async refreshSMSChats({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/SMSChats");
    const { data } = await res;
    commit("setSMSChats", { chats: data });
    state.isLoading = false;
  },

  async initStore({ commit, state, dispatch }, data = {}) {
    // Required actions
    if (data.store) {
      await dispatch("refreshViewedOwnerStore", data);
    }

    // await Promise.all([dispatch("refreshUpcomingOrdersWithoutItems")]);

    await Promise.all([dispatch("refreshLazy"), dispatch("refreshLazyStore")]);

    dispatch("refreshInactiveMeals"),
      dispatch("refreshStoreSubscriptions"),
      // dispatch("refreshStoreCustomers"),
      // dispatch("refreshStoreLeads"),
      dispatch("refreshStoreStaff");
    // dispatch("refreshOrderIngredients"),
    // dispatch("refreshIngredients");
    // dispatch("refreshSMS");
    // dispatch("refreshUpcomingOrders");
  },

  async initCustomer({ commit, state, dispatch }, data = {}) {
    if (data.store) {
      await dispatch("refreshViewedCustomerStore", data);
    }
    //dispatch("refreshStores");
    dispatch("refreshLazy");
    dispatch("refreshCards");
    dispatch("refreshCustomerOrders");
    dispatch("refreshSubscriptions");
    dispatch("refreshInactiveMeals");
  },

  async initGuest({ commit, state, dispatch }, data = {}) {
    auth.deleteToken();

    if (data.store) {
      await dispatch("refreshViewedCustomerStore", data);
    }

    //dispatch("refreshStores");
    dispatch("refreshLazy");
    dispatch("refreshInactiveMeals");
  },

  async logout({ commit, state }) {
    await axios.post("/api/auth/logout");
    auth.deleteToken();
    window.location = window.app.urls.logout_redirect;
  },

  addJob({ state, dispatch }, args = {}) {
    if (!("id" in args)) {
      args.id = uuid.v1();
    }
    if (!("expires" in args)) {
      args.expires = 1000000;
    }

    if (!state.isLoading && state.initialized) {
      Vue.set(
        state.jobs,
        args.id,
        moment()
          .add(args.expires, "seconds")
          .unix()
      );
    }

    // Automatically remove after 10s
    setTimeout(() => {
      dispatch("removeJob", args.id);
    }, args.expires);

    return args.id;
  },

  removeJob({ state }, id) {
    Vue.delete(state.jobs, id);
  },

  async refreshViewedCustomerStore({ commit, state }, data = {}) {
    if (_.isObject(data.store) && !_.isEmpty(data.store)) {
      commit("setViewedStore", data.store);
    }

    try {
      if (data.store_distance) {
        commit("setViewedStoreDistance", data.store_distance);
      }
    } catch (e) {}

    try {
      if (data.distance) {
        commit("setViewedStoreDistance", data.distance);
      }
    } catch (e) {}

    try {
      if (_.isBoolean(data.will_deliver)) {
        commit("setViewedStoreWillDeliver", data.will_deliver);
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.coupons)) {
        let coupons = data.coupons;

        if (!_.isEmpty(coupons)) {
          commit("setViewedStoreCoupons", { coupons });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.gift_cards)) {
        let gift_cards = data.gift_cards;

        if (!_.isEmpty(gift_cards)) {
          commit("setViewedStoreGiftCards", { gift_cards });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.purchased_gift_cards)) {
        let purchased_gift_cards = data.purchased_gift_cards;

        if (!_.isEmpty(purchased_gift_cards)) {
          commit("setViewedStorePurchasedGiftCards", { purchased_gift_cards });
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

    try {
      if (!_.isEmpty(data.store.delivery_days)) {
        let lineItems = data.store.line_items;

        if (!_.isEmpty(lineItems)) {
          commit("setViewedStoreLineItems", { lineItems });
        }
      }
    } catch (e) {}
  },

  async refreshViewedOwnerStore({ commit, state }, data = {}) {
    if (_.isObject(data.store) && !_.isEmpty(data.store)) {
      commit("setViewedStore", data.store);
    }

    try {
      if (data.store_distance) {
        commit("setViewedStoreDistance", data.store_distance);
      }
    } catch (e) {}

    try {
      if (data.distance) {
        commit("setViewedStoreDistance", data.distance);
      }
    } catch (e) {}

    try {
      if (_.isBoolean(data.will_deliver)) {
        commit("setViewedStoreWillDeliver", data.will_deliver);
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.coupons)) {
        let coupons = data.coupons;

        if (!_.isEmpty(coupons)) {
          commit("setViewedStoreCoupons", { coupons });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.gift_cards)) {
        let gift_cards = data.gift_cards;

        if (!_.isEmpty(gift_cards)) {
          commit("setViewedStoreGiftCards", { gift_cards });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.purchased_gift_cards)) {
        let purchased_gift_cards = data.purchased_gift_cards;

        if (!_.isEmpty(purchased_gift_cards)) {
          commit("setViewedStorePurchasedGiftCards", { purchased_gift_cards });
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
    } catch (e) {}

    try {
      if (data.distance) {
        commit("setViewedStoreDistance", data.distance);
      }
    } catch (e) {}

    try {
      if (_.isBoolean(data.will_deliver)) {
        commit("setViewedStoreWillDeliver", data.will_deliver);
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.coupons)) {
        let coupons = data.coupons;

        if (!_.isEmpty(coupons)) {
          commit("setViewedStoreCoupons", { coupons });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.gift_cards)) {
        let gift_cards = data.gift_cards;

        if (!_.isEmpty(gift_cards)) {
          commit("setViewedStoreGiftCards", { gift_cards });
        }
      }
    } catch (e) {}

    try {
      if (!_.isEmpty(data.purchased_gift_cards)) {
        let purchased_gift_cards = data.purchased_gift_cards;

        if (!_.isEmpty(purchased_gift_cards)) {
          commit("setViewedStorePurchasedGiftCards", { purchased_gift_cards });
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
      commit("storeModuleSettings", { moduleSettings: data });
    } else {
      throw new Error("Failed to retrieve modules");
    }
  },

  async refreshStoreDeliveryFeeZipCodes({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/deliveryFeeZipCodes");
    const { data } = await res;
    if (_.isObject(data)) {
      commit("storeDeliveryFeeZipCodes", { deliveryFeeZipCodes: data });
    } else {
      throw new Error("Failed to retrieve delivery fee zip codes");
    }
  },

  async refreshStoreDeliveryFeeRanges({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/deliveryFeeRanges");
    const { data } = await res;
    if (_.isObject(data)) {
      commit("storeDeliveryFeeRanges", { deliveryFeeRanges: data });
    } else {
      throw new Error("Failed to retrieve delivery fee ranges");
    }
  },

  async refreshStoreHolidayTransferTimes({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/holidayTransferTimes");
    const { data } = await res;
    if (_.isObject(data)) {
      commit("storeHolidayTransferTimes", { holidayTransferTimes: data });
    } else {
      throw new Error("Failed to retrieve holiday transfer times");
    }
  },

  async refreshStoreReportSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/reportSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeReportSettings", { report_settings: data });
    } else {
      throw new Error("Failed to retrieve report settings");
    }
  },

  async refreshStoreLabelSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/labelSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeLabelSettings", { label_settings: data });
    } else {
      throw new Error("Failed to retrieve label settings");
    }
  },

  async refreshStoreOrderLabelSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/orderLabelSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeOrderLabelSettings", { order_label_settings: data });
    } else {
      throw new Error("Failed to retrieve order label settings");
    }
  },

  async refreshStoreMenuSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/menuSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeMenuSettings", { menu_settings: data });
    } else {
      throw new Error("Failed to retrieve menu settings");
    }
  },

  async refreshStoreSMSSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/smsSettings");
    const { data } = await res;

    if (_.isObject(data)) {
      commit("storeSMSSettings", { smsSettings: data });
    } else {
      throw new Error("Failed to retrieve sms settings");
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

  async refreshStoreGiftCards({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/giftCards");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeGiftCards", { gift_cards: data });
    } else {
      throw new Error("Failed to retrieve gift cards");
    }
  },

  async refreshStorePurchasedGiftCards({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/purchasedGiftCards");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storePurchasedGiftCards", { purchased_gift_cards: data });
    } else {
      throw new Error("Failed to retrieve gift cards");
    }
  },

  async refreshStoreSurveyQuestions({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/surveyQuestions");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeSurveyQuestions", { survey_questions: data });
    } else {
      throw new Error("Failed to retrieve survey questions");
    }
  },

  async refreshStoreSurveyResponses({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/surveyResponses");
    const { data } = await res;

    if (_.isArray(data)) {
      commit("storeSurveyResponses", { survey_responses: data });
    } else {
      throw new Error("Failed to retrieve survey responses");
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
        // subscription.next_delivery_date = subscription.next_delivery_date
        //   ? moment.utc(subscription.next_delivery_date.date)
        //   : null;
        // subscription.next_renewal_at = moment
        //   .utc(subscription.next_renewal_at)
        //   .local();
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

  async refreshStoreMealPackageBag({ state }, oldMealPackage = {}) {
    let index = _.findIndex(state.viewed_store.packages, [
      "id",
      parseInt(oldMealPackage.id)
    ]);

    if (isNaN(index) || index < 0) {
      return null;
    } else {
      if (oldMealPackage.refreshed_bag || oldMealPackage.refreshed) {
        return oldMealPackage;
      }

      const res = await axios.get(
        "/api/refresh_bag/meal_package/" + oldMealPackage.id
      );
      const { data } = await res;

      if (data.package) {
        let meal_package = data.package;
        meal_package.refreshed_bag = true;

        state.viewed_store.packages.splice(index, 1, meal_package);

        return meal_package;
      } else {
        return null;
      }
    }
  },

  async refreshStoreMealPackage({ state }, oldMealPackage = {}) {
    let index = _.findIndex(state.viewed_store.packages, [
      "id",
      parseInt(oldMealPackage.id)
    ]);
    /*if (oldMealPackage.refreshed) {
        return oldMealPackage;
      }*/

    // if (
    //   state.viewed_store.refreshed_package_ids.includes(oldMealPackage.id)
    // ) {
    //   return oldMealPackage;
    // }

    let url = "";
    if (oldMealPackage.selectedSizeId !== undefined) {
      url =
        "/api/refresh/meal_package_with_size/" + oldMealPackage.selectedSizeId;
    } else {
      url = "/api/refresh/meal_package/" + oldMealPackage.id;
    }

    const res = await axios.get(url);
    const { data } = await res;

    if (data.package) {
      let meal_package = data.package;
      if (oldMealPackage.delivery_day) {
        meal_package = {
          ...meal_package,
          delivery_day: oldMealPackage.delivery_day
        };
      }

      //meal_package.refreshed = true;
      //meal_package.refreshed_bag = true;

      state.viewed_store.packages.splice(index, 1, meal_package);
      state.viewed_store.refreshed_package_ids.push(meal_package.id);

      if (meal_package.meals) {
        meal_package.meals.forEach(meal => {
          meal.delivery_day_id = meal.pivot.delivery_day_id;
        });
      }
      if (oldMealPackage.selectedSizeId) {
        meal_package.sizes
          .find(size => {
            return size.id == oldMealPackage.selectedSizeId;
          })
          .meals.forEach(meal => {
            meal.delivery_day_id = meal.pivot.delivery_day_id;
          });
      }
      return meal_package;
    } else {
      return null;
    }
  },

  async refreshStoreMealBag({ state }, oldMeal = {}) {
    let mealIndex = _.findIndex(state.viewed_store.meals, [
      "id",
      parseInt(oldMeal.id)
    ]);

    if (isNaN(mealIndex) || mealIndex < 0) {
      return null;
    } else {
      if (oldMeal.refreshed || oldMeal.refreshed_bag) {
        return oldMeal;
      }

      const res = await axios.get("/api/refresh_bag/meal/" + oldMeal.id);
      const { data } = await res;

      if (data.meal) {
        let meal = data.meal;
        meal.refreshed_bag = true;

        state.viewed_store.meals.splice(mealIndex, 1, meal);

        return meal;
      } else {
        return null;
      }
    }
  },

  async refreshStoreMeal({ state }, oldMeal = {}) {
    let mealIndex = _.findIndex(state.viewed_store.meals, [
      "id",
      parseInt(oldMeal.id)
    ]);

    if (isNaN(mealIndex) || mealIndex < 0) {
      return null;
    } else {
      if (oldMeal.refreshed) {
        return oldMeal;
      }

      const res = await axios.get("/api/refresh/meal/" + oldMeal.id);
      const { data } = await res;

      if (data.meal) {
        let meal = data.meal;
        meal.refreshed = true;
        meal.refreshed_bag = true;

        state.viewed_store.meals.splice(mealIndex, 1, meal);

        return meal;
      } else {
        return null;
      }
    }
  },

  async refreshLazyStore({ state }, args = {}) {
    if (state.isLazyStore) {
      return false;
    }

    state.isLazyStore = true;
    triggerLazyStore(state, 0, 0, 0, "", 0);
  },

  async refreshLazyDD({ state }, args = {}) {
    const { delivery_day } = args;

    if (!delivery_day) {
      return false;
    } else if (!delivery_day.has_items) {
      state.viewed_store = {
        ...state.viewed_store,
        delivery_day
      };

      return false;
    }

    const key = "dd_" + delivery_day.id;
    if (state.isLazyDD[key]) {
      state.viewed_store = {
        ...state.viewed_store,
        delivery_day
      };

      return false;
    }

    Vue.set(state.isLazyDD, key, true);
    Vue.set(state.isLazyDDLoading, key, true);

    // state.viewed_store = {
    //   ...state.viewed_store,
    //   delivery_day
    // };

    triggerLazyDD(state, 0, 0, 0, "", 0, delivery_day);
  },

  async refreshLazy({ state }, args = {}) {
    if (state.isLazy || !state.viewed_store.id) {
      return false;
    }

    state.isLazy = true;
    state.isLazyLoading = true;

    triggerLazy(state, 0, 0, 0, "", 0);
  },

  async refreshInactiveMeals({ state }, args = {}) {
    const res = await axios.get("/api/refresh_inactive_meals");
    const { data } = await res;

    if (data.meals) {
      data.meals.forEach(meal => {
        let found =
          _.find(state.viewed_store.meals, ["id", parseInt(meal.id)]) || null;
        if (!found) {
          state.viewed_store.meals.push(meal);
        }
      });
    }
  },

  async refreshDeliveryDay({ state }, args = {}) {
    const base_day = moment().format("YYYY-MM-DD");
    const store_id = state.viewed_store.id;

    if (store_id) {
      const res = await axios.get(
        "/api/delivery_days?base_day=" + base_day + "&store_id=" + store_id
      );
      const { data } = await res;

      if (data && data.delivery_days) {
        state.viewed_store = {
          ...state.viewed_store,
          delivery_days: data.delivery_days
        };
      }
    }
  },

  async refreshStoreMeals({ commit, state }, args = {}) {
    if (state.refreshed == true) {
      return;
    }

    const res = await axios.get("/api/refresh");
    const { data } = await res;

    if (data && data.store) {
      state.viewed_store = {
        ...state.viewed_store,
        meals: data.store.meals,
        packages: data.store.packages,
        refreshed: true
      };
    } else {
      throw new Error("Failed to refresh");
    }
  },

  async refreshStoreCustomers({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/customers");
    const { data } = await res;
    const customers = data;

    if (_.isArray(customers)) {
      commit("storeCustomers", { customers });
    } else {
      throw new Error("Failed to retrieve customers");
    }
    state.isLoading = false;
  },

  async refreshStoreCustomersNoOrders({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/customersNoOrders");
    const { data } = await res;
    const customers = data;

    if (_.isArray(customers)) {
      commit("storeCustomers", { customers });
    } else {
      throw new Error("Failed to retrieve customers");
    }
  },

  async refreshStoreLeads({ commit, state }, args = {}) {
    state.isLoading = true;
    const res = await axios.get("/api/me/leads");
    const { data } = await res;
    const leads = data;

    if (_.isArray(leads)) {
      commit("storeLeads", { leads });
    } else {
      throw new Error("Failed to retrieve leads");
    }
    state.isLoading = false;
  },

  async refreshStoreStaff({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/staff");
    const { data } = await res;
    const staff = data;

    if (_.isArray(staff)) {
      commit("storeStaff", { staff });
    } else {
      throw new Error("Failed to retrieve staff");
    }
  },

  async refreshStoreReferrals({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/referrals");
    const { data } = await res;
    const referrals = data;

    if (_.isArray(referrals)) {
      commit("storeReferrals", { referrals });
    } else {
      throw new Error("Failed to retrieve referrals");
    }
  },

  async refreshStoreReferralSettings({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/referralSettings");
    const { data } = await res;
    const referral_settings = data;

    if (_.isArray(referral_settings)) {
      commit("storeReferralSettings", { referral_settings });
    } else {
      throw new Error("Failed to retrieve referral settings");
    }
  },

  async refreshStorePromotions({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/promotions");
    const { data } = await res;
    const promotions = data;

    if (_.isArray(promotions)) {
      commit("storePromotions", { promotions });
    } else {
      throw new Error("Failed to retrieve promotions");
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
    state.isLoading = true;
    const res = await axios.get("/api/me/orders/ingredients", {
      params: args
    });
    let { data } = await res;

    if (_.isObject(data)) {
      data = Object.values(data);
    }

    if (_.isArray(data)) {
      commit("orderIngredientsSpecial", {
        ingredients: _.keyBy(data, "id")
      });
    } else {
      throw new Error("Failed to retrieve order ingredients");
    }
    state.isLoading = false;
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

  async updateGiftCard({ commit, state, getters, dispatch }, { data, id }) {
    if (!id || !data) {
      return;
    }

    const index = _.findIndex(getters.storeGiftCards, ["id", id]);

    if (index === -1) {
      return;
    }

    Vue.set(
      state.store.gift_cards.data,
      index,
      _.merge(state.store.gift_cards.data[index], data)
    );
    const resp = await axios.patch(`/api/me/giftCards/${id}`, data);
    Vue.set(state.store.gift_cards.data, index, resp.data);
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
      throw new Error("Failed to retrieve packages");
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

  async refreshUpcomingOrdersWithoutItems({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getUpcomingOrdersWithoutItems");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeUpcomingOrdersWithoutItems", { orders });
    } else {
      throw new Error("Failed to retrieve orders");
    }
  },

  async refreshRecentOrders({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getRecentOrders");
    const { data } = await res;

    if (_.isArray(data)) {
      const orders = _.map(data, order => {
        order.created_at = moment.utc(order.created_at).local(); //.format('ddd, MMMM Do')
        order.updated_at = moment.utc(order.updated_at).local(); //.format('ddd, MMMM Do')
        order.delivery_date = moment.utc(order.delivery_date);
        //.local(); //.format('ddd, MMMM Do')
        return order;
      });
      commit("storeUpcomingOrdersWithoutItems", { orders });
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

  async refreshPayments({ commit, state }, args = {}) {
    const res = await axios.post("/api/me/getPayments");
    const { data } = await res;

    if (_.isArray(data)) {
      const payments = _.map(data, payment => {
        payment.created_at = moment.utc(payment.created_at).local();
        payment.updated_at = moment.utc(payment.updated_at).local();
        payment.delivery_date = moment.utc(payment.delivery_date);
        return payment;
      });
      commit("storePayments", { payments });
    } else {
      throw new Error("Failed to retrieve payments");
    }
  },

  async refreshSubscriptions({ commit, state }, args = {}) {
    const res = await axios.get("/api/me/subscriptions");
    const { data } = await res;

    if (_.isArray(data)) {
      const subscriptions = _.map(data, subscription => {
        subscription.created_at = moment.utc(subscription.created_at).local();
        subscription.updated_at = moment.utc(subscription.updated_at).local();
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
  },

  async showSpinner({ commit, state }, args = {}) {
    commit("showSpinner", {});
  },

  async hideSpinner({ commit, state }, args = {}) {
    commit("hideSpinner", {});
  },

  async enableSpinner({ commit, state }, args = {}) {
    commit("enableSpinner", {});
  },

  async disableSpinner({ commit, state }, args = {}) {
    commit("disableSpinner", {});
  }
};

// getters are functions
const getters = {
  SMSMessages(state) {
    return state.store.sms.messages.data;
  },
  SMSTemplates(state) {
    return state.store.sms.templates.data;
  },
  SMSLists(state) {
    return state.store.sms.lists.data;
  },
  SMSContacts(state) {
    return state.store.sms.contacts.data;
  },
  SMSChats(state) {
    return state.store.sms.chats.data;
  },
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
  context(state) {
    return state.context;
  },
  isLazy(state) {
    return state.isLazy;
  },
  isLazyLoading(state) {
    return state.isLazyLoading;
  },
  isLazyDDLoading: state => delivery_day => {
    if (delivery_day) {
      return state.isLazyDDLoading["dd_" + delivery_day.id];
    } else {
      return false;
    }
  },
  isLazyStore(state) {
    return state.isLazyStore;
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
  viewedStoreSettings(state, getters) {
    return state.viewed_store.settings;
  },
  viewedStoreDistance(state, getters) {
    return state.viewed_store.distance;
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
  viewedStoreGiftCards(state, getters) {
    try {
      return state.viewed_store.gift_cards;
    } catch (e) {
      return null;
    }
  },
  viewedStorePurchasedGiftCards(state, getters) {
    try {
      return state.viewed_store.purchased_gift_cards;
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
  viewedStoreMeal: state => (id, defaultMeal = null) => {
    try {
      let meal = null;
      if (defaultMeal != null) {
        meal = defaultMeal;
      } else {
        meal = _.find(state.viewed_store.meals, ["id", parseInt(id)]) || null;
      }
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
        special_instructions = null,
        showSize = true,
        customTitle = null,
        customSize = null
      ) => {
        let title = "";

        if (customTitle && customSize) {
          title = customTitle + " - " + customSize;
        } else if (customTitle && !customSize) {
          title = customTitle + " - " + size.title;
        } else if (!customTitle && customSize) {
          title = meal.title + " - " + customSize;
        } else {
          title = meal.title;
          if (_.isObject(size) && showSize) {
            title = size.full_title;
          }
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
  viewedStoreMealPackage: state => (id, defaultMeal = null) => {
    try {
      let meal = null;
      if (defaultMeal != null) {
        meal = defaultMeal;
      } else {
        meal =
          _.find(state.viewed_store.packages, ["id", parseInt(id)]) || null;
      }

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
        special_instructions = null,
        showSize = true
      ) => {
        let title = meal.title;

        if (_.isObject(size) && showSize) {
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
  viewedStoreDeliveryFeeZipCodes: state => {
    try {
      return state.viewed_store.delivery_fee_zip_codes || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreDeliveryFeeRanges: state => {
    try {
      return state.viewed_store.delivery_fee_ranges || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreHolidayTransferTimes: state => {
    try {
      return state.viewed_store.holiday_transfer_times || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreReportSettings: state => {
    try {
      return state.viewed_store.report_settings || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreLabelSettings: state => {
    try {
      return state.viewed_store.label_settings || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreOrderLabelSettings: state => {
    try {
      return state.viewed_store.order_label_settings || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreMenuSettings: state => {
    try {
      return state.viewed_store.menu_settings || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreReferrals: state => {
    try {
      return state.viewed_store.referrals || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreReferralSettings: state => {
    try {
      return state.viewed_store.referral_settings || {};
    } catch (e) {
      return {};
    }
  },
  viewedStorePromotions: state => {
    try {
      return state.viewed_store.promotions || {};
    } catch (e) {
      return {};
    }
  },
  viewedStoreDeliveryDays: state => {
    return state.viewed_store.delivery_days || [];
  },
  viewedStoreDeliveryDay: state => {
    return state.viewed_store.delivery_day || {};
  },
  isLoading(state) {
    if (!state.hideSpinner) {
      return state.isLoading || !_.isEmpty(state.jobs);
    } else {
      return false;
    }
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
  mealMixItems(state) {
    if (
      !state.viewed_store.delivery_day ||
      !state.viewed_store.delivery_day.has_items
    ) {
      return {
        finalCategories: state.viewed_store.finalCategories,
        items: [...state.viewed_store.items, {}],
        isRunningLazy: state.isLazyLoading
      };
    } else {
      const key = "dd_" + state.viewed_store.delivery_day.id;

      if (state.viewed_store.dataDD[key]) {
        return {
          finalCategories: state.viewed_store.dataDD[key].finalCategories,
          items: state.viewed_store.dataDD[key].items,
          isRunningLazy: state.isLazyDDLoading[key]
        };
      }

      return {
        finalCategories: [],
        items: []
      };
    }
  },
  bagItems(state) {
    let menu_update_time = 0;

    if (state.viewed_store && state.viewed_store.menu_update_time) {
      menu_update_time = moment(state.viewed_store.menu_update_time).unix();
    }

    /* Check */
    // if (menu_update_time != 0) {
    //   for (let key in state.bag.items) {
    //     let object = state.bag.items[key];

    //     if (object.added < menu_update_time) {
    //       Vue.delete(state.bag.items, key);
    //       delete state.bag.items[key];
    //       ``;
    //     }
    //   }
    // }
    /* Check End */

    let items = _.filter(state.bag.items);
    items = _.sortBy(items, "added");
    return items;
  },
  bagQuantity(state) {
    return _.sumBy(_.compact(_.toArray(state.bag.items)), item => {
      // if (!item.meal_package) {
      return item.quantity;
      // } else {
      //   return (
      //     item.quantity *
      //     _.sumBy(_.compact(_.toArray(item.meal.meals)), item =>
      //       item.pivot ? item.pivot.quantity : 1
      //     )
      //   );
      // }
    });
  },
  bagSubscription(state) {
    return state.bag.subscription;
  },
  bagCoupon(state) {
    return state.bag.coupon;
  },
  bagPurchasedGiftCard(state) {
    return state.bag.purchased_gift_card;
  },
  bagReferral(state) {
    return state.bag.referral;
  },
  bagReferralUrl(state) {
    return state.bag.referralUrl;
  },
  bagMealPlan(state) {
    return state.bag.meal_plan;
  },
  bagPickup(state) {
    return state.bag.pickup;
  },
  bagNotes(state) {
    return state.bag.notes;
  },
  bagPublicNotes(state) {
    return state.bag.publicNotes;
  },
  bagGratuityPercent(state) {
    return state.bag.gratuityPercent;
  },
  bagCustomGratuity(state) {
    return state.bag.customGratuity;
  },
  bagSubscriptionInterval(state) {
    return state.bag.subscriptionInterval;
  },
  bagMultDDZipCode(state) {
    return state.multDDZipCode;
  },
  bagPickupSet(state) {
    return state.bag.pickupSet;
  },
  bagDeliveryDate(state) {
    return state.delivery_date;
  },
  bagZipCode(state) {
    return state.zip_code;
  },
  bagPickupLocation(state) {
    return state.bag.pickupLocation;
  },
  bagTransferTime(state) {
    return state.bag.transferTime;
  },
  bagStaffMember(state) {
    return state.bag.staffMember;
  },
  bagCustomerModel(state) {
    return state.bag.customerModel;
  },
  bagDeliveryFee(state) {
    return state.bag.deliveryFee;
  },
  bagFrequencyType(state) {
    return state.bag.frequencyType;
  },
  bagLineItems(state) {
    return state.bag.lineItems;
  },
  bagDeliverySettings(state, getters) {
    const { bagCustomDeliveryDay } = getters;

    if (bagCustomDeliveryDay) {
      const {
        day,
        type,
        instructions,
        cutoff_type,
        cutoff_days,
        cutoff_hours,
        applyFee,
        fee,
        feeType,
        mileageBase,
        mileagePerMile,
        pickup_location_ids
      } = getters.bagCustomDeliveryDay;
      return {
        instructions,
        cutoff_type,
        cutoff_days,
        cutoff_hours,
        applyDeliveryFee: applyFee,
        deliveryFee: fee,
        deliveryFeeType: feeType,
        mileageBase,
        mileagePerMile,
        pickup_location_ids
      };
    } else {
      return getters.viewedStoreSettings;
    }
  },
  // DeliveryDay which matches delivery date selection
  bagCustomDeliveryDay(state, getters) {
    if (!getters.viewedStoreModules.customDeliveryDays) {
      return null;
    }

    const weekIndex = moment(state.delivery_date).format("d");
    const { pickup } = state.bag;

    const dday = _.find(state.viewed_store.delivery_days, {
      day: weekIndex,
      type: pickup ? "pickup" : "delivery"
    });

    return dday || null;
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

    const delivery_day = meal.delivery_day ? meal.delivery_day : null;

    let guid = CryptoJS.MD5(
      JSON.stringify({
        meal: mealId,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        delivery_day,
        title: meal.customTitle ? meal.customTitle : meal.title,
        mappingId: meal.mappingId ? meal.mappingId : null
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
    let totalBagPricePreFees = 0;
    let items = getters.bagItems;

    if (items) {
      items.forEach(item => {
        if (state.bag.frequencyType === "sub") {
          if (
            !isNaN(item.price) &&
            !isNaN(item.quantity) &&
            !item.free &&
            item.meal.frequencyType == "sub"
          ) {
            totalBagPricePreFees += item.price * item.quantity;
          }
        } else {
          if (
            !isNaN(item.price) &&
            !isNaN(item.quantity) &&
            !item.free &&
            item.meal.frequencyType !== "sub"
          ) {
            totalBagPricePreFees += item.price * item.quantity;
          }
        }
      });
    }

    return totalBagPricePreFees;
  },
  totalBagPricePreFeesBothTypes(state, getters) {
    let totalBagPricePreFeesBothTypes = 0;
    let items = getters.bagItems;

    if (items) {
      items.forEach(item => {
        if (!isNaN(item.price) && !isNaN(item.quantity) && !item.free) {
          totalBagPricePreFeesBothTypes += item.price * item.quantity;
        }
      });
    }

    return totalBagPricePreFeesBothTypes;
  },
  totalBagPrice(state, getters) {
    let totalBagPrice = 0;
    let items = getters.bagItems;

    if (items) {
      items.forEach(item => {
        if (!isNaN(item.price) && !isNaN(item.quantity) && !item.free) {
          totalBagPrice += item.price * item.quantity;
        }
      });
    }

    if (getters.viewedStoreSetting("applyDeliveryFee", false)) {
      totalBagPrice += getters.viewedStore.settings.deliveryFee;
    }
    if (getters.viewedStoreSetting("applyProcessingFee", false)) {
      totalBagPrice += getters.viewedStore.settings.processingFee;
    }

    return totalBagPrice;

    // Old Flow
    /*
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
    */
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
  orderIngredientsSpecial(state) {
    return state.store.order_ingredients_special.data || {};
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

  storeDeliveryFeeZipCodes: state => {
    try {
      return state.store.delivery_fee_zip_codes.data || {};
    } catch (e) {
      return {};
    }
  },

  storeDeliveryFeeRanges: state => {
    try {
      return state.store.delivery_fee_ranges.data || {};
    } catch (e) {
      return {};
    }
  },

  storeHolidayTransferTimes: state => {
    try {
      return state.store.holiday_transfer_times.data || {};
    } catch (e) {
      return {};
    }
  },

  storeReportSettings: state => {
    try {
      return state.store.report_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeLabelSettings: state => {
    try {
      return state.store.label_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeOrderLabelSettings: state => {
    try {
      return state.store.order_label_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeMenuSettings: state => {
    try {
      return state.store.menu_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeSMSSettings: state => {
    try {
      return state.store.sms_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storeReferrals: state => {
    try {
      return state.store.referrals.data || {};
    } catch (e) {
      return {};
    }
  },
  storeReferralSettings: state => {
    try {
      return state.store.referral_settings.data || {};
    } catch (e) {
      return {};
    }
  },
  storePromotions: state => {
    try {
      return state.store.promotions.data || {};
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
  storeGiftCards: state => {
    try {
      return state.store.gift_cards.data || {};
    } catch (e) {
      return {};
    }
  },
  storePurchasedGiftCards: state => {
    try {
      return state.store.purchased_gift_cards.data || {};
    } catch (e) {
      return {};
    }
  },
  storeSurveyQuestions: state => {
    try {
      return state.store.survey_questions.data || {};
    } catch (e) {
      return {};
    }
  },
  storeSurveyResponses: state => {
    try {
      return state.store.survey_responses.data || {};
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
  storeMeal: state => (id, defaultMeal = null) => {
    try {
      let meal = null;

      if (defaultMeal) {
        meal = defaultMeal;
      } else {
        meal = _.find(state.store.meals.data, ["id", parseInt(id)]) || null;
      }

      if (!meal) {
        return null;
      }

      meal.getSize = (sizeId, customSize = null) => {
        if (customSize) {
          return customSize;
        }
        return _.find(meal.sizes, ["id", parseInt(sizeId)]);
      };

      meal.getTitle = (
        html = false,
        size = null,
        components = null,
        addons = null,
        special_instructions = null,
        showSize = true,
        customTitle = null,
        customSize = null
      ) => {
        let title = meal.title;

        if (
          meal.default_size_title != null &&
          meal.default_size_title.length > 0 &&
          showSize
        ) {
          title = title + " - " + meal.default_size_title;
        }

        if (_.isObject(size) && showSize && !customSize) {
          title = size.full_title;
        }

        let hasComponents = _.isArray(components) && components.length;
        let hasAddons = _.isArray(addons) && addons.length;

        if (customTitle) {
          title = customTitle;
        }

        if (customTitle && customSize) {
          title = title + " - " + customSize;
        }

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

  storeLineItem: state => id => {
    try {
      let lineItem =
        _.find(state.store.lineItems.data, ["id", parseInt(id)]) || null;

      if (!lineItem) {
        return null;
      }

      return lineItem;
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
  storeLeads: state => {
    try {
      return state.store.leads.data || {};
    } catch (e) {
      return {};
    }
  },
  storeStaff: state => {
    try {
      return state.store.staff.data || {};
    } catch (e) {
      return {};
    }
  },
  storeOrders: state => {
    try {
      return state.orders.data || [];
    } catch (e) {
      return {};
    }
  },
  storePayments: state => {
    try {
      return state.payments.data || [];
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
  storeUpcomingOrdersWithoutItems: state => {
    try {
      return state.upcomingOrdersWithoutItems.data || [];
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
    paths: ["bag", "cards", "printer.device", "delivery_date"]
    // paths: ["cards"]
  })
];

// A Vuex instance is created by combining the state, mutations, actions, and
// getters.
export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
  plugins,
  modules: {
    resources: ResourceStore,
    payoutsResources: PayoutResourceStore,
    printer: PrinterStore
  }
});
