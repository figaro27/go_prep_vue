<template>
  <div :class="mealPackagePageClass" v-if="showPage" style="min-height: 100%;">
    Test
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  data() {
    return {};
  },
  components: {},
  props: {
    mealPackage: {},
    mealPackageSize: null,
    storeSettings: {}
  },
  mixins: [MenuBag],
  computed: {
    showPage() {
      return this.$parent.mealPackagePageView;
    },
    mealPackagePageClass() {
      return this.storeSettings.menuStyle === "image"
        ? "left-right-box-shadow main-customer-container"
        : "left-right-box-shadow main-customer-container gray-background";
    }
  }
};
</script>
