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
              style="width:250px"
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
        <div slot="amount" slot-scope="props">
          <p class="d-flex">
            <span v-if="!enablingEditAmount[props.row.id]" class="d-inline">{{
              props.row.amount
            }}</span>
            <span v-else class="d-inline"
              ><b-form-input
                v-model="props.row.amount"
                style="width:80px"
                type="number"
                min="0"
              ></b-form-input
            ></span>
            <i
              v-if="
                enablingEditAmount[props.row.id]
                  ? enablingEditAmount[props.row.id] === false
                  : true
              "
              @click="enableEditAmount(props.row)"
              class="fa fa-edit text-warning font-15 pl-1 d-inline"
              style="padding-top:10px"
            ></i>
            <i
              v-if="enablingEditAmount[props.row.id] === true"
              class="fas fa-check-circle text-primary pl-1 font-15 d-inline"
              @click="endEditAmount(props.row)"
              style="padding-top:10px"
            ></i>
          </p>
        </div>
        <div slot="balance" slot-scope="props">
          <p class="d-flex">
            <span v-if="!enablingEditBalance[props.row.id]" class="d-inline">{{
              props.row.balance
            }}</span>
            <span v-else class="d-inline"
              ><b-form-input
                v-model="props.row.balance"
                style="width:80px"
                type="number"
                min="0"
              ></b-form-input
            ></span>
            <i
              v-if="
                enablingEditBalance[props.row.id]
                  ? enablingEditBalance[props.row.id] === false
                  : true
              "
              @click="enableEditBalance(props.row)"
              class="fa fa-edit text-warning font-15 pl-1 d-inline"
              style="padding-top:10px"
            ></i>
            <i
              v-if="enablingEditBalance[props.row.id] === true"
              class="fas fa-check-circle text-primary pl-1 font-15 d-inline"
              @click="endEditBalance(props.row)"
              style="padding-top:10px"
            ></i>
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
      enablingEditAmount: {},
      enablingEditBalance: {},
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
    },
    enableEditAmount(row) {
      this.$nextTick(() => {
        this.$set(this.enablingEditAmount, row.id, true);
      });
    },
    endEditAmount(row) {
      row.amount = row.amount === "0" ? "0.00" : row.amount;
      axios
        .patch(`/api/me/purchasedGiftCards/${row.id}`, {
          id: row.id,
          amount: row.amount
        })
        .then(resp => {
          this.refreshStorePurchasedGiftCards();
          this.$nextTick(() => {
            this.$set(this.enablingEditAmount, row.id, false);
          });
        });
    },
    enableEditBalance(row) {
      this.$nextTick(() => {
        this.$set(this.enablingEditBalance, row.id, true);
      });
    },
    endEditBalance(row) {
      row.balance = row.balance === "0" ? "0.00" : row.balance;
      axios
        .patch(`/api/me/purchasedGiftCards/${row.id}`, {
          id: row.id,
          balance: row.balance
        })
        .then(resp => {
          this.refreshStorePurchasedGiftCards();
          this.$nextTick(() => {
            this.$set(this.enablingEditBalance, row.id, false);
          });
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
