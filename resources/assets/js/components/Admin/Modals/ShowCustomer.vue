<template>
    <div>

		<!-- <b-button @click="showCustomerModal = !showCustomerModal">
	      Show Customer Modal
	    </b-button>
		  <b-modal v-model="showCustomerModal" title="Bootstrap-Vue">
		    <p class="my-4">Hello from modal!</p>
		</b-modal> -->

    <!-- <h1>
    <slot name="h">{{ idk }}</slot>
    </h1>
    
    <h2><slot name="y"></slot></h2> -->

    <!-- <b-button @click="showCustomerModal = !showCustomerModal">
        Show Customer Modal
      </b-button> -->
    <!-- <b-button @click="view(1)">
      Show Customer Modal
    </b-button> -->
      <b-modal v-model="showCustomerModal" title="Customer">
        <p class="my-4">{{ selectedUserId }}</p>
      </b-modal>


    </div>
</template>

<script>
export default {
  props: ['userId', 'selectedUserId'],
  data () {
    return {
      showCustomerModal: false,
    }
  },
  methods: {
      view(id){
        axios.get('/user/' + id).then(
          response => {
              this.id = response.data.id;
          }
        );
        this.showCustomerModal = true
      }
    },
    watch: {
    userId: function(selectedUserId) {
      axios.get('/user/' + selectedUserId).then(
          response => {
              this.selectedUserId = response.data;
          }
        );
        this.showCustomerModal = true
      }
    }
}
</script>

