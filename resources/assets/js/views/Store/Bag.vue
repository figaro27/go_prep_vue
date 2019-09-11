<template>
  <customer-bag :preview="preview"></customer-bag>
</template>

<script>
import Spinner from "../../components/Spinner";
import { mapGetters, mapActions, mapMutations } from "vuex";
import CustomerBag from "../Customer/Bag";

export default {
  props: {
    preview: false
  },
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
  created() {
    this.setBagMealPlan(false);
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshViewedStore: "refreshViewedStore"
    }),
    ...mapMutations({
      setBagMealPlan: "setBagMealPlan"
    })
  }
};
</script>
