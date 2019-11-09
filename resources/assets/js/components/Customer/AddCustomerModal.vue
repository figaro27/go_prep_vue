<template>
  <div>
    <b-modal
      size="lg"
      title="Add New Customer"
      v-model="addCustomerModal"
      v-if="addCustomerModal"
      @hide="hideModal"
      hide-footer
      no-fade
    >
      <b-alert show variant="secondary">
        <b-row>
          <b-col sm="3">
            <label for="customerCheck" class="pt-1"
              >Check if user exists:</label
            >
          </b-col>
          <b-col sm="7">
            <b-form-input
              id="customerCheck"
              type="email"
              v-model="existingEmail"
              placeholder="Email Address"
            ></b-form-input>
          </b-col>

          <b-col sm="2">
            <b-btn class="ml-2" variant="primary" @click="checkExistingCustomer"
              >Check</b-btn
            >
          </b-col>
        </b-row>
      </b-alert>

      <b-alert show variant="secondary" v-if="showExistingCustomerAlert"
        >A user with this email exists. Would you like to add them to your
        customers list?
        <b-btn class="ml-2" variant="success" @click="addExistingCustomer"
          >Add Customer</b-btn
        >
      </b-alert>

      <b-form @submit.prevent="addCustomer" class="mt-1">
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
          <b-form-checkbox v-model="noEmail">Don't Have</b-form-checkbox>
          <b-form-input
            v-if="!noEmail"
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
          <b-form-checkbox v-model="noAddress">Don't Have</b-form-checkbox>
          <b-form-input
            v-if="!noAddress"
            v-model="form.address"
            type="text"
            required
            placeholder="Address"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="City" v-if="!noAddress">
          <b-form-input
            v-model="form.city"
            type="text"
            required
            placeholder="City"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="State" v-if="!noAddress">
          <v-select
            v-model="form.state"
            label="name"
            :options="stateNames"
            @keypress.enter.native.prevent=""
          ></v-select>
        </b-form-group>
        <b-form-group horizontal label="Zip" v-if="!noAddress">
          <b-form-input
            v-model="form.zip"
            type="text"
            required
            placeholder="Zip"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Delivery" v-if="!noAddress">
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
import { mapGetters, mapActions } from "vuex";

export default {
  props: {
    addCustomerModal: false
  },
  data() {
    return {
      noEmail: false,
      noAddress: false,
      existingEmail: "",
      showExistingCustomerAlert: false,
      form: {
        state: null,
        email: null,
        delivery: "Please call my phone when outside"
      }
    };
  },
  mounted() {
    let stateAbr = this.store.details.state;
    let state = this.stateNames.filter(stateName => {
      return stateName.value.toLowerCase() === stateAbr.toLowerCase();
    });

    this.form.state = state[0];
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    }),
    stateNames() {
      return states.selectOptions("US");
    }
  },
  methods: {
    ...mapActions(["refreshStoreCustomersNoOrders", "refreshStoreCustomers"]),
    addCustomer() {
      if (this.noAddress) {
        this.form.state = null;
      }
      let form = this.form;

      if (!form.accepted_tos) {
        this.$toastr.e(
          "Please accept the terms of service.",
          "Registration failed"
        );
        return;
      }

      axios.post("/api/me/register", form).then(async response => {
        this.$parent.addCustomerModal = false;
        this.form = {};

        //await this.refreshStoreCustomersNoOrders();
        await this.refreshStoreCustomers();

        this.$toastr.s("Customer Added");
        this.$parent.setCustomer(response.data);
        //if (this.$route.params.manualOrder) this.$parent.getCards();
      });
      // .catch(e => {
      //   this.$toastr.e("Please try again.", "Registration failed");
      // });
    },
    checkExistingCustomer() {
      axios
        .post("/api/me/checkExistingCustomer", { email: this.existingEmail })
        .then(response => {
          if (response.data === "existsCustomer")
            this.$toastr.w("This user is already on your customers list.");
          if (response.data === "existsNoCustomer")
            this.showExistingCustomerAlert = true;
          if (response.data === "noCustomer") {
            this.$toastr.w(
              "An account has not been registered with this email yet. Please add them below."
            );
            this.form.email = this.existingEmail;
          }
        });
    },
    addExistingCustomer() {
      axios
        .post("/api/me/addExistingCustomer", { email: this.existingEmail })
        .then(response => {
          this.$toastr.s("Customer added.");
          (this.existingEmail = ""), (this.showExistingCustomerAlert = false);
          this.$parent.addCustomerModal = false;
          this.$parent.setCustomer();
          if ($route.params.manualOrder) this.$parent.getCards();
        });
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
    },
    hideModal() {
      this.$parent.addCustomerModal = false;
      (this.existingEmail = ""), (this.showExistingCustomerAlert = false);
      this.$parent.addCustomerModal = false;
      this.form.email = null;
    }
  }
};
</script>
