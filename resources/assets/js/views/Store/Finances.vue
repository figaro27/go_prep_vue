<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-tabs v-model="tabs">
        <b-tab title="Payments">
          <payments :tabs="tabs"></payments>
        </b-tab>
        <b-tab title="Payouts">
          <payouts :tabs="tabs"></payouts>
        </b-tab>
        <b-tab title="Refunds">
          <refunds :tabs="tabs"></refunds>
        </b-tab>
        <b-tab title="Card Declines">
          <declines :tabs="tabs"></declines>
        </b-tab>
        <!-- <b-tab title="Errors">
          
        </b-tab> -->
      </b-tabs>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";
import Payments from "../../components/Finances/Payments";
import Payouts from "../../components/Finances/Payouts";
import Declines from "../../components/Finances/Declines";
import Refunds from "../../components/Finances/Refunds";

export default {
  components: {
    Spinner,
    vSelect,
    Payments,
    Payouts,
    Declines,
    Refunds
  },
  mixins: [checkDateRange],
  data() {
    return {
      tabs: null
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money
  }
};
</script>
