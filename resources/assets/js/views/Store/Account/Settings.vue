<template>
  <div class="row">
    <div class="col-md-8 offset-2">
      <b-alert :show="!canOpen" variant="success">Welcome to GoPrep! Enter all settings to open your store for business.</b-alert>
      
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
                class="d-inline w-auto mr-1 custom-select"
                :options="cutoffHoursOptions"
              ></b-select>
              <img
                v-b-popover.hover="'This is the amount of time you want to lock in orders before a specific delivery day. For example - you set the cut off period to 1 day, and it is currently Tuesday. If you have a Wednesday delivery day, your customer will not see Wednesday as a delivery day option. They will see the next available delivery day. This prevents you from getting new orders right before your delivery day and possibly already after you prepped your meals for that day.'"
                title="Cut Off Period"
                src="/images/store/popover.png"
                class="popover-size"
              >
            </b-form-group>

            <b-form-group label="Timezone">
              <b-select :options="timezoneOptions" v-model="storeSettings.timezone" class="w-100"></b-select>
            </b-form-group>

            <b-form-group label="Delivery Day(s)" label-for="delivery-days" :state="true">
              <b-form-checkbox-group
                buttons
                v-model="storeSettings.delivery_days"
                class="storeFilters"
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
              <img
                v-b-popover.hover="'These are the day(s) you plan on delivering your meals to your customers and will show up as options on the checkout page for the customer. You can set it to one day per week as many smaller meal prep companies do, or as many as you like.'"
                title="Delivery / Pickup Day(s)"
                src="/images/store/popover.png"
                class="popover-size"
              >
            </b-form-group>

            <b-form-group
              label="Delivery Distance Type"
              label-for="delivery-distance-type"
              :state="true"
            >
              <b-form-radio-group
                buttons
                v-model="storeSettings.delivery_distance_type"
                class="storeFilters"
                :options="[
                 { value: 'radius', text: 'Radius' },
                 { value: 'zipcodes', text: 'Zip Codes' },
              ]"
              ></b-form-radio-group>
              <img
                v-b-popover.hover="'As you do local delivery, you may have a certain cutoff distance. Here you can set this distance by radius by the number of miles around you or by zip codes separated by commas.'"
                title="Delivery Distance Type"
                src="/images/store/popover.png"
                class="popover-size"
              >
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
              label="Delivery Zip Codes"
              label-for="delivery-distance-zipcodes"
              description="Separate zip codes by comma"
              :state="true"
            >
              <textarea
                v-model="deliveryDistanceZipcodes"
                @input="e => { updateZips(e) }"
                class="form-control"
                placeholder="Zip Codes"
              ></textarea>
            </b-form-group>

            <b-form-group>
              <b-form-radio-group v-model="storeSettings.minimumOption" :options="minimumOptions"></b-form-radio-group>
            </b-form-group>

            <b-form-group :state="true" v-if="storeSettings.minimumOption === 'price'">
              <p>
                <span class="mr-1">Minimum Price Requirement</span>
                <img
                  v-b-popover.hover="'Here you can set a minimum bag price required before a customer can place an order. Leave it at 0 if you have no minimum requirement.'"
                  title="Minimum Price Requirement"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
              </p>
              <b-form-input
                type="text"
                v-model="storeSettings.minimumPrice"
                placeholder="Minimum Price"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group :state="true" v-if="storeSettings.minimumOption === 'meals'">
              <p>
                <span class="mr-1">Minimum Meals Requirement</span>
                <img
                  v-b-popover.hover="'Here you can set a minimum number of meals required before a customer can place an order. Leave it at 0 if you have no minimum requirement.'"
                  title="Minimum Meals Requirement"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
              </p>
              <b-form-input
                type="text"
                v-model="storeSettings.minimumMeals"
                placeholder="Minimum Number of Meals"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group :state="true">
              <p>
                <span class="mr-1">Weekly Meal Plan Discount</span>
                <img
                  v-b-popover.hover="'Give your customers an incentive to create a weekly meal plan with you by offering a discount percentage.'"
                  title="Weekly Meal Plan Discount"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
              </p>
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
            <b-form-group :state="true">
              <p>
                <span class="mr-1">Delivery Fee</span>
                <img
                  v-b-popover.hover="'Here you can apply an optional delivery fee paid for by your customers.'"
                  title="Delivery Fee"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.applyDeliveryFee"
              />
              <b-form-input
                v-if="storeSettings.applyDeliveryFee"
                type="string"
                v-model="storeSettings.deliveryFee"
                placeholder="Delivery Fee"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group :state="true">
              <p>
                <span class="mr-1">Processing Fee</span>
                <img
                  v-b-popover.hover="'Here you can apply an optional processing fee paid for by your customers.'"
                  title="Processing Fee"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.applyProcessingFee"
              />
              <b-form-input
                v-if="storeSettings.applyProcessingFee"
                type="string"
                v-model="storeSettings.processingFee"
                placeholder="Processing Fee"
                required
              ></b-form-input>
            </b-form-group>

            <b-form-group label="I Will Be:">
              <b-form-checkbox-group v-model="transferSelected" :options="transferOptions"></b-form-checkbox-group>
            </b-form-group>
            <p v-if="transferTypeCheck">Pickup Instructions:</p>
            <b-form-textarea
              v-if="transferTypeCheck"
              type="text"
              rows="3"
              v-model="storeSettings.pickupInstructions"
              placeholder="Please include pickup instructions to your customers (pickup address, phone number, and time)."
              required
            ></b-form-textarea>

            <p class="mt-2">
                <span class="mr-1">Notes For Customer</span>
                <img
                  v-b-popover.hover="'Here you can optionally add any notes that you want to communicate to your customer on your packing slips and new order email notifications. Some examples include heating instructions, expiration periods of your meals, or any personalized message.'"
                  title="Notes For Customer"
                  src="/images/store/popover.png"
                  class="popover-size"
                >
            </p>
            <b-form-textarea
              type="text"
              rows="3"
              v-model="storeSettings.notesForCustomer"
              placeholder="E.G. Heating instructions, meal expiration periods, or any personalized message. This goes on your packing slips & email notifications to your customers."
            ></b-form-textarea>


            <b-button type="submit" variant="primary" class="mt-3">Save</b-button>
          </b-form>
        </div>
      </div>
      <p>Menu</p>
      <div class="card">
        <div class="card-body">
          <p>
          <span class="mr-1">Show Nutrition Facts</span>
          <img
            v-b-popover.hover="'Nutrition facts are generated based on the ingredients you enter in for each meal on your Menu page. The nutrition is then shown to your customers if they click on any of your meals when ordering from you.'"
            title="Show Nutrition Facts"
            src="/images/store/popover.png"
            class="popover-size"
          >
        </p>
          <b-form @submit.prevent="updateStoreSettings">
            <b-form-group :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showNutrition"
                @change.native="updateStoreSettings"
              />
            </b-form-group>
          </b-form>

          <p v-if="storeSettings.showNutrition">
          <span class="mr-1">Show Ingredients</span>
          <img
            v-b-popover.hover="'Ingredients of your meals are listed at the bottom of the nutrition facts that show on your menu. You can choose to show or hide them with this option.'"
            title="Show Ingredients"
            src="/images/store/popover.png"
            class="popover-size"
          >
        </p>
          <b-form @submit.prevent="updateStoreSettings" v-if="storeSettings.showNutrition">
            <b-form-group :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showIngredients"
                @change.native="updateStoreSettings"
              />
            </b-form-group>
          </b-form>

          <p class="mb-0 pb-0">
            <span class="mr-1">Categories</span>
            <img
              v-b-popover.hover="'Categories are ways to group your meals together into different sections that show up on your menu. Some examples include Entrees and Breakfast. You can then rearrange the order of the categories which rearranges the order they are shown on your menu to customers.'"
              title="Categories"
              src="/images/store/popover.png"
              class="popover-size"
            >
          </p>
          <b-form-group :state="true">
            <div class="categories">
              <draggable
                v-model="categories"
                @change="onChangeCategories"
                element="ol"
                class="plain"
              >
                <li
                  v-for="category in categories"
                  :key="`category-${category.id}`"
                  style="cursor: n-resize"
                >
                  <p>
                    {{ category.category }}
                    <i
                      v-if="category.id"
                      @click="deleteCategory(category.id)"
                      class="fa fa-minus-circle text-danger"
                    ></i>
                  </p>
                </li>
              </draggable>
            </div>

            <b-form class="mt-2" @submit.prevent="onAddCategory" inline>
              <b-input v-model="new_category" type="text" placeholder="New Category..."></b-input>
              <b-button type="submit" variant="primary ml-2">Create</b-button>
            </b-form>
          </b-form-group>

          <b-form @submit.prevent="updateStoreSettings">
            <b-form-group :state="true">
              <p>
              <span class="mr-1">Menu Brand Color</span>
              <img
                v-b-popover.hover="'Set the main color to show on your menu for buttons & the top navigation area. Try to match this to the main color of your logo.'"
                title="Menu Brand Color"
                src="/images/store/popover.png"
                class="popover-size"
              >
              </p>
              <swatches v-model="color"></swatches>
              <b-button type="submit" variant="primary mt-2">Save</b-button>
            </b-form-group>
          </b-form>

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
                @change.native="updateStoreSettings"
              />
            </b-form-group>

            <b-form-group label="New Meal Plans" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.new_subscription"
                @change.native="updateStoreSettings"
              />
            </b-form-group>

            <b-form-group label="Cancelled Meal Plans" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.cancelled_subscription"
                @change.native="updateStoreSettings"
              />
            </b-form-group>

            <b-form-group label="Ready to Print" :state="true">
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.notifications.ready_to_print"
                @change.native="updateStoreSettings"
              />
            </b-form-group>

            <!-- <b-button type="submit" variant="primary">Save</b-button> -->
          </b-form>
        </div>
      </div>
      <!-- <div class="card">
        <div class="card-body">
          <p>Automatic Ordering</p>
          
          <p>Low Threshold</p>
        </div>
      </div>-->
      <!--
      <p>Reporting</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings">
              <p>
              Show # Delivery Days By Default
              <img
                v-b-popover.hover="'This sets the default date view in the calendars on all of the tables of the application. You may only want to see information about the next single upcoming delivery day, or you may want to see the next two delivery days. Enter 0 to see all upcoming delivery days.'"
                title="Default Delivery Days"
                src="/images/store/popover.png"
                class="popover-size"
              >
            </p>
            <b-form-group description="Enter 0 to Display All" :state="true">
              <number-input
                v-if="storeSettings"
                type="number"
                :disabled="null === storeSettings.view_delivery_days"
                v-model="storeSettings.view_delivery_days"
                :min="0"
                :max="100"
                placeholder
                required
              ></number-input>
            </b-form-group>

            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      -->

      <p>Payments</p>
      <div class="card">
        <div class="card-body">
          <div v-if="!storeSettings.stripe_id">
            <b-form-group label="Payment Info" :state="true">
              <b-button
                variant="primary"
                :href="`https://connect.stripe.com/express/oauth/authorize?client_id=ca_ER2OUNQq30X2xHMqkWo8ilUSz7Txyn1A&state=${store.id}`"
              >Connect Account</b-button>
            </b-form-group>
          </div>
          <div v-else>
            <b-form-group label="Stripe" :state="true">ID: {{ storeSettings.stripe_id }}</b-form-group>
            <a :href="payments_url" target="_blank">
              <b-button type="submit" variant="primary">View Account</b-button>
            </a>
          </div>
        </div>
      </div>

      <p>Open</p>
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateStoreSettings" v-if="canOpen">
            <p>
              <span class="mr-1">Open</span>
              <img
                v-b-popover.hover="'You can toggle this off to stop showing your menu page and accepting new orders for any reason. Be sure to fill out the reason below to communicate to your customers.'"
                title="Open or Closed"
                src="/images/store/popover.png"
                class="popover-size"
              >
            </p>
            <c-switch
              color="success"
              variant="pill"
              size="lg"
              v-model="storeSettings.open"
            />

            <b-form-input
              v-if="!storeSettings.open"
              type="text"
              v-model="storeSettings.closedReason"
              placeholder="Please include the reason to give to customers as to why you are currently not accepting new orders."
              required
            ></b-form-input>
            <div class="mt-3">
              <b-button type="submit" variant="primary">Save</b-button>
            </div>
          </b-form>
          <div v-else>
            Please enter all settings fields to open your store. 
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
import timezones from "../../../data/timezones.js";
import Swatches from 'vue-swatches';
import "vue-swatches/dist/vue-swatches.min.css";


export default {
  components: {
    cSwitch,
    Swatches
  },
  data() {
    return {
      color: '',
      transferSelected: [],
      transferOptions: [
        { text: "Delivering to Customers", value: "delivery" },
        { text: "Letting Customers Pickup", value: "pickup" }
      ],
      minimumSelected: "price",
      minimumOptions: [
        { text: "Require Minimum Price", value: "price" },
        { text: "Require Minimum Meals", value: "meals" }
      ],
      loginAlertSuccess: false,
      loginAlertFail: false,
      zipCodes: [],
      new_category: "",
      view_delivery_days: 1,
      payments_url: ""
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
      return _.chain(this.storeCategories)
        .orderBy("order")
        .toArray()
        .value();
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
      return _.isArray(this.storeSettings.delivery_distance_zipcodes)
        ? this.storeSettings.delivery_distance_zipcodes.join(",")
        : [];
    },
    transferType() {
      return this.storeSettings.transferType;
    },
    transferTypes() {
      return _.isArray(this.transferSelected)
        ? this.transferSelected.join(",")
        : [];
    },
    transferTypeCheck() {
      if (_.includes(this.transferSelected, "pickup")) {
        return true;
      }
    },
    timezoneOptions() {
      return timezones.selectOptions();
    },
    canOpen() {
      return (this.storeSettings.cutoff_days + this.storeSettings.cutoff_hours > 0 &&
        this.storeSettings.delivery_days.length > 0 &&
        this.storeSettings.delivery_distance_radius > 0 &&
        !_.isEmpty(this.storeSettings.stripe_id));
    },
  },
  created() {

  },
  mounted() {
    this.view_delivery_days = this.storeSettings.view_delivery_days;
    this.color = this.storeSettings.color;

    if (_.isString(this.deliveryDistanceZipcodes)) {
      this.zipCodes = this.deliveryDistanceZipcodes.split(",") || [];
    }
    
    if (_.isString(this.transferType)) {
      this.transferSelected = this.transferType.split(",") || [];
    }

    axios.get("/api/me/stripe/login").then(resp => {
      if (resp.data.url) {
        this.payments_url = resp.data.url;
      }
    });
  },
  methods: {
    ...mapActions(["refreshCategories", "refreshStoreSettings"]),
    updateStoreSettings() {
      this.spliceCharacters();
      let settings = { ...this.storeSettings };

      // Ensure numerical
      if (!_.isNull(this.storeSettings.view_delivery_days)) {
        settings.view_delivery_days = parseInt(
          this.storeSettings.view_delivery_days
        );

        // All
        if (settings.view_delivery_days === 0) {
          settings.view_delivery_days = null;
        }
      }
      settings.transferType = this.transferTypes;
      settings.delivery_distance_zipcodes = this.zipCodes;
      settings.color = this.color;

      // this.spliceCharacters();
      axios
        .patch("/api/me/settings", settings)
        .then(response => {
          // Refresh everything
          this.refreshStoreSettings();

          this.$toastr.s("Your settings have been saved.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        });
    },
    spliceCharacters() {
      if (this.storeSettings.deliveryFee != null) {
        let deliveryFee = this.storeSettings.deliveryFee;
        if (deliveryFee.toString().includes("$")) {
          let intToString = deliveryFee.toString();
          let newFee = intToString.replace("$", "");
          this.storeSettings.deliveryFee = newFee;
        }
      }

      if (this.storeSettings.processingFee != null) {
        let processingFee = this.storeSettings.processingFee;
        if (processingFee.toString().includes("$")) {
          let intToString = processingFee.toString();
          let newFee = intToString.replace("$", "");
          this.storeSettings.processingFee = newFee;
        }
      }

      if (this.storeSettings.mealPlanDiscount != null) {
        let mealPlanDiscount = this.storeSettings.mealPlanDiscount;
        if (this.storeSettings.mealPlanDiscount.toString().includes("%")) {
          let intToString = this.storeSettings.mealPlanDiscount.toString();
          let newDiscount = intToString.replace("%", "");
          this.storeSettings.mealPlanDiscount = newDiscount;
        }
      }

      if (this.storeSettings.minimumPrice != null) {
        let minimumPrice = this.storeSettings.minimumPrice;
        if (this.storeSettings.minimumPrice.toString().includes("$")) {
          let intToString = this.storeSettings.minimumPrice.toString();
          let newPrice = intToString.replace("$", "");
          this.storeSettings.minimumPrice = newPrice;
        }
      }
    },
    createStripeAccount() {
      axios.post("/api/me/stripe").then(resp => {});
    },
    stripeLogIn() {
      axios.get("/api/me/stripe/login").then(resp => {
        if (resp.data.url) {
          window.location = resp.data.url;
        }
      });
    },
    onAddCategory() {
      axios
        .post("/api/me/categories", { category: this.new_category })
        .then(response => {
          this.refreshCategories();
          this.new_category = "";
        });
    },
    onChangeCategories(e) {
      if (_.isObject(e.moved)) {
        let newCats = _.toArray({ ...this.categories });
        newCats[e.moved.oldIndex] = this.categories[e.moved.newIndex];
        newCats[e.moved.newIndex] = this.categories[e.moved.oldIndex];

        newCats = _.map(newCats, (cat, i) => {
          cat.order = i;
          return cat;
        });

        axios
          .post("/api/me/categories", { categories: newCats })
          .then(response => {
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
    onChangeTimezone(val) {
      if (val) {
        this.storeSettings.timezone = val.value;
      }
    },
    updateZips(e) {
      this.zipCodes = e.target.value.split(",");
    }
  }
};
</script>