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
            <div slot="notes" class="text-nowrap" slot-scope="props">
              <p v-if="props.row.has_notes">!</p>
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn btn-primary btn-sm"
                @click="unfulfill(props.row.id)"
              >Mark As Unfulfilled</button>

              <button
                class="btn btn-success btn-sm"
                @click="viewOrder(props.row.id)">
                View
              </button>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>



    <div class="modal-full">
       <b-modal v-model="viewOrderModal">
        <b-row>
          <b-col>
            <h3>Name</h3>
            {{ user_detail.firstname }} {{ user_detail.lastname }}
            
            <h3>Address</h3>
            {{ user_detail.address }}
            <h3>Zip Code</h3>

            {{ user_detail.zip }}
            <h3>Phone</h3>

            {{ user_detail.phone }}
            <h3>Order Amount</h3>
            ${{ order.amount }}



             <h3>Delivery Instructions</h3>
                  {{ user_detail.delivery }}

              <h3>Delivery Notes</h3>
                  <textarea
                    type="text"
                    id="form7"
                    class="md-textarea form-control"
                    rows="3"
                    v-model="deliveryNote"
                  ></textarea>
                  <button class="btn btn-primary btn-sm" @click="saveNotes(orderId)">Save</button>

                <h3>Meals</h3>
                  <ul class="meal-quantities">
                    <li v-for="(quantity, meal_title) in getMealQuantities(meals)">{{ meal_title }} x {{quantity}}</li>
                  </ul>
          </b-col>
        </b-row>
      </b-modal>
    </div>
  </div>
</template>



<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner
  },

  data() {
    return {
      isLoading: false,
      viewOrderModal: false,
      order: {},
      orderId: '',
      user_detail: {},
      meals: {},
      columns: [
        "notes",
        "order_number",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        "user.user_detail.phone",
        "amount",
        "created_at",
        "actions"
      ],
      options: {
        headings: {
          "notes": "Notes",
          order_number: "Order #",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Order Placed",
          actions: "Actions"
        }
      },
      deliveryNote: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
    }),
    tableData() {
      return _.filter(this.orders, {'fulfilled': 1});
    }
  },
  mounted() {

  },
  methods: {
    ...mapActions({
      refreshOrders: 'refreshOrders',
    }),
    refreshTable(){
      this.refreshOrders();
    },
    formatMoney: format.money,
    syncEditables() {
      this.editing = _.keyBy({ ...this.tableData }, "id");
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, ["id", id]);
    },
    getTableDataById(id) {
      return _.find(this.tableData, ["id", id]);
    },
    unfulfill(id) {
      axios
        .patch(`/api/me/orders/${id}`, {
          fulfilled: 0
        })
        .then(resp => {
          this.refreshTable();
        });
      
    },
    saveNotes(id) {
      let deliveryNote = deliveryNote;
        axios.patch(`/api/me/orders/${id}`, 
          {notes: this.deliveryNote}
          )
        .then(resp => {
          this.refreshTable();
        });
    },
    getMealQuantities(meals) {
      return _.countBy(meals, 'title');
    },
    viewOrder(id){
      axios.get(`/api/me/orders/${id}`).then(response => {
        this.orderId = response.data.id;
        this.deliveryNote = response.data.notes;
        this.order = response.data;
        this.user_detail = response.data.user.user_detail;
        this.meals = response.data.meals;

        this.$nextTick(function() {
          window.dispatchEvent(new window.Event("resize"));
        });
      });
      this.viewOrderModal = true;
    },

  }
};
</script>