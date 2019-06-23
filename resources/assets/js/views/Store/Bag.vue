<template>
  <customer-bag :manualOrder="true"></customer-bag>
</template>

<script>
import Spinner from "../../components/Spinner";
import { mapGetters, mapActions, mapMutations } from "vuex";
import CustomerBag from "../Customer/Bag";

export default {
  components: {
    Spinner,
    CustomerBag
  },
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      isLoading: "isLoading"
    })
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions({
      refreshViewedStore: "refreshViewedStore"
    })
  }
};
</script>
