import SalesTax from "sales-tax";
import store from "../store";
import { mapGetters, mapMutations } from "vuex";
import format from "../lib/format";

export default {
  watch: {
    bagPickup: function(val) {
      this.transferDayType = val;
    }
  },
  data() {
    return {
      minimumDeliveryDayAmount: 0,
      transferDayType: 0
    };
  },
  computed: {
    ...mapGetters({
      storeSetting: "viewedStoreSetting",
      storeModules: "viewedStoreModules",
      bagDeliveryDate: "bagDeliveryDate",
      store: "viewedStore",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      totalBagPrice: "totalBagPrice",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPricePreFeesBothTypes: "totalBagPricePreFeesBothTypes",
      mealMixItems: "mealMixItems",
      bag: "bagItems",
      bagSubscription: "bagSubscription"
    }),
    hasDeliveryOption() {
      return (
        this.store.settings.transferType.includes("delivery") ||
        ((this.store.modules.customDeliveryDays ||
          this.store.modules.multipleDeliveryDays) &&
          this.store.delivery_days.some(day => {
            return day.type == "delivery";
          }))
      );
    },
    adjustingSubscription() {
      if (this.bagSubscription) {
        return true;
      }
    },
    noAvailableDays() {
      if (
        this.sortedDeliveryDays &&
        this.sortedDeliveryDays.length === 0 &&
        !this.bagPickup &&
        this.bagZipCode
      ) {
        return true;
      }
      return false;
    },
    deliveryDayZipCodeMatch() {
      let match = this.store.delivery_day_zip_codes.some(ddZipCode => {
        return ddZipCode.zip_code === this.bagZipCode;
      });
      return !this.bagZipCode
        ? true
        : this.hasDeliveryDayZipCodes
        ? match
        : true;
    },
    sortedDeliveryDays() {
      // If delivery_days table has the same day of the week for both pickup & delivery, only show the day once
      let baseDeliveryDays = this.store.delivery_days;

      if (this.context === "store") {
        baseDeliveryDays.forEach(day => {
          let m = moment(day.day_friendly);
          let newDate = moment(m).subtract(1, "week");
          day.day_friendly = newDate;
        });
      }

      let deliveryWeeks = this.store.settings.deliveryWeeks;
      if (this.context === "store" && deliveryWeeks < 8) {
        deliveryWeeks = 8;
      }
      let storeDeliveryDays = [];

      for (let i = 0; i <= deliveryWeeks; i++) {
        baseDeliveryDays.forEach(day => {
          let m = moment(day.day_friendly);
          let newDate = moment(m).add(i, "week");
          let newDay = { ...day };
          newDay.day_friendly = newDate.format("YYYY-MM-DD");
          storeDeliveryDays.push(newDay);
        });
      }

      // storeDeliveryDays = storeDeliveryDays.reverse();

      // Add all future dates with no cutoff for manual orders
      // if (this.context == "store") {
      //   storeDeliveryDays = [];
      //   let today = new Date();
      //   let year = today.getFullYear();
      //   let month = today.getMonth();
      //   let date = today.getDate();

      //   for (let i = 0; i < 30; i++) {
      //     let day = new Date(year, month, date + i);
      //     let multDD = { ...this.store.delivery_days[0] };
      //     multDD.day_friendly = moment(day).format("YYYY-MM-DD");
      //     multDD.type = "pickup";
      //     storeDeliveryDays.push(multDD);
      //   }
      //   for (let i = 0; i < 30; i++) {
      //     let day = new Date(year, month, date + i);
      //     let multDD = { ...this.store.delivery_days[0] };
      //     multDD.day_friendly = moment(day).format("YYYY-MM-DD");
      //     multDD.type = "delivery";
      //     storeDeliveryDays.push(multDD);
      //   }
      // }

      let sortedDays = storeDeliveryDays;
      // let sortedDays = [];

      // Not restricting this on stores for now, however stores may want to be restricted since they don't memorize which days for which postal codes
      if (this.context !== "store") {
        // If the store only serves certain zip codes on certain delivery days
        if (this.store.delivery_day_zip_codes.length > 0) {
          let deliveryDayIds = [];
          this.store.delivery_day_zip_codes.forEach(ddZipCode => {
            if (ddZipCode.zip_code === parseInt(this.bagZipCode)) {
              deliveryDayIds.push(ddZipCode.delivery_day_id);
            }
          });
          sortedDays = sortedDays.filter(day => {
            if (this.bagPickup) {
              return true;
            } else {
              if (deliveryDayIds.includes(day.id) && day.type == "delivery") {
                return true;
              }
            }

            // return deliveryDayIds.includes(day.id);
          });
        }
      }

      if (this.bagPickup) {
        sortedDays = sortedDays.filter(day => {
          return day.type === "pickup";
        });
      } else {
        sortedDays = sortedDays.filter(day => {
          return day.type === "delivery";
        });
      }

      // sortedDays.sort(function(a, b) {
      //   return new Date(a.day_friendly) - new Date(b.day_friendly);
      // });

      // Removing past dates
      sortedDays = sortedDays.filter(day => {
        return !moment(day.day_friendly).isBefore(moment().startOf("day"));
      });

      // Removing inactive days
      sortedDays = sortedDays.filter(day => {
        return day.active;
      });

      if (this.adjustingSubscription) {
        let next_renewal_at = moment(this.bagSubscription.next_renewal_at);
        let now = moment();
        sortedDays.map(day => {
          if (moment(day.day_friendly).isBetween(now, next_renewal_at)) {
            day.day_friendly = moment(day.day_friendly)
              .add(1, "week")
              .format("YYYY-MM-DD");
            return day;
          } else {
            return day;
          }
        });
      }

      return sortedDays;
    },
    allDeliveryDays() {
      // If delivery_days table has the same day of the week for both pickup & delivery, only show the day once
      let baseDeliveryDays = this.store.delivery_days;
      let deliveryWeeks = this.store.settings.deliveryWeeks;
      let storeDeliveryDays = [];

      for (let i = 0; i <= deliveryWeeks; i++) {
        baseDeliveryDays.forEach(day => {
          let m = moment(day.day_friendly);
          let newDate = moment(m).subtract(i, "week");
          let newDay = { ...day };
          newDay.day_friendly = newDate.format("YYYY-MM-DD");
          storeDeliveryDays.push(newDay);
        });
      }

      storeDeliveryDays = storeDeliveryDays.reverse();

      // Add all future dates with no cutoff for manual orders
      if (this.context == "store") {
        storeDeliveryDays = [];
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let date = today.getDate();

        for (let i = 0; i < 30; i++) {
          let day = new Date(year, month, date + i);
          let multDD = { ...this.store.delivery_days[0] };
          multDD.day_friendly = moment(day).format("YYYY-MM-DD");
          storeDeliveryDays.push(multDD);
        }
      }

      let allDays = storeDeliveryDays;
      // let allDays = [];

      // If the store only serves certain zip codes on certain delivery days
      if (this.store.delivery_day_zip_codes.length > 0) {
        let deliveryDayIds = [];
        this.store.delivery_day_zip_codes.forEach(ddZipCode => {
          if (ddZipCode.zip_code === parseInt(this.bagZipCode)) {
            deliveryDayIds.push(ddZipCode.delivery_day_id);
          }
        });
        allDays = allDays.filter(day => {
          if (this.bagPickup) {
            return true;
          } else {
            if (deliveryDayIds.includes(day.id) && day.type == "delivery") {
              return true;
            }
          }

          // return deliveryDayIds.includes(day.id);
        });
      }

      allDays.sort(function(a, b) {
        return new Date(a.day_friendly) - new Date(b.day_friendly);
      });

      // Removing past dates
      allDays = allDays.filter(day => {
        return !moment(day.day_friendly).isBefore(moment().startOf("day"));
      });

      // Removing inactive days
      allDays = allDays.filter(day => {
        return day.active;
      });

      if (this.adjustingSubscription) {
        let next_renewal_at = moment(this.bagSubscription.next_renewal_at);
        let now = moment();
        allDays.map(day => {
          if (moment(day.day_friendly).isBetween(now, next_renewal_at)) {
            day.day_friendly = moment(day.day_friendly)
              .add(1, "week")
              .format("YYYY-MM-DD");
            return day;
          } else {
            return day;
          }
        });
      }

      return allDays;
    },
    hasDeliveryDayZipCodes() {
      return this.store.delivery_day_zip_codes &&
        this.store.delivery_day_zip_codes.length > 0
        ? true
        : false;
    },
    prefix() {
      if (this.loggedIn) {
        return "/api/me/";
      } else {
        return "/api/guest/";
      }
    },
    isLoadingDeliveryDays() {
      if (!this.store.delivery_days || this.store.delivery_days.length == 0) {
        return true;
      }
      return false;
    },
    hasBothTranserTypes() {
      let hasPickup = false;
      let hasDelivery = false;
      this.store.delivery_days.forEach(day => {
        if (day.type === "delivery") {
          hasDelivery = true;
        }
        if (day.type === "pickup") {
          hasPickup = true;
        }
      });
      if (hasPickup && hasDelivery) {
        return true;
      } else {
        return false;
      }
    },
    isMultipleDelivery() {
      return this.storeModules.multipleDeliveryDays == 1 ? true : false;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFeesBothTypes;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "items";
      }
      return "item";
    },
    addMore() {
      let message = "";
      if (this.isMultipleDelivery) {
        if (this.minimumDeliveryDayAmount > 0) {
          let groupTotal = [];
          this.groupBag.forEach((group, index) => {
            groupTotal[index] = 0;
            group.items.forEach(item => {
              groupTotal[index] += item.price * item.quantity;
            });
          });
          if (
            !groupTotal.every(item => {
              return item > this.minimumDeliveryDayAmount;
            }) ||
            groupTotal.length == 0
          ) {
            message =
              "Must meet " +
              format.money(
                this.minimumDeliveryDayAmount,
                this.storeSettings.currency
              ) +
              " minimum for each day.";

            return message;
          }
        }
      }

      if (this.minOption === "meals")
        message =
          "Please add " +
          this.remainingMeals +
          " " +
          this.singOrPlural +
          " to continue.";
      else if (this.minOption === "price")
        message =
          "Please add " +
          format.money(this.remainingPrice, this.storeSettings.currency) +
          " more to continue.";

      if (
        this.mealMixItems.finalCategories.some(cat => {
          return cat.minimumType !== null;
        }) &&
        !this.checkCategoryMinimums()
      ) {
        message = this.categoryMinimumMessage();
      }

      return message;
    },
    minimumMet() {
      let settingsPassed = true;
      let deliveryDayMinimumPassed = true;
      let categoryMinimumPassed = true;
      let giftCardOnly = true;

      // Check delivery day minimum passes
      if (this.isMultipleDelivery) {
        let groupBag = _.groupBy(this.bag, function(item) {
          return item.delivery_day.day_friendly;
        });
        Object.values(groupBag).forEach(groupBagItem => {
          let groupBagItemTotal = 0;
          groupBagItem.forEach(bagItem => {
            groupBagItemTotal += bagItem.price * bagItem.quantity;
          });
          if (
            groupBagItem[0].delivery_day.minimum &&
            groupBagItemTotal < groupBagItem[0].delivery_day.minimum
          ) {
            deliveryDayMinimumPassed = false;
          }
          groupBagItemTotal = 0;
        });
      }

      // Check if category minimum passes
      if (
        this.mealMixItems.finalCategories.some(cat => {
          return cat.minimumType !== null;
        })
      ) {
        categoryMinimumPassed = this.checkCategoryMinimums();
      }

      // Check if general settings minimum passes
      if (
        (this.minOption === "meals" && this.total >= this.minMeals) ||
        (this.minOption === "price" &&
          this.totalBagPricePreFeesBothTypes >= this.minPrice) ||
        (this.store.settings.minimumDeliveryOnly && this.bagPickup == 1)
      ) {
        settingsPassed = true;
      } else {
        settingsPassed = false;
      }

      // Check if the bag contains only a gift card
      this.bag.forEach(item => {
        if (!item.meal.gift_card) {
          giftCardOnly = false;
        }
      });
      if (this.bag.length == 0) {
        giftCardOnly = false;
      }

      if (
        (settingsPassed && deliveryDayMinimumPassed && categoryMinimumPassed) ||
        giftCardOnly
      ) {
        return true;
      } else {
        return false;
      }
    },
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.day_friendly;
              if (!groupedDD[key]) {
                groupedDD[key] = {
                  items: [],
                  delivery_day: bagItem.delivery_day
                };
              }

              groupedDD[key].items.push(bagItem);
            }
          });

          if (JSON.stringify(groupedDD) != "{}") {
            for (let i in groupedDD) {
              grouped.push(groupedDD[i]);
            }
          }

          // Add all delivery days
          if (this.selectedDeliveryDay) {
            let included = false;
            grouped.forEach(group => {
              if (group.delivery_day.id === this.selectedDeliveryDay.id) {
                included = true;
              }
            });
            if (!included) {
              grouped.push({
                items: [],
                delivery_day: this.selectedDeliveryDay
              });
            }
          }
        } else {
          grouped.push({
            items: this.bag
          });
        }
      }

      return grouped;
    },
    deliveryDateOptions() {
      let deliveryDays = this.storeSetting(
        "next_orderable_delivery_dates",
        []
      ).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do"),
          moment: moment(date.date)
        };
      });
      let pickupDays = this.storeSetting("next_orderable_pickup_dates", []).map(
        date => {
          return {
            value: date.date,
            text: moment(date.date).format("dddd MMM Do"),
            moment: moment(date.date)
          };
        }
      );

      let orderableDates = !this.store.modules.pickupOnly
        ? deliveryDays.concat(pickupDays)
        : pickupDays;

      orderableDates = orderableDates
        .filter((obj, pos, arr) => {
          return (
            arr.map(mapObj => mapObj["value"]).indexOf(obj["value"]) === pos
          );
        })
        .sort((a, b) => (a.value > b.value ? 1 : -1));

      // Removing past dates
      orderableDates = orderableDates.filter(day => {
        return !moment(day.value).isBefore(moment().startOf("day"));
      });

      return orderableDates;
    },
    /**
     * Whether at least one category has a delivery date restriction,
     * for today/selected delivery date
     */
    hasDeliveryDateRestriction() {
      const today = this.bagDeliveryDate
        ? moment(this.bagDeliveryDate)
        : moment();
      const cats = this._categories;

      for (let cat of cats) {
        if (
          cat.date_range &&
          today.isBetween(cat.date_range_from, cat.date_range_to)
        ) {
          return true;
        }
      }

      return false;
    },
    /**
     * Whether at least one category has a delivery date restriction,
     * for today/selected delivery date
     * and restricts / hides other categories
     */
    hasExclusiveDeliveryDateRestriction() {
      return 0 < this.exclusiveDateRestrictedCategories.length;
    },
    /**
     * A list of categories which are exclusively restricted today
     */
    exclusiveDateRestrictedCategories() {
      const today = this.bagDeliveryDate
        ? moment(this.bagDeliveryDate)
        : moment();
      const cats = this._categories;

      return _.filter(cats, cat => {
        if (
          cat.date_range &&
          cat.date_range_exclusive &&
          today.isBetween(
            cat.date_range_exclusive_from,
            cat.date_range_exclusive_to
          )
        ) {
          return true;
        }
      });
    }
  },
  mounted() {
    this.transferDayType = this.bagPickup;
    if (this.isMultipleDelivery) {
      this.minimumDeliveryDayAmount = this.store.delivery_days[0].minimum;
    }
  },
  methods: {
    ...mapMutations(["setBagPurchasedGiftCard", "setBagReferral"]),
    getMealQuantity(meal, quantity) {
      if (!quantity) {
        quantity = 1;
      } else {
        quantity = parseInt(quantity);
      }
      let bagItem = this.groupBag[0]
        ? this.groupBag[0].items.find(item => {
            return item.meal ? item.meal.id === meal.id : null;
          })
        : null;

      let currentBagQuantity = bagItem ? bagItem.quantity + 1 : 0;

      if (!meal.force_quantity || currentBagQuantity > meal.force_quantity) {
        return quantity;
      }

      if (currentBagQuantity < meal.force_quantity) {
        return meal.force_quantity - currentBagQuantity > quantity
          ? meal.force_quantity - currentBagQuantity
          : quantity;
      }
    },
    async addOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null,
      free = false,
      item = {}
    ) {
      if (meal.gift_card) {
        this.$store.commit("addToBag", {
          meal,
          quantity: meal.quantity ? meal.quantity : 1
        });
        return;
      }
      if (!mealPackage) {
        meal = this.getMeal(meal.id, meal);
      } else {
        meal = this.getMealPackage(meal.id, meal);
      }
      if (!meal) {
        return;
      }

      let sizeId = size;

      //if (!mealPackage) {
      if (_.isObject(size) && size.id) {
        sizeId = size.id;
      } else {
        size = meal.getSize(sizeId);
      }

      if (!size) {
        size = null;
      }
      //}

      let sizeCriteria = !mealPackage
        ? { meal_size_id: sizeId }
        : { meal_package_size_id: sizeId };

      if (!meal.adjustOrder && !meal.adjustSubscription) {
        if (
          (meal.components &&
            meal.components.length &&
            _.maxBy(meal.components, "minimum") &&
            _.find(meal.components, component => {
              return _.find(component.options, sizeCriteria);
            }) &&
            !components) ||
          (meal.addons &&
            meal.addons.length &&
            _.find(meal.addons, sizeCriteria) &&
            !addons)
        ) {
          if (this.mealModal && this.hideMealModal) {
            await this.hideMealModal();
          }
          if (this.mealPackageModal && this.hideMealPackageModal) {
            await this.hideMealPackageModal();
          }

          const result = !mealPackage
            ? await this.$refs.componentModal.show(meal, mealPackage, size)
            : await this.$refs.packageComponentModal.show(meal, size);

          if (!result) {
            return;
          }

          components = { ...result.components };
          addons = { ...result.addons };
        }
      }

      if (free) {
        meal.price = 0;
      }

      let custom = {};
      if (item && item.customTitle) {
        meal.item_title = item.full_title;
        meal.title = item.customTitle;
        custom.title = item.customTitle;
      }
      if (item && item.customSize) {
        size.full_title = item.full_title;
        size.title = item.customSize;
        custom.size = item.customSize;
        size.price = item.price;
      }

      if (size && size.delivery_day) {
        meal.delivery_day = size.delivery_day;
      }

      if (meal.dday) {
        // Unable to directly set delivery_day on AdjustOrder
        meal.delivery_day = meal.dday;
      }

      this.$store.commit("addToBag", {
        meal,
        quantity: meal.quantity ? meal.quantity : 1,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        free,
        custom
      });

      if (this.mobile) {
        let quantity = meal.quantity ? meal.quantity : 1;
        this.$toastr.defaultPosition = "toast-top-left";
        this.$toastr.s(quantity + " x " + meal.title + " added to bag.");
        this.$toastr.defaultPosition = "toast-top-right";
      }
      this.mealModal = false;
      this.mealPackageModal = false;
    },
    addOneFromAdjust(order_bag) {
      this.$store.commit("addToBagFromAdjust", order_bag);
    },
    removeFromAdjust(order_bag) {
      this.$store.commit("removeFromBagFromAdjust", order_bag);
    },
    removeOneFromAdjust(order_bag) {
      this.$store.commit("removeOneFromBagFromAdjust", order_bag);
    },
    updateOneSubItemFromAdjust(item, order_bag, plus = false) {
      this.$store.commit("updateOneSubItemFromAdjust", {
        item,
        order_bag,
        plus
      });
    },
    minusOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    ) {
      this.setBagReferral(null);
      this.setBagPurchasedGiftCard(null);
      this.$store.commit("removeFromBag", {
        meal,
        quantity: 1,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      });
    },
    updateBagItem(item) {
      this.$store.commit("updateBagItem", item);
    },
    addBagItems(bag) {
      this.$store.commit("addBagItems", bag);
    },
    clearMeal(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    ) {
      let quantity = this.quantity(
        meal,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      );
      this.$store.commit("removeFromBag", {
        meal,
        quantity,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      });
    },
    clearMealFullQuantity(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    ) {
      let quantity = this.quantity(
        meal,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      );
      this.$store.commit("removeFullQuantityFromBag", {
        meal,
        quantity,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      });
    },
    clearAll() {
      this.$store.commit("emptyBag");
    },
    quantity(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    ) {
      let qty = this.$store.getters.bagItemQuantity(
        meal,
        mealPackage,
        size,
        components,
        addons,
        special_instructions
      );
      // size === true gets quantity for all sizes
      if (size === true) {
        meal.sizes.forEach(sizeObj => {
          qty += this.$store.getters.bagItemQuantity(
            meal,
            mealPackage,
            sizeObj,
            components,
            addons,
            special_instructions
          );
        });
      }
      return qty;
    },
    // Total quantity of a meal regardless of size, components etc.
    mealQuantity(meal) {
      return this.$store.getters.bagMealQuantity(meal);
    },
    itemComponents(item) {
      const mealPackage = !!item.meal_package;

      const meal = !mealPackage
        ? this.getMeal(item.meal.id, item.meal)
        : this.getMealPackage(item.meal.id, item.meal);

      let wat = _(item.components)
        .map((options, componentId) => {
          const component = meal ? meal.getComponent(componentId) : null;
          const optionIds = mealPackage ? Object.keys(options) : options;

          let optionTitles = [];

          _.forEach(optionIds, optionId => {
            const option = meal
              ? meal.getComponentOption(component, optionId)
              : null;
            if (!option) {
              return null;
            }

            if (!option.selectable) {
              optionTitles.push(option.title || null);
            } else {
              return null;

              _.forEach(options[option.id], option => {
                optionTitles.push(option.meal.title);
              });
            }
          });

          return _(optionTitles)
            .filter()
            .toArray()
            .value();
        })
        .flatten()
        .value();

      return wat;
    },
    itemAddons(item) {
      const meal = this.getMeal(item.meal.id, item.meal);
      let wat = _(item.addons)
        .map(addonId => {
          const addon = meal ? meal.getAddon(addonId) : null;
          return addon ? addon.title : null;
        })
        .filter()
        .value();

      return wat;
    },
    async updateSubscriptionMeals() {
      this.deliveryFee = this.deliveryFeeAmount;
      if (this.pickup === 0) {
        this.selectedPickupLocation = null;
      }

      // let deposit = this.deposit;
      // if (deposit.toString().includes("%")) {
      //   deposit.replace("%", "");
      //   deposit = parseInt(deposit);
      // }

      try {
        const { data } = await axios.post(
          `/api/me/subscriptions/${this.subscriptionId}/meals`,
          {
            subscriptionId: this.subscriptionId,
            subtotal: this.subtotal,
            afterDiscount: this.afterDiscount,
            bag: this.bag,
            card_id: !this.cashOrder ? this.card : 0,
            plan: this.weeklySubscription,
            pickup: this.pickup,
            shipping: this.deliveryShipping == "Shipping" ? 1 : 0,
            store_id: this.store.id,
            salesTax: this.tax,
            coupon_id: this.couponApplied ? this.coupon.id : null,
            couponReduction: this.couponReduction,
            couponCode: this.couponApplied ? this.coupon.code : null,
            deliveryFee: this.deliveryFeeAmount,
            pickupLocation: this.selectedPickupLocation,
            customer: this.customer,
            // deposit: deposit,
            cashOrder: this.cashOrder,
            transferTime: this.transferTime,
            grandTotal: this.grandTotal,
            processingFee: this.processingFeeAmount,
            mealPlanDiscount: this.mealPlanDiscount,
            referralReduction: this.referralReduction,
            purchased_gift_card_id: this.purchasedGiftCardApplied
              ? this.purchasedGiftCard.id
              : null,
            purchasedGiftCardReduction: this.purchasedGiftCardReduction,
            promotionReduction: this.promotionReduction,
            pointsReduction: this.promotionPointsReduction,
            gratuity: this.tip,
            coolerDeposit: this.coolerDeposit,
            publicNotes: this.publicOrderNotes
          }
        );
        await this.refreshSubscriptions();
        this.emptyBag();
        this.setBagMealPlan(false);
        this.setBagCoupon(null);

        if (this.$route.params.storeView) {
          this.$store.commit("refreshStoreSubscriptions");
          this.$router.push({
            path: "/store/subscriptions",
            query: {
              updated: true
            }
          });
        } else {
          this.$router.push({
            path: "/customer/subscriptions",
            query: {
              updated: true
            }
          });
        }
      } catch (e) {
        if (!_.isEmpty(e.response.data.error)) {
          this.$toastr.w(e.response.data.error);
        }
        if (!_.isEmpty(e.response.data.message)) {
          this.$toastr.w(e.response.data.message);
        } else {
          this.$toastr.w(
            "Please try again or contact our support team",
            "Failed to update items."
          );
        }
        return;
      }
    },

    isCategoryVisible(category) {
      if (this.$route.params.storeView) {
        return true;
      }
      const id = _.isNumber(category)
        ? category
        : category.category_id || category.id;

      const today = this.bagDeliveryDate
        ? moment(this.bagDeliveryDate)
        : moment();

      // If there is at least one "exclusive" category for the delivery date
      // hide the others
      if (this.hasExclusiveDeliveryDateRestriction) {
        const exclusiveCats = this.exclusiveDateRestrictedCategories;
        return _.find(exclusiveCats, { id });
      }

      // Hide if this category is date-restricted
      // but delivery date doesn't fall within its date range
      if (
        category &&
        category.date_range &&
        !today.isBetween(category.date_range_from, category.date_range_to)
      ) {
        return false;
      }

      return true;
    },

    showCheckoutErrorToasts() {
      if (this.hasMultipleSubscriptionItems) {
        this.$toastr.w(
          "You have multiple subscription types in your bag (e.g weekly & monthly). Please checkout one subscription type at a time."
        );
        return true;
      }

      if (
        this.staffMember == null &&
        this.store.modules.showStaff &&
        this.$route.params.manualOrder
      ) {
        this.$toastr.w("Please select a staff member.");
        return true;
      }

      if (
        this.pickup === 1 &&
        this.store.modules.pickupLocations &&
        this.pickupLocationOptions.length > 0 &&
        !this.selectedPickupLocation
      ) {
        this.$toastr.w("Please select a pickup location from the dropdown.");
        return true;
      }
      if (!this.isMultipleDelivery && !this.store.modules.hideTransferOptions) {
        if (!this.bagDeliveryDate) {
          if (this.pickup === 1) {
            this.$toastr.w("Please select a pickup date from the dropdown.");
          }
          if (this.pickup === 0) {
            this.$toastr.w("Please select a delivery date from the dropdown.");
          }
          return true;
        }
      }

      if (
        this.store.modules.pickupHours &&
        this.pickup === 1 &&
        this.transferTime === null
      ) {
        this.$toastr.w("Please select a pickup time from the dropdown.");
        return true;
      }

      if (
        this.store.modules.deliveryHours &&
        this.pickup === 0 &&
        this.transferTime === null
      ) {
        this.$toastr.w("Please select a delivery time from the dropdown.");
        return true;
      }
    },

    async redirectAfterCheckoutOrAdjust(resp) {
      if (this.isManualOrder) {
        this.refreshStorePurchasedGiftCards();
        this.refreshResource("orders");
        if (this.weeklySubscription) {
          this.refreshStoreSubscriptions();
          this.$router.push({
            path: "/store/subscriptions"
          });
        } else {
          this.$router.push({
            name: "store-orders",
            params: {
              autoPrintPackingSlip: this.storeModules.autoPrintPackingSlip,
              orderId: resp.data
            }
          });
        }
        return;
      } else {
        if (this.weeklySubscription) {
          await this.refreshSubscriptions();
          let query = {
            created: true,
            pickup: this.bagPickup === 1 ? true : false
          };
          if (this.doubleCheckout) {
            query.order = true;
          }
          this.$router.push({
            path: "/customer/subscriptions",
            query: query
          });
        } else {
          this.$router.push({
            path: "/customer/orders",
            query: {
              created: true,
              pickup: this.bagPickup === 1 ? true : false
            }
          });
        }
      }
    },
    checkCategoryMinimums() {
      let passed = true;
      this.mealMixItems.finalCategories.forEach(category => {
        if (category.minimumType === "price") {
          let bagCategoryPrice = 0;
          if (
            !category.minimumIfAdded ||
            (category.minimumIfAdded &&
              this.bag.some(item => {
                return item.meal.category_id === category.id;
              }))
          ) {
            this.bag.forEach(item => {
              if (item.meal.category_id === category.id) {
                bagCategoryPrice += item.price * item.quantity;
              }
            });
            if (bagCategoryPrice < category.minimum) {
              passed = false;
            }
          }
        }
        if (category.minimumType === "items") {
          let bagCategoryQuantity = 0;
          if (
            !category.minimumIfAdded ||
            (category.minimumIfAdded &&
              this.bag.some(item => {
                return item.meal.category_id === category.id;
              }))
          ) {
            this.bag.forEach(item => {
              if (item.meal.category_id === category.id) {
                bagCategoryQuantity += item.quantity;
              }
            });
            if (bagCategoryQuantity < category.minimum) {
              passed = false;
            }
          }
        }
      });
      return passed;
    },
    getCategoryMinimum(group) {
      if (!group.minimumType) {
        return;
      }
      if (group.minimumType === "price") {
        return (
          group.minimum -
          this.bag.reduce((acc, item) => {
            return item.meal.category_id === group.category_id
              ? (acc = acc + item.price * item.quantity)
              : acc;
          }, 0)
        );
      } else {
        return (
          group.minimum -
          this.bag.reduce((acc, item) => {
            return item.meal.category_id === group.category_id
              ? (acc = acc + item.quantity)
              : acc;
          }, 0)
        );
      }
    },
    categoryMinimumMessage() {
      let message = "";
      let cont = true;
      this.mealMixItems.finalCategories.forEach(category => {
        if (category.minimumType === "price" && cont) {
          if (
            !category.minimumIfAdded ||
            (category.minimumIfAdded &&
              this.bag.some(item => {
                return item.meal.category_id === category.id;
              }))
          ) {
            let bagCategoryPrice = 0;
            this.bag.forEach(item => {
              if (item.meal.category_id === category.id) {
                bagCategoryPrice += item.price * item.quantity;
              }
            });
            if (bagCategoryPrice < category.minimum) {
              message =
                "Please add " +
                format.money(
                  category.minimum - bagCategoryPrice,
                  this.storeSettings.currency
                ) +
                " more to the " +
                category.category +
                " category";
              cont = false;
            }
          }
        }
        if (category.minimumType === "items" && cont) {
          if (
            !category.minimumIfAdded ||
            (category.minimumIfAdded &&
              this.bag.some(item => {
                return item.meal.category_id === category.id;
              }))
          ) {
            let bagCategoryQuantity = 0;
            this.bag.forEach(item => {
              if (item.meal.category_id === category.id) {
                bagCategoryQuantity += item.quantity;
              }
            });
            if (bagCategoryQuantity < category.minimum) {
              message =
                "Please add " +
                (category.minimum - bagCategoryQuantity) +
                " more items to the " +
                category.category +
                " category";
              cont = false;
            }
          }
        }
      });
      return message;
    },
    checkMinimum() {
      if (!this.minimumMet && !this.storeView) {
        this.$toastr.w(this.addMore);
      }
    },
    getBrandColor(delivery_day) {
      if (this.selectedDeliveryDay) {
        if (
          this.selectedDeliveryDay.day_friendly == delivery_day.day_friendly
        ) {
          if (this.store.settings) {
            let style = "background-color:";
            style += this.store.settings.color;
            return style;
          }
        }
      }
    }
  }
};
