<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-btn
        variant="success"
        size="md"
        @click="showReferralSettings"
        class="mb-3"
        >Referral Settings</b-btn
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
        <span slot="beforeLimit">
          <b-btn
            variant="primary"
            @click="exportData('referrals', 'pdf', true)"
          >
            <i class="fa fa-print"></i>&nbsp; Print
          </b-btn>
          <b-dropdown class="mx-1" right text="Export as">
            <b-dropdown-item @click="exportData('referrals', 'csv')"
              >CSV</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('referrals', 'xls')"
              >XLS</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('referrals', 'pdf')"
              >PDF</b-dropdown-item
            >
          </b-dropdown>
        </span>

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
      size="lg"
      title="Referral Settings"
      v-model="showReferralSettingsModal"
      v-if="showReferralSettingsModal"
      @ok.prevent="updateReferralSettings"
      no-fade
    >
      <div class="container-md mt-3">
        <b-form @submit.prevent="updateReferralSettings">
          <b-form-group>
            <h5 class="strong mt-2 mb-2">Enable</h5>
            <b-form-checkbox v-model="referralSettings.enabled"
              ><p>
                Enable Referrals
                <img
                  v-b-popover.hover="
                    'Enable or disable a referral program for your store. This will give your customers the ability to refer other customers to you and get rewarded for doing so based on the settings and amounts you choose.'
                  "
                  title="Enable Referrals"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                /></p
            ></b-form-checkbox>
            <div v-if="referralSettings.enabled">
              <!-- <b-form-checkbox v-model="referralSettings.signupEmail"
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
              ></b-form-checkbox> -->
              <h5 class="strong mt-3 mb-2">Display</h5>
              <b-form-checkbox v-model="referralSettings.showInNotifications"
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
              <b-form-checkbox v-model="referralSettings.showInMenu"
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

              <h5 class="strong mt-3 mb-2">
                Frequency
                <img
                  v-b-popover.hover="
                    'Choose the frequency and conditions in which your affiliate gets rewarded. \'First Order Only\' applies the reward only on the first order of a new customer they referred. \'All Orders With Link\' applies the reward on all orders in which the referral link is used. \'All Orders\' applies the reward on all orders that were created by a referred customer regardless if they use the referral link or not.'
                  "
                  title="Type"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                />
              </h5>
              <b-form-radio-group
                v-model="referralSettings.frequency"
                :options="[
                  { text: 'All Orders With Link', value: 'urlOnly' },
                  { text: 'First Order Only', value: 'firstOrder' },
                  { text: 'All Orders', value: 'allOrders' }
                ]"
              >
              </b-form-radio-group>

              <h5 class="strong mt-4 mb-2">
                Amount
                <img
                  v-b-popover.hover="
                    'Choose flat for the referring customer to receive a flat amount per order no matter the amount of the order. Choose percent for the referring customer to receive a percentage of the order amount.'
                  "
                  title="Type"
                  src="/images/store/popover.png"
                  class="popover-size ml-1"
                />
              </h5>
              <b-form-radio-group
                v-model="referralSettings.type"
                :options="[
                  { text: 'Percent', value: 'percent' },
                  { text: 'Flat', value: 'flat' }
                ]"
              >
              </b-form-radio-group>
              <b-form-input
                placeholder="Amount"
                v-model="referralSettings.amount"
                class="mt-1"
                type="number"
              ></b-form-input>

              <div v-if="storeCoupons.length > 0" class="mt-4">
                <p class="strong">
                  Link Coupons To Referrals
                  <img
                    v-b-popover.hover="
                      'You can attach one of your existing coupons to a referral user using the table below. If you create a unique coupon code for a certain affiliate individual or company, and a customer uses that coupon code, the referral bonus will be applied to that user. This is another way to get referral sales other than your affiliates giving out their URL. Instead, they can give our their unique coupon code given to them by you in order to give customers an incentive to order.'
                    "
                    title="Link Coupons To Referrals"
                    src="/images/store/popover.png"
                    class="popover-size ml-1"
                  />
                </p>
                <v-client-table
                  :columns="couponColumns"
                  :data="couponTableData"
                  :options="{
                    orderBy: {
                      column: 'id',
                      ascending: true
                    },
                    headings: {
                      code: 'Coupon',
                      referredUserName: 'User'
                    },
                    filterable: false
                  }"
                >
                  <div slot="referredUserName" slot-scope="props">
                    <v-select
                      label="name"
                      :options="users"
                      :reduce="user => user.value"
                      @input="
                        assignCouponToUser(
                          props.row.id,
                          referredCouponUser[props.row.id - 1]
                        )
                      "
                      v-model="referredCouponUser[props.row.id - 1]"
                    >
                    </v-select>
                  </div>
                  <div slot="actions" slot-scope="props">
                    <b-btn
                      variant="warning"
                      @click="removeCouponUser(props.row.id)"
                      >Remove</b-btn
                    >
                  </div>
                </v-client-table>
              </div>
            </div>
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
      referredCouponUser: [],
      showReferralSettingsModal: false,
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
      ],
      couponColumns: ["code", "referredUserName", "actions"]
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
      referralSettings: "storeReferralSettings",
      leads: "storeLeads",
      customers: "storeCustomers"
    }),
    tableData() {
      return Object.values(this.referrals);
    },
    couponTableData() {
      return this.storeCoupons;
    },
    users() {
      let customers = this.customers;
      // let leads = this.leads;
      // let users = customers.concat(leads);

      return _.map(customers, customer => {
        return {
          name: customer.name,
          value: customer.user_id
        };
      });
    },
    storeReferralUrl() {
      let host = this.store.details.host ? this.store.details.host : "goprep";
      return (
        "http://" +
        this.store.details.domain +
        "." +
        host +
        ".com/customer/Menu?r="
      );
    }
  },
  methods: {
    ...mapActions(["refreshStoreReferrals", "refreshStoreReferralSettings"]),
    formatMoney: format.money,
    updateReferralSettings() {
      let referralSettings = { ...this.referralSettings };

      axios
        .patch("/api/me/referralSettings", referralSettings)
        .then(response => {
          this.refreshStoreReferralSettings();
          this.$toastr.s("Your referral rules have been saved.", "Success");
          this.showReferralSettingsModal = false;
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
    },
    assignCouponToUser(couponId, userId) {
      axios
        .patch("/api/me/coupons", {
          id: couponId,
          referral_user_id: userId
        })
        .then(response => {
          this.$toastr.s("Coupon has been assigned to a user.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    },
    removeCouponUser(couponId) {
      axios
        .patch("/api/me/coupons", { id: couponId, referral_user_id: null })
        .then(response => {
          let index = this.referredCouponUser.indexOf(response.data);
          this.referredCouponUser[index] = undefined;
          this.$toastr.s("Coupon has been removed from user.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    },
    async showReferralSettings() {
      // Setting referral users to coupons
      if (this.couponTableData.length > 0) {
        await this.couponTableData.forEach(row => {
          this.referredCouponUser.push(row.referredUserName);
        });
      }
      this.showReferralSettingsModal = true;
    },
    exportData(report, format = "pdf", print = false) {
      axios
        .get(`/api/me/print/${report}/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if (print) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                },
                false
              );
            }
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>
