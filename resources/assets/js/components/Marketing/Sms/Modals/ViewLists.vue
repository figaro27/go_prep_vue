<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

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
        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-success btn-sm"
            @click="$emit('insertList', props.row)"
          >
            Insert List
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
      tableData: [],
      columns: ["name", "membersCount", "actions"],
      listId: null
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
    formatMoney: format.money
  }
};
</script>
