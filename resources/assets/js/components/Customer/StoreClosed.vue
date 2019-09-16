<template>
  <div>
    <div v-if="store.settings.open === false">
      <div class="row">
        <div class="col-sm-12 mt-3">
          <h3 class="center-text">
            We are currently not accepting orders.
          </h3>
          <p class="center-text mt-3">
            {{ store.settings.closedReason }}
          </p>
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
    })
  }
};
</script>
