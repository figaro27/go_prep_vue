<template>
  <div>
    <div
      v-if="!willDeliver && loggedIn && !storeView"
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
    })
  }
};
</script>
