<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-tabs>
        <b-tab title="Chats">
          <chats></chats>
        </b-tab>
        <b-tab title="Messages">
          <messages></messages>
        </b-tab>
        <b-tab title="Contacts">
          <contacts></contacts>
        </b-tab>
        <b-tab title="Lists">
          <lists></lists>
        </b-tab>
        <b-tab title="Settings">
          <settings></settings>
        </b-tab>
      </b-tabs>
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
import Messages from "./Sms/Messages.vue";
import Chats from "./Sms/Chats.vue";
import Contacts from "./Sms/Contacts.vue";
import Lists from "./Sms/Lists.vue";
import Settings from "./Sms/Settings.vue";

export default {
  components: {
    Spinner,
    vSelect,
    Messages,
    Chats,
    Contacts,
    Lists,
    Settings
  },
  mixins: [checkDateRange],
  data() {
    return {
      pageView: "chats"
    };
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
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    }
  }
};
</script>

<style>
.VueTables__search {
  display: none !important;
}
</style>
