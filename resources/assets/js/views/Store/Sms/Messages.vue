<template>
  <div class="row mt-2">
    <div class="col-md-12">
      <div>
        <img
          v-b-popover.rightbottom.hover="
            'Here is where you can send a new custom SMS message to your clients. You can personalize the message by adding tags like their first name. The table below will then show you all messages you have sent in the past.'
          "
          title="Messages"
          src="/images/store/popover.png"
          class="popover-size mb-3"
        />
      </div>
      <Spinner v-if="isLoading" />

      <b-modal
        size="lg"
        title="Templates"
        v-model="showTemplateModal"
        v-if="showTemplateModal"
        no-fade
        hide-footer
      >
        <view-templates
          @insertTemplate="insertTemplate($event)"
        ></view-templates>
      </b-modal>

      <b-modal
        size="md"
        title="Lists"
        v-model="showListModal"
        v-if="showListModal"
        no-fade
        hide-footer
      >
        <view-lists @insertList="insertList($event)"></view-lists>
      </b-modal>

      <b-modal
        size="md"
        title="Activate"
        v-model="showActivateModal"
        v-if="showActivateModal"
        no-fade
        hide-footer
      >
        <activate @closeModal="showActivateModal = false"></activate>
      </b-modal>

      <b-modal
        size="md"
        title="Contacts"
        v-model="showContactsModal"
        v-if="showContactsModal"
        no-fade
        hide-footer
      >
        <view-contacts @insertContacts="insertContacts($event)"></view-contacts>
      </b-modal>

      <b-modal
        size="lg"
        title="Message"
        v-model="showMessageModal"
        v-if="showMessageModal"
        no-fade
        hide-footer
      >
        <view-message
          :message="selectedMessage"
          :recipients="recipients"
        ></view-message>
      </b-modal>

      <b-btn variant="success" @click="composeSMS">Compose New SMS</b-btn>

      <div class="newSMSArea mt-4" v-if="showNewSMSArea">
        <div>
          <div class="row">
            <div class="col-md-2">
              <h5 class="pull-right pt-3 gray-text">To</h5>
            </div>
            <div class="col-md-7">
              <div class="d-flex pt-3">
                <b-form-input
                  class="mr-1 d-inline"
                  placeholder="Type comma separated numbers or insert contacts or lists on the right."
                  v-model="phonesList"
                ></b-form-input>
                <button
                  @click="addPhones"
                  class="btn btn-primary btn-sm d-inline"
                  style="width:100px"
                >
                  Add
                </button>
              </div>
              <div class="d-flex mt-1" style="flex-wrap:wrap">
                <li v-for="phone in phones" class="d-inline">
                  <span
                    class="badge badge-warning mr-1"
                    @click="removePhone(phone)"
                  >
                    {{ phone }}
                  </span>
                </li>
                <li v-for="list in lists" class="d-inline">
                  <span
                    class="badge badge-primary mr-1"
                    @click="removeList(list)"
                  >
                    {{ list.name }}
                  </span>
                </li>
                <li v-for="contact in contacts">
                  <span
                    class="badge badge-success d-inline mr-1"
                    @click="removeContact(contact)"
                  >
                    {{ contact.firstName }} {{ contact.lastName }} (+{{
                      contact.phone
                    }})
                  </span>
                </li>
              </div>
              <p class="pull-right">Recipients: {{ recipientCount }}</p>
            </div>
            <div class="col-md-3 pt-3">
              <div
                class="d-flex"
                @click="showContactsModal = !showContactsModal"
              >
                <i
                  class="fas fa-user d-inline pr-1 pt-1"
                  style="color:#737373"
                ></i>
                <p class="d-inline"><u>Insert contact</u></p>
              </div>
              <div class="d-flex" @click="showListModal = !showListModal">
                <i
                  class="fas fa-users d-inline pr-1 pt-1"
                  style="color:#737373"
                ></i>
                <p class="d-inline"><u>Insert list</u></p>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-2">
              <h5 class="pull-right pt-3 gray-text">Message</h5>
            </div>
            <div class="col-md-7">
              <b-form-textarea
                class="m-2"
                style="overflow:auto;resize:both"
                v-model="message.content"
                placeholder="Type your message."
                rows="6"
                maxlength="918"
              ></b-form-textarea>
              <p class="pull-right">
                Characters:
                <span style="font-weight:bold;color:#6C6C6C"
                  >{{ message.content.length }}/918</span
                >
                | Parts:
                <span style="font-weight:bold;color:#6C6C6C"
                  >{{ messageParts }}/6</span
                >
                | Cost:
                <span style="font-weight:bold;color:#6C6C6C">{{
                  format.money(messageCost, this.store.settings.currency)
                }}</span
                ><img
                  v-b-popover.hover="
                    'The number of recipients times the number of parts the text will be sent in (160 characters per part) times .06 cents. Your GoPrep account will be charged via Stripe every time your balance reaches a $5.00 threshold. This applies to automatic texts sent out if you have them turned on in settings.'
                  "
                  title="Cost"
                  src="/images/store/popover.png"
                  class="popover-size-small ml-1 mb-1"
                />
              </p>
            </div>
            <div class="col-md-3 pt-3">
              <div
                class="d-flex"
                @click="showTemplateModal = !showTemplateModal"
              >
                <i
                  class="far fa-file-alt d-inline pr-1 pt-1"
                  style="color:#737373"
                ></i>
                <p class="d-inline"><u>Insert template</u></p>
              </div>
              <div class="d-flex" @click="showTagDropdown = !showTagDropdown">
                <i
                  class="fas fa-tag d-inline pr-1 pt-1"
                  style="color:#737373"
                ></i>
                <p class="d-inline"><u>Insert tag</u></p>
              </div>
              <div v-if="showTagDropdown" class="tagDropdown">
                <li v-for="tag in tags" :key="tag" @click="addTag(tag)">
                  <p>{{ tag }}</p>
                  <hr />
                </li>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-9">
              <b-button
                @click="sendMessage"
                variant="primary"
                class="pull-right"
                >Send Message</b-button
              >
            </div>
          </div>
        </div>
      </div>

      <v-client-table
        v-show="initialized"
        :columns="columns"
        :data="SMSMessagesData"
        :options="{
          orderBy: {
            column: 'messageTime',
            ascending: 'desc'
          },
          headings: {
            messageTime: 'Sent On',
            numbersCount: 'Recipients',
            text: 'Message'
          },
          filterable: false
        }"
      >
        <div slot="messageTime" slot-scope="props">
          {{ moment(props.row.messageTime).format("llll") }}
        </div>
        <div slot="text" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.text, 50, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-warning btn-sm"
            @click="showViewedMessage(props.row)"
          >
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
import ViewTemplates from "./Modals/ViewTemplates.vue";
import ViewLists from "./Modals/ViewLists.vue";
import ViewContacts from "./Modals/ViewContacts.vue";
import ViewMessage from "./Modals/ViewMessage.vue";
import Activate from "./Modals/Activate.vue";

export default {
  props: {
    tabs: null
  },
  watch: {
    tabs(val) {
      if (val === 0 && !this.loaded) {
        this.loaded = true;
      }
    }
  },
  components: {
    Spinner,
    vSelect,
    ViewTemplates,
    ViewLists,
    ViewContacts,
    ViewMessage,
    Activate
  },
  mixins: [checkDateRange],
  data() {
    return {
      recipients: [],
      loaded: false,
      showActivateModal: false,
      showNewSMSArea: false,
      showTagDropdown: false,
      showTemplateModal: false,
      showListModal: false,
      showContactsModal: false,
      showMessageModal: false,
      viewedMessage: null,
      message: {
        content: ""
      },
      // tableData: [],
      columns: ["messageTime", "numbersCount", "text", "actions"],
      lists: [],
      contacts: [],
      phonesList: "",
      phones: [],
      selectedContact: null
    };
  },
  created() {},
  mounted() {
    let host = this.store.details.host ? this.store.details.host : "goprep";
    this.message.content =
      "Order now at https://" +
      this.store.details.domain +
      "." +
      host +
      ".com. To opt out, reply STOP or UNSUBSCRIBE.";
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      SMSMessages: "SMSMessages",
      SMSContacts: "SMSContacts",
      SMSLists: "SMSLists",
      smsSettings: "storeSMSSettings",
      initialized: "initialized"
    }),
    SMSMessagesData() {
      if (_.isArray(this.SMSMessages)) {
        return this.SMSMessages;
      } else {
        return [];
      }
    },
    recipientCount() {
      let phones = this.phones.length;
      let contacts = this.contacts.length;
      let lists =
        this.lists.length > 0
          ? _.reduce(
              this.lists,
              (sum, list) => {
                return sum + list.membersCount;
              },
              0
            )
          : 0;
      return phones + contacts + lists;
    },
    tags() {
      return ["First name", "Last name", "Phone", "Email", "Unsubscribe"];
    },
    messageCost() {
      return this.messageParts * 0.06 * this.recipientCount;
    },
    messageParts() {
      return Math.ceil(this.message.content.length / 160);
    }
  },
  methods: {
    ...mapActions({
      refreshSMSMessages: "refreshSMSMessages",
      refreshSMSContacts: "refreshSMSContacts",
      refreshSMSLists: "refreshSMSLists"
    }),
    formatMoney: format.money,
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    },
    refreshTable() {
      this.refreshSMSMessages();
    },
    addTag(tag) {
      if (this.phones.length > 0) {
        this.$toastr.w(
          "You are sending this to phone numbers that may not be in your contacts. Tags like their first name will show blank to these recipients."
        );
      }
      tag == "Unsubscribe"
        ? (this.message.content += "To opt out, reply STOP or UNSUBSCRIBE.")
        : (this.message.content += "{" + tag + "}");
      this.showTagDropdown = false;
    },
    insertTemplate(content) {
      this.message.content = content;
      this.showTemplateModal = false;
    },
    sendMessage() {
      if (!this.smsSettings.phone) {
        this.showActivateModal = true;
        return;
      }
      if (
        this.phones.length == 0 &&
        this.contacts.length == 0 &&
        this.lists.length == 0
      ) {
        this.$toastr.w("Please add at least one recipient.");
        return;
      }
      if (this.message.content === "") {
        this.$toastr.w("Please add a message.");
        return;
      }
      axios
        .post("/api/me/SMSMessages", {
          message: this.message.content,
          lists: this.lists,
          contacts: this.contacts,
          phones: this.phones,
          charge: this.messageCost
        })
        .then(resp => {
          this.refreshTable();
          this.resetMessage();
          this.$toastr.s("SMS has been sent.", "Success");
        });
    },
    resetMessage() {
      this.message.content = "";
      this.lists = [];
      this.contacts = [];
      this.phones = [];
    },
    insertList(selectedLists) {
      this.lists = selectedLists;
      this.showListModal = false;
      this.SMSLists.forEach(list => {
        list.included = false;
      });
    },
    insertContacts(contacts) {
      contacts.forEach(contact => {
        if (!this.contacts.includes(contact)) {
          this.contacts.push(contact);
        }
      });
      this.showContactsModal = false;

      this.SMSContacts.forEach(contact => {
        contact.included = false;
      });
    },
    removeContact(contact) {
      let index = this.contacts.indexOf(contact);
      this.contacts.splice(index, 1);
    },
    removeList(list) {
      let index = this.contacts.indexOf(list);
      this.lists.splice(index, 1);
    },
    addContact() {
      this.contacts.push(this.selectedContact);
    },
    addPhones() {
      if (this.phonesList === "") {
        this.$toastr.w("Please add at least one phone number.");
        return;
      }
      this.phones = this.phonesList.split(",");
      this.phonesList = "";
    },
    removePhone(phone) {
      this.phones.pop(phone);
    },
    composeSMS() {
      this.showNewSMSArea = !this.showNewSMSArea;
      this.refreshSMSContacts();
      this.refreshSMSLists();
    },
    async showViewedMessage(message) {
      this.viewedMessage = message;
      await axios.get("/api/me/SMSMessages/" + message.id).then(resp => {
        this.recipients = resp.data;
        this.showMessageModal = true;
      });
    }
  }
};
</script>
