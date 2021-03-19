<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div v-if="storePlan && storePlan.charge_failed">
        <b-alert variant="danger" show dismissible>
          <p>
            We attempted to charge your card on
            <b>{{
              moment(storePlan.charge_failed).format("ddd, MM/D/YYYY")
            }}</b>
            and it failed with the reason:
            <b>{{ storePlan.charge_failed_reason }}</b
            >.
          </p>
          <p>
            Please update your credit card or settle the issue with your current
            credit card.
          </p>
        </b-alert>
      </div>
      <div class="card">
        <div class="card-body">
          <div v-if="storePlan" class="d-flex">
            <p class="mr-5 font-18">
              <b>Plan Name:</b>
              {{
                storePlan.plan_name.charAt(0).toUpperCase() +
                  storePlan.plan_name.substring(1)
              }}
            </p>
            <p class="mr-5 font-18">
              <b>Amount:</b>
              {{
                format.money(storePlan.amount / 100, store.settings.currency)
              }}
            </p>
            <p class="mr-5 font-18">
              <b>Interval:</b>
              {{
                storePlan.period.charAt(0).toUpperCase() +
                  storePlan.period.substring(1)
              }}
            </p>
            <p
              class="mr-5 font-18"
              v-if="storePlan.plan_name !== 'pay-as-you-go'"
            >
              <b>Orders Limit:</b> {{ storePlan.allowed_orders }}
            </p>
            <p
              class="mr-5 font-18"
              v-if="storePlan.plan_name !== 'pay-as-you-go'"
            >
              <b
                >Month To Date Orders:
                <span v-if="storePlan.joined_store_ids"
                  >(Multiple Stores)</span
                ></b
              >
              {{ monthToDateOrders }}
              <span v-if="monthToDateOrders > storePlan.allowed_orders">
                <i
                  class="fas fa-exclamation-triangle"
                  style="color:#FF9933"
                  @mouseover="showOverOrdersToast"
                ></i>
              </span>
            </p>
            <p
              class="mr-5 font-18"
              v-if="storePlan.plan_name !== 'pay-as-you-go'"
            >
              <b>Remaining Orders:</b>
              <span v-if="storePlan.allowed_orders - monthToDateOrders > 0">{{
                storePlan.allowed_orders - monthToDateOrders
              }}</span>
              <span v-else>0</span>
            </p>
            <p
              class="mr-5 font-18"
              v-if="storePlan.plan_name === 'pay-as-you-go'"
            >
              <b>Application Fee:</b> {{ store.settings.application_fee }}%
            </p>
          </div>
          <div v-if="storePlan">
            <p v-if="storePlan.plan_notes" class="font-18">
              <b>Notes:</b> {{ storePlan.plan_notes }}
            </p>
          </div>
          <div class="mt-2 mb-2">
            <b-btn variant="primary" @click="showUpdatePlanModal = true"
              >Update Plan</b-btn
            >
            <b-btn
              variant="warning"
              @click="(showUpdateCardModal = true), getStorePlanCards()"
              v-if="storePlan && storePlan.method == 'credit_card'"
              >Update Credit Card</b-btn
            >
          </div>

          <v-client-table
            :columns="columns"
            :data="storePlanTransactions"
            :options="options"
            class="mt-4"
          >
            <div slot="beforeTable" class="mb-2" v-if="storePlan">
              <h3>Invoices</h3>
              <p
                class="mt-3"
                v-if="
                  storePlanTransactions.length > 0 &&
                    storePlan.status !== 'cancelled'
                "
              >
                <b>Upcoming Charge:</b>
                {{
                  format.money(storePlan.amount / 100, store.settings.currency)
                }}
                on {{ moment(nextChargeDate).format("ddd, MM/D/YYYY") }}
              </p>
              <p
                class="mt-3"
                v-if="
                  storePlanTransactions.length == 0 &&
                    storePlan.status !== 'cancelled' &&
                    storePlan.free_trial
                "
              >
                <b>Upcoming Charge:</b>
                {{
                  moment(storePlan.created_at)
                    .add(2, "w")
                    .format("ddd, MM/D/YYYY")
                }}
              </p>
            </div>
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format("ddd, MM/D/YYYY") }}
            </div>
            <div slot="card_brand" slot-scope="props">
              {{
                props.row.card_brand.charAt(0).toUpperCase() +
                  props.row.card_brand.substring(1)
              }}
            </div>
            <div slot="amount" slot-scope="props">
              {{
                format.money(props.row.amount / 100, store.settings.currency)
              }}
            </div>
            <div slot="period_start" slot-scope="props">
              {{ moment(props.row.period_start).format("ddd, MM/D/YYYY") }}
            </div>
            <div slot="period_end" slot-scope="props">
              {{ moment(props.row.period_end).format("ddd, MM/D/YYYY") }}
            </div>
            <div slot="receipt_url" slot-scope="props">
              <a :href="props.row.receipt_url" target="_blank"
                ><b-btn variant="primary">View Receipt</b-btn></a
              >
            </div>
          </v-client-table>

          <div v-if="storePlan">
            <b-btn
              variant="danger"
              v-if="storePlan.status !== 'cancelled'"
              size="sm"
              @click="showCancelModal = true"
              >Cancel Subscription</b-btn
            >
            <p v-else>
              Subscription has been cancelled.
              <span v-if="storePlanTransactions.length > 0"
                >Your store will remain active until
                {{
                  moment(storePlanTransactions[0].period_end).format(
                    "ddd, MM/D/YYYY"
                  )
                }}.</span
              >
            </p>
          </div>
        </div>
        <b-modal
          size="md"
          v-model="showCancelModal"
          v-if="showCancelModal"
          hide-header
          hide-footer
          no-fade
        >
          <center>
            <p class="mt-3">
              You will no longer be rebilled.
              <span v-if="storePlanTransactions.length > 0"
                >Your account will remain active until
                {{
                  moment(storePlanTransactions[0].period_end).format(
                    "ddd, MM/D/YYYY"
                  )
                }}. Orders will then be disabled.</span
              >
            </p>
            <b-form-select
              v-model="cancellationReason"
              :options="cancellationReasonOptions"
              class="w-300"
            ></b-form-select>
            <b-form-textarea
              class="mt-3"
              v-if="cancellationReasonFilled"
              v-model="cancellationAdditionalInfo"
              placeholder="Please add any additional info that would be helpful for us to improve."
            ></b-form-textarea>
            <div class="d-flex d-center">
              <b-btn
                variant="secondary"
                class="mt-2 mr-2"
                @click="showCancelModal = false"
                >Back</b-btn
              >
              <b-btn
                variant="danger"
                class="mt-2"
                @click="cancelSubscription"
                :disabled="!cancellationReasonFilled"
                >Cancel Subscription</b-btn
              >
            </div>
          </center>
        </b-modal>

        <b-modal
          size="lg"
          v-model="showUpdatePlanModal"
          v-if="showUpdatePlanModal"
          hide-header
          hide-footer
          no-fade
        >
          <center>
            <b-form-group label="Billing Period" horizontal class="mt-3">
              <b-form-radio-group
                v-model="selectedPlanPeriod"
                :options="[
                  { text: 'Monthly', value: 'monthly' },
                  { text: 'Annually', value: 'annually' }
                ]"
              ></b-form-radio-group>
            </b-form-group>
            <b-form-group label="Select Plan" horizontal>
              <b-form-radio-group
                v-model="selectedPlan"
                :options="planOptions"
                stacked
              ></b-form-radio-group>
            </b-form-group>
            <b-btn variant="success" @click="updatePlan">Update Plan</b-btn>
          </center>
        </b-modal>

        <b-modal
          size="lg"
          v-model="showUpdateCardModal"
          v-if="showUpdateCardModal"
          hide-header
          hide-footer
          no-fade
        >
          <center>
            <card-picker
              :selectable="true"
              :creditCards="creditCardList"
              v-model="card"
              class="mb-2 mt-3"
              ref="cardPicker"
              :gateway="gateway"
              :billingPage="true"
              :activeCardId="storePlan.stripe_card_id"
              @addCard="addCard($event)"
              @updateCard="updateCard($event)"
              @deleteCard="deleteCard($event)"
              @back="showUpdateCardModal = false"
            ></card-picker>
          </center>
        </b-modal>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import format from "../../../lib/format";
import Spinner from "../../../components/Spinner";
import CardPicker from "../../../components/Billing/CardPicker";

export default {
  components: {
    CardPicker,
    cSwitch,
    Spinner
  },
  data() {
    return {
      gateway: {
        type: String,
        default: "stripe"
      },
      card: [],
      creditCardList: null,
      plans: [],
      selectedPlan: null,
      selectedPlanPeriod: "monthly",
      showUpdatePlanModal: false,
      showUpdateCardModal: false,
      showCancelModal: false,
      cancellationReason: "Please tell us the reason you are cancelling",
      cancellationAdditionalInfo: null,
      storePlan: null,
      stripeSubscription: null,
      storePlanTransactions: [],
      monthToDateOrders: 0,
      columns: [
        "created_at",
        "card_brand",
        "card_last4",
        "amount",
        "period_start",
        "period_end",
        "receipt_url"
      ],
      options: {
        headings: {
          created_at: "Charge Date",
          card_brand: "Card",
          card_last4: "Last 4",
          period_start: "Period Start",
          period_end: "Period End",
          receipt_url: "Receipt"
        },
        filterable: false
      }
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "viewedStore",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings"
    }),
    nextChargeDate() {
      let now = moment();

      if (now.date() > this.storePlan.day) {
        return moment()
          .add(this.storePlan.day - now.date(), "days")
          .add(1, "months");
      } else {
        return moment().add(this.storePlan.day - now.date(), "days");
      }
    },
    planOptions() {
      return _.map(this.plans, (plan, planId) => {
        const period = this.selectedPlanPeriod;
        const planDetails = plan[period];
        return {
          text: sprintf(
            "%s - %s %s %s",
            plan.title,
            format.money(planDetails.price / 100),
            period === "monthly" ? "Per Month" : "Per Year",
            planDetails.price_upfront
              ? ` - ${format.money(planDetails.price_upfront / 100)} up front`
              : ""
          ),
          value: planId
        };
      });
    },
    cancellationReasonOptions() {
      return [
        {
          value: "Please tell us the reason you are cancelling",
          text: "Please tell us the reason you are cancelling"
        },
        {
          value: "I can no longer afford the billing",
          text: "I can no longer afford the billing"
        },
        {
          value: "My business closed down or is on hold",
          text: "My business closed down or is on hold"
        },
        {
          value:
            "You don't have certain features I need to run my business effectively",
          text:
            "You don't have certain features I need to run my business effectively"
        },
        {
          value: "I switched to a different company",
          text: "I switched to a different company"
        },
        { value: "Other", text: "Other" }
      ];
    },
    cancellationReasonFilled() {
      return this.cancellationReason !==
        "Please tell us the reason you are cancelling"
        ? true
        : false;
    }
  },
  mounted() {
    this.getStorePlan();
    this.getMonthToDateOrders();
    this.getStripeSubscription();
    this.getStorePlanTransactions();
    this.getPlanOptions();
  },
  methods: {
    ...mapActions([
      "disableSpinner",
      "enableSpinner",
      "showSpinner",
      "hideSpinner"
    ]),
    ...mapMutations(["setCards"]),
    showOverOrdersToast() {
      this.$toastr.w(
        "You are currently over your order limit for the month. If this occurs for two months in a row your subscription will be upgraded to the next plan."
      );
    },
    getStorePlan() {
      this.showSpinner();
      axios
        .get("/api/me/getStorePlan")
        .then(resp => {
          this.storePlan = resp.data;
          this.selectedPlan = resp.data.plan_name
            ? resp.data.plan_name
            : "pay-as-you-go";
          this.selectedPlanPeriod = resp.data.period
            ? resp.data.period
            : "monthly";
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    getMonthToDateOrders() {
      this.showSpinner();
      axios
        .get("/api/me/getMonthToDateOrders")
        .then(resp => {
          this.monthToDateOrders = resp.data;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    getStripeSubscription() {
      this.showSpinner();
      axios
        .get("/api/me/getStripeSubscription")
        .then(resp => {
          this.stripeSubscription = resp.data;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    getStorePlanTransactions() {
      this.showSpinner();
      axios
        .get("/api/me/getStorePlanTransactions")
        .then(resp => {
          this.storePlanTransactions = resp.data;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    getPlanOptions() {
      this.showSpinner();
      axios
        .get("/api/plans")
        .then(resp => {
          if (!this.freeTrial) {
            delete resp.data.plans.free_trial;
          }
          this.plans = resp.data.plans;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    cancelSubscription() {
      this.showSpinner();
      axios
        .post("/api/me/cancelSubscription", {
          reason: this.cancellationReason,
          additionalInfo: this.cancellationAdditionalInfo
        })
        .then(resp => {
          this.$toastr.s("Subscription successfuly cancelled.");
          this.showCancelModal = false;
          this.storePlan = resp.data;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    updatePlan() {
      this.showSpinner();
      axios
        .post("/api/me/updatePlan", {
          plan: this.selectedPlan,
          period: this.selectedPlanPeriod
        })
        .then(resp => {
          this.storePlan = resp.data;
          this.$toastr.s(
            "Plan updated. You will be charged the new price at the next monthly billing cycle."
          );
          this.showUpdatePlanModal = false;
          this.hideSpinner();
        })
        .catch(resp => {
          this.hideSpinner();
        });
    },
    getStorePlanCards() {
      axios.get("/api/me/getStorePlanCards").then(resp => {
        resp.data.id = 0;
        this.card.push(resp.data);
        this.creditCardList = resp.data;
        this.setCards(resp.data);
      });
    },
    addCard(obj) {
      let card = obj.card;
      let token = obj.token;
      axios
        .post("/api/me/addStorePlanCard", { card: card, token: token })
        .then(resp => {
          this.getStorePlan();
          this.getStorePlanCards();
          // Change stripe customer ID in store plan?
          this.$toastr.s("New card added & made default paying method.");
        });
    },
    updateCard(card) {
      axios.post("/api/me/updateStorePlanCard", { card: card }).then(resp => {
        this.getStorePlan();
        this.getStorePlanCards();
        this.$toastr.s("Default paying method updated.");
      });
    },
    deleteCard(card) {
      axios.post("/api/me/deleteStorePlanCard", { card: card }).then(resp => {
        this.getStorePlanCards();
        this.$toastr.s("Card deleted");
      });
    }
  }
};
</script>
