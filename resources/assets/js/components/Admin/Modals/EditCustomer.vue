<template>
    <div>
      <b-modal v-model="viewCustomerModal" title="Customer">
        <ul>
          <li class="my-4">First Name: <input v-model="user.user_detail.firstname"></input></li>
          <li class="my-4">Last Name: <input v-model="user.user_detail.lastname"></input></li>
          <li class="my-4">Email: <input v-model="user.email"></input></li>
          <li class="my-4">Customer Since: <input v-model="user.created_at"></input></li>
          <li class="my-4">Address: <input v-model="user.user_detail.address"></input></li>
          <li class="my-4">City: <input v-model="user.user_detail.city"></input></li>
          <li class="my-4">State: <input v-model="user.user_detail.state"></input></li>
          <li class="my-4">Phone: <input v-model="user.user_detail.phone"></input></li>
          <li class="my-4">Delivery Instructions: <input v-model="user.user_detail.delivery"></input></li>
          <li class="my-4" v-for="order in orders">
            <ul>
              <li>Order ID: <input v-model="order.id"></input></li>
              <li>Total: <input v-model="order.amount"></input></li>
              <li>Date: <input v-model="order.created_at"></input></li>
            </ul>
          </li>
        </ul>
        <button @click="updateUser(id)">Save</button>
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
      orders: [],
      id: ''
    }
  },
  methods: {
      updateUser: function(viewUserId){
        axios.put('/user/' + viewUserId, {
              user: this.user
            }
          );
      }
    },
    watch: {
    userId: function(viewUserId) {
      axios.get('/user/' + viewUserId).then(
          response => {
              this.user = response.data;
              this.orders = response.data.user_payment;
          }
        );
        this.viewCustomerModal = true
        this.id = viewUserId
      }
    }
}
</script>

