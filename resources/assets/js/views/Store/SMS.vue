<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <p>{{ hasUnreadSMSMessages }}</p>
      <p>{{ unreadSMSMessages }}</p>
      <Spinner v-if="isLoading" />
      <b-card no-body>
        <b-tabs>
          <b-tab title="Messages">
            <div class="badge badge-primary" v-if="hasUnreadSMSMessages">
              {{ unreadSMSMessages }} {{ chatsText }}
            </div>
            <messages></messages>
          </b-tab>
          <b-tab title="Chats">
            <div class="badge badge-primary" v-if="hasUnreadSMSMessages">
              {{ unreadSMSMessages }} {{ chatsText }}
            </div>
            <!-- <template v-slot:title>
              <span
                class="badge badge-primary unreadBadge"
                v-if="unreadSMSMessages"
                >{{ unreadSMSMessages }}</span
              >
              Chats
            </template> -->
            <chats></chats>
          </b-tab>
          <b-tab title="Contacts">
            <div class="badge badge-primary" v-if="hasUnreadSMSMessages">
              {{ unreadSMSMessages }} {{ chatsText }}
            </div>
            <contacts></contacts>
          </b-tab>
          <b-tab title="Lists">
            <div class="badge badge-primary" v-if="hasUnreadSMSMessages">
              {{ unreadSMSMessages }} {{ chatsText }}
            </div>
            <lists></lists>
          </b-tab>
          <b-tab title="Settings">
            <div class="badge badge-primary" v-if="hasUnreadSMSMessages">
              {{ unreadSMSMessages }} {{ chatsText }}
            </div>
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
    hasUnreadSMSMessages() {
      if (this.unreadSMSMessages && this.unreadSMSMessages.length > 0) {
        return true;
      } else {
        return false;
      }
    },
    unreadSMSMessages() {
      if (_.isArray(this.SMSChats)) {
        console.log(1);
        console.log(this.SMSChats.length);
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
      } else {
        console.log(2);
        return 0;
      }
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
.unreadBadge {
  width: 15px;
}
</style>
