<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="lg"
        title="Add New Template"
        v-model="showCreateModal"
        v-if="showCreateModal"
        no-fade
        hide-footer
      >
        <b-form @submit.prevent="addTemplate">
          <b-form-input
            v-model="template.name"
            placeholder="optional"
            label="Template Name"
          ></b-form-input>
          <b-form-textarea
            v-model="template.content"
            label="Text"
          ></b-form-textarea>
          <b-button type="submit" variant="primary">Save</b-button>
        </b-form>
      </b-modal>
      <b-modal
        size="md"
        title="Delete Template"
        v-model="showDeleteModal"
        v-if="showDeleteModal"
        hide-header
        hide-footer
        no-fade
      >
        <h5 class="center-text p-2 mt-2">
          Are you sure you want to delete this template?
        </h5>
        <div class="d-flex pt-2" style="justify-content:center">
          <b-btn
            class="d-inline mr-2"
            variant="secondary"
            @click="(showDeleteModal = false), (templateId = null)"
            >Cancel</b-btn
          >
          <b-btn class="d-inline" variant="danger" @click="destroy(templateId)"
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
        <p v-if="template.name">Name: {{ template.name }}</p>
        <p v-if="template.content">Message: {{ template.content }}</p>
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
            content: 'Message'
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="showCreateModal = true"
          >
            Add New Template
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
            @click="(showDeleteModal = true), (templateId = props.row.id)"
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
      columns: ["name", "content", "actions"],
      showViewModal: false,
      showCreateModal: false,
      showDeleteModal: false,
      template: {},
      templateId: null
    };
  },
  created() {},
  mounted() {
    axios.get("/api/me/SMSTemplates").then(resp => {
      this.tableData = resp.data;
    });
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
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    },
    addTemplate() {
      let template = this.template;
      axios
        .post("/api/me/SMSTemplates", {
          name: template.name,
          content: template.content
        })
        .then(resp => {
          this.$toastr.s("New template has been saved.", "Success");
          this.template = {};
          this.refreshTable();
          this.showCreateModal = false;
        });
    },
    view(id) {
      axios.get("/api/me/SMSTemplates/" + id).then(resp => {
        this.template = resp.data;
      });
      this.showViewModal = true;
    },
    destroy(id) {
      axios.delete("/api/me/SMSTemplates/" + id).then(resp => {
        this.refreshTable();
        this.showDeleteModal = false;
        this.$toastr.s("Template has been deleted.", "Success");
      });
    },
    refreshTable() {
      axios.get("/api/me/SMSTemplates").then(resp => {
        this.tableData = resp.data;
      });
    }
  }
};
</script>
