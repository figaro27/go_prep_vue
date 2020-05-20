<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

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
                <h5 class="float-right pt-3">To</h5>
              </div>
              <div class="col-md-7">
                <b-form-textarea
                  class="m-2"
                  style="overflow:auto;resize:both"
                  v-model="contacts"
                  placeholder="Type comma separated numbers or choose contacts or lists on the right."
                  rows="3"
                ></b-form-textarea>
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
                <h5 class="float-right pt-3">Message</h5>
              </div>
              <div class="col-md-7">
                <b-form-textarea
                  class="m-2"
                  style="overflow:auto;resize:both"
                  v-model="message"
                  placeholder="Type your message."
                  rows="6"
                ></b-form-textarea>
              </div>
              <div class="col-md-3 pt-3">
                <div class="d-flex">
                  <i
                    class="far fa-file-alt d-inline pr-1 pt-1"
                    style="color:#737373"
                  ></i>
                  <p class="d-inline"><u>Insert template</u></p>
                </div>
                <div class="d-flex">
                  <i
                    class="fas fa-tag d-inline pr-1 pt-1"
                    style="color:#737373"
                  ></i>
                  <p class="d-inline"><u>Insert tag</u></p>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-9">
                <b-button type="submit" variant="primary" class="float-right"
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

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      showNewSMSArea: false,
      showSMSSettingsModal: false,
      tableData: [],
      columns: ["messageTime", "text", "actions"]
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
      axios.get("/api/me/SMSMessages").then(resp => {
        this.tableData = resp.data;
      });
    }
  }
};
</script>
