<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <center>
        <div>
          <div class="row">
            <div class="col-md-5">
              <p class="strong pull-right">Condition Type</p>
            </div>
            <div class="col-md-5">
              <p class="strong">Condition Amount</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-5">
              <b-form-select
                :options="conditionOptions"
                v-model="conditionType"
                class="pull-right"
              ></b-form-select>
            </div>
            <div class="col-md-5">
              <b-form-input
                type="number"
                min="1"
                v-model="conditionAmount"
                style="width:100px"
              ></b-form-input>
            </div>
          </div>
          <br />
          <p class="strong" v-if="conditionType === 'date'">
            Insert all customers who haven't ordered in
            {{ conditionAmount }} days.
          </p>
          <p class="strong" v-if="conditionType === 'orders'">
            Insert all customers who have only ordered {{ conditionAmount }} or
            less times.
          </p>
          <b-btn variant="primary" @click="insertSpecialSMSContacts"
            >Fetch</b-btn
          >
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="$emit('insertSpecialContacts', specialContactsTableData)"
            :disabled="specialContactsTableData.length === 0"
          >
            Insert
          </button>
        </div>
      </center>
      <v-client-table
        :columns="columns"
        :data="specialContactsTableData"
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
      columns: ["included", "firstName", "lastName", "phone"],
      conditionType: "date",
      conditionAmount: 14,
      specialContactsTableData: []
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
    conditionOptions() {
      return [
        { value: "date", text: "Date" },
        { value: "orders", text: "Orders" }
      ];
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    insertSpecialSMSContacts() {
      this.specialContactsTableData = [];
      axios
        .post("/api/me/insertSpecialSMSContacts", {
          conditionType: this.conditionType,
          conditionAmount: this.conditionAmount
        })
        .then(resp => {
          this.specialContactsTableData = resp.data;
        });
    }
  }
};
</script>
