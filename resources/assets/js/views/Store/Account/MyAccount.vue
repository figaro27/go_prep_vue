<template>
  <div class="row">
    <div class="col-md-8 offset-2">
      <p>My Account</p>
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
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <p>Company</p>
      <div class="card">
        <div class="card-body">
          <p>https://{{ storeDetails.domain }}.goprep.com</p>
          <b-form @submit.prevent="updateStoreDetails">
            <b-form-group label="Company Name" :state="true">
              <b-form-input
                type="text"
                v-model="storeDetail.name"
                placeholder="Company Name"
                required
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
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="Address" :state="true">
              <b-form-input
                type="text"
                v-model="storeDetail.address"
                placeholder="Address"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="City" :state="true">
              <b-form-input
                type="text"
                v-model="storeDetail.city"
                placeholder="City"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="State" :state="true">
              <b-form-input
                type="text"
                v-model="storeDetail.state"
                placeholder="State"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="Zip Code" :state="true">
              <b-form-input
                type="text"
                v-model="storeDetail.zip"
                placeholder="Zip Code"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="About" :state="true">
              <wysiwyg v-model="storeDetail.description" />
            </b-form-group>

            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>

          <!--        
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

export default {
  components: {
    cSwitch
  },
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "store",
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
          return this.this.storeDetail.logo;
        }
      }
      return null;
    }
  },
  mounted() {},
  methods: {
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
          this.$toastr.e(error, "Error");
        });
    },
    updateStoreDetails() {
      let data = { ...this.storeDetails };
      this.spliceZip();
      axios
        .patch("/api/me/details", data)
        .then(response => {
          this.$toastr.s("Your company details have been updated.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        });
    },
    updateStoreSettings() {
      let settings = { ...this.storeSettings };
      settings.delivery_distance_zipcodes = this.zipcodes;

      axios.patch("/api/me/settings", settings).then(response => {});
    },
    spliceZip() {
      if (this.storeDetail.zip.toString().length > 5) {
        let intToString = this.storeDetail.zip.toString();
        let newZip = parseInt(intToString.substring(0, 5));
        this.storeDetail.zip = newZip;
      }
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
          this.$toastr.e("message", "Error");
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
    }
  }
};
</script>
