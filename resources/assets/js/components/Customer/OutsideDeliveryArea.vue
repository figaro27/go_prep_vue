<template>
  <div>
    <div v-if="!willDeliver && loggedIn">
      <b-alert variant="danger center-text" show
        >You are outside of the delivery area.</b-alert
      >
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
