<template>
  <div>
    <!-- <Spinner v-if="loading" /> -->
    <customer-menu
      :adjustMealPlan="true"
      :subscription="subscription"
    ></customer-menu>
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
    subscription() {
      return this.$route.params.subscription;
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
