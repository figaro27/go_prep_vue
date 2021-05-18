<template>
  <div class="main-customer-container box-shadow">
    <p>
      <b>Referral URL</b>
      <a :href="referralUrl">{{ referralUrl }}</a>
      <i
        class="fas fa-clipboard ml-1"
        style="color:#20A8D8"
        v-clipboard:copy="referralUrl"
        v-clipboard:success="onCopy"
      ></i>
      <img
        v-b-popover.hover="
          'Give this exact link out to prospective customers to earn rewards.'
        "
        title="Referral URL"
        src="/images/store/popover.png"
        class="popover-size ml-1"
      />
    </p>
    <div v-if="referral">
      <p v-if="store.referral_settings.kickbackType === 'credit'" class="mr-3">
        <b>Redeem Code:</b>
        {{ referral.code }}
        <img
          v-b-popover.hover="
            'Enter this in the promotion box on the checkout page to use your store credit on orders.'
          "
          title="Redeem Code"
          src="/images/store/popover.png"
          class="popover-size ml-1"
        />
      </p>

      <p v-if="store.referral_settings.kickbackType === 'credit'">
        <b>Total Store Credit Used:</b>
        {{ format.money(referral.total_paid_or_used, storeSettings.currency) }}
      </p>
      <p v-else>
        <b>Total amount paid out:</b>
        {{ format.money(referral.total_paid_or_used, storeSettings.currency) }}
      </p>

      <p v-if="store.referral_settings.kickbackType === 'credit'">
        <b>Store Credit Balance:</b>
        {{ format.money(referral.balance, storeSettings.currency) }}
      </p>
      <p v-else>
        <b>Balance:</b>
        {{ format.money(referral.balance, storeSettings.currency) }}
      </p>
    </div>

    <v-client-table
      :columns="columns"
      :data="referralOrders"
      :options="options"
      v-show="initialized"
      class="table-countless"
    >
      <div slot="beforeTable" class="mb-2">
        <h3 class="mt-2">Referred Orders</h3>
      </div>
      <div slot="paid_at" slot-scope="props">
        {{ moment(props.row.paid_at).format("dddd, MMM Do") }}
      </div>
      <div slot="pickup" slot-scope="props">
        {{ props.row.transfer_type }}
      </div>
      <div slot="amount" slot-scope="props">
        <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
      </div>
      <div slot="referral_kickback_amount" slot-scope="props">
        <div>
          {{
            formatMoney(props.row.referral_kickback_amount, props.row.currency)
          }}
        </div>
      </div>
    </v-client-table>
  </div>
</template>
<style lang="scss" scoped></style>

<script>
import { mapGetters, mapActions } from "vuex";
import format from "../../../lib/format.js";

export default {
  components: {},
  data() {
    return {
      referralOrders: [],
      columns: [
        "order_number",
        "store_name",
        "customer_name",
        "paid_at",
        "pickup",
        "amount",
        "referral_kickback_amount"
      ],
      options: {
        filterable: false,
        headings: {
          dailyOrderNumber: "Daily Order #",
          order_number: "Order ID",
          store_name: "Store",
          customer_name: "Customer",
          paid_at: "Order Placed",
          delivery_date: "Delivery Date",
          pickup: "Type",
          amount: "Total",
          balance: "Balance",
          referral_kickback_amount: "Kickback Amount"
        }
      }
    };
  },
  mounted() {
    if (this.referralOrders.length === 0) {
      this.getReferralOrders();
    }
  },
  computed: {
    ...mapGetters({
      user: "user",
      userDetail: "userDetail",
      storeSettings: "viewedStoreSettings",
      store: "viewedStore",
      initialized: "initialized"
    }),
    referralUrl() {
      let host = this.store.details.host ? this.store.details.host : "goprep";
      return (
        "http://" +
        this.store.details.domain +
        "." +
        host +
        ".com/customer/menu?r=" +
        this.user.referralUrlCode
      );
    },
    referral() {
      return this.user.referrals.find(referral => {
        return (referral.store_id = this.store.id);
      });
    }
  },
  methods: {
    formatMoney: format.money,
    getReferralOrders() {
      axios.post("/api/me/getReferralOrders").then(resp => {
        this.referralOrders = resp.data;
      });
    },
    onCopy: function(e) {
      this.$toastr.s("Link copied to clipboard.");
    }
  }
};
</script>
