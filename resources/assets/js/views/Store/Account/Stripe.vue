<template>
  <div class="row"></div>
</template>

<style lang="scss" scoped>
</style>


<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {},
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      user: "user",
      initialized: "initialized",
    })
  },
  created() {},
  mounted() {
    this.start();
  },
  methods: {
    ...mapActions(["init", "refreshStoreSettings"]),
    start() {
      const init = this.initialized;
      axios
        .post(`/api/me/stripe/connect`, { code: this.$route.query.code })
        .finally(async () => {
          await this.refreshStoreSettings();
          this.$router.replace("/store/account/settings");
        });
    }
  }
};
</script>