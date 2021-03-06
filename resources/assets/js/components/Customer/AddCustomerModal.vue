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
        <b-form-group horizontal label="Company Name">
          <b-form-input
            v-model="form.company_name"
            type="text"
            placeholder="Optional"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="First Name">
          <b-form-input
            v-model="form.firstname"
            type="text"
            required
            pattern=".*\S+.*"
            placeholder="First name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Last Name">
          <b-form-input
            v-model="form.lastname"
            type="text"
            required
            pattern=".*\S+.*"
            placeholder="Last name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Phone">
          <b-form-input
            v-model="form.phone"
            type="text"
            required
            pattern=".*\S+.*"
            placeholder="Phone"
            @input="asYouType()"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Email">
          <b-form-checkbox v-model="noEmail">Don't Have</b-form-checkbox>
          <b-form-input
            v-if="!noEmail"
            v-model="form.email"
            type="email"
            required
            pattern="\S+"
            placeholder="Enter email"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Password">
          <b-form-checkbox v-model="noPassword">Make Random</b-form-checkbox>
          <b-form-input
            v-if="!noPassword"
            v-model="form.password"
            type="text"
            required
            pattern=".*\S+.*"
            placeholder="Enter password"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Address">
          <b-form-checkbox v-model="noAddress">Don't Have</b-form-checkbox>
          <b-form-input
            v-if="!noAddress"
            v-model="form.address"
            type="text"
            required
            pattern=".*\S+.*"
            placeholder="Address"
          ></b-form-input>
        </b-form-group>
        <b-form-group
          horizontal
          :label="cityLabel"
          v-if="!noAddress && showCityInput"
        >
          <b-form-input
            v-model="form.city"
            type="text"
            required
            pattern=".*\S+.*"
            :placeholder="cityLabel"
          ></b-form-input>
        </b-form-group>
        <b-form-group
          horizontal
          :label="stateLabel"
          v-if="!noAddress && store.details.state && showStatesInput"
        >
          <v-select
            v-model="form.state"
            label="name"
            :options="getStateNames(store.details.country)"
            @keypress.enter.native.prevent=""
          ></v-select>
        </b-form-group>
        <b-form-group horizontal :label="postalLabel" v-if="!noAddress">
          <b-select
            :placeholder="postalLabel"
            :options="getPostalNames(store.details.country)"
            v-model="form.zip"
            class="w-180"
            style="font-size:16px"
            v-if="selectPostal"
          >
          </b-select>
          <b-form-input
            v-else
            v-model="form.zip"
            type="text"
            required
            pattern=".*\S+.*"
            :placeholder="postalLabel"
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
import { mapGetters, mapActions, mapMutations } from "vuex";
import { AsYouType } from "libphonenumber-js";
import postals from "../../data/postals.js";

export default {
  props: {
    addCustomerModal: false
  },
  data() {
    return {
      showStatesInput: true,
      showCityInput: true,
      noEmail: false,
      noPassword: false,
      noAddress: false,
      existingEmail: "",
      showExistingCustomerAlert: false,
      form: {
        state: null,
        email: null,
        delivery: ""
      }
    };
  },
  mounted() {
    if (this.store.details) {
      this.form.country = this.store.details.country;
      if (this.store.details.state) {
        this.form.state = this.store.details.state;
        let stateAbr = this.store.details.state;
        let state = this.stateNames.filter(stateName => {
          return stateName.value.toLowerCase() === stateAbr.toLowerCase();
        });
        this.form.state = state[0].value;
      }
    }

    // Hides certain inputes like state & city depending on the selected country
    this.hideInputs();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    }),
    selectPostal() {
      // Certain countries have string based postal areas like Barbados having 'Parishes' and this is needed for delivery_day_zip_code input matching
      if (
        this.store &&
        this.store.details &&
        this.store.details.country === "BB"
      ) {
        return true;
      } else {
        return false;
      }
    },
    stateNames() {
      return states.selectOptions("US");
    },
    cityLabel() {
      if (this.store.details.country === "BH") {
        return "Town";
      } else {
        return "City";
      }
    },
    stateLabel() {
      switch (this.store.details.country) {
        case "GB":
          return "County";
          break;
        case "CA":
          return "Province";
          break;
        default:
          return "State";
      }
    },
    postalLabel() {
      switch (this.store.details.country) {
        case "US":
          return "Zip Code";
          break;
        case "BH":
          return "Block";
          break;
        case "BB":
          return "Parish";
          break;
        default:
          return "Postal Code";
      }
    }
  },
  methods: {
    ...mapActions(["refreshStoreCustomersNoOrders", "refreshStoreCustomers"]),
    ...mapMutations(["setBagCustomerModel"]),
    addCustomer() {
      this.asYouType();

      if (this.noAddress) {
        this.form.state = null;
      }
      let form = this.form;

      if (!form.accepted_tos) {
        this.$toastr.w(
          "Please accept the terms of service.",
          "Registration failed"
        );
        return;
      }

      if (this.form.state && this.form.state.value) {
        this.form.state = this.form.state.value;
      }

      axios
        .post("/api/me/register", form)
        .then(async response => {
          let resp = JSON.parse(JSON.stringify(response));
          this.setBagCustomerModel(resp.data);
          this.$parent.addCustomerModal = false;
          this.form = {};
          await this.refreshStoreCustomersNoOrders();
          // await this.refreshStoreCustomers();

          this.$toastr.s("Customer Added");
          // this.$parent.setCustomer(resp.data);
          //if (this.$route.params.manualOrder) this.$parent.getCards();
        })
        .catch(e => {
          let error = e.response.data.message;
          this.$toastr.w(error);
        });
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
          this.$parent.setCustomer(response.data);
          // if (this.$route.params.manualOrder) this.$parent.getCards();
        });
    },
    getStateNames(country = "US") {
      if (country == "BH") {
        this.showStatesInput = false;
      }
      return states.selectOptions(country);
    },
    getPostalNames(country = "US") {
      return postals.getPostals(country);
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
    },
    asYouType() {
      this.form.phone = this.form.phone.replace(/[^\d.-]/g, "");
      this.form.phone = new AsYouType(this.store.details.country).input(
        this.form.phone
      );
    },
    hideInputs() {
      // Eventually move to external js file
      if (this.store.details.country === "BH") {
        this.showStatesInput = false;
        this.form.state = "N/A";
      }

      if (this.store.details.country === "BB") {
        this.showStatesInput = false;
        this.showCityInput = false;
        this.form.state = "N/A";
        this.form.city = "N/A";
      }
    }
  }
};
</script>
