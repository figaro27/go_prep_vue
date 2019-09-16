import SalesTax from "sales-tax";

export default {
  computed: {},
  methods: {
    async addOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null
    ) {
      if (!mealPackage) {
        meal = this.getMeal(meal.id);
      } else {
        meal = this.getMealPackage(meal.id);
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

      this.$store.commit("addToBag", {
        meal,
        quantity: 1,
        mealPackage,
        size,
        components,
        addons
      });
      this.mealModal = false;
      this.mealPackageModal = false;
    },
    minusOne(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null
    ) {
      this.$store.commit("removeFromBag", {
        meal,
        quantity: 1,
        mealPackage,
        size,
        components,
        addons
      });
    },
    addBagItems(bag) {
      this.$store.commit("addBagItems", bag);
    },
    clearMeal(
      meal,
      mealPackage = false,
      size = null,
      components = null,
      addons = null
    ) {
      let quantity = this.quantity(meal, mealPackage, size, components, addons);
      this.$store.commit("removeFromBag", {
        meal,
        quantity,
        mealPackage,
        size,
        components,
        addons
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
      addons = null
    ) {
      let qty = this.$store.getters.bagItemQuantity(
        meal,
        mealPackage,
        size,
        components,
        addons
      );

      // size === true gets quantity for all sizes
      if (size === true) {
        meal.sizes.forEach(sizeObj => {
          qty += this.$store.getters.bagItemQuantity(
            meal,
            mealPackage,
            sizeObj,
            components,
            addons
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
      const meal = !item.meal_package
        ? this.getMeal(item.meal.id)
        : this.getMealPackage(item.meal.id);
      let wat = _(item.components)
        .map((options, componentId) => {
          const component = meal.getComponent(componentId);

          const optionTitles = _(options)
            .map(optionId => {
              const option = meal.getComponentOption(component, optionId);
              return option ? option.title : null;
            })
            .filter()
            .toArray()
            .value();

          return optionTitles;
        })
        .flatten()
        .value();

      return wat;
    },
    itemAddons(item) {
      const meal = this.getMeal(item.meal.id);
      let wat = _(item.addons)
        .map(addonId => {
          const addon = meal.getAddon(addonId);
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

      if (this.$route.params.adjustMealPlan) {
        axios
          .post("/api/me/subscriptions/updateMeals", {
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
          })
          .then(resp => {
            this.refreshStoreSubscriptions();
            this.emptyBag();
            this.setBagMealPlan(false);
            this.setBagCoupon(null);
            this.$router.push({
              path: "/store/subscriptions",
              query: {
                updated: true
              }
            });
          });
      } else {
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

          this.$router.push({
            path: "/customer/subscriptions",
            query: {
              updated: true
            }
          });
        } catch (e) {
          if (!_.isEmpty(e.response.data.error)) {
            this.$toastr.e(e.response.data.error);
          } else {
            this.$toastr.e(
              "Please try again or contact our support team",
              "Failed to update meals!"
            );
          }
          return;
        }
      }
    }
  }
};
