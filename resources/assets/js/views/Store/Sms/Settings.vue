<template>
  <div class="row mt-3 sms-settings">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
        title="Activate"
        v-model="showActivateModal"
        v-if="showActivateModal"
        no-fade
        hide-footer
      >
        <activate @closeModal="showActivateModal = false"></activate>
      </b-modal>

      <b-modal
        size="lg"
        title="Order Reminder"
        v-model="reminderTemplateModal"
        v-if="reminderTemplateModal"
        no-fade
        hide-footer
      >
        <reminder-template
          @closeModal="reminderTemplateModal = false"
          @update="updateSettings(true)"
        ></reminder-template>
      </b-modal>

      <b-modal
        size="lg"
        title="Order Confirmation"
        v-model="orderConfirmationTemplateModal"
        v-if="orderConfirmationTemplateModal"
        no-fade
        hide-footer
      >
        <order-confirmation-template
          @closeModal="orderConfirmationTemplateModal = false"
          @update="updateSettings(true)"
        ></order-confirmation-template>
      </b-modal>

      <b-modal
        size="lg"
        title="Delivery"
        v-model="deliveryTemplateModal"
        v-if="deliveryTemplateModal"
        no-fade
        hide-footer
      >
        <delivery-template
          @closeModal="deliveryTemplateModal = false"
          @update="updateSettings(true)"
        ></delivery-template>
      </b-modal>

      <b-modal
        size="lg"
        title="Subscription Renewal"
        v-model="subscriptionTemplateModal"
        v-if="subscriptionTemplateModal"
        no-fade
        hide-footer
      >
        <subscription-template
          @closeModal="subscriptionTemplateModal = false"
          @update="updateSettings(true)"
        ></subscription-template>
      </b-modal>

      <div class="mb-4">
        <p v-if="smsSettings.phone" class="strong">
          Your Number - {{ smsSettings.phone }}
        </p>
        <span v-if="smsSettings.phone" class="strong">
          Balance -
          {{ format.money(smsSettings.balance, store.settings.currency) }}
        </span>
        <img
          v-if="smsSettings.phone"
          v-b-popover.hover="
            'Your connected Stripe account will be charged every time it reaches a $5.00 threshold.'
          "
          title="Balance"
          src="/images/store/popover.png"
          class="popover-size"
        />
        <b-btn
          v-if="!smsSettings.phone"
          variant="primary"
          @click="showActivateModal = true"
          >Buy Phone Number</b-btn
        >
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Notify Me on Responses</span>
          <img
            v-b-popover.hover="
              'If a customer replies to one of your messages, we can text a notification to your phone (charges apply).'
            "
            title="Notify Me on Responses"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="smsSettings.notifyChats"
          @change.native="updateSettings"
        />
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Auto Add New Customers to Contacts</span>
          <img
            v-b-popover.hover="
              'Automatically add new customers that place orders to your All Contacts list.'
            "
            title="Add New Customers to Contacts"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="smsSettings.autoAddCustomers"
          @change.native="updateSettings"
        />
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Auto Send Order Reminder Texts</span>
          <img
            v-b-popover.hover="
              'Automatically send an reminder to all of your customers to order a certain period before your cutoff time(s).'
            "
            title="Auto Send Order Reminder Texts"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="smsSettings.autoSendOrderReminder"
          @change.native="updateSettings"
        />
        <div v-if="smsSettings.autoSendOrderReminder">
          <p class="small">
            Next Delivery Date:
            {{
              moment(smsSettings.nextDeliveryDate.date).format("dddd, MMM Do")
            }}
          </p>
          <p class="small">
            Next Cutoff Time:
            {{
              moment(smsSettings.nextCutoff.date).format("dddd, MMM Do, h:mm a")
            }}
          </p>
          <p class="small">
            Next Reminder Text:
            {{
              moment(smsSettings.orderReminderTime.date).format(
                "dddd, MMM Do, h:mm a"
              )
            }}
          </p>
          <div class="d-flex">
            <b-form-input
              type="number"
              v-model="smsSettings.autoSendOrderReminderHours"
              :options="reminderHoursOptions"
              class="d-inline w-180"
              style="height:30px"
              @change="updateSettings"
            ></b-form-input>
            <img
              v-b-popover.hover="
                'Select the number of hours before your next cutoff that you want the order reminder text to be sent. The time above will update.'
              "
              title="Hours Before Cutoff"
              src="/images/store/popover.png"
              class="popover-size d-inline mt-1 ml-2"
            />
          </div>
          <b-btn
            @click="reminderTemplateModal = true"
            variant="warning"
            class="mt-2"
            >Edit Template</b-btn
          >
        </div>
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Auto Send Order Confirmation Text</span>
          <img
            v-b-popover.hover="
              'Automatically send a confirmation text to the customer immediately after a new order is placed.'
            "
            title="Auto Send Order Confirmation Text"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="smsSettings.autoSendOrderConfirmation"
          @change.native="updateSettings"
        />
        <div>
          <b-btn
            @click="orderConfirmationTemplateModal = true"
            variant="warning"
            class="mt-2"
            v-if="smsSettings.autoSendOrderConfirmation"
            >Edit Template</b-btn
          >
        </div>
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Auto Send Delivery / Pickup Text</span>
          <img
            v-b-popover.hover="
              'On the day of delivery or pickup, automatically send a reminder notification text to the customer at a certain time.'
            "
            title="Auto Send Delivery Text"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <div class="d-flex">
          <c-switch
            color="success"
            variant="pill"
            size="lg"
            v-model="smsSettings.autoSendDelivery"
            class="d-inline mr-2 pt-1"
            @change.native="updateSettings"
          />

          <b-form-input
            type="number"
            v-model="smsSettings.autoSendDeliveryTime"
            v-if="smsSettings.autoSendDelivery"
            :options="deliveryTimeOptions"
            class="d-inline w-180"
            style="height:30px"
            @change="updateSettings"
          ></b-form-input>
        </div>
        <div>
          <b-btn
            v-if="smsSettings.autoSendDelivery"
            @click="deliveryTemplateModal = true"
            class="mt-3"
            variant="warning"
            >Edit Template</b-btn
          >
        </div>
      </div>
      <div class="mb-4">
        <p class="strong">
          <span class="mr-1">Auto Send Subscription Renewal Text</span>
          <img
            v-b-popover.hover="
              '24 hours before the customer\'s subscription renews, send them a reminder text telling them they have 24 hours left to update their meals if they wish. The customer also automatically receives a reminder email 24 hours before.'
            "
            title="Auto Send Subscription Renewal"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <div class="d-flex">
          <c-switch
            color="success"
            variant="pill"
            size="lg"
            v-model="smsSettings.autoSendSubscriptionRenewal"
            class="d-inline mr-2 pt-1"
            @change.native="updateSettings"
          />
        </div>
        <div>
          <b-btn
            v-if="smsSettings.autoSendSubscriptionRenewal"
            @click="subscriptionTemplateModal = true"
            class="mt-3"
            variant="warning"
            >Edit Template</b-btn
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../mixins/deliveryDates";
import format from "../../../lib/format";
import store from "../../../store";
import times from "../../../data/times";
import Activate from "./Modals/Activate.vue";
import ReminderTemplate from "./Modals/ReminderTemplate.vue";
import OrderConfirmationTemplate from "./Modals/OrderConfirmationTemplate.vue";
import DeliveryTemplate from "./Modals/DeliveryTemplate.vue";
import SubscriptionTemplate from "./Modals/SubscriptionTemplate.vue";

export default {
  props: {
    tabs: null
  },
  watch: {
    tabs(val) {
      if (val === 3 && !this.loaded) {
        this.refreshSMSSettings();
        this.loaded = true;
      }
    }
  },
  components: {
    Spinner,
    vSelect,
    Activate,
    ReminderTemplate,
    OrderConfirmationTemplate,
    DeliveryTemplate,
    SubscriptionTemplate
  },
  mixins: [checkDateRange],
  data() {
    return {
      loaded: false,
      showActivateModal: false,
      reminderTemplateModal: false,
      orderConfirmationTemplateModal: false,
      deliveryTemplateModal: false,
      subscriptionTemplateModal: false
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      smsSettings: "storeSMSSettings"
    }),
    deliveryTimeOptions() {
      return times;
    },
    reminderHoursOptions() {
      return [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20,
        21,
        22,
        23,
        24,
        25,
        26,
        27,
        28,
        29,
        30,
        31,
        32,
        33,
        34,
        35,
        36,
        37,
        38,
        39,
        40,
        41,
        42,
        43,
        44,
        45,
        46,
        47,
        48
      ];
    }
  },
  methods: {
    ...mapActions({
      refreshSMSSettings: "refreshStoreSMSSettings",
      disableSpinner: "disableSpinner",
      enableSpinner: "enableSpinner"
    }),
    formatMoney: format.money,
    updateSettings(fromModal = false) {
      if (fromModal == true) {
        this.$toastr.s("Template updated.");
      }
      this.disableSpinner();
      this.checkIfActivated();
      this.$nextTick(() => {
        axios
          .post("/api/me/updateSMSSettings", { settings: this.smsSettings })
          .then(resp => {
            this.refreshSMSSettings();
            this.enableSpinner();
          });
      });
    },
    checkIfActivated() {
      if (!this.smsSettings.phone) {
        this.smsSettings.autoSendDelivery = false;
        this.smsSettings.autoSendOrderConfirmation = false;
        this.showActivateModal = true;
      }
    }
  }
};
</script>
