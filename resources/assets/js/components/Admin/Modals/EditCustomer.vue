<template>
    <div>
      <b-modal title="Customer" v-model="viewCustomerModal" v-if="viewCustomerModal" @hide="resetUserId" @hidden="toggleModalVisibility">
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
      resetUserId(){
        this.$parent.resetUserId();
      },
      toggleModalVisibility(){
        this.viewCustomerModal = false;
      },
      updateUser: function(viewUserId){
        axios.put('/user/' + viewUserId, {
              user: this.user
            }
          );
        this.$parent.getTableData();
      }
    },
    watch: {
    userId: function(viewUserId) {
      this.viewCustomerModal = true
      axios.get('/user/' + viewUserId).then(
          response => {
              this.user = response.data;
              this.orders = response.data.order;
          }
        );
        this.id = viewUserId
      }
    }
}
</script>

