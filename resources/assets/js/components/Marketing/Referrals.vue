<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-btn
        variant="success"
        size="md"
        @click="showReferralRulesModal = true"
        class="mb-3"
        >Referral Rules</b-btn
      >
      <v-client-table
        :columns="columns"
        :data="tableData"
        :options="{
          headings: {
            fullName: 'Name',
            totalCustomers: 'Referred Customers',
            totalOrders: 'Referred Orders',
            totalRevenue: 'Total Revenue Generated',
            referralURL: 'Referral URL',
            redeemCode: 'Redeem Code'
          }
        }"
      >
      </v-client-table>
    </div>
    <b-modal
      size="lg"
      title="Referral Rules"
      v-model="showReferralRulesModal"
      v-if="showReferralRulesModal"
      @ok.prevent="onViewMealModalOk"
      no-fade
    >
      <p>Referral Rules</p>
    </b-modal>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      showReferralRulesModal: false,
      columns: [
        "fullName",
        "email",
        "totalCustomers",
        "totalOrders",
        "totalRevenue",
        "referralURL",
        "redeemCode",
        "balance",
        "actions"
      ]
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
    }),
    tableData() {
      return [];
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money
  }
};
</script>
