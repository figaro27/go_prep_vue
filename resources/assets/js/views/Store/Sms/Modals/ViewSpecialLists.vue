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
                @change="updateConditionAmount()"
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
            Get all customers who haven't ordered in
            {{ conditionAmount }} or more days.
          </p>
          <p class="strong" v-if="conditionType === 'orders'">
            Get all customers who have only ordered {{ conditionAmount }} or
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
      columns: ["firstName", "lastName", "phone"],
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
    updateConditionAmount() {
      if (this.conditionType === "date") {
        this.conditionAmount = 2;
      }
      if (this.conditionType === "orders") {
        this.conditionAmount = 14;
      }
    },
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
