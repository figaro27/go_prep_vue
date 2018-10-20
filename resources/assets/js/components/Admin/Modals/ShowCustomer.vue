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

    <b-button @click="showCustomerModal = !showCustomerModal">
        Show Customer Modal
      </b-button>
      <b-modal v-model="showCustomerModal" title="Bootstrap-Vue">
        <p class="my-4">ID: {{ id }}</p>
    </b-modal>



    </div>
</template>

<script>
export default {
  props: ['id'],
  data () {
    return {
      showCustomerModal: false,
    }
  }
}
</script>

