<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-btn @click="sendTestMessage">Send Test Message</b-btn>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {};
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    sendTestMessage() {
      axios.get("/api/me/sendTestMessage");
    }
  }
};
</script>
