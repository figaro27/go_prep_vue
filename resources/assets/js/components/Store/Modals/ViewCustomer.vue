<template>
    <div>
      <b-modal title="Customer" v-model="viewCustomerModal" v-if="viewCustomerModal" @hide="resetUserId" @hidden="toggleModalVisibility">
        <ul>
          <li class="my-4">Name: {{ user.user_detail.firstname }} {{ user.user_detail.lastname }}</li>
          <li class="my-4">Email: {{ user.email }}</li>
          <li class="my-4">Customer Since: {{ user.created_at }}</li>
          <li class="my-4">Address: {{ user.user_detail.address }}</li>
          <li class="my-4">City: {{ user.user_detail.city }}</li>
          <li class="my-4">State: {{ user.user_detail.state }}</li>
          <li class="my-4">Phone: {{ user.user_detail.phone }}</li>
          <li class="my-4">Delivery Instructions: {{ user.user_detail.delivery }}</li>
          <li class="my-4" v-for="order in orders">
            <ul>
              <li>Order ID: {{ order.id }}</li>
              <li>Total: {{ order.amount }}</li>
              <li>Date: {{ order.created_at }}</li>
            </ul>
          </li>
        </ul>
      </b-modal>


    </div>
</template>

<script>
export default {
  props: ['userId'],
  data () {
    return {
      viewCustomerModal: false,
      user: {},
      orders: []
    }
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
      axios.get('/user/' + viewUserId).then(
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

