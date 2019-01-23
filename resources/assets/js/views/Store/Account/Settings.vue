<template>
  <div class="row">
    <div class="col-md-8 offset-2">
      <p>My Account</p>
      <div class="card">
        <div class="card-body">
          <!-- toastr -->
          <b-btn @click="toast('s')">Success</b-btn>
          <b-btn @click="toast('w')">Warning</b-btn>
          <b-btn @click="toast('e')">Error</b-btn>
          <!-- /toastr -->
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
            
            <b-form-group label="Password" label-for="password" :state="true">
              <b-form-input
                id="password"
                type="password"
                v-model="user.password"
                placeholder="Password"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Confirm new password" label-for="password2" :state="true">
              <b-form-input
                id="password2"
                type="password"
                v-model="user.password2"
                placeholder="Confirm new password"
                required
              ></b-form-input>
            </b-form-group>
            <b-button type="submit" variant="primary" @click="updateLogin">Submit</b-button>
          </b-form>
            <b-alert variant="success" class="alert" dismissible :show="loginAlertSuccess" @dismissed="loginAlert=false">
                    Login Info Updated!
            </b-alert>
            <b-alert variant="danger" class="alert" dismissible :show="loginAlertFail" @dismissed="loginAlert=false">
                    Error. Please make sure you typed in a correct email address and the same password in both fields.
            </b-alert>
        </div>
      </div>
      <p>Company</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreDetails">
            <b-form-group label="Company Name" :state="true">
              <b-form-input type="text" v-model="storeDetail.name" placeholder="Company Name" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="Logo" :state="true">
              <b-form-input type="text" v-model="storeDetail.logo" placeholder="Logo" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="Phone number" :state="true">
              <b-form-input type="text" v-model="storeDetail.phone" placeholder="Phone" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="Address" :state="true">
              <b-form-input type="text" v-model="storeDetail.address" placeholder="Address" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="City" :state="true">
              <b-form-input type="text" v-model="storeDetail.city" placeholder="City" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="State" :state="true">
              <b-form-input type="text" v-model="storeDetail.state" placeholder="State" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="Zip Code" :state="true">
              <b-form-input type="text" v-model="storeDetail.zip" placeholder="Zip Code" required></b-form-input>
            </b-form-group>
            
            <b-form-group label="About" :state="true">
              <b-form-textarea :rows="3" v-model="storeDetail.description" placeholder="About" required></b-form-textarea :rows="3">
            </b-form-group>
            
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
                v-model="storeSettings.cutoff_days"
                class="d-inline w-auto mr-1"
                :options="cutoffDaysOptions"
              ></b-select>
              <b-select
                v-model="storeSettings.cutoff_hours"
                class="d-inline w-auto mr-1"
                :options="cutoffHoursOptions"
              ></b-select>
              <img v-b-popover.hover="'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lobortis elit eu eleifend molestie. Phasellus nec gravida ipsum. Donec ornare ullamcorper nunc et eleifend. Nam semper, nisl ut hendrerit facilisis, tellus dolor commodo.'" title="Cut Off Period" src="/images/store/popover.png">
            </b-form-group>

            
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
              <img v-b-popover.hover="'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lobortis elit eu eleifend molestie. Phasellus nec gravida ipsum. Donec ornare ullamcorper nunc et eleifend. Nam semper, nisl ut hendrerit facilisis, tellus dolor commodo.'" title="Delivery Day(s)" src="/images/store/popover.png">
            </b-form-group>
            
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
              <img v-b-popover.hover="'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lobortis elit eu eleifend molestie. Phasellus nec gravida ipsum. Donec ornare ullamcorper nunc et eleifend. Nam semper, nisl ut hendrerit facilisis, tellus dolor commodo.'" title="Delivery Distance Type" src="/images/store/popover.png">
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
            <b-form-group label="Minimum Meals Requirement" :state="true">
              <b-form-input
                type="text"
                v-model="storeSettings.minimum"
                placeholder="Minimum Number of Meals"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Weekly Meal Plan Discount" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.applyMealPlanDiscount"
              />
              <b-form-input
                v-if="storeSettings.applyMealPlanDiscount"
                id="meal-plan-discount"
                v-model="storeSettings.mealPlanDiscount"
                placeholder="Weekly Meal Plan Discount %"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Delivery Fee" :state="true">
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
            </b-form-group>
            <b-form-group label="Allow Pickup" :state="true">
              <c-switch color="success" variant="pill" size="lg" v-model="storeSettings.allowPickup"/>
              <b-form-input
                v-if="storeSettings.allowPickup"
                type="text"
                v-model="storeSettings.pickupInstructions"
                placeholder="Please include pickup instructions to your customers (pickup address, phone number, and day/time)."
                required
              ></b-form-input>
            </b-form-group>
            
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <p>Menu</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings">
            <b-form-group label="Show Nutrition Facts" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showNutrition"
              />
            </b-form-group>
            <!-- <p>Ingredients</p> -->
          </b-form>

          <b-form-group label="Categories" :state="true">
            <b-btn-group class="categories">
              <draggable v-model="categories" @change="onChangeCategories">
                <b-btn v-for="category in categories" :key="`category-${category.id}`">
                  {{ category.category }}
                  <i v-if="category.id" @click="deleteCategory(category.id)" class="fa fa-minus-circle text-danger"></i>
                </b-btn>
              </draggable>
            </b-btn-group>

            <b-form class="mt-2" @submit.prevent="onAddCategory" inline>
              <b-input v-model="new_category" :type="text" placeholder="New category..."></b-input>
              <b-button type="submit" variant="primary">Create</b-button>
            </b-form>
          </b-form-group>
        </div>
      </div>
      <p>Notifications</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings">
           <b-form-group label="New Orders" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.new_order"
              />
           </b-form-group>

            <b-form-group label="New Subscriptions" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.new_subscription"
              />
           </b-form-group>

           <b-form-group label="Cancelled Subscriptions" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.cancelled_subscription"
              />
           </b-form-group>

           <b-form-group label="Ready to Print" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.ready_to_print"
              />
           </b-form-group>

           <b-button type="submit" variant="primary" @click="updateStoreSettings">Submit</b-button>
          </b-form>
        </div>
      </div>
      <!-- <div class="card">
        <div class="card-body">
          <p>Automatic Ordering</p>
          
          <p>Low Threshold</p>
        </div>
      </div> -->
      <p>Payments</p>
      <div class="card">
        <div class="card-body">
          <div v-if="!storeSettings.stripe_id">
            <b-form-group label="Payment Info" :state="true">
              <b-button href="https://connect.stripe.com/express/oauth/authorize?redirect_uri=http://goprep.localhost/store/stripe/redirect&client_id=ca_EKiyZcHDxZPyExm41NqBFJ7kMAkDItAl&state={STATE_VALUE}">Create account</b-button>
            </b-form-group>
          </div>
          <div v-else>
            <b-form-group label="Stripe" :state="true">
              <b-button @click="stripeLogIn">Log in</b-button>
            </b-form-group>
          </div>
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
export default {
  components: {
    cSwitch
  },
  data() {
    return {
      loginAlertSuccess: false,
      loginAlertFail: false,
      zipcodes: [],
      new_category: "",
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "store",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories"
    }),
    categories() {
      return _.chain(this.storeCategories).toArray().orderBy('order').value();
    },
    // storeDetail(){
    //     return this.store.store_detail;
    // }
    cutoffDaysOptions() {
      let options = [];
      for (let i = 0; i <= 7; i++) {
        options.push({ value: i, text: i + " Days" });
      }
      return options;
    },
    cutoffHoursOptions() {
      let options = [];
      for (let i = 0; i <= 23; i++) {
        options.push({ value: i, text: i + " Hours" });
      }
      return options;
    },
    cutoffTime() {
      return {
        HH: "10",
        mm: "05",
        ss: "00"
      };
      //storeSettings.cutoff_time
    },
    deliveryDistanceZipcodes() {
      return this.storeSettings.delivery_distance_zipcodes.join(",");
    }
  },
  mounted() {},
  methods: {
    ...mapActions(['refreshCategories']),
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
        if (response.data == 200)
        this.loginAlertSuccess = true;
        else
        this.loginAlertFail = true;
      });


    },
    updateStoreDetails() {
      this.spliceZip();
      axios
        .post("/api/updateStoreDetails", this.storeDetail)
        .then(response => {});
    },
    updateStoreSettings() {
      let settings = { ...this.storeSettings };
      settings.delivery_distance_zipcodes = this.zipcodes;

      axios.post("/api/updateStoreSettings", settings).then(response => {});
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
    createStripeAccount() {
      axios.post('/api/me/stripe').then((resp) => {
        
      });
    },
    stripeLogIn() {
      axios.get('/api/me/stripe/login').then((resp) => {
        if(resp.data.url) {
          window.location = resp.data.url;
        }
      });
    },
    onAddCategory() {
      axios.post("/api/me/categories", {category: this.new_category}).then(response => {
        this.refreshCategories();
        this.new_category = "";
      });
    },
    onChangeCategories(e) {
      if(_.isObject(e.moved)) {
        let newCats = _.toArray({...this.categories});
        newCats[e.moved.oldIndex].order = e.moved.newIndex;
        newCats[e.moved.newIndex].order = e.moved.oldIndex;

        axios.post("/api/me/categories", {categories: newCats}).then(response => {
          this.refreshCategories();
        });
      }
    },
    deleteCategory(id) {
        axios.delete("/api/me/categories/" + id).then(response => {
          this.refreshCategories();
        });
    },
    toast(type) {
      switch(type) {
        case 's':
          this.$toastr.s('message', 'Success');
        break;

        case 'w':
          this.$toastr.w('message', 'Warning');
        break;

        case 'e':
          this.$toastr.e('message', 'Error');
        break;
      }
    }
  }
};
</script>