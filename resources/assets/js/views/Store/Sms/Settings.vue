<template>
  <div class="row mt-3">
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
      <p>
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
      />

      <p>
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
        @change.native="checkIfActivated"
      />

      <p>
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
          @change.native="checkIfActivated"
        />

        <b-form-select
          v-model="smsSettings.autoSendDeliveryTime"
          :options="deliveryTimeOptions"
          class="d-inline"
          style="height:30px"
        ></b-form-select>
      </div>
      <b-btn variant="primary" class="mt-3" @click="updateSettings">Save</b-btn>
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

export default {
  components: {
    Spinner,
    vSelect,
    Activate
  },
  mixins: [checkDateRange],
  data() {
    return {
      showActivateModal: false
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
    ...mapActions({}),
    formatMoney: format.money,
    updateSettings() {
      axios
        .post("/api/me/updateSMSSettings", { settings: this.smsSettings })
        .then(resp => {
          this.$toastr.s("Your settings have been saved.", "Success");
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
