<template>
  <div class="row mt-3">
    <div class="col-md-12 pb-3">
      <Spinner v-if="isLoading" />
      <p>
        Please select an available phone number from the choices below.
        Activating this feature and choosing a phone number will charge your
        connected account $4.00 per month. This is a reimbursement for what we
        are charged monthly from our integrated service provided (TextMagic). We
        do not take a markup on this.
      </p>
      <p>
        There is a .05 cent charge per every 160 character message that gets
        sent out.
      </p>
      <p>
        Example: You send out a 300 character message to 50 recipients. 100
        texts will be sent out (50 recipients times 2 texts). .05 cents times
        100 texts will be a charge of $5.00.
      </p>

      <p>Available Numbers list</p>
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
    return {};
  },
  props: {},
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money
  }
};
</script>
