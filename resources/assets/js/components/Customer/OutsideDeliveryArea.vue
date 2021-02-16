<template>
  <div>
    <div
      v-if="!willDeliver && loggedIn && !storeView && hasDeliveryOption"
      v-bind:class="
        store.settings.menuStyle === 'image'
          ? 'main-customer-container customer-menu-container'
          : 'main-customer-container customer-menu-container gray-background'
      "
    >
      <div class="row">
        <div class="col-sm-12">
          <b-alert show variant="warning">
            <h4 class="center-text">You are outside of the delivery area.</h4>
          </b-alert>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  props: {
    storeView: false
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      willDeliver: "viewedStoreWillDeliver",
      loggedIn: "loggedIn"
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
    }
    // hasPickupOption() {
    //   return (
    //     this.store.settings.transferType.includes("pickup") ||
    //     ((this.store.modules.customDeliveryDays ||
    //       this.store.modules.multipleDeliveryDays) &&
    //       this.store.delivery_days.some(day => {
    //         return day.type == "pickup";
    //       }))
    //   );
    // }
  }
};
</script>
