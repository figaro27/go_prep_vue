<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading"/>
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="!isLoading"
          >
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn btn-primary btn-sm"
                @click="fulfill(props.row.id)"
              >Mark As Delivered</button>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>

            <div slot="child_row" slot-scope="props">
              <div class="row">
                <div class="col-3">
                  <h3>Delivery Instructions</h3>
                  {{ props.row.user.user_detail.delivery }}
                </div>
                <div class="col-3">
                  <h3>Delivery Notes</h3>
                  <textarea
                    type="text"
                    id="form7"
                    class="md-textarea form-control"
                    rows="3"
                    v-model="getTableDataById(props.row.id).notes"
                  ></textarea>
                  <button class="btn btn-primary btn-sm" @click="saveNotes(props.row.id)">Save</button>
                </div>
                <div class="col-6">
                  <h3>Meals</h3>
                  <ul class="meal-quantities">
                    <li v-for="(quantity, meal_title) in getMealQuantities(props.row.meals)">{{ meal_title }} x {{quantity}}</li>
                  </ul>
                </div>
              </div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
th:nth-child(3) {
  text-align: center;
}

.VueTables__child-row-toggler {
  width: 16px;
  height: 16px;
  line-height: 16px;
  display: block;
  margin: auto;
  text-align: center;
}

.VueTables__child-row-toggler--closed::before {
  content: "+";
}

.VueTables__child-row-toggler--open::before {
  content: "-";
}

.meal-quantities {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  margin-left: -5px;
  margin-right: -5px;

  li {
    flex: 0 1 calc(33% - 10px);
    margin-left: 5px;
    margin-right: 5px;
    margin-bottom: 5px;
    min-height: 2em;
  }
}
</style>


<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";

export default {
  components: {
    Spinner
  },
  data() {
    return {
      isLoading: true,
      columns: [
        "id",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        "user.user_detail.phone",
        "amount",
        "created_at",
        "actions"
      ],
      tableData: [],
      options: {
        headings: {
          id: "Order #",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Order Placed",
          actions: "Actions"
        }
      },
      orderID: "",
      notes: ""
    };
  },
  mounted() {
    this.getTableData();
  },
  methods: {
    formatMoney: format.money,
    getTableData() {
      let self = this;
      axios.get("/api/me/orders").then(function(response) {
        (self.tableData = response.data), (self.isLoading = false);
      });
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, ["id", id]);
    },
    getTableDataById(id) {
      return _.find(this.tableData, ["id", id]);
    },
    fulfill(id) {
      axios
        .patch(`/api/me/orders/${id}`, {
          fulfilled: 1
        })
        .then(resp => {
          this.getTableData();
        });
    },
    saveNotes(id) {
      let self = this;
      axios
        .patch(`/api/me/orders/${id}`, {
          notes: self.notes
        })
        .then(resp => {
          this.getTableData();
        });
    },
    getMealQuantities(meals) {
      return _.countBy(meals, 'title');
    },
  }
};
</script>