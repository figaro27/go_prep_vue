<template>
  <div class="row mt-2">
    <div class="col-md-12">
      <!-- <p class="small" v-if="smsSettings.aboveFiftyContacts">
        Contacts take about 10 seconds to load per 50. Thanks for your patience.
      </p> -->
      <b-form-radio-group
        buttons
        v-model="page"
        class="storeFilters pb-3"
        :options="[
          { value: 'contacts', text: 'Contacts' },
          { value: 'lists', text: 'Lists' }
        ]"
      ></b-form-radio-group>
    </div>
    <div class="col-md-12" v-if="page == 'contacts'">
      <div>
        <img
          v-b-popover.rightbottom.hover="
            'Contacts are recipients you can send a message to along with their names so you can personalize the message to them using tags. By default, all past customers are added to your contacts. New customers will be automatically added as a contact as long as you keep that enabled in Settings.'
          "
          title="Contacts"
          src="/images/store/popover.png"
          class="popover-size mb-3"
        />
      </div>
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
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

      <button
        class="btn btn-success btn-md mb-2 mb-sm-0"
        @click="showCreateContactModal = true"
      >
        Add New Contact
      </button>

      <v-client-table
        v-show="initialized"
        :columns="columns"
        :data="SMSContactsData"
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
        <div slot="firstName" class="text-nowrap" slot-scope="props">
          {{ getFirstName(props.row.id) }}
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
    <div class="col-md-12" v-if="page == 'lists'">
      <lists></lists>
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
import Lists from "./Lists.vue";

export default {
  props: {
    tabs: null
  },
  watch: {
    tabs(val) {
      if (val === 2 && !this.loaded) {
        this.refreshSMSContacts();
        this.refreshSMSLists();
        this.loaded = true;
      }
    }
  },
  components: {
    Spinner,
    vSelect,
    CreateContact,
    DeleteContact,
    EditContact,
    Lists
  },
  mixins: [checkDateRange],
  data() {
    return {
      loaded: false,
      page: "contacts",
      columns: ["firstName", "lastName", "phone", "actions"],
      showCreateContactModal: false,
      showEditContactModal: false,
      showDeleteContactModal: false,
      contact: null
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      SMSContacts: "SMSContacts",
      initialized: "initialized",
      smsSettings: "storeSMSSettings"
    }),
    SMSContactsData() {
      if (_.isArray(this.SMSContacts)) {
        return this.SMSContacts;
      } else {
        return [];
      }
    }
  },
  methods: {
    ...mapActions({
      refreshSMSContacts: "refreshSMSContacts",
      refreshSMSLists: "refreshSMSLists"
    }),
    formatMoney: format.money,
    refreshTable() {
      this.refreshSMSContacts();
    },
    addContact(contact) {
      axios.post("/api/me/SMSContacts", { contact: contact }).then(resp => {
        this.refreshTable();
        this.showCreateContactModal = false;
        this.$toastr.s("Contact Added.", "Success");
      });
    },
    editContact(contact) {
      if (contact.firstName === contact.phone) {
        contact.firstName = "";
      }
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
    },
    getFirstName(id) {
      let contact = this.SMSContacts.find(contact => {
        return contact.id === id;
      });
      return contact.firstName !== contact.phone ? contact.firstName : "";
    }
  }
};
</script>
