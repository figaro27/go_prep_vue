<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <v-client-table
        :columns="purchasedGiftCardColumns"
        :data="purchasedGiftCardTableData"
        :options="{
          orderBy: {
            column: 'created_at',
            ascending: false
          },
          headings: {
            created_at: 'Purchased',
            purchased_by: 'Purchased By',
            emailRecipient: 'Emailed To',
            code: 'Code',
            amount: 'Amount',
            balance: 'Balance'
          },
          filterable: false
        }"
      >
        <div slot="purchased_by" slot-scope="props">
          <p>
            {{ getPurchasedByUser(props.row.user_id) }}
          </p>
        </div>
        <div slot="created_at" slot-scope="props">
          <p>
            {{ moment(props.row.created_at).format("dddd MMM Do") }}
          </p>
        </div>
      </v-client-table>
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

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      purchasedGiftCardColumns: [
        "created_at",
        "purchased_by",
        "emailRecipient",
        "code",
        "amount",
        "balance"
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
      purchasedGiftCards: "storePurchasedGiftCards",
      customers: "storeCustomers"
    }),
    purchasedGiftCardTableData() {
      if (this.purchasedGiftCards.length > 0) return this.purchasedGiftCards;
      else return [];
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    getPurchasedByUser(userId) {
      let user =
        this.customers.length > 0
          ? this.customers.find(customer => {
              return customer.user_id === userId;
            })
          : null;
      if (user) {
        return user.firstname + " " + user.lastname;
      }
    }
  }
};
</script>
