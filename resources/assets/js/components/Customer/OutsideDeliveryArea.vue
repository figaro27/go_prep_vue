<template>
  <div>
    <div
      v-if="!willDeliver && loggedIn"
      class="main-customer-container customer-menu-container left-right-box-shadow"
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
  computed: {
    ...mapGetters({
      willDeliver: "viewedStoreWillDeliver",
      loggedIn: "loggedIn"
    })
  }
};
</script>
