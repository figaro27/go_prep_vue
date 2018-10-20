<template>
    <div>

		<b-button @click="editCustomerModal = !editCustomerModal">
	      Edit Customer Modal
	    </b-button>
		  <b-modal v-model="editCustomerModal" title="Bootstrap-Vue">
		    <p class="my-4">Hello from modal!</p>
		</b-modal>


    </div>
</template>

<script>
export default {
  data () {
    return {
      editCustomerModal: false
    }
  }
}
</script>

