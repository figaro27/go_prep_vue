<template>
  <div>
    <b-form-group label="Add New Card" v-if="gateway === 'stripe'">
      <card
        class="stripe-card"
        :class="{ newCard }"
        :stripe="stripeKey"
        @change="newCard = $event.complete"
      />
    </b-form-group>
    <b-form-group label="Add New Card" inline v-else>
      <inline-credit-card-field
        @change="evt => onChangeNewCard(evt)"
      ></inline-credit-card-field>
    </b-form-group>
    <b-btn
      v-if="newCard"
      variant="primary"
      @click="onClickCreateCard()"
      class="mb-3"
      >Add Card</b-btn
    >
    <div v-if="cards.length && !$route.params.manualOrder">
      <b-list-group class="card-list">
        <b-list-group-item
          v-for="card in cards"
          :key="card.id"
          :active="value === card.id"
          @click="e => selectCard(card.id)"
          class="card-list-item"
          :href="selectable ? '#' : ''"
        >
          <img class="card-logo" :src="icons.cards[card.brand.toLowerCase()]" />
          <div class="flex-grow-1">Ending in {{ card.last4 }}</div>
          <div>
            <b-btn
              class="card-delete"
              variant="plain"
              @click="e => deleteCard(card.id)"
            >
              <i class="fa fa-minus-circle text-danger"></i>
            </b-btn>
          </div>
        </b-list-group-item>
      </b-list-group>
    </div>
    <div v-if="$route.params.manualOrder">
      <b-list-group class="card-list">
        <b-list-group-item
          v-if="$route.params.manualOrder"
          v-for="card in creditCards"
          :key="card.id"
          :active="value === card.id"
          @click="e => selectCard(card.id)"
          class="card-list-item"
          :href="selectable ? '#' : ''"
        >
          <img class="card-logo" :src="icons.cards[card.brand.toLowerCase()]" />
          <div class="flex-grow-1">Ending in {{ card.last4 }}</div>
          <div>
            <b-btn
              class="card-delete"
              variant="plain"
              @click="e => deleteCard(card.id)"
            >
              <i class="fa fa-minus-circle text-danger"></i>
            </b-btn>
          </div>
        </b-list-group-item>
      </b-list-group>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.card-list {
  .card-list-item {
    display: inline-flex;
    align-items: center;

    &.active {
      .card-delete {
        visibility: hidden;
      }
    }
  }
  .card-logo {
    width: 45px;
    margin-right: 1rem;
  }
  .fa {
    font-size: 25px;
    vertical-align: middle;
    color: #dcdfe6;
  }
}
</style>

<script>
import { createToken } from "vue-stripe-elements-plus";
import InlineCreditCardField from "vue-credit-card-field/src/Components/InlineCreditCardField.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    InlineCreditCardField
  },
  props: {
    value: {
      default: null
    },
    selectable: {
      default: false
    },
    creditCards: {
      default: 0
    },
    manualOrder: {
      default: false
    },
    gateway: {
      required: true
    }
  },
  data() {
    return {
      stripeKey: window.app.stripe_key,
      // stripeOptions,
      card: null,
      newCard: null
    };
  },
  computed: {
    ...mapGetters({
      cards: "cards",
      storeSettings: "viewedStoreSettings"
    })
  },
  methods: {
    ...mapActions(["refreshCards"]),
    async onClickCreateCard() {
      let token = null;
      let card = null;

      if (this.gateway === "stripe") {
        const data = await createToken();

        if (!data.token) {
          if (this.storeSettings.payment_gateway === "authorize") {
            this.$toast.e(
              "Failed to save payment method. Does your billing address match the credit card? You can update your billing address in My Account."
            );
          } else {
            this.$toastr.e("Failed to save payment method");
          }
          throw new Error("Failed to save payment method", data);
        }

        token = data.token.id;
        card = data.token.card;
      } else if (this.gateway === "authorize") {
        token = await this.createAuthorizeToken();
        card = {
          brand: this.newCard.brand || null,
          exp_month: this.newCard.expMonth,
          exp_year: this.newCard.expYear,
          last4: this.newCard.number.substr(-4),
          country: "US"
        };
      }

      if (token) {
        this.createCard(token, card);
      }
    },
    createCard(token, card) {
      let customer = this.$parent.getCustomer();
      axios
        .post("/api/me/cards", {
          token,
          card,
          customer: customer,
          payment_gateway: this.gateway
        })
        .then(async resp => {
          if (this.manualOrder || this.$route.params.manualOrder) {
            this.$parent.getCards();
          } else {
            await this.refreshCards();
          }
          this.selectCard(resp.data.id);
          this.newCard = null;
          this.$toastr.s("Payment method saved.");
        })
        .catch(resp => {
          let error = "Failed to add card.";

          if (!_.isEmpty(resp.response.data.error)) {
            error = resp.response.data.error;
          }

          this.$toastr.e(error, "Error");
        })
        .finally(() => {
          this.$parent.loading = false;
        });
    },
    deleteCard(id) {
      axios.delete("/api/me/cards/" + id).then(async resp => {
        if (this.manualOrder || this.$route.params.manualOrder) {
          this.$parent.getCards();
        }
        await this.refreshCards();
        this.$parent.card = null;
        if (this.value === id) {
          this.selectCard(_.first(this.cards).id);
        }
        this.$toastr.s("Payment method deleted.");
      });
    },
    selectCard(id) {
      if (!this.selectable) {
        return;
      }

      if (this.creditCard > 0) {
        this.$emit("input", this.creditCard);
        this.creditCards.push(this.creditCard);
        return;
      }

      this.$parent.creditCardId = id;
      this.value = id;
      this.$emit("input", id);
    },
    setCard(id) {
      this.value = id;
    },
    onChangeNewCard(evt) {
      if (!evt.invalid && evt.complete) {
        this.newCard = evt.card;
      } else {
        this.newCard = null;
      }
    },
    async createAuthorizeToken() {
      const authorize = window.app.authorize;

      const authData = {
        clientKey: this.storeSettings.authorize_public_key,
        apiLoginID: this.storeSettings.authorize_login_id
      };

      const cardData = {
        cardNumber: this.newCard.number,
        month: this.newCard.expMonth,
        year: this.newCard.expYear,
        cardCode: this.newCard.cvc
      };

      const data = {
        authData,
        cardData
      };

      const token = await (async () => {
        return new Promise((resolve, reject) => {
          Accept.dispatchData(data, resp => {
            if (!resp.messages || resp.messages.resultCode !== "Ok") {
              reject(resp);
            }

            const _token = resp.opaqueData.dataValue;
            resolve(_token);
          });
        });
      })();

      return token;
    }
  }
};
</script>

<style></style>
