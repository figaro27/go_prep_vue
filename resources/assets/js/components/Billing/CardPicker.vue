<template>
  <div>
    <b-modal
      size="md"
      title="Delete Card"
      v-model="showDeleteCardModal"
      v-if="showDeleteCardModal"
      no-fade
      hide-footer
    >
      <p class="center-text pt-2">
        This card is tied to an active subscription. If you delete this card,
        the subscription will be cancelled.
      </p>
      <p class="center-text">
        You can first update the credit card on your subscription on the
        <router-link to="/customer/subscriptions"
          ><strong>Subscriptions</strong></router-link
        >
        page, then come back and delete this card after.
      </p>

      <h5 class="center-text mb-3">Proceed?</h5>
      <div class="d-flex" style="justify-content:center">
        <b-btn @click="showDeleteCardModal = false" class="d-inline mr-2"
          >Cancel</b-btn
        >
        <b-btn @click="deleteCard(cardId)" variant="danger" class="d-inline"
          >Delete</b-btn
        >
      </div>
    </b-modal>

    <b-list-group class="card-list mb-3" v-if="billingPage">
      <b-list-group-item
        v-for="card in creditCards"
        :key="card.id"
        :active="card.id == activeCardId"
        class="card-list-item"
        :href="selectable ? '#' : ''"
      >
        <img
          class="card-logo"
          :src="icons.cards[card.card.brand.toLowerCase()]"
          @click="$emit('updateCard', card)"
        />
        <div class="flex-grow-1" @click="$emit('updateCard', card)">
          Ending in {{ card.card.last4 }}
        </div>
        <div>
          <b-btn class="card-delete" variant="plain" @click="deleteCard(card)">
            <i class="fa fa-minus-circle text-danger"></i>
          </b-btn>
        </div>
      </b-list-group-item>
    </b-list-group>

    <b-form-group
      label="Add New Card"
      v-if="gateway === 'stripe' || billingPage"
    >
      <card
        v-if="!isLoading && stripeKey && showCard"
        class="stripe-card"
        :class="{ newCard }"
        :stripe="stripeKey"
        :options="{ hidePostalCode: hidePostalCode }"
        @change="(newCard = $event.complete), fillingOutCard()"
      />
    </b-form-group>
    <b-form-group label="Add New Card" inline v-else>
      <inline-credit-card-field
        @change="evt => onChangeNewCard(evt)"
      ></inline-credit-card-field>
    </b-form-group>
    <div class="d-flex">
      <b-btn
        variant="primary"
        @click="onClickCreateCard()"
        :disabled="addingCard && !billingPage"
        class="mb-3 mr-2 d-inline"
        >Add Card</b-btn
      >
      <b-btn
        v-if="billingPage"
        variant="secondary"
        @click="$emit('back')"
        class="mb-3 mr-2 d-inline"
        >Back</b-btn
      >
      <b-form-checkbox
        v-model="saveCard"
        class="d-inline pt-1"
        v-if="user && user.user_role_id !== 4 && !billingPage && !bagMealPlan"
        >Save card for future use</b-form-checkbox
      >
    </div>
    <!-- <b-btn variant="primary" @click="onClickCreateCard()" class="mb-3"
      >Add Card</b-btn
    > -->

    <div v-if="cards.length && !$route.params.manualOrder && !billingPage">
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
              @click.stop="e => checkCardSubscriptions(card.id)"
            >
              <i class="fa fa-minus-circle text-danger"></i>
            </b-btn>
          </div>
        </b-list-group-item>
      </b-list-group>
    </div>
    <div v-if="$route.params.manualOrder || $route.params.subscription">
      <b-list-group class="card-list">
        <b-list-group-item
          v-if="$route.params.manualOrder || $route.params.subscription"
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
              @click.stop="e => checkCardSubscriptions(card.id)"
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
<script src="https://js.stripe.com/v3/"></script>
<script>
import { createToken } from "vue-stripe-elements-plus";
import InlineCreditCardField from "vue-credit-card-field/src/Components/InlineCreditCardField.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    InlineCreditCardField
  },
  props: {
    billingPage: {
      default: false
    },
    checkoutPage: {
      default: false
    },
    activeCardId: {
      default: null
    },
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
      hidePostalCode: false,
      showDeleteCardModal: false,
      cardId: null,
      stripeKey: window.app.stripe_key,
      // stripeOptions,
      addingCard: false,
      card: null,
      newCard: null,
      saveCard: true,
      showCard: false
    };
  },
  computed: {
    ...mapGetters({
      context: "context",
      cards: "cards",
      storeSettings: "viewedStoreSettings",
      store: "viewedStore",
      isLoading: "isLoading",
      user: "user",
      bagMealPlan: "bagMealPlan"
    })
  },
  mounted() {
    if (this.user.user_role_id === 4) {
      this.saveCard = false;
    }
    if (this.store.details.country !== "US") {
      this.hidePostalCode = true;
    }
    this.showCard = true;
  },
  methods: {
    ...mapActions(["refreshCards", "refreshSubscriptions"]),
    async onClickCreateCard() {
      this.addingCard = true;
      let token = null;
      let card = null;

      if (this.gateway === "stripe" || this.billingPage) {
        const data = await createToken();

        if (!data.token) {
          if (this.storeSettings.payment_gateway === "authorize") {
            this.$toast.e(
              "Failed to add card. Please make sure all of the info is correct."
            );
          } else {
            this.$toastr.w("Failed to save payment method");
          }
          this.addingCard = false;
          throw new Error("Failed to save payment method", data);
        }

        token = data.token.id;
        card = data.token.card;
      } else if (this.gateway === "authorize") {
        token = await this.createAuthorizeToken();
        let year = this.newCard.expYear;
        if (year.length === 4) {
          year.substr(-2);
        }
        card = {
          brand: this.newCard.brand || null,
          exp_month: this.newCard.expMonth,
          exp_year: year,
          last4: this.newCard.number.substr(-4),
          country: "US"
        };
      }

      // Updates card in store subscription
      if (this.billingPage) {
        let obj = {
          token: token,
          card: card
        };
        this.$emit("addCard", obj);
      }

      if (token && !this.billingPage) {
        this.createCard(token, card);
      }
    },
    createCard(token, card) {
      if (this.bagMealPlan) {
        this.saveCard = true;
      }
      let customer = this.$parent.getCustomer()
        ? this.$parent.getCustomer()
        : this.user;

      axios
        .post("/api/me/cards", {
          token,
          card,
          customer: customer,
          payment_gateway: this.gateway,
          saveCard: this.saveCard
        })
        .then(async resp => {
          if (
            this.manualOrder ||
            this.$route.params.manualOrder ||
            this.$route.params.subscription
          ) {
            this.$parent.getCards();
          } else {
            await this.refreshCards();
          }
          this.selectCard(resp.data.id);
          this.newCard = null;
          this.$toastr.s("Payment method saved.");
        })
        .catch(resp => {
          let error = "";
          if (this.storeSettings.payment_gateway === "authorize") {
            error =
              "Failed to add card. Does your billing address match the credit card? You can update your billing address in My Account. Click the icon on the top right.";
          } else {
            error = resp;
          }
          this.addingCard = false;
          if (!_.isEmpty(resp.response.data.error)) {
            error = resp.response.data.error;
          }

          this.$toastr.w(error);
        })
        .finally(() => {
          this.$parent.loading = false;
          this.addingCard = false;
        });
    },
    checkCardSubscriptions(id) {
      axios.post("/api/me/cardHasSubscription", { cardId: id }).then(resp => {
        if (resp.data === true && !this.checkoutPage) {
          this.showDeleteCardModal = true;
          this.cardId = id;
        } else {
          this.deleteCard(id);
        }
      });
    },
    deleteCard(id) {
      axios.delete("/api/me/cards/" + id).then(async resp => {
        if (this.checkoutPage) {
          this.$parent.getCards();
        } else {
          this.refreshCards();
        }
        this.showDeleteCardModal = false;
        this.$toastr.s("Payment method deleted.");
        if (this.context !== "store") {
          this.refreshSubscriptions();
        }
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
      this.$parent.overrideCardId = id;
      this.$parent.creditCardId = id;
      this.value = id;
      this.$emit("input", id);
    },
    setCard(id) {
      this.value = id;
    },
    onChangeNewCard(evt) {
      this.fillingOutCard();
      this.newCard = evt.card;
      // This is not working when entering four digit year
      // if (!evt.invalid && evt.complete) {
      //   this.newCard = evt.card;
      // } else {
      //   this.newCard = null;
      // }
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
    },
    fillingOutCard() {
      this.$parent.fillingOutCard = true;
    }
  }
};
</script>

<style></style>
