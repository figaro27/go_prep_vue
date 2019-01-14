<style>

.modalMeal{
  height:80px;
  width:80px;
}

</style>

<template>
  <div class="store-customer-container">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading"/>
        <div class="card">
          <!-- <ViewCustomer :userId="userId" :order="order"></ViewCustomer> -->
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
              v-show="!isLoading"
            >
              <div slot="actions" class="text-nowrap" slot-scope="props">
                <!-- <button class="btn btn-primary btn-sm" @click="userId = props.row.id">View</button> -->
                <button class="btn btn-primary btn-sm" @click="viewCustomer(props.row.id)">View Customer</button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>




    <div class="modal-basic">
      <b-modal size="lg" title="Customer Details" v-model="viewCustomerModal" v-if="viewCustomerModal">
        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ customer.user_detail.firstname }} {{ customer.user_detail.lastname }}</p>
 
            <h4>Phone</h4>
            <p>{{ customer.user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>{{ customer.user_detail.address }}</p>
            <p>{{ customer.user_detail.city }}, {{ customer.user_detail.state }}</p>
            <p>{{ customer.user_detail.zip }}</p>
          </div>
          <div class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ customer.user_detail.delivery }}</p>
          </div>
        </div>

        <hr/>
        <div v-for="order in orders" :key="order.id">
        <div v-b-toggle="'collapse' + order.id">
          
          <b-list-group-item>
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

              <!-- Need to fix this bug & get meal quantities -->
          <b-collapse :id="'collapse' + order.id" class="mt-2">
            <b-card>
              <p class="card-text">
                <div v-for="meal in meals" :key="meal.id">
                    <img :src="meal.meals[meal.id].featured_image" class="modalMeal">
                    {{ meal.meals[meal.id].title }}
                    ${{ meal.meals[meal.id].price }}
                </div>
              </p>
            </b-card>
          </b-collapse>
          </b-list-group-item>
        
        </div>
        
      </div>
      </b-modal>
    </div>






  </div>
</template>

<style>
</style>
<script>
import Spinner from "../../components/Spinner";
// import ViewCustomer from "./Modals/ViewCustomer";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner,
    // ViewCustomer
  },
  data() {
    return {
      viewCustomerModal: false,
      userId: '',
      editUserId: "",
      id: "",
      customer: [],
      orders: [],
      columns: [
        "Name",
        "phone",
        "address",
        "city",
        "state",
        "Joined",
        "TotalPayments",
        "TotalPaid",
        "LastOrder",
        "actions"
      ],
      options: {
        headings: {
          LastOrder: "Last Order",
          TotalPayments: "Total Orders",
          TotalPaid: "Total Paid",
          Name: "Name",
          phone: "Phone",
          address: "Address",
          city: "City",
          state: "State",
          Joined: "Customer Since",
          actions: "Actions"
        },
        customSorting: {
          TotalPaid: function(ascending) {
            return function(a, b) {
              var numA = parseInt(a.TotalPaid);
              var numB = parseInt(b.TotalPaid);
              if (ascending) return numA >= numB ? 1 : -1;
              return numA <= numB ? 1 : -1;
            };
          }
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      customers: "storeCustomers",
      meals_order: "storeOrders",
      isLoading: "isLoading"
    }),
    tableData() {
      return Object.values(this.customers);
    },
    meals(){
      return _.filter(this.meals_order, {'user_id': this.userId});
    }
  },
  created() {},
  mounted() {

  },
  methods: {
    resetUserId() {
      this.userId = 0;
      this.editUserId = 0;
    },
  viewCustomer(id){
    this.userId = id;
    this.viewCustomerModal = true;
    axios.get(`/api/me/customers/${id}`).then(response => {
      this.customer = response.data;
      this.orders = response.data.order;
    });
  },
  }
};
</script>