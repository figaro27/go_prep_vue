<template>
  <div>
    <b-button
      size="lg"
      class="brand-color white-text"
      to="/customer/menu"
      v-if="!manualOrder"
    >
      <span class="d-sm-inline">Change Meals</span>
    </b-button>
    <b-button
      size="lg"
      class="brand-color white-text"
      to="/store/manual-order"
      v-if="manualOrder"
    >
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
  props: {
    manualOrder: false
  },
  mixins: [MenuBag]
};
</script>
