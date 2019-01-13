<style lang="scss">
.meal-quantities {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  margin-left: -5px;
  margin-right: -5px;

  li {
    flex: 0 1 calc(50% - 10px);
    margin-left: 5px;
    margin-right: 5px;
    margin-bottom: 5px;
    min-height: 2em;
  }
}


.modalMeal{
  height:80px;
  width:80px;
}

.order-quantity{
  font-size:17px;
  color:#838181;
  position:relative;
  top:2px;
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
            <div slot="beforeTable" class="mb-2">
              <button @click="filterNotes" class="btn btn-primary">Filter Notes</button>
            </div>

            <div slot="notes" class="text-nowrap" slot-scope="props">
              <p v-if="props.row.has_notes">
                <img src="/images/store/note.png">
              </p>
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button class="btn btn-warning btn-sm" @click="viewOrder(props.row.id)">View Order</button>
              <button
                class="btn btn-primary btn-sm"
                @click="fulfill(props.row.id)"
              >Mark As Fulfilled</button>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div class="modal-basic">
      <b-modal v-model="viewOrderModal" size="lg" title="Order Information">
        <div class="row">
          <div class="col-md-4">
            <h4>Order ID</h4>
            <p>{{ order.order_number }}</p>
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ order.created_at }}</p>
          </div>
          <div class="col-md-4">
            <h2>${{ order.amount }}</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <hr>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ user_detail.firstname }} {{ user_detail.lastname }}</p>

            <h4>Phone</h4>
            <p>{{ user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>{{ user_detail.address }}</p>
            <p>{{ user_detail.city }}, {{ user_detail.state }}</p>
            <p>{{ user_detail.zip }}</p>
          </div>
          <div class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ user_detail.delivery }}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Delivery Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="deliveryNote"
              placeholder="E.G. Customer didn't answer phone or doorbell."
            ></textarea>
            <button class="btn btn-primary btn-md pull-right mt-2" @click="saveNotes(orderId)">Save</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Meals</h4>
            <hr>
            <ul class="meal-quantities">
              <li v-for="(order) in getMealQuantities(meals)"><span class="order-quantity">{{order.order}}</span> <img src="/images/store/x-modal.png"> 
                  <img :src="order.featured_image" class="modalMeal"> 
                    {{order.title}}
                    ${{order.price * order.order}}
              </li>
            </ul>
          </div>
        </div>
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
      filter: false,
      viewOrderModal: false,
      order: {},
      orderId: "",
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
          notes: "Notes",
          order_number: "Order #",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Order Placed",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          return classes;
        }
      },
      deliveryNote: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading"
    }),
    tableData() {
      if (!this.filter) return _.filter(this.orders, { fulfilled: 0 });
      else return _.filter(this.orders, { fulfilled: 0, has_notes: true });
    }
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders"
    }),
    refreshTable() {
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
    fulfill(id) {
      $(".order-" + id).fadeOut(2000);
      axios
        .patch(`/api/me/orders/${id}`, {
          fulfilled: 1
        })
        .then(resp => {
          this.refreshTable();
        });
    },
    saveNotes(id) {
      let deliveryNote = deliveryNote;
      axios
        .patch(`/api/me/orders/${id}`, { notes: this.deliveryNote })
        .then(resp => {
          this.refreshTable();
        });
    },
    getMealQuantities(meals) {

      let order = _.toArray(_.countBy(meals, 'id'));

      return order.map((order, id) => { return { 
        order, 
        featured_image: meals[id].featured_image,
        title: meals[id].title,
        price: meals[id].price
      }})

    },
    viewOrder(id) {
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
    filterNotes() {
      this.filter = !this.filter;
      this.refreshTable();
    }
  }
};
</script>