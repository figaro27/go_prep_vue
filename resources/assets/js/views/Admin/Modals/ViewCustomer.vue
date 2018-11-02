<template>
    <div>
      <b-modal size="lg" title="Customer" v-model="viewCustomerModal" v-if="viewCustomerModal" @hide="resetUserId" @hidden="toggleModalVisibility">
        <b-list-group>
          <b-list-group-item>Name: {{ user.user_detail.firstname }} {{ user.user_detail.lastname }}</b-list-group-item>
          <b-list-group-item>Email: {{ user.email }}</b-list-group-item>
          <b-list-group-item>Customer Since: {{ user.created_at }}</b-list-group-item>
          <b-list-group-item>Address: {{ user.user_detail.address }}</b-list-group-item>
          <b-list-group-item>City: {{ user.user_detail.city }}</b-list-group-item>
          <b-list-group-item>State: {{ user.user_detail.state }}</b-list-group-item>
          <b-list-group-item>Phone: {{ user.user_detail.phone }}</b-list-group-item>
          <b-list-group-item>Delivery Instructions: {{ user.user_detail.delivery }}</b-list-group-item>
        </b-list-group>
        <hr/>
        <b-list-group v-for="order in orders" :key="order.id">
              <b-list-group-item>Order ID: {{ order.id }}</b-list-group-item>
              <b-list-group-item>Total: {{ order.amount }}</b-list-group-item>
              <b-list-group-item>Date: {{ order.created_at }}</b-list-group-item>
        </b-list-group>

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

