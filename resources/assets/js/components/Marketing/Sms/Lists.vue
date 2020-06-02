<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-modal
        size="md"
        title="Create List"
        v-model="showCreateListModal"
        v-if="showCreateListModal"
        no-fade
        hide-footer
      >
        <create-list @addList="addList($event)"></create-list>
      </b-modal>

      <b-modal
        size="md"
        title="Edit List"
        v-model="showEditListModal"
        v-if="showEditListModal"
        no-fade
        hide-footer
      >
        <edit-list
          :list="list"
          :tableData="contacts"
          @updateList="updateList($event)"
        ></edit-list>
      </b-modal>

      <b-modal
        size="md"
        title="Delete List"
        v-model="showDeleteListModal"
        v-if="showDeleteListModal"
        no-fade
        hide-footer
      >
        <delete-list
          @cancel="showDeleteListModal = false"
          @deleteList="destroy($event)"
        ></delete-list>
      </b-modal>

      <v-client-table
        :columns="columns"
        :data="SMSLists"
        :options="{
          orderBy: {
            column: 'created_at',
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
            @click="showCreateListModal = true"
          >
            Add New List
          </button>
        </div>
        <div slot="content" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.content, 150, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            v-if="!isAllContactsList(props.row.id)"
            class="btn view btn-warning btn-sm"
            @click="(list = props.row), (showEditListModal = true)"
          >
            Edit
          </button>
          <button
            v-if="!isAllContactsList(props.row.id)"
            class="btn btn-danger btn-sm"
            @click="deleteList(props.row.id)"
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
import CreateList from "./Modals/CreateList";
import EditList from "./Modals/EditList";
import DeleteList from "./Modals/DeleteList";

export default {
  components: {
    Spinner,
    vSelect,
    CreateList,
    EditList,
    DeleteList
  },
  mixins: [checkDateRange],
  data() {
    return {
      columns: ["name", "membersCount", "actions"],
      showEditListModal: false,
      showCreateListModal: false,
      showDeleteListModal: false,
      list: {},
      contacts: null
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      SMSLists: "SMSLists",
      SMSContacts: "SMSContacts"
    })
  },
  methods: {
    ...mapActions({
      refreshSMSLists: "refreshSMSLists"
    }),
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
          this.refreshTable();
        });
    },
    updateList(data) {
      let list = data.list;
      let includedContactIds = "";
      let allContactIds = "";
      data.contacts.forEach(contact => {
        if (contact.included) {
          includedContactIds = includedContactIds + contact.id + ",";
        }
      });
      this.SMSContacts.forEach(contact => {
        allContactIds = allContactIds + contact.id + ",";
      });
      axios
        .post("/api/me/updateList", {
          list: list,
          includedContactIds: includedContactIds,
          allContactIds: allContactIds
        })
        .then(resp => {
          this.refreshTable();
          this.showEditListModal = false;
          this.$toastr.s("List has been updated.", "Success");
        });
    },
    deleteList(id) {
      this.list.id = id;
      this.showDeleteListModal = true;
    },
    destroy() {
      axios.delete("/api/me/SMSLists/" + this.list.id).then(resp => {
        this.refreshTable();
        this.showDeleteListModal = false;
        this.$toastr.s("List has been deleted.", "Success");
      });
    },
    refreshTable() {
      this.refreshSMSLists();
    },
    addList(list) {
      axios.post("/api/me/SMSLists", { list: list }).then(resp => {
        this.refreshTable();
        this.showCreateListModal = false;
        this.$toastr.s("List has been added.", "Success");
      });
    },
    isAllContactsList(id) {
      if (this.SMSLists[0].id === id) {
        return true;
      } else {
        return false;
      }
    }
  }
};
</script>
