<template>
  <div>
    <div v-if="store.settings.open === false" :class="storeClosedClass">
      <div class="row mt-3">
        <div class="col-sm-12 mt-3">
          <b-alert show variant="danger">
            <h4 class="center-text">
              We are currently not accepting orders.
            </h4>
            <p class="center-text mt-3">
              {{ store.settings.closedReason }}
            </p>
          </b-alert>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  computed: {
    ...mapGetters({
      store: "viewedStore"
    }),
    storeClosedClass() {
      if (this.$route.name === "customer-menu") {
        return "main-customer-container customer-menu-container left-right-box-shadow";
      } else return "";
    }
  }
};
</script>
