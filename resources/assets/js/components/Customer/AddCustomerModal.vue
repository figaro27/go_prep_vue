<template>
  <div>
    <b-modal
      size="lg"
      title="Add New Customer"
      v-model="addCustomerModal"
      v-if="addCustomerModal"
      @hide="$parent.addCustomerModal = false"
      hide-footer
      no-fade
    >
      <b-form @submit.prevent="addCustomer" class="mt-3">
        <b-form-group horizontal label="First Name">
          <b-form-input
            v-model="form.first_name"
            type="text"
            required
            placeholder="First name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Last Name">
          <b-form-input
            v-model="form.last_name"
            type="text"
            required
            placeholder="Last name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Email">
          <b-form-input
            v-model="form.email"
            type="email"
            required
            placeholder="Enter email"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Phone">
          <b-form-input
            v-model="form.phone"
            type="text"
            required
            placeholder="Phone"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Address">
          <b-form-input
            v-model="form.address"
            type="text"
            required
            placeholder="Address"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="City">
          <b-form-input
            v-model="form.city"
            type="text"
            required
            placeholder="City"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="State">
          <v-select
            v-model="form.state"
            label="name"
            :options="stateNames"
            @keypress.enter.native.prevent=""
          ></v-select>
        </b-form-group>
        <b-form-group horizontal label="Zip">
          <b-form-input
            v-model="form.zip"
            type="text"
            required
            placeholder="Zip"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Delivery">
          <b-form-input
            v-model="form.delivery"
            type="text"
            placeholder="Delivery Instructions"
          ></b-form-input>
        </b-form-group>
        <b-form-checkbox
          id="accepted-tos"
          name="accepted-tos"
          v-model="form.accepted_tos"
          :value="1"
          :unchecked-value="0"
        >
          This customer gave me permission to create their account and accepts
          the
          <a href="https://www.goprep.com/terms-of-service/" target="_blank"
            ><span class="strong">terms of service</span></a
          >
        </b-form-checkbox>
        <b-button type="submit" variant="primary" class="float-right"
          >Add</b-button
        >
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import states from "../../data/states.js";
import { mapActions } from "vuex";

export default {
  props: {
    addCustomerModal: false
  },
  data() {
    return {
      form: {}
    };
  },
  computed: {
    stateNames() {
      return states.selectOptions("US");
    }
  },
  methods: {
    ...mapActions(["refreshStoreCustomers"]),
    addCustomer() {
      let form = this.form;

      if (!form.accepted_tos) {
        this.$toastr.e(
          "Please accept the terms of service.",
          "Registration failed"
        );
        return;
      }

      axios.post("/api/me/register", form).then(async response => {
        this.addCustomerModal = false;
        this.$parent.addCustomerModal = false;
        this.form = {};
        await this.refreshStoreCustomers();
        this.$toastr.s("Customer Added");
        this.$parent.setCustomer();
      });
      // .catch(e => {
      //   this.$toastr.e("Please try again.", "Registration failed");
      // });
    },
    getStateNames(country = "US") {
      return states.selectOptions(country);
    },
    state(step, key) {
      if (
        !_.isEmpty(this.form[step][key]) &&
        this.$v.form[step][key] &&
        this.$v.form[step][key].$dirty
      ) {
        if (
          this.$v.form[step][key].$error ||
          !_.isNull(this.invalidFeedback(step, key))
        ) {
          return false;
        }
        return true;
      } else return null;
    }
  }
};
</script>
