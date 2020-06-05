<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-card no-body>
        <b-tabs>
          <b-tab title="Messages">
            <messages></messages>
          </b-tab>
          <b-tab>
            <template v-slot:title>
              <b-spinner
                type="grow"
                small
                variant="primary"
                v-if="unreadSMSMessages"
              ></b-spinner
              >Chats
              <!-- <span class="badge badge-primary" v-if="unreadSMSMessages">{{ unreadSMSMessages }}</span>Chats -->
            </template>
            <chats></chats>
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
      </b-card>
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
      initialized: "initialized",
      SMSChats: "SMSChats"
    }),
    unreadSMSMessages() {
      return this.SMSChats.length > 0
        ? _.reduce(
            this.SMSChats,
            (sum, chat) => {
              if (chat.unread === true) {
                return sum + 1;
              }
            },
            0
          )
        : 0;
    }
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
