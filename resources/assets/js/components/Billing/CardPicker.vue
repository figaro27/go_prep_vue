<template>
  <div>
    <b-form-group label="Add New Card">
      <card
        class="stripe-card"
        :class="{ newCard }"
        :stripe="stripeKey"
        :options="stripeOptions"
        @change="newCard = $event.complete"
      />
    </b-form-group>
    <b-btn v-if="newCard" variant="primary" @click="createCard" class="mb-3"
      >Add Card</b-btn
    >
    <div v-if="cards.length && !manualOrder">
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
    <div v-if="manualOrder">
      <b-list-group class="card-list">
        <b-list-group-item
          v-if="manualOrder"
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
// import { stripeKey, stripeOptions } from "../../config/stripe.json";
import { createToken } from "vue-stripe-elements-plus";
import { mapGetters, mapActions } from "vuex";

export default {
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
    }
  },
  data() {
    return {
      stripeKey: window.app.stripe_key,
      // stripeOptions,
      newCard: null,
      creditCards: []
    };
  },
  computed: {
    ...mapGetters({
      cards: "cards"
    })
  },
  methods: {
    ...mapActions(["refreshCards"]),
    createCard() {
      let customer = this.$parent.getCustomer();
      // this.$parent.loading = true;
      createToken().then(data => {
        console.log(data);

        if (!data.token) {
          this.$toastr.e("Failed to save payment method");
          throw new Error("Failed to save payment method", data);
        }

        axios
          .post("/api/me/cards", {
            token: data.token,
            customer: customer
          })
          .then(async resp => {
            if (this.manualOrder) {
              this.$parent.getCards();
            } else {
              await this.refreshCards();
            }
            this.selectedCard = resp.id;
            this.newCard = null;
            this.$toastr.s("Payment method saved.");
          })
          .catch(resp => {
            let error = "Failed to add card.";

            if (!_.isEmpty(resp.response.data.error)) {
              error = resp.response.data.error;
            }

            // let error = _.first(Object.values(resp.resp.data.errors));
            // error = error.join(" ");
            // this.$toastr.e(error, "Error");
            this.$toastr.e(error, "Error");
          })
          .finally(() => {
            this.$parent.loading = false;
          });
      });
    },
    deleteCard(id) {
      axios.delete("/api/me/cards/" + id).then(async resp => {
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
    }
  }
};
</script>

<style></style>
