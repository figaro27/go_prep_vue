<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-form-radio-group
        buttons
        v-model="pageView"
        class="smsFilters"
        :options="[
          { value: 'messages', text: 'Messages' },
          { value: 'contacts', text: 'Contacts' },
          { value: 'lists', text: 'Lists' },
          { value: 'settings', text: 'Settings' }
        ]"
      ></b-form-radio-group>
      <messages v-if="pageView === 'messages'"></messages>
      <contacts v-if="pageView === 'contacts'"></contacts>
      <lists v-if="pageView === 'lists'"></lists>
      <settings v-if="pageView === 'settings'"></settings>
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
import Contacts from "./Sms/Contacts.vue";
import Lists from "./Sms/Lists.vue";
import Settings from "./Sms/Settings.vue";

export default {
  components: {
    Spinner,
    vSelect,
    Messages,
    Contacts,
    Lists,
    Settings
  },
  mixins: [checkDateRange],
  data() {
    return {
      pageView: "messages"
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
