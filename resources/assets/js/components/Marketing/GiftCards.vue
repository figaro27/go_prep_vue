<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
        title="Add Gift Card Code"
        v-model="showCreateGiftCardModal"
        v-if="showCreateGiftCardModal"
        hide-footer
      >
        <b-form @submit.prevent="createGiftCard()">
          <b-form-input
            class="mt-2"
            v-model="newGiftCard.amount"
            placeholder="Amount"
            type="number"
            min="0"
            required
          ></b-form-input>
          <div class="d-flex d-inline">
            <b-form-input
              class="mt-2"
              v-model="newGiftCard.code"
              v-if="!newGiftCard.customCode"
              placeholder="Custom Code"
              style="width:300px"
            ></b-form-input
            ><b-form-checkbox class="ml-2 pt-3" v-model="newGiftCard.customCode"
              >Create Code For Me</b-form-checkbox
            >
          </div>
          <b-form-input
            class="mt-2"
            v-model="newGiftCard.emailRecipient"
            placeholder="Optional Email Recipient"
          ></b-form-input>
          <b-button type="submit" variant="primary" class="mt-2 float-right"
            >Add</b-button
          >
        </b-form>
      </b-modal>
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
        <div slot="beforeTable" class="mb-2">
          <div class="d-flex">
            <b-btn variant="primary" @click="showCreateGiftCardModal = true"
              >Add Gift Card Code</b-btn
            >
          </div>
        </div>
        <!-- <div slot="purchased_by" slot-scope="props">
          <p>
            {{ getPurchasedByUser(props.row.user_id) }}
          </p>
        </div> -->
        <div slot="created_at" slot-scope="props">
          <p>
            {{ moment(props.row.created_at).format("dddd MMM Do") }}
          </p>
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <b-btn variant="danger" @click="deleteGiftCard(props.row.id)"
            >Delete</b-btn
          >
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
      newGiftCard: {
        amount: null,
        code: null,
        emailRecipient: null
      },
      showCreateGiftCardModal: false,
      purchasedGiftCardColumns: [
        "created_at",
        "purchased_by",
        "emailRecipient",
        "code",
        "amount",
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
      purchasedGiftCards: "storePurchasedGiftCards",
      customers: "storeCustomers"
    }),
    purchasedGiftCardTableData() {
      if (this.purchasedGiftCards.length > 0) return this.purchasedGiftCards;
      else return [];
    }
  },
  methods: {
    ...mapActions({
      refreshStorePurchasedGiftCards: "refreshStorePurchasedGiftCards"
    }),
    formatMoney: format.money,
    createGiftCard() {
      axios
        .post("/api/me/purchasedGiftCards", { newGiftCard: this.newGiftCard })
        .then(resp => {
          this.refreshStorePurchasedGiftCards();
          this.newGiftCard = {
            amount: null,
            code: null,
            emailRecipient: null
          };
          this.$toastr.s("Gift card added.");
          this.showCreateGiftCardModal = false;
        });
    },
    deleteGiftCard(id) {
      axios.delete(`/api/me/purchasedGiftCards/${id}`).then(resp => {
        this.refreshStorePurchasedGiftCards();
        this.$toastr.s("Gift card deleted!");
      });
    }
    // getPurchasedByUser(userId) {
    //   let user =
    //     this.customers.length > 0
    //       ? this.customers.find(customer => {
    //           return customer.user_id === userId;
    //         })
    //       : null;
    //   if (user) {
    //     return user.firstname + " " + user.lastname;
    //   }
    // }
  }
};
</script>
