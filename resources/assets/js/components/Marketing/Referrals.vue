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
            'user.name': 'Name',
            'user.email': 'Email',
            referredCustomers: 'Referred Customers',
            ordersReferred: 'Referred Orders',
            amountReferred: 'Total Revenue',
            referralUrlCode: 'Referral URL',
            code: 'Redeem Code'
          }
        }"
      >
        <div slot="amountReferred" slot-scope="props">
          {{ formatMoney(props.row.amountReferred, store.settings.currency) }}
        </div>
        <div slot="balance" slot-scope="props">
          {{ formatMoney(props.row.balance, store.settings.currency) }}
        </div>
        <div slot="referralUrlCode" slot-scope="props">
          <a :href="getFullReferralUrl(props.row.user.referralUrlCode)"
            >{{ storeReferralUrl }}{{ props.row.user.referralUrlCode }}</a
          >
        </div>
        <div slot="actions" slot-scope="props" class="d-flex">
          <img
            v-b-popover.hover="
              'You may wish to pay your affiliates their balance outside of the application instead of giving them store credit to order from you. Click this button to settle their balance to 0.'
            "
            title="Settle Balance"
            src="/images/store/popover.png"
            class="popover-size d-inline mr-1"
          />
          <b-btn
            variant="success"
            @click="settleBalance(props.row.id)"
            class="d-inline"
          >
            Settle Balance</b-btn
          >
        </div>
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
        "user.name",
        "user.email",
        "referredCustomers",
        "ordersReferred",
        "amountReferred",
        "referralUrlCode",
        "code",
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
      referrals: "storeReferrals",
      referralRules: "storeReferralRules"
    }),
    tableData() {
      return this.referrals;
    },
    storeReferralUrl() {
      let host = this.store.details.host ? this.store.details.host : "goprep";
      return "http://" + this.store.details.domain + "." + host + ".com/?r=";
    }
  },
  methods: {
    ...mapActions(["refreshStoreReferrals", "refreshStoreReferralRules"]),
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
    },
    getFullReferralUrl(referralUrlCode) {
      return this.storeReferralUrl + referralUrlCode;
    },
    settleBalance(id) {
      axios.post("/api/me/settleReferralBalance", { id: id }).then(resp => {
        this.$toastr.s("Balance Settled");
        this.refreshStoreReferrals();
      });
    }
  }
};
</script>
