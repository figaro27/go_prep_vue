<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-card no-body>
        <b-tabs v-model="tabs">
          <b-tab title="Messages">
            <messages :tabs="tabs"></messages>
          </b-tab>
          <b-tab title="Chats">
            <div class="badge badge-primary" v-if="unreadSMSMessages > 0"></div>
            <template v-slot:title>
              <span
                class="badge badge-primary unreadBadge"
                v-if="unreadSMSMessages"
                >{{ unreadSMSMessages }}</span
              >
              Chats
            </template>
            <chats :tabs="tabs"></chats>
          </b-tab>
          <b-tab title="Contacts">
            <contacts :tabs="tabs"></contacts>
          </b-tab>
          <b-tab title="Settings">
            <settings :tabs="tabs"></settings>
          </b-tab>

          <b-tab title="Billing">
            <billing :tabs="tabs"></billing>
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
import Billing from "./Sms/Billing.vue";

export default {
  components: {
    Spinner,
    vSelect,
    Messages,
    Chats,
    Contacts,
    Lists,
    Settings,
    Billing
  },
  mixins: [checkDateRange],
  data() {
    return {
      pageView: "chats",
      tabs: 0
    };
  },
  created() {},
  mounted() {
    // this.refreshSMS();
    this.refreshSMSMessages();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      SMSChats: "SMSChats"
    }),
    unreadSMSMessages() {
      let count = 0;
      if (_.isArray(this.SMSChats)) {
        this.SMSChats.forEach(chat => {
          if (chat.unread) {
            count++;
          }
        });
      }

      return count;
    },
    chatsText() {
      if (this.unreadSMSMessages === 1) {
        return "Unread Chat";
      } else {
        return "Unread Chats";
      }
    }
  },
  methods: {
    ...mapActions({
      refreshSMS: "refreshSMS",
      refreshSMSMessages: "refreshSMSMessages"
    }),
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
.unreadBadge {
  width: 15px;
}
</style>
