<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-btn variant="success" @click="$refs.messages.createSMS()"
        >Compose New SMS</b-btn
      >
      <messages ref="messages"></messages>
      <!-- <templates ref="templates"></templates>
        <lists ref="lists"></lists> -->
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
import Messages from "../../../components/Marketing/Sms/Messages";
import Templates from "../../../components/Marketing/Sms/Templates";
import Lists from "../../../components/Marketing/Sms/Lists";

export default {
  components: {
    Spinner,
    vSelect,
    Messages,
    Templates,
    Lists
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
