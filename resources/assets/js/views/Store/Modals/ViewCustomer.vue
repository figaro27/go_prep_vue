

<template>
    <div class="modal-basic">
      <b-modal size="lg" title="Customer Details" v-model="viewCustomerModal" v-if="viewCustomerModal" @hide="resetUserId" @hidden="toggleModalVisibility">

        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ user.user_detail.firstname }} {{ user.user_detail.lastname }}</p>
 
            <h4>Phone</h4>
            <p>{{ user.user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>{{ user.user_detail.address }}</p>
            <p>{{ user.user_detail.city }}, {{ user.user_detail.state }}</p>
            <p>{{ user.user_detail.zip }}</p>
          </div>
          <div class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ user.user_detail.delivery }}</p>
          </div>
        </div>

        <hr/>
        <div v-for="order in orders" :key="order.id">
        <div v-b-toggle="'replace'">
          
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
          </b-list-group-item>
        
        </div>
        <b-collapse id="replace" class="mt-2">
          <b-card>
            <p class="card-text">
              Meals Info
            </p>
          </b-card>
        </b-collapse>
      </div>
      </b-modal>
    </div>









</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  props: ['userId'],
  data () {
    return {
      viewCustomerModal: false,
      user: {},
      orders: [],
      test: '1'
    }
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
    }),
  },
  methods: {
      resetUserId(){
        this.$parent.resetUserId();
      },
      toggleModalVisibility(){
        this.viewCustomerModal = false;
      }
    },
    watch: {
    userId: function(viewUserId) {
      axios.get('/api/me/customers/' + viewUserId).then(
          response => {
              this.user = response.data;
              this.orders = response.data.order;
              this.viewCustomerModal = true
          }
        );
        
      }
    }
}
</script>

