<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

      <b-button
        @click="$emit('updateList', { list: list, contacts: tableData })"
        variant="warning"
        >Update</b-button
      >

      <b-form-input v-model="list.name"></b-form-input>

      <v-client-table
        :columns="columns"
        :data="tableData"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            add: 'Add To List',
            firstName: 'First Name',
            lastName: 'Last Name'
          },
          filterable: false
        }"
      >
        <div slot="included" slot-scope="props">
          <b-form-checkbox
            v-model="props.row.included"
            type="checkbox"
            :value="true"
            :unchecked-value="false"
            @change="val => updateIncluded(props.row.id, val)"
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
      columns: ["included", "firstName", "lastName", "phone"]
    };
  },
  props: {
    list: null,
    tableData: []
  },
  created() {},
  mounted() {},
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
    updateIncluded(id, val) {
      this.tableData.forEach(row => {
        if (row.id === id) {
          row.included = val;
        }
      });
    }
  }
};
</script>
