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
        <template-modal @setTemplate="setTemplate($event)"></template-modal>
      </b-modal>

      <b-btn variant="success" @click="showNewSMSArea = !showNewSMSArea"
        >Compose New SMS</b-btn
      >
      <b-btn variant="primary" @click="showSMSSettingsModal = true"
        >SMS Settings</b-btn
      >

      <div v-if="showNewSMSArea" class="newSMSArea mt-4">
        <b-form @submit.prevent="sendMessage()">
          <div>
            <div class="row">
              <div class="col-md-2">
                <h5 class="pull-right pt-3">To</h5>
              </div>
              <div class="col-md-7">
                <b-form-textarea
                  class="m-2"
                  style="overflow:auto;resize:both"
                  v-model="contacts"
                  placeholder="Type comma separated numbers or choose contacts or lists on the right."
                  rows="3"
                ></b-form-textarea>
                <p class="pull-right">Recipients: {{ recipientCount }}</p>
              </div>
              <div class="col-md-3 pt-3">
                <div class="d-flex">
                  <i
                    class="fas fa-user d-inline pr-1 pt-1"
                    style="color:#737373"
                  ></i>
                  <p class="d-inline"><u>Insert contact</u></p>
                </div>
                <div class="d-flex">
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
                <h5 class="pull-right pt-3">Message</h5>
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
                  <strong>{{ message.content.length }}/918</strong> | Parts:
                  <strong>{{ messageParts }}/6</strong> | Cost:
                  <strong>{{
                    format.money(messageCost, this.store.settings.currency)
                  }}</strong
                  ><img
                    v-b-popover.hover="
                      'The number of recipients times the number of parts the text will be sent in (160 characters per part) times 6 cents. Your GoPrep account will be charged via Stripe.'
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
        :data="tableData"
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
          <button class="btn btn-danger btn-sm" @click="destroy(props.row.id)">
            Delete
          </button>
        </div>
      </v-client-table>
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
import TemplateModal from "./TemplateModal.vue";

export default {
  components: {
    Spinner,
    vSelect,
    TemplateModal
  },
  mixins: [checkDateRange],
  data() {
    return {
      showNewSMSArea: false,
      showSMSSettingsModal: false,
      showTagDropdown: false,
      showTemplateModal: false,
      message: {
        content: ""
      },
      tableData: [],
      columns: ["messageTime", "text", "actions"],
      recipientCount: 1
    };
  },
  created() {},
  mounted() {
    this.refreshTable();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    }),
    tags() {
      return ["First name", "Last name", "Company name", "Phone", "Email"];
    },
    messageCost() {
      return this.messageParts * 0.06 * this.recipientCount;
    },
    messageParts() {
      return Math.ceil(this.message.content.length / 160);
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    },
    refreshTable() {
      axios.get("/api/me/SMSMessages").then(resp => {
        this.tableData = resp.data;
      });
    },
    addTag(tag) {
      this.message.content += "{" + tag + "}";
    },
    setTemplate(content) {
      this.message.content = content;
      this.showTemplateModal = false;
    },
    sendMessage() {
      axios
        .post("/api/me/SMSMessages", {
          message: this.message.content,
          listId: this.listId,
          charge: this.messageCost
        })
        .then(resp => {
          this.refreshTable();
          this.$toastr.s("SMS has been sent.", "Success");
        });
    }
  }
};
</script>
