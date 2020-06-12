<template>
  <div class="row mt-3">
    <div class="col-md-12 pb-3">
      <Spinner v-if="isLoading" />
      <p class="center-text">
        Please select an available phone number from the choices below.
      </p>
      <p class="center-text pb-2">
        Your account will be charged $4.00 per month.
      </p>

      <div v-if="numbers.length > 0">
        <h5 class="center-text pb-3">Available Numbers</h5>
        <b-form-radio-group
          buttons
          v-model="selectedNumber"
          class="storeFilters d-flex"
          style="flex-wrap:wrap"
          :options="numbers"
        ></b-form-radio-group>
        <div class="d-flex d-center mt-3">
          <b-btn variant="primary" @click="buyNumber" v-if="selectedNumber"
            >Activate Number</b-btn
          >
        </div>
      </div>
      <div v-else>
        <b-alert variant="secondary" show>
          <h5 class="center-text">Loading Numbers...</h5>
        </b-alert>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../../mixins/deliveryDates";
import format from "../../../../lib/format";
import store from "../../../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      numbers: [],
      selectedNumber: null
    };
  },
  props: {},
  created() {},
  mounted() {
    axios.get("/api/me/findAvailableNumbers").then(resp => {
      resp.data.numbers.forEach(number => {
        this.numbers.length < 8
          ? this.numbers.push({ value: number, text: number })
          : null;
      });
    });
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers"
    })
  },
  methods: {
    ...mapActions({
      refreshSMSSettings: "refreshStoreSMSSettings"
    }),
    formatMoney: format.money,
    buyNumber() {
      axios
        .post("/api/me/buyNumber", { phone: this.selectedNumber })
        .then(resp => {
          this.refreshSMSSettings();
          this.$emit("closeModal");
          this.$toastr.s("Number activated.");
        });
    }
  }
};
</script>
