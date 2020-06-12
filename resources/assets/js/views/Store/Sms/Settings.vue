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

      <div class="mb-5">
        <p v-if="smsSettings.phone" class="strong">
          Sending Number - {{ smsSettings.phone }}
        </p>
        <b-btn v-else variant="primary" @click="showActivateModal = true"
          >Buy Phone Number</b-btn
        >
      </div>
      <div class="mb-5">
        <p class="strong">
          <span class="mr-1">Add New Customers to Contacts</span>
          <img
            v-b-popover.hover="
              'Automatically add new customers to your All Contacts list.'
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
      <div class="mb-5">
        <p class="strong">
          <span class="mr-1">Auto Send Order Reminder Texts</span>
          <img
            v-b-popover.hover="
              'Automatically send an automatic reminder to all of your customers to order a certain period before your cutoff time.'
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
          <p class="font-11">
            Next Delivery Date:
            {{
              moment(smsSettings.nextDeliveryDate.date).format("dddd, MMM Do")
            }}
          </p>
          <p class="font-11">
            Next Cutoff Time:
            {{
              moment(smsSettings.nextCutoff.date).format("dddd, MMM Do, h:mm a")
            }}
          </p>
          <p class="font-11">
            Next Reminder Text:
            {{
              moment(smsSettings.orderReminderTime.date).format(
                "dddd, MMM Do, h:mm a"
              )
            }}
          </p>
          <div class="d-flex">
            <b-form-input
              v-model="smsSettings.autoSendOrderReminderHours"
              placeholder="Hours before cutoff to send"
              class="w-180 d-inline"
              v-on:keyup="updateSettings"
            ></b-form-input>
            <img
              v-b-popover.hover="
                'Insert the number of hours before your next cutoff that you want the order reminder text to be sent. The time it above will update.'
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
      <div class="mb-5">
        <p class="strong">
          <span class="mr-1">Auto Send Order Confirmation Text</span>
          <img
            v-b-popover.hover="
              'Automatically send a confirmation text immediately after a new order is placed. Customers also receive an email confirmation. Charges apply.'
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
      <div class="mb-5">
        <p class="strong">
          <span class="mr-1">Auto Send Delivery Text</span>
          <img
            v-b-popover.hover="
              'On the day of order delivery or pickup, send a reminder notification text to the customer. Charges apply.'
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

          <b-form-select
            v-model="smsSettings.autoSendDeliveryTime"
            v-if="smsSettings.autoSendDelivery"
            :options="deliveryTimeOptions"
            class="d-inline"
            style="height:30px"
          ></b-form-select>
        </div>
        <div>
          <b-btn
            @click="deliveryTemplateModal = true"
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

export default {
  components: {
    Spinner,
    vSelect,
    Activate,
    ReminderTemplate,
    OrderConfirmationTemplate,
    DeliveryTemplate
  },
  mixins: [checkDateRange],
  data() {
    return {
      showActivateModal: false,
      reminderTemplateModal: false,
      orderConfirmationTemplateModal: false,
      deliveryTemplateModal: false
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
      axios
        .post("/api/me/updateSMSSettings", { settings: this.smsSettings })
        .then(resp => {
          this.refreshSMSSettings();
          this.enableSpinner();
        });
    },
    checkIfActivated() {
      if (!this.smsSettings.phone) {
        this.smsSettings.autoSendDelivery = false;
        this.smsSettings.autoSendOrderConfirmation = false;
        this.showActivateModal = true;
        return;
      }
    }
  }
};
</script>
