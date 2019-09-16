<template>
  <div>
    <div v-if="!willDeliver && loggedIn">
      <h3 class="center-text mb-4">You are outside of the delivery area.</h3>
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
