<template>
  <div>
    <!-- <Spinner v-if="loading" /> -->
    <customer-menu :adjustOrder="true" :order="order"></customer-menu>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import { mapGetters, mapActions, mapMutations } from "vuex";
import CustomerMenu from "../Customer/Menu";

export default {
  components: {
    Spinner,
    CustomerMenu
  },
  data() {
    return {
      loading: true
    };
  },
  computed: {
    ...mapGetters({
      isLoading: "isLoading"
    }),
    order() {
      return this.$route.params.order;
    }
  },
  created() {
    this.refreshViewedStore();
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshViewedStore: "refreshViewedStore"
    }),
    setLoadingToFalse() {
      this.loading = false;
    }
  }
};
</script>
