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
      const min = _.maxBy(meal.components, "minimum");

      let sizeId = size;
      if (_.isObject(size) && size.id) {
        sizeId = size.id;
      }

      if (
        (meal.components.length &&
          _.maxBy(meal.components, "minimum") &&
          _.find(meal.components, component => {
            return _.find(component.options, { meal_size_id: sizeId });
          }) &&
          !components) ||
        (meal.addons.length &&
          _.find(meal.addons, { meal_size_id: sizeId }) &&
          !addons)
      ) {
        const result = await this.$refs.componentModal.show(
          meal,
          mealPackage,
          size
        );
        components = { ...result.components };
        addons = [...result.addons];
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
      const meal = this.getMeal(item.meal.id);
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
    getSalesTax(state) {
      SalesTax.getSalesTax("US", state).then(tax => {
        this.setSalesTax(tax.rate);
      });
    },
    setSalesTax(rate) {
      this.salesTax = rate;
    }
  }
};
