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
            firstName: 'First Name',
            lastName: 'Last Name'
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="$emit('insertContacts', selectedContacts)"
          >
            Insert Contacts
          </button>
        </div>

        <div slot="included" slot-scope="props">
          <b-form-checkbox
            type="checkbox"
            :value="true"
            :unchecked-value="false"
            @change="val => addToSelectedContacts(props.row, val)"
          ></b-form-checkbox>
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
      columns: ["included", "firstName", "lastName", "phone"],
      selectedContacts: []
    };
  },
  created() {},
  mounted() {
    axios.get("/api/me/SMSContacts").then(resp => {
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
    addToSelectedContacts(contact, val) {
      if (val === true) {
        this.selectedContacts.push(contact);
      } else {
        this.selectedContacts.pop(contact);
      }
    }
  }
};
</script>
