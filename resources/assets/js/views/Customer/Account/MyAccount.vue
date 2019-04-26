<template>
  <div class="row">
    <div class="col-md-8 offset-2">
      <p>My Account</p>
      <div class="card">
        <div class="card-body">
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
            <b-button type="submit" variant="primary" class="mt-3"
              >Save</b-button
            >
          </b-form>
        </div>
      </div>

      <p>Change Password</p>
      <div class="card">
        <div class="card-body">
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

            <b-button type="submit" variant="primary" class="mt-3"
              >Save</b-button
            >
          </b-form>
        </div>
      </div>

      <p>Notifications</p>
      <div class="card">
        <div class="card-body">
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
            <b-form-group label="New Meal Plans" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="userDetail.notifications.meal_plan"
                @change.native="updateCustomer"
              />
            </b-form-group>
            <b-form-group label="Renewing Meal Plans" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="userDetail.notifications.subscription_renewing"
                @change.native="updateCustomer"
              />
            </b-form-group>
            <b-form-group label="Meal Plan Paused" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="userDetail.notifications.meal_plan_paused"
                @change.native="updateCustomer"
              />
            </b-form-group>
            <b-form-group label="Meal Plan Meal Substitution" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="userDetail.notifications.subscription_meal_substituted"
                @change.native="updateCustomer"
              />
            </b-form-group>
          </b-form>
        </div>
      </div>

      <p>Payment Methods</p>
      <div class="card">
        <div class="card-body">
          <card-picker :selectable="false"></card-picker>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped></style>

<script>
import CardPicker from "../../../components/Billing/CardPicker";
import { mapGetters, mapActions } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";

import toasts from "../../../mixins/toasts";

export default {
  mixins: [toasts],
  components: {
    CardPicker,
    cSwitch
  },
  data() {
    return {
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
      userDetail: "userDetail"
    })
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshUser"]),
    updateCustomer() {
      this.spliceZip();
      axios
        .patch("/api/me/detail", this.userDetail)
        .then(response => {
          this.$toastr.s("Profile updated.");
          this.refreshUser();
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
    }
  }
};
</script>
