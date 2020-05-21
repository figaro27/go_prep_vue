<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="xl"
        title="Create Contact"
        v-model="showCreateContactModal"
        v-if="showCreateContactModal"
        no-fade
        hide-footer
      >
        <create-contact @addContact="addContact($event)"></create-contact>
      </b-modal>

      <b-modal
        size="md"
        title="Delete Contact"
        v-model="showDeleteContactModal"
        v-if="showDeleteContactModal"
        no-fade
        hide-footer
      >
        <delete-contact
          @cancel="showDeleteContactModal = false"
          @deleteContact="destroy($event)"
        ></delete-contact>
      </b-modal>

      <b-modal
        size="md"
        title="Edit Contact"
        v-model="showEditContactModal"
        v-if="showEditContactModal"
        no-fade
        hide-footer
      >
        <edit-contact
          :contact="contact"
          @updateContact="updateContact($event)"
        ></edit-contact>
      </b-modal>

      <b-btn @click="importCustomers">Import Customers Temp</b-btn>
      <v-client-table
        :columns="columns"
        :data="tableData"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            firstName: 'First Name',
            lastName: 'Last Name'
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="showCreateContactModal = true"
          >
            Add New Contact
          </button>
        </div>
        <div slot="content" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.content, 150, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-warning btn-sm"
            @click="editContact(props.row)"
          >
            Edit
          </button>
          <button
            class="btn btn-danger btn-sm"
            @click="deleteContact(props.row.id)"
          >
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
import CreateContact from "./Modals/CreateContact.vue";
import DeleteContact from "./Modals/DeleteContact.vue";
import EditContact from "./Modals/EditContact.vue";

export default {
  components: {
    Spinner,
    vSelect,
    CreateContact,
    DeleteContact,
    EditContact
  },
  mixins: [checkDateRange],
  data() {
    return {
      tableData: [],
      columns: ["firstName", "lastName", "phone", "actions"],
      showCreateContactModal: false,
      showEditContactModal: false,
      showDeleteContactModal: false,
      contact: null
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
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    refreshTable() {
      axios.get("/api/me/SMSContacts").then(resp => {
        this.tableData = resp.data;
      });
    },
    importCustomers() {
      axios.post("/api/me/SMSimportCustomers", {}).then(resp => {
        this.$toastr.s("Customers imported & added.", "Success");
        this.refreshTable();
      });
    },
    addContact(contact) {
      axios.post("/api/me/SMSContacts", { contact: contact }).then(resp => {
        this.refreshTable();
        this.showCreateContactModal = false;
        this.$toastr.s("Contact Added.", "Success");
      });
    },
    editContact(contact) {
      this.contact = contact;
      this.showEditContactModal = true;
    },
    updateContact(contact) {
      axios
        .post("/api/me/SMSContactUpdate", { contact: contact })
        .then(resp => {
          this.refreshTable();
          this.showEditContactModal = false;
          this.$toastr.s("Contact Updated.", "Success");
        });
    },
    deleteContact(id) {
      this.contact = id;
      this.showDeleteContactModal = true;
    },
    destroy() {
      axios.delete("/api/me/SMSContacts/" + this.contact).then(resp => {
        this.refreshTable();
        this.showDeleteContactModal = false;
        this.$toastr.s("Contact Deleted.", "Success");
      });
    }
  }
};
</script>
