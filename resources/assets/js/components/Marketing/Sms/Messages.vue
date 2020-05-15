<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        size="md"
        title="Add New Message"
        v-model="showCreateModal"
        v-if="showCreateModal"
        no-fade
        hide-footer
      >
        Test
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
          headings: {},
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="showCreateModal = true"
          >
            Add New Message
          </button>
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
      columns: ["test", "actions"],
      showViewModal: false,
      showCreateModal: false
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    }),
    tableData() {
      return [{ test: "test" }];
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    view(id) {
      this.showViewModal = true;
    },
    destroy(id) {}
  }
};
</script>
