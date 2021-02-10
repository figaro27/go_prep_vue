<template>
  <div class="row mt-3">
    <div class="col-md-12 pb-3">
      <Spinner v-if="isLoading" />
      <div v-if="text !== ''">
        <p class="mb-2">
          <strong>Sent - </strong> {{ moment(sent).format("ddd, MMM Do YYYY") }}
        </p>
        <p class="mb-2">
          <strong>Cost - </strong>
          {{ format.money(cost, store.settings.currency) }}
        </p>
        <p class="mb-2"><strong>Message - </strong> {{ text }}</p>
      </div>
      <v-client-table
        :columns="columns"
        :data="message"
        :options="{
          orderBy: {},
          headings: {
            phone: 'Phone'
          },
          filterable: false
        }"
      >
        <div slot="recipient" slot-scope="props">
          {{ props.row.firstName }} {{ props.row.lastName }}
        </div>
        <div slot="status" slot-scope="props">
          {{ status(props.row.status) }}
        </div>
      </v-client-table>
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
      message: [],
      columns: ["recipient", "phone", "status"]
    };
  },
  props: {
    messageId: null,
    text: null
  },
  created() {},
  mounted() {
    axios.get("/api/me/SMSMessages/" + this.messageId).then(resp => {
      this.message = resp.data;
    });
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers"
    }),
    cost() {
      let total =
        this.message !== null
          ? _.reduce(
              this.message,
              (sum, m) => {
                return sum + m.price;
              },
              0
            )
          : 0;

      return total;
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    status(code) {
      switch (code) {
        case "a":
          return "En Route";
          break;
        case "b":
          return "En Route";
          break;
        case "d":
          return "Delivered";
          break;
        case "e":
          return "Failed";
          break;
        case "f":
          return "Failed";
          break;
        case "j":
          return "Failed";
          break;
        case "q":
          return "En Route";
          break;
        case "r":
          return "En Route";
          break;
        case "s":
          return "En Route";
          break;
        case "u":
          return "Unknown";
          break;
      }
    }
  }
};
</script>
