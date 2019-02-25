<template>
  <customer-menu :subscription-id="$route.params.id"></customer-menu>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import format from "../../lib/format.js";
import Spinner from "../../components/Spinner";
import CustomerMenu from './Menu';

export default {
  components: {
    Spinner,
    CustomerMenu
  },
  data() {
    return {
      isLoading: false
    };
  },
  computed: {
    ...mapGetters(["subscriptions", "store", "bag"]),
    subscriptionId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    this.initBag();
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    async initBag() {
      await this.refreshSubscriptions();
      const subscription = _.find(this.subscriptions, {id: this.subscriptionId});
      console.log(subscription);
    }
  }
};
</script>