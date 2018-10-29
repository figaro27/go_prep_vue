<template>
    <div>
      <b-modal title="Customer" v-model="viewCustomerModal" v-if="viewCustomerModal" @hide="resetUserId" @hidden="toggleModalVisibility">
        <b-list-group>
          <b-list-group-item><b-form-input v-model="user.user_detail.firstname"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.user_detail.lastname"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.email"></b-form-input></b-list-group-item>
          <!-- <b-list-group-item>Customer Since: <b-form-input v-model="user.created_at"></b-form-input></b-list-group-item> -->
          <b-list-group-item><b-form-input v-model="user.user_detail.address"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.user_detail.city"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.user_detail.state"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.user_detail.phone"></b-form-input></b-list-group-item>
          <b-list-group-item><b-form-input v-model="user.user_detail.delivery"></b-form-input></b-list-group-item>
        </b-list-group>

        <button class="btn btn-primary mt-3 float-right" @click="updateUser(id)">Save</button>
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

