import SalesTax from "sales-tax";
import store from "../store";
import { mapGetters, mapMutations } from "vuex";

export default {
  computed: {
    ...mapGetters({
      storeSetting: "viewedStoreSetting",
      bagDeliveryDate: "bagDeliveryDate",
      store: "viewedStore"
    }),
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
  methods: {
    ...mapMutations(["setBagPurchasedGiftCard", "setBagReferral"]),
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

      if (!meal.adjustOrder) {
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
            coolerDeposit: this.coolerDeposit
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
          this.$toastr.e(
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
    }
  }
};
