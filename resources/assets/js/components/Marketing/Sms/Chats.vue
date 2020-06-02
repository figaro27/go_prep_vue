<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
        title="View Chat"
        v-model="showViewChatModal"
        v-if="showViewChatModal"
        no-fade
        hide-footer
      >
        <view-chat
          :chat="chat"
          :phone="phone"
          :row="row"
          @refreshChatMessage="refreshChatMessage($event)"
        ></view-chat>
      </b-modal>

      <v-client-table :columns="columns" :data="SMSChats" :options="options">
        <div slot="name" class="text-nowrap" slot-scope="props">
          {{ props.row.firstName }} {{ props.row.lastName }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button class="btn view btn-warning btn-sm" @click="view(props.row)">
            View
          </button>
        </div>
      </v-client-table>
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
import ViewChat from "./Modals/ViewChat.vue";

export default {
  components: {
    Spinner,
    vSelect,
    ViewChat
  },
  mixins: [checkDateRange],
  data() {
    return {
      showViewChatModal: false,
      chat: null,
      phone: null,
      row: null,
      columns: ["name", "phone", "lastMessage", "actions"],
      options: {
        headings: { lastMessage: "Last Message" },
        rowClassCallback: function(row) {
          let classes = `chat-${row.id}`;
          classes += row.unreadMessages ? " strong" : "";
          return classes;
        }
      }
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
    })
  },
  methods: {
    ...mapActions({
      refreshSMSChats: "refreshSMSChats"
    }),
    formatMoney: format.money,
    view(row) {
      this.phone = row.phone;
      this.row = row;
      this.refreshChatMessage(row);
    },
    refreshChatMessage(row) {
      axios
        .post("/api/me/getChatMessages", { phone: row.phone, id: row.id })
        .then(resp => {
          this.showViewChatModal = true;
          this.chat = resp.data.resources;
          let lastIncomingId = "";
          let lastOutgoingId = "";
          this.chat.forEach(text => {
            text.css = "";
            text.direction == "o"
              ? (text.css += "mine ")
              : (text.css += "yours ");
            text.css += "messages ";
            text.direction == "o" ? (lastOutgoingId = text.id) : null;
            text.direction == "i" ? (lastIncomingId = text.id) : null;
          });
          this.chat.forEach(text => {
            text.innerCSS = "message ";
            if (text.id === lastIncomingId || text.id === lastOutgoingId) {
              text.innerCSS += "last";
            }
          });
        });
    }
  }
};
</script>

<style lang="scss"></style>
