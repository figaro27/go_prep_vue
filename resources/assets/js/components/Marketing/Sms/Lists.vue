<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-modal
        size="xl"
        title="Create List"
        v-model="showCreateListModal"
        v-if="showCreateListModal"
        no-fade
        hide-footer
      >
        <create-list></create-list>
      </b-modal>

      <b-modal
        size="xl"
        title="Edit List"
        v-model="showEditListModal"
        v-if="showEditListModal"
        no-fade
        hide-footer
      >
        <edit-list></edit-list>
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
            class="btn view btn-warning btn-sm"
            @click="edit(props.row.id)"
          >
            Edit
          </button>
          <button
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
      tableData: [],
      columns: ["name", "membersCount", "actions"],
      showEditListModal: false,
      showCreateListModal: false,
      showDeleteListModal: false,
      list: {},
      contacts: {}
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
          this.refreshTable();
        });
    },
    edit(id) {
      this.showEditListModal = true;
      axios.get("/api/me/SMSLists/" + id).then(resp => {
        this.list = resp.data;
      });
    },
    deleteList(id) {
      this.list.id = id;
      this.showDeleteListModal = true;
    },
    destroy(id) {
      axios.delete("/api/me/SMSLists/" + id).then(resp => {
        this.refreshTable();
        this.$toastr.s("List has been deleted.", "Success");
      });
    },
    refreshTable() {
      axios.get("/api/me/SMSLists").then(resp => {
        this.tableData = resp.data;
      });
    }
  }
};
</script>
