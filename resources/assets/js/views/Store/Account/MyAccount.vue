<template>
  <div class="row mt-3">
    <div class="col-md-8 offset-2">
      <div class="card">
        <div class="card-body">
          <!-- toastr -->
          <!--           <b-btn @click="toast('s')">Success</b-btn>
          <b-btn @click="toast('w')">Warning</b-btn>
          <b-btn @click="toast('e')">Error</b-btn>-->
          <!-- /toastr -->
          <b-form @submit.prevent="updateLogin">
            <b-form-group label="Email address" label-for="email" :state="true">
              <b-form-input
                id="email"
                type="email"
                v-model="storeUser.email"
                placeholder="Email address"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="Password" label-for="password" :state="true">
              <b-form-input
                id="password"
                type="password"
                v-model="storeUser.password"
                placeholder="Password"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group
              label="Confirm new password"
              label-for="password2"
              :state="true"
            >
              <b-form-input
                id="password2"
                type="password"
                v-model="storeUser.password2"
                placeholder="Confirm new password"
                required
              ></b-form-input>
            </b-form-group>
            <b-button type="submit" variant="primary">Save</b-button>
          </b-form>
        </div>
      </div>
      <p>Company</p>
      <div class="card">
        <div class="card-body">
          <p>https://{{ storeDetails.domain }}.goprep.com</p>
          <b-form-group label="Company Name" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.name"
              placeholder="Company Name"
              required
              @input="updateStoreDetails()"
            ></b-form-input>
          </b-form-group>

          <b-form-group label="Logo" :state="true">
            <p class="small">
              Please keep height & width dimensions the exact same.
            </p>
            <picture-input
              :ref="`storeImageInput`"
              :prefill="logoPrefill"
              @prefill="$refs[`storeImageInput`].onResize()"
              :alertOnError="false"
              :autoToggleAspectRatio="true"
              margin="0"
              size="10"
              button-class="btn"
              style="width: 180px; height: auto; margin: 0;"
              @change="val => updateLogo(val)"
            ></picture-input>
          </b-form-group>

          <b-form-group label="Phone number" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.phone"
              placeholder="Phone"
              @input="saveTextInput()"
              required
            ></b-form-input>
          </b-form-group>

          <b-form-group label="Address" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.address"
              placeholder="Address"
              required
              @input="saveTextInput()"
            ></b-form-input>
          </b-form-group>

          <b-form-group label="City" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.city"
              placeholder="City"
              required
              @input="saveTextInput()"
            ></b-form-input>
          </b-form-group>

          <b-form-group
            label="State"
            :state="true"
            v-if="store.details.state"
            @input="saveTextInput()"
          >
            <b-form-input
              type="text"
              v-model="storeDetail.state"
              placeholder="State"
              required
              @input="saveTextInput()"
            ></b-form-input>
          </b-form-group>

          <b-form-group label="Zip Code" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.zip"
              placeholder="Zip Code"
              required
              @input="saveTextInput()"
            ></b-form-input>
          </b-form-group>

          <b-form-group label="Social Media Handle" :state="true">
            <b-form-input
              type="text"
              v-model="storeDetail.social"
              placeholder="@name"
              @input="saveTextInput()"
            ></b-form-input>
          </b-form-group>

          <b-form-input
            type="text"
            v-model="stripeId"
            placeholder="Stripe ID"
            v-if="store.id === 3"
          ></b-form-input>
          <p>
            <b-btn @click="testRenewSubscription" v-if="store.id === 3"
              >TEST RENEW SUBSCRIPTION</b-btn
            >
          </p>
          <p>
            <b-form-input
              type="text"
              v-model="stripeId"
              placeholder="Stripe ID"
              v-if="store.id === 3 || store.id === 13 || store.id === 16"
            ></b-form-input>
            <b-form-input
              type="text"
              v-model="unixTimestamp"
              placeholder="Unix Timestamp"
              v-if="store.id === 3 || store.id === 13 || store.id === 16"
            ></b-form-input>
            <b-btn
              @click="changeSubscriptionAnchor"
              v-if="store.id === 3 || store.id === 13 || store.id === 16"
              >Change Subscription Anchor</b-btn
            >
          </p>

          <b-form-input
            v-model="storeId"
            placeholder="Store ID"
            v-if="store.id === 3 || store.id === 13"
          ></b-form-input>
          <b-btn @click="deleteInactiveStoreImages" v-if="store.id === 3"
            >Delete Inactive Store Images</b-btn
          >
          <br /><br /><br />
          <b-btn @click="deleteStore" v-if="store.id === 3 || store.id === 13"
            >Delete Store</b-btn
          >

          <!--
            <b-btn @click="testIncomingSMS">TEST INCOMING SMS</b-btn>
            
            <b-btn @click="testChargeDescriptor">TEST CHARGE DESCRIPTOR</b-btn>

            <p><b-btn @click="testRenewSubscription">TEST RENEW SUBSCRIPTION</b-btn></p>
            
            <p><b-btn @click="testDeleteMealOrders">TEST DELETE MEAL ORDERS</b-btn></p>
    
            <p><b-btn @click="testRenewSubscription">TEST RENEW SUBSCRIPTION</b-btn></p>

            <p><b-btn @click="getDeliveryRoutes">TEST DELIVERY ROUTE</b-btn></p>

            <p><b-btn @click="cancelledSubscription">cancelledSubscription</b-btn></p>

            <p><b-btn @click="readyToPrint">readyToPrint</b-btn></p>

            <p><b-btn @click="deliveryToday">deliveryToday</b-btn></p>

            <p><b-btn @click="mealPlan">mealPlan</b-btn></p>

            <p><b-btn @click="subscriptionRenewing">subscriptionRenewing</b-btn></p>

            <p><b-btn @click="newSubscription">newSubscription</b-btn></p> 

            <p><b-btn @click="customerMealPlanPaused">mealPlanPaused</b-btn></p>

            <p><b-btn @click="customerNewOrder">customerNewOrder</b-btn></p>

            <p><b-btn @click="storeNewOrder">storeNewOrder</b-btn></p>
            
          -->
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.categories {
  .btn {
    position: relative;

    i {
      position: absolute;
      top: 0;
      right: 0;
      opacity: 0;
    }

    &:hover {
      i {
        opacity: 1;
      }
    }
  }
}
</style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import fs from "../../../lib/fs.js";
import { AsYouType } from "libphonenumber-js";

export default {
  components: {
    cSwitch
  },
  data() {
    return {
      stripeId: null,
      unixTimestamp: null,
      storeId: null
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "viewedStore",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings"
    }),
    storeUser() {
      return this.user;
    },
    storeDetails() {
      return this.storeDetail;
    },
    logoPrefill() {
      if (this.storeDetail.logo) {
        if (this.storeDetail.logo.url_thumb) {
          return this.storeDetail.logo.url_thumb;
        } else if (_.isString(this.storeDetail.logo)) {
          return this.storeDetail.logo;
        }
      }
      return null;
    }
  },
  mounted() {
    this.disableSpinner();
  },
  destroyed() {
    this.enableSpinner();
  },
  methods: {
    ...mapActions(["disableSpinner", "enableSpinner"]),
    updateLogin() {
      let data = this.storeUser;

      axios
        .patch("/api/me/user", data)
        .then(response => {
          this.$toastr.s("Your login info has been updated.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error, "Error");
        });
    },
    invalidData() {
      if (!this.storeDetail.phone) {
        return "Please fill out your phone number.";
      }
      if (!this.storeDetail.address) {
        return "Please fill out your address.";
      }
      if (!this.storeDetail.city) {
        return "Please fill out your city.";
      }
      if (!this.storeDetail.state) {
        return "Please fill out your state.";
      }
      if (!this.storeDetail.zip) {
        return "Please fill out your zip code.";
      }
      return null;
    },
    updateStoreDetails() {
      let data = { ...this.storeDetails };
      if (this.invalidData()) {
        this.$toastr.w(this.invalidData());
        return;
      }
      if (data.social && !data.social.includes("@")) {
        data.social = "@" + data.social;
      }
      this.asYouType();

      if (typeof data.logo !== "string") {
        delete data.logo;
      }
      axios
        .patch("/api/me/details", data)
        .then(response => {})
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error, "Error");
        });
    },
    updateStoreSettings() {
      let settings = { ...this.storeSettings };
      settings.delivery_distance_zipcodes = this.zipcodes;

      axios.patch("/api/me/settings", settings).then(response => {});
    },
    updateZips(zips) {
      this.zipcodes = zips.split(",").map(zip => {
        return zip.trim();
      });
    },
    toast(type) {
      switch (type) {
        case "s":
          this.$toastr.s("message", "Success!");
          break;

        case "w":
          this.$toastr.w("message", "Warning");
          break;

        case "e":
          this.$toastr.w("message", "Error");
          break;
      }
    },
    async updateLogo(logo) {
      let b64 = await fs.getBase64(this.$refs.storeImageInput.file);
      this.storeDetail.logo = b64;
    },
    cancelledSubscription() {
      axios.get("/mail/cancelledSubscription");
    },
    readyToPrint() {
      axios.get("/mail/readyToPrint");
    },
    deliveryToday() {
      axios.get("/mail/deliveryToday");
    },
    mealPlan() {
      axios.get("/mail/mealPlan");
    },
    subscriptionRenewing() {
      axios.get("/mail/subscriptionRenewing");
    },
    newSubscription() {
      axios.get("/mail/newSubscription");
    },
    customerNewOrder() {
      axios.get("/mail/newOrder");
    },
    customerMealPlanPaused() {
      axios.get("/mail/mealPlanPaused");
    },
    storeNewOrder() {
      axios.get("/mail/storeNewOrder");
    },
    getDeliveryRoutes() {
      axios.get("/getDeliveryRoutes");
    },
    testDeleteMealOrders() {
      axios.get("/testDeleteMealOrders");
    },
    testRenewSubscription() {
      axios.post("/testRenewSubscription", { stripe_id: this.stripeId });
    },
    asYouType() {
      this.storeDetail.phone = this.storeDetail.phone.replace(/[^\d.-]/g, "");
      this.storeDetail.phone = new AsYouType(this.storeDetail.country).input(
        this.storeDetail.phone
      );
    },
    testChargeDescriptor() {
      axios.get("/testChargeDescriptor");
    },
    testIncomingSMS() {
      axios.get("/testIncomingSMS");
    },
    changeSubscriptionAnchor() {
      axios.post("/changeSubscriptionAnchor", {
        stripe_id: this.stripeId,
        unixTimestamp: this.unixTimestamp,
        store_id: this.storeId
      });
    },
    deleteInactiveStoreImages() {
      axios.post("/deleteInactiveStoreImages", {
        store_id: this.storeId
      });
    },
    deleteStore() {
      axios.post("/deleteStore", {
        store_id: this.storeId
      });
    },
    saveTextInput: _.debounce(function() {
      this.updateStoreDetails();
    }, 1000)
  }
};
</script>
