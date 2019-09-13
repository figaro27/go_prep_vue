<template>
  <div>
    <b-button size="lg" class="brand-color white-text" :to="changeMealsRouter">
      <span class="d-sm-inline">Change Meals</span>
    </b-button>
    <b-button size="lg" class="gray white-text" @click="clearAll">
      <span class="d-sm-inline">Empty Bag</span>
    </b-button>
  </div>
</template>
<script>
import MenuBag from "../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  computed: {
    changeMealsRouter() {
      if (!this.$route.params.storeView) return "/customer/menu";
      else if (this.$route.params.preview) return "/store/menu/preview";
      else if (this.$route.params.manualOrder) return "/store/manual-order";
    }
  }
};
</script>
