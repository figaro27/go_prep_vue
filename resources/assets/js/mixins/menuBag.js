import SalesTax from "sales-tax";
import store from "../store";
import { mapGetters } from "vuex";

export default {
  computed: {
    ...mapGetters({
      storeSetting: "viewedStoreSetting",
      bagDeliveryDate: "bagDeliveryDate"
    }),
    deliveryDateOptions() {
      return this.storeSetting("next_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do"),
          moment: moment(date.date)
        };
      });
    },
    hasDeliveryDateRestrictionToday() {
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
    hasExclusiveDeliveryDateRestrictionToday() {
      return this.exclusiveDateRestrictedCategory !== null;
    },
    exclusiveDateRestrictedCategory() {
      const today = this.bagDeliveryDate
        ? moment(this.bagDeliveryDate)
        : moment();
      const cats = this._categories;
      console.log("cats:" + cats);

      for (let cat of cats) {
        if (
          cat.date_range &&
          cat.date_range_exclusive &&
          today.isBetween(cat.date_range_from, cat.date_range_to)
        ) {
          console.log("cat:" + cat);
          return cat;
        }
      }

      return null;
    }
  },
  methods: {
    async addOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null,
      free = false
    ) {
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

      if (
        (meal.components.length &&
          _.maxBy(meal.components, "minimum") &&
          _.find(meal.components, component => {
            return _.find(component.options, sizeCriteria);
          }) &&
          !components) ||
        (meal.addons.length && _.find(meal.addons, sizeCriteria) && !addons)
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

      if (free) {
        meal.price = 0;
      }

      this.$store.commit("addToBag", {
        meal,
        quantity: 1,
        mealPackage,
        size,
        components,
        addons,
        special_instructions,
        free
      });
      this.mealModal = false;
      this.mealPackageModal = false;
    },
    minusOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null,
      special_instructions = null
    ) {
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
    makeItemFree(bag) {
      this.$store.commit("makeItemFree", bag);
    },
    makeItemNonFree(bag) {
      this.$store.commit("makeItemNonFree", bag);
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

      let deposit = this.deposit;
      if (deposit.toString().includes("%")) {
        deposit.replace("%", "");
        deposit = parseInt(deposit);
      }

      try {
        const { data } = await axios.post(
          `/api/me/subscriptions/${this.subscriptionId}/meals`,
          {
            subscriptionId: this.subscriptionId,
            subtotal: this.subtotal,
            afterDiscount: this.afterDiscount,
            bag: this.bag,
            plan: this.weeklySubscription,
            pickup: this.pickup,
            store_id: this.store.id,
            salesTax: this.tax,
            coupon_id: this.couponApplied ? this.coupon.id : null,
            couponReduction: this.couponReduction,
            couponCode: this.couponApplied ? this.coupon.code : null,
            deliveryFee: this.deliveryFee,
            pickupLocation: this.selectedPickupLocation,
            customer: this.customer,
            deposit: deposit,
            cashOrder: this.cashOrder,
            transferTime: this.transferTime
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
          this.$toastr.e(e.response.data.error);
        } else {
          this.$toastr.e(
            "Please try again or contact our support team",
            "Failed to update items."
          );
        }
        return;
      }
    },

    isCategoryVisible(category) {
      const id = _.isNumber(category)
        ? category
        : category.category_id || category.id;
      const today = moment();

      if (this.hasExclusiveDeliveryDateRestrictionToday) {
        const categories = { ...this._categories };
        const exclusiveCat = this.exclusiveDateRestrictedCategory;
        return id === exclusiveCat.id;
      }

      if (
        category &&
        category.date_range &&
        !today.isBetween(category.date_range_from, category.date_range_to)
      ) {
        return false;
      }

      return true;
    }
  }
};
