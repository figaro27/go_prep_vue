<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <v-client-table
        :columns="columns"
        :data="SMSTemplates"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {},
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <h4 v-if="editingTemplate" class="center-text">Edit Template</h4>
          <button
            v-if="!editingTemplate"
            class="btn btn-success btn-md mb-2"
            @click="showTemplateArea = !showTemplateArea"
          >
            Add Template
          </button>
          <div v-if="showTemplateArea" class="pt-3 newTemplateArea">
            <div class="row">
              <div class="col-md-2">
                <h6 class="float-right pt-1 gray-text">Name</h6>
              </div>
              <div class="col-md-7">
                <b-form-input
                  v-model="template.name"
                  placeholder="Optional"
                ></b-form-input>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2">
                <h6 class="float-right pt-1 gray-text">Message</h6>
              </div>
              <div class="col-md-7">
                <b-form-textarea
                  class="mt-2"
                  style="overflow:auto;resize:both"
                  v-model="template.content"
                  rows="5"
                ></b-form-textarea>
                <b-button
                  v-if="!editingTemplate"
                  type="submit"
                  variant="primary"
                  class="float-right mt-2 mb-2"
                  @click="addTemplate()"
                  >Save</b-button
                >
                <b-button
                  v-if="editingTemplate"
                  type="submit"
                  variant="warning"
                  class="float-right mt-2 mb-2"
                  @click="update()"
                  >Update</b-button
                >
              </div>
              <div class="col-md-3">
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
          </div>
        </div>
        <div slot="content" class="text-nowrap" slot-scope="props">
          {{ truncate(props.row.content, 50, "...") }}
        </div>
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn btn-success btn-sm"
            @click="$emit('insertTemplate', props.row.content)"
          >
            Insert Template
          </button>
          <button
            class="btn btn-warning btn-sm"
            @click="edit(props.row.id, props.row.name, props.row.content)"
          >
            Edit
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
import Spinner from "../../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../../mixins/deliveryDates";
import format from "../../../../lib/format";
import store from "../../../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      columns: ["name", "content", "actions"],
      template: {
        id: null,
        content: ""
      },
      showTemplateArea: false,
      showTagDropdown: false,
      showViewModal: false,
      editingTemplate: false
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      SMSTemplates: "SMSTemplates"
    }),
    tags() {
      return ["First name", "Last name", "Company name", "Phone", "Email"];
    }
  },
  methods: {
    ...mapActions({
      refreshSMSTemplates: "refreshSMSTemplates"
    }),
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
          this.refreshSMSTemplates();
          this.$toastr.s("New template has been saved.", "Success");
          this.template = {};
          this.showCreateModal = false;
        });
    },
    edit(id, name, content) {
      this.template.id = id;
      this.template.name = name;
      this.template.content = content;
      this.editingTemplate = true;
      this.showTemplateArea = true;
    },
    update() {
      axios
        .put("/api/me/SMSTemplates/" + this.template.id, {
          content: this.template.content,
          name: this.template.name
        })
        .then(resp => {
          this.refreshSMSTemplates();
          this.$toastr.s("Template has been updated.", "Success");
          this.editingTemplate = false;
          this.showTemplateArea = false;
          this.template = { content: "" };
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
        this.refreshSMSTemplates();
        this.showDeleteModal = false;
        this.$toastr.s("Template has been deleted.", "Success");
      });
    },
    addTag(tag) {
      this.template.content += "{" + tag + "}";
      this.showTagDropdown = false;
    }
  }
};
</script>
