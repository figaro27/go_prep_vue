<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-tabs>
        <b-tab title="Leads">
          <leads></leads>
        </b-tab>
        <b-tab title="Promotions">
          <promotions></promotions>
        </b-tab>
        <b-tab title="Referrals">
          <referrals></referrals>
        </b-tab>
        <b-tab title="Coupons">
          <coupons></coupons>
        </b-tab>
        <b-tab title="Purchased Gift Cards">
          <gift-cards></gift-cards>
        </b-tab>
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
import Leads from "../../components/Marketing/Leads";
import Promotions from "../../components/Marketing/Promotions";
import Referrals from "../../components/Marketing/Referrals";
import Coupons from "../../components/Marketing/Coupons";
import GiftCards from "../../components/Marketing/GiftCards";

export default {
  components: {
    Spinner,
    vSelect,
    Leads,
    Referrals,
    Promotions,
    Coupons,
    GiftCards
  },
  mixins: [checkDateRange],
  data() {
    return {};
  },
  created() {},
  mounted() {
    this.refreshStorePurchasedGiftCards();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized"
    })
  },
  methods: {
    ...mapActions({
      refreshStorePurchasedGiftCards: "refreshStorePurchasedGiftCards"
    }),
    formatMoney: format.money
  }
};
</script>
