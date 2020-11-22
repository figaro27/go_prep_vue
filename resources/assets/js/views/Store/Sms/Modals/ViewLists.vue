<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <v-client-table
        :columns="columns"
        :data="SMSListsTableData"
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
            v-if="!allSelected"
            class="btn btn-primary btn-md mb-2 mb-sm-0"
            @click="selectAll"
          >
            Select All
          </button>
          <button
            v-if="allSelected"
            class="btn btn-danger btn-md mb-2 mb-sm-0"
            @click="selectAll"
          >
            Deselect All
          </button>
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="$emit('insertList', selectedLists)"
            :disabled="selectedLists.length === 0"
          >
            Insert Lists
          </button>
        </div>

        <div slot="included" slot-scope="props">
          <b-form-checkbox
            v-model="props.row.included"
            type="checkbox"
            :value="true"
            :unchecked-value="false"
            @change="val => addToSelectedLists(props.row, val)"
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
      columns: ["included", "name", "membersCount"],
      selectedLists: [],
      allSelected: false
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
      SMSLists: "SMSLists"
    }),
    SMSListsTableData() {
      return this.SMSLists ? Object.values(this.SMSLists) : [];
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    addToSelectedLists(list, val) {
      if (val === true) {
        if (!this.selectedLists.includes(list)) {
          this.selectedLists.push(list);
        }
      } else {
        this.selectedLists.pop(list);
      }
    },
    selectAll() {
      this.allSelected = !this.allSelected;
      this.selectedLists = [];
      this.SMSLists.forEach(list => {
        if (this.allSelected) {
          list.included = true;
          this.selectedLists.push(list);
        } else {
          list.included = false;
          this.selectedLists.pop(list);
        }
      });
    }
  }
};
</script>
