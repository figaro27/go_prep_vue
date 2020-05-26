<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-modal
        size="xl"
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
        size="xl"
        title="Lists"
        v-model="showListModal"
        v-if="showListModal"
        no-fade
        hide-footer
      >
        <view-lists @insertList="insertList($event)"></view-lists>
      </b-modal>

      <b-modal
        size="xl"
        title="Contacts"
        v-model="showContactsModal"
        v-if="showContactsModal"
        no-fade
        hide-footer
      >
        <view-contacts @insertContacts="insertContacts($event)"></view-contacts>
      </b-modal>

      <b-btn variant="success" @click="showNewSMSArea = !showNewSMSArea"
        >Compose New SMS</b-btn
      >

      <div class="newSMSArea mt-4" v-if="showNewSMSArea">
        <b-form @submit.prevent="sendMessage()">
          <div>
            <div class="row">
              <div class="col-md-2">
                <h5 class="pull-right pt-3 gray-text">To</h5>
              </div>
              <div class="col-md-7">
                <b-form-input
                  class="m-2"
                  style="overflow:auto;resize:both"
                  placeholder="Type contact name or number."
                  rows="3"
                ></b-form-input>
                <div class="contact-area d-flex" style="flex-wrap:wrap">
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
                      'The number of recipients times the number of parts the text will be sent in (160 characters per part) times 5 cents. Your GoPrep account will be charged via Stripe.'
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
                  <div v-if="showTagDropdown" class="tagDropdown">
                    <li v-for="tag in tags" :key="tag" @click="addTag(tag)">
                      <p>{{ tag }}</p>
                      <hr />
                    </li>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-9">
                <b-button type="submit" variant="primary" class="pull-right"
                  >Send Message</b-button
                >
              </div>
            </div>
          </div>
        </b-form>
      </div>

      <v-client-table
        :columns="columns"
        :data="SMSMessages"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            messageTime: 'Sent On',
            text: 'Message'
          },
          filterable: false
        }"
      >
        <div slot="messageTime" slot-scope="props">
          {{ moment(props.row.messageTime).format("llll") }}
        </div>
        <div slot="text" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.text, 150, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-warning btn-sm"
            @click="view(props.row.id)"
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

export default {
  components: {
    Spinner,
    vSelect,
    ViewTemplates,
    ViewLists,
    ViewContacts
  },
  mixins: [checkDateRange],
  data() {
    return {
      showNewSMSArea: false,
      showSMSSettingsModal: false,
      showTagDropdown: false,
      showTemplateModal: false,
      showListModal: false,
      showContactsModal: false,
      message: {
        content: ""
      },
      // tableData: [],
      columns: ["messageTime", "text", "actions"],
      lists: [],
      contacts: []
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      SMSMessages: "SMSMessages",
      SMSContacts: "SMSContacts",
      SMSLists: "SMSLists"
    }),
    recipientCount() {
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
      return contacts + lists;
    },
    tags() {
      return ["First name", "Last name", "Company name", "Phone", "Email"];
    },
    messageCost() {
      return this.messageParts * 0.05 * this.recipientCount;
    },
    messageParts() {
      return Math.ceil(this.message.content.length / 160);
    }
  },
  methods: {
    ...mapActions({
      refreshSMSMessages: "refreshSMSMessages"
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
      this.message.content += "{" + tag + "}";
    },
    insertTemplate(content) {
      this.message.content = content;
      this.showTemplateModal = false;
    },
    sendMessage() {
      axios
        .post("/api/me/SMSMessages", {
          message: this.message.content,
          lists: this.lists,
          charge: this.messageCost
        })
        .then(resp => {
          this.refreshTable();
          this.$toastr.s("SMS has been sent.", "Success");
        });
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
        this.contacts.push(contact);
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
    }
  }
};
</script>
