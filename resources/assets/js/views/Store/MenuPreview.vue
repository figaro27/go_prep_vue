<template>
  <customer-menu :preview="true" :storeView="true"></customer-menu>
</template>

<style>
.hidden {
  right: 0vw;
}

.categories {
  margin-right: 100px !important;
}
</style>

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
    return {};
  },
  computed: {
    ...mapGetters({
      isLoading: "isLoading"
    })
  },
  created() {
    this.refreshViewedStore();
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshViewedStore: "refreshViewedStore"
    })
  }
};
</script>
