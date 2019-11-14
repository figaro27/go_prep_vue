<template>
  <div class="main-customer-container box-shadow">
    <div class="row">
      <div class="col-md-8 offset-2">
        <p class="strong">My Account</p>
        <b-form @submit.prevent="updateCustomer">
          <b-form-input
            type="text"
            v-model="userDetail.firstname"
            placeholder="First Name"
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.lastname"
            placeholder="Last Name"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.phone"
            placeholder="Phone"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.address"
            placeholder="Address"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.city"
            placeholder="City"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.state"
            placeholder="State"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.zip"
            placeholder="Zip Code"
            required
          ></b-form-input>
          <hr />
          <b-form-input
            type="text"
            v-model="userDetail.delivery"
            placeholder="Delivery Instructions"
            required
          ></b-form-input>
          <b-button type="submit" variant="primary" class="mt-3">Save</b-button>
        </b-form>
        <p class="strong mt-3">Billing Address</p>
        <div v-if="gateway === 'authorize'">
          <b-form @submit.prevent="addBillingAddress" class="mt-4">
            <b-form-group>
              <b-form-input
                v-model="userDetail.billingAddress"
                type="text"
                required
                placeholder="Billing Address"
              ></b-form-input>
            </b-form-group>
            <b-form-group>
              <b-form-input
                v-model="userDetail.billingCity"
                type="text"
                required
                placeholder="Billing City"
              ></b-form-input>
            </b-form-group>
            <b-form-group>
              <v-select
                v-model="userDetail.billingState"
                label="name"
                :options="stateNames"
                @keypress.enter.native.prevent=""
              ></v-select>
            </b-form-group>
            <b-form-group>
              <b-form-input
                v-model="userDetail.billingZip"
                type="text"
                required
                placeholder="Billing Zip"
              ></b-form-input>
            </b-form-group>
            <b-button type="submit" variant="primary" class="mt-3"
              >Save</b-button
            >
          </b-form>
        </div>

        <p class="strong mt-4">Change Password</p>
        <b-form @submit.prevent="updatePassword">
          <b-form-group :state="true">
            <b-form-input
              type="password"
              v-model="password.current"
              placeholder="Current Password"
              required
            ></b-form-input>
          </b-form-group>

          <hr />

          <b-form-group :state="true">
            <b-form-input
              type="password"
              v-model="password.new"
              placeholder="New Password"
              required
            ></b-form-input>
          </b-form-group>

          <hr />

          <b-form-group :state="true" class="mb-0">
            <b-form-input
              type="password"
              v-model="password.new_confirmation"
              placeholder="Confirm New Password"
              required
            ></b-form-input>
          </b-form-group>

          <b-button type="submit" variant="primary" class="mt-3">Save</b-button>
        </b-form>

        <p class="strong mt-4">Notifications</p>
        <b-form @submit.prevent="updateCustomer">
          <b-form-group label="New Orders" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.new_order"
              @change.native="updateCustomer"
            />
          </b-form-group>
          <b-form-group label="Delivery Today" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.delivery_today"
              @change.native="updateCustomer"
            />
          </b-form-group>
          <b-form-group label="New Subscriptions" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.meal_plan"
              @change.native="updateCustomer"
            />
          </b-form-group>
          <b-form-group label="Renewing Subscriptions" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.subscription_renewing"
              @change.native="updateCustomer"
            />
          </b-form-group>
          <b-form-group label="Subscription Paused" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.meal_plan_paused"
              @change.native="updateCustomer"
            />
          </b-form-group>
          <b-form-group label="Subscription Meal Substitution" :state="true">
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="userDetail.notifications.subscription_meal_substituted"
              @change.native="updateCustomer"
            />
          </b-form-group>
        </b-form>

        <p class="strong mt-4">Payment Methods</p>
        <card-picker :selectable="false" :gateway="gateway"></card-picker>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped></style>

<script>
import CardPicker from "../../../components/Billing/CardPicker";
import { mapGetters, mapActions } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import states from "../../../data/states.js";

import toasts from "../../../mixins/toasts";

export default {
  mixins: [toasts],
  components: {
    CardPicker,
    cSwitch
  },
  data() {
    return {
      form: {
        billingState: null
      },
      password: {
        current: null,
        new: null,
        new_confirmation: null
      }
    };
  },
  computed: {
    ...mapGetters({
      cards: "cards",
      user: "user",
      userDetail: "userDetail",
      storeSettings: "viewedStoreSettings"
    }),
    gateway() {
      return this.storeSettings.payment_gateway;
    },
    stateNames() {
      return states.selectOptions("US");
    }
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshUser", "refreshViewedStore"]),
    updateCustomer() {
      this.spliceZip();
      axios
        .patch("/api/me/detail", this.userDetail)
        .then(response => {
          this.$toastr.s("Profile updated.");
          this.refreshUser();
          this.refreshViewedStore();
        })
        .catch(e => {
          this.$toastr.e("Failed to update profile.");
        });
    },
    updatePassword() {
      axios
        .patch("/api/me/password", this.password)
        .then(response => {
          this.$toastr.s("Password updated.");
          this.refreshUser();
        })
        .catch(e => {
          this.toastErrorResponse(
            e.response.data,
            "Failed to update password."
          );
        });
    },
    spliceZip() {
      if (this.userDetail.zip.toString().length > 5) {
        let reducedZip = this.userDetail.zip.toString();
        this.userDetail.zip = parseInt(reducedZip.substring(0, 5));
      }
    },
    getCustomer() {
      axios.get("/api/me/getCustomer").then(resp => {
        return resp.data.id;
      });
    },
    addBillingAddress() {
      axios.patch("/api/me/detail", this.userDetail).then(resp => {
        this.$toastr.s("Billing address updated.");
      });
    }
  }
};
</script>
