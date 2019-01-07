<template>
  <div class="row">
    <div class="col-md-8 offset-2">
      <p>My Account</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateLogin">
            <b-form-group label="Email address" label-for="email" :state="true">
              <b-form-input
                id="email"
                type="email"
                v-model="user.email"
                placeholder="Email address"
                required
              ></b-form-input>
            </b-form-group>
            <hr>
            <b-form-group label="Password" label-for="password" :state="true">
              <b-form-input
                id="password"
                type="password"
                v-model="user.password"
                placeholder="Password"
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Confirm new password" label-for="password2" :state="true">
              <b-form-input
                id="password2"
                type="password"
                v-model="user.password2"
                placeholder="Confirm new password"
              ></b-form-input>
            </b-form-group>
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <p>Store</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreDetails">
            <b-form-input type="text" v-model="storeDetail.name" placeholder="Store Name" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.logo" placeholder="Logo" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.phone" placeholder="Phone" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.address" placeholder="Address" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.city" placeholder="City" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.state" placeholder="State" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="storeDetail.zip" placeholder="Zip Code" required></b-form-input>
            <hr>
            <b-form-input
              type="text"
              v-model="storeDetail.description"
              placeholder="About"
              required
            ></b-form-input>
            <hr>
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>

      <p>Orders</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings">
            <b-form-group label="Cut Off Period" label-for="cut-off-period" :state="true" inline>
              <b-select
                v-model="storeSettings.cutoff_day"
                class="d-inline w-auto mr-1"
                :options="[
                 { value: 'sun', text: 'Sunday' },
                 { value: 'mon', text: 'Monday' },
                 { value: 'tue', text: 'Tuesday' },
                 { value: 'wed', text: 'Wednesday' },
                 { value: 'thu', text: 'Thursday' },
                 { value: 'fri', text: 'Friday' },
                 { value: 'sat', text: 'Saturday' },
              ]"
              ></b-select>
              <timepicker v-model="cutoffTime" format="HH:mm:ss"></timepicker>
            </b-form-group>
            <hr>
            <b-form-group label="Delivery Day(s)" label-for="delivery-days" :state="true">
              <b-form-checkbox-group
                buttons
                button-variant="primary"
                v-model="storeSettings.delivery_days"
                :options="[
                 { value: 'sun', text: 'Sunday' },
                 { value: 'mon', text: 'Monday' },
                 { value: 'tue', text: 'Tuesday' },
                 { value: 'wed', text: 'Wednesday' },
                 { value: 'thu', text: 'Thursday' },
                 { value: 'fri', text: 'Friday' },
                 { value: 'sat', text: 'Saturday' },
              ]"
              ></b-form-checkbox-group>
            </b-form-group>
            <hr>
            <b-form-group
              label="Delivery Distance Type"
              label-for="delivery-distance-type"
              :state="true"
            >
              <b-form-radio-group
                buttons
                button-variant="primary"
                v-model="storeSettings.delivery_distance_type"
                :options="[
                 { value: 'radius', text: 'Radius' },
                 { value: 'zipcodes', text: 'Zipcodes' },
              ]"
              ></b-form-radio-group>
            </b-form-group>
            <b-form-group
              v-if="storeSettings.delivery_distance_type === 'radius'"
              label="Delivery Distance Radius"
              label-for="delivery-distance-radius"
              :state="true"
            >
              <b-form-input
                type="number"
                v-model="storeSettings.delivery_distance_radius"
                placeholder="Radius (miles)"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group
              v-if="storeSettings.delivery_distance_type === 'zipcodes'"
              label="Delivery Zipcodes"
              label-for="delivery-distance-zipcodes"
              description="Comma-delimited list of zipcodes"
              :state="true"
            >
              <textarea
                :value="deliveryDistanceZipcodes"
                @input="e => { updateZips(e.target.value) }"
                class="form-control"
                placeholder="Zipcodes"
              ></textarea>
            </b-form-group>
            <hr>
            <p>Minimum Meals Requirement</p>
            <b-form-input
              type="text"
              v-model="storeSettings.minimum"
              placeholder="Minimum Number of Meals"
              required
            ></b-form-input>
            <hr>
            <p>Delivery Fee</p>
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="storeSettings.applyDeliveryFee"
            />
            <b-form-input
              v-if="storeSettings.applyDeliveryFee"
              type="text"
              v-model="storeSettings.deliveryFee"
              placeholder="Delivery Fee"
              required
            ></b-form-input>
            <hr>
            <p>Allow Pickup</p>
            <c-switch color="success" variant="pill" size="lg" v-model="storeSettings.allowPickup"/>
            <b-form-input
              v-if="storeSettings.allowPickup"
              type="text"
              v-model="storeSettings.pickupInstructions"
              placeholder="Pickup Instructions (Include address, phone number, and time)"
              required
            ></b-form-input>
            <hr>
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <p>Menu</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings">
            <p>Show Nutrition Facts</p>
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="storeSettings.showNutrition"
            />
            <hr>
            <p>Ingredients</p>
            <hr>
            <p>Categories</p>
            <hr>
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <p>Notifications</p>
      <div class="card">
        <div class="card-body">
          <p>New Orders</p>
        </div>
      </div>
      <p>Inventory</p>
      <div class="card">
        <div class="card-body">
          <p>Automatic Ordering</p>
          <hr>
          <p>Low Threshold</p>
        </div>
      </div>
      <p>Payments</p>
      <div class="card">
        <div class="card-body">
          <p>Payment Info</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
export default {
  components: {
    cSwitch
  },
  data() {
    return {
      zipcodes: [],
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "store",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings"
    }),
    // storeDetail(){
    //     return this.store.store_detail;
    // }
    cutoffTime() {
      return {
        HH: "10",
        mm: "05",
        ss: "00"
      };
      //storeSettings.cutoff_time
    },
    deliveryDistanceZipcodes() {
      return this.storeSettings.delivery_distance_zipcodes.join(',');
    },
  },
  mounted() {},
  methods: {
    updateLogin() {
      let data = {};

      if (!_.isEmpty(this.user.email)) {
        data.email = this.user.email;
      }
      if (!_.isEmpty(this.user.password)) {
        data.password = this.user.password;
      }

      axios.patch("/api/me/user", data).then(response => {
        this.$store.commit("user", response.data);
      });
    },
    updateStoreDetails() {
      this.spliceZip();
      axios.post("/api/updateStoreDetails", this.storeDetail).then(response => {});
    },
    updateStoreSettings() {
      let settings = {...this.storeSettings};
      settings.delivery_distance_zipcodes = this.zipcodes;

      axios
        .post("/api/updateStoreSettings", settings)
        .then(response => {});
    },
    spliceZip() {
      if (this.storeDetail.zip.toString().length > 5) {
        let intToString = this.storeDetail.zip.toString();
        let newZip = parseInt(intToString.substring(0, 5));
        this.storeDetail.zip = newZip;
      }
    },
    updateZips(zips) {
      this.zipcodes = zips.split(',').map(zip => {
        return zip.trim();
      });
    },
  }
};
</script>