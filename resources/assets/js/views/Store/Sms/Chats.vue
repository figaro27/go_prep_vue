<template>
  <div class="row mt-2">
    <div class="col-md-12">
      <div>
        <img
          v-b-popover.rightbottom.hover="
            'If a client responds to one of your messages, a chat begins and will show in the table below. You can view the chat and see the conversation history and send your reply.'
          "
          title="Chats"
          src="/images/store/popover.png"
          class="popover-size mb-3"
        />
      </div>
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
        title="View Chat"
        v-model="showViewChatModal"
        v-if="showViewChatModal"
        no-fade
        hide-footer
        @hide="enableSpinner(), (modalOpened = false)"
        id="viewChatModal"
      >
        <view-chat
          :chat="chat"
          :phone="phone"
          :row="row"
          @showChat="showChat($event)"
          @disableSpinner="disableSpinner"
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
      modalOpened: false,
      chat: null,
      phone: null,
      row: null,
      columns: ["name", "lastMessage", "actions"],
      options: {
        headings: { lastMessage: "Last Message" },
        rowClassCallback: function(row) {
          let classes = `chat-${row.id}`;
          classes += row.unread ? " strong" : "";
          return classes;
        },
        orderBy: {
          column: "updated_at",
          ascending: true
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
      refreshSMSChats: "refreshSMSChats",
      disableSpinner: "disableSpinner",
      enableSpinner: "enableSpinner"
    }),
    formatMoney: format.money,
    view(row) {
      this.phone = row.phone;
      this.row = row;
      this.showChat(row);
      this.modalOpened = true;
    },
    showChat(chat) {
      let chatId = chat.id;
      axios.get("/api/me/SMSChats/" + chatId).then(resp => {
        if (this.modalOpened) {
          this.showViewChatModal = true;
        }

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

        // Refresh chats
        this.refreshSMSChats();
      });
    }
  }
};
</script>
<style lang="scss"></style>
