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
      store: "store",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories"
    })
  },
  created() {},
  mounted() {
    this.start();
  },
  methods: {
    ...mapActions(["init"]),
    async start() {
      axios
        .post(`/api/me/stripe/connect`, { code: this.$route.query.code })
        .finally(() => {
          this.$router.replace("/store/account/settings");
        });
    }
  }
};
</script>