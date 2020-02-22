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
      size="md"
      title="Referral Rules"
      v-model="showReferralRulesModal"
      v-if="showReferralRulesModal"
      @ok.prevent="onViewMealModalOk"
      no-fade
    >
      <div class="container-md mt-3">
        <b-form @submit.prevent="updateReferralRules">
          <b-form-group>
            <b-form-checkbox v-model="referralRules.signupEmail"
              ><p>
                Send Signup Email
                <img
                  v-b-popover.hover="
                    'When a new user registers an account, automatically send them a welcome email with their referral link.'
                  "
                  title="Send Signup Email"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                /></p
            ></b-form-checkbox>
            <b-form-checkbox v-model="referralRules.showInNotifications"
              ><p>
                Show in Notifications
                <img
                  v-b-popover.hover="
                    'Include the customer\'s referral link at the bottom of New Order and New Subscription emails they receive when they order from you.'
                  "
                  title="Show in Notifications"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                /></p
            ></b-form-checkbox>
            <b-form-checkbox v-model="referralRules.showInMenu"
              ><p>
                Show on Menu
                <img
                  v-b-popover.hover="
                    'Show your customer\'s referral link on your menu when your customer is ordering from you.'
                  "
                  title="Show on Menu"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                /></p
            ></b-form-checkbox>
            <p>
              Type
              <img
                v-b-popover.hover="
                  'Choose flat for the referring customer to receive a flat amount per order no matter the amount of the order. Choose percent for the referring customer to receive a percentage of the order amount.'
                "
                title="Type"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </p>
            <b-form-radio-group
              v-model="referralRules.type"
              :options="[
                { text: 'Flat', value: 'flat' },
                { text: 'Percent', value: 'percent' }
              ]"
            >
            </b-form-radio-group>
            <b-form-input
              placeholder="Amount"
              v-model="referralRules.amount"
              class="mt-1"
            ></b-form-input>
          </b-form-group>
          <b-button type="submit" variant="primary" class="mt-3">Save</b-button>
        </b-form>
      </div>
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
      initialized: "initialized",
      referralRules: "storeReferralRules"
    }),
    tableData() {
      return [];
    }
  },
  methods: {
    ...mapActions(["refreshStoreReferralRules"]),
    formatMoney: format.money,
    updateReferralRules() {
      let referralRules = { ...this.referralRules };

      axios
        .patch("/api/me/referralRules", referralRules)
        .then(response => {
          this.refreshStoreReferralRules();
          this.$toastr.s("Your referral rules have been saved.", "Success");
          this.showReferralRulesModal = false;
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    }
  }
};
</script>
