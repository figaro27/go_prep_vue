<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="xl"
        title="Add New Message"
        v-model="showCreateModal"
        v-if="showCreateModal"
        no-fade
        hide-footer
      >
        <b-form @submit.prevent="sendMessage()">
          <b-form-textarea
            class="flex-grow-1 m-2"
            v-model="message.content"
            placeholder="Enter SMS message content here."
            rows="3"
            max-rows="6"
          ></b-form-textarea>
          <b-form-input
            v-model="message.name"
            placeholder="Optional message template name"
          ></b-form-input>
          <b-btn variant="primary" @click="saveNewTemplate"
            >Save as Template</b-btn
          >
          <p @click="showTagDropdown = !showTagDropdown">Insert Tag</p>
          <div v-if="showTagDropdown">
            <div v-for="tag in tags" :key="tag">
              <p @click="addTag(tag)">{{ tag }}</p>
            </div>
          </div>
          <v-select
            label="text"
            :options="templateOptions"
            v-model="templateId"
            placeholder="Choose Existing Template"
            :reduce="template => template.value"
            @input="setTemplate"
            class="mb-3"
          >
          </v-select>
          <v-select
            label="text"
            :options="listOptions"
            v-model="listId"
            placeholder="Choose Contact List"
            :reduce="list => list.value"
            class="mb-3"
          >
          </v-select>
          <b-button type="submit" variant="primary">Send Message</b-button>
        </b-form>
      </b-modal>

      <b-modal
        size="md"
        title="View"
        v-model="showViewModal"
        v-if="showViewModal"
        no-fade
        hide-footer
      >
        Test
      </b-modal>
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
import Spinner from "../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../mixins/deliveryDates";
import format from "../../../lib/format";
import store from "../../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      tableData: [],
      columns: ["messageTime", "text", "actions"],
      showViewModal: false,
      showCreateModal: false,
      message: { content: "", name: "" },
      templateOptions: [],
      templateId: null,
      listOptions: [],
      listId: null,
      showTagDropdown: false
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
    addTag(tag) {
      this.message.content += "{" + tag + "}";
      this.showTagDropdown = false;
    },
    createSMS() {
      this.refreshTemplates();
      this.refreshLists();
      this.showCreateModal = true;
    },
    sendMessage() {
      axios
        .post("/api/me/SMSMessages", {
          message: this.message.content,
          // templateId: this.templateId,
          listId: this.listId
        })
        .then(resp => {
          this.$toastr.s("SMS has been sent.", "Success");
          this.refreshTable();
          this.showCreateModal = false;
        });
    },
    refreshTemplates() {
      axios.get("/api/me/SMSTemplates").then(resp => {
        resp.data.forEach(template => {
          this.templateOptions.push({
            text: template.name,
            value: template.id
          });
        });
      });
    },
    setTemplate() {
      axios.get("/api/me/SMSTemplates/" + this.templateId).then(resp => {
        this.message.content = resp.data.content;
      });
    },
    saveNewTemplate() {
      axios
        .post("/api/me/SMSTemplates", {
          content: this.message.content,
          name: this.message.name
        })
        .then(resp => {
          this.$toastr.s("New template has been saved.", "Success");
          this.refreshTemplates();
        });
    },
    refreshLists() {
      axios.get("/api/me/SMSLists").then(resp => {
        resp.data.forEach(list => {
          this.listOptions.push({
            text: list.name,
            value: list.id
          });
        });
      });
    },
    refreshTable() {
      axios.get("/api/me/SMSMessages").then(resp => {
        this.tableData = resp.data;
      });
    },
    view(id) {
      this.showViewModal = true;
    },
    destroy(id) {}
  }
};
</script>
