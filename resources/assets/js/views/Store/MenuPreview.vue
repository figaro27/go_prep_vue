<template>
  <customer-menu :preview="true" :storeView="true"></customer-menu>
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
    ...mapActions({})
  }
};
</script>
