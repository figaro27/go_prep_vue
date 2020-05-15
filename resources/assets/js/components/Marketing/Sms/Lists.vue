<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="lg"
        title="Add New List"
        v-model="showCreateModal"
        v-if="showCreateModal"
        no-fade
        hide-footer
      >
        <b-form @submit.prevent="addList">
          <b-form-input
            v-model="list.name"
            placeholder="optional"
            label="List Name"
          ></b-form-input>
          <b-btn @click="selectAllCustomers">Select All Customers</b-btn>
          <ul>
            <li v-for="customer in customers">
              <b-form-checkbox @input="addCustomer(customer.id)">{{
                customer.name
              }}</b-form-checkbox>
            </li>
          </ul>
          <b-button type="submit" variant="primary">Save</b-button>
        </b-form>
      </b-modal>
      <b-modal
        size="md"
        title="Delete List"
        v-model="showDeleteModal"
        v-if="showDeleteModal"
        hide-header
        hide-footer
        no-fade
      >
        <h5 class="center-text p-2 mt-2">
          Are you sure you want to delete this list?
        </h5>
        <div class="d-flex pt-2" style="justify-content:center">
          <b-btn
            class="d-inline mr-2"
            variant="secondary"
            @click="(showDeleteModal = false), (listId = null)"
            >Cancel</b-btn
          >
          <b-btn class="d-inline" variant="danger" @click="destroy(listId)"
            >Delete</b-btn
          >
        </div>
      </b-modal>

      <b-modal
        size="md"
        title="View"
        v-model="showViewModal"
        v-if="showViewModal"
        no-fade
        hide-footer
      >
        <p v-if="list.name">Name: {{ list.name }}</p>
        <p v-if="list.membersCount">Contacts: {{ list.membersCount }}</p>

        <p v-for="contact in contacts">
          {{ contact.firstName }} {{ contact.lastName }} {{ contact.phone }}
        </p>
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
            membersCount: 'Contacts'
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="showCreateModal = true"
          >
            Add New List
          </button>
        </div>
        <div slot="content" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.content, 150, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-warning btn-sm"
            @click="view(props.row.id)"
          >
            View
          </button>
          <button
            class="btn btn-danger btn-sm"
            @click="(showDeleteModal = true), (listId = props.row.id)"
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

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      tableData: [],
      columns: ["name", "membersCount", "actions"],
      showViewModal: false,
      showCreateModal: false,
      showDeleteModal: false,
      list: {},
      contacts: {},
      listId: null,
      selectedCustomers: []
    };
  },
  created() {},
  mounted() {
    axios.get("/api/me/SMSLists").then(resp => {
      this.tableData = resp.data;
    });
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    },
    addList() {
      axios
        .post("/api/me/SMSLists", {
          name: this.list.name,
          customers: this.selectedCustomers
        })
        .then(resp => {
          this.$toastr.s("New list has been saved.", "Success");
          this.list = {};
          this.selectedCustomers = [];
          this.refreshTable();
          this.showCreateModal = false;
        });
    },
    view(id) {
      axios.get("/api/me/SMSLists/" + id).then(resp => {
        this.list = resp.data;
      });
      this.getContacts(id);
    },
    getContacts(id) {
      axios.post("/api/me/SMSContacts", { id: id }).then(response => {
        this.contacts = response.data.resources;
      });
      this.showViewModal = true;
    },
    destroy(id) {
      axios.delete("/api/me/SMSLists/" + id).then(resp => {
        this.refreshTable();
        this.showDeleteModal = false;
        this.$toastr.s("List has been deleted.", "Success");
      });
    },
    refreshTable() {
      axios.get("/api/me/SMSLists").then(resp => {
        this.tableData = resp.data;
      });
    },
    addCustomer(id) {
      // This won't work if unchecked. Change front end.
      this.selectedCustomers.push(id);
    },
    selectAllCustomers() {
      if ((this.selectedCustomers = [])) {
        this.customers.forEach(customer => {
          this.selectedCustomers.push(true);
        });
      } else {
        this.selectedCustomers = [];
      }
    }
  }
};
</script>
