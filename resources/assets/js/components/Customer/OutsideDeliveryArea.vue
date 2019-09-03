<template>
  <div>
    <h1>TEST</h1>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
export default {
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    })
  },
  methods: {
    ...mapActions([
      "refreshSubscriptions",
      "emptyBag",
      "refreshUpcomingOrders"
    ]),
    ...mapMutations(["emptyBag", "setBagMealPlan", "setBagCoupon"])
  }
};
</script>
