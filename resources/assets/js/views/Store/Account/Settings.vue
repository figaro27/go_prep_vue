<template>
  <div class="row mt-2">
    <div class="col-md-8 offset-md-2">
      <b-alert :show="!canOpen" variant="success"
        >Welcome to GoPrep! Enter all settings to open your store for
        business.</b-alert
      >
      <b-row>
        <b-col>
          <b-tabs>
            <b-tab title="Orders" active>
              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group
                  label="Delivery / Pickup Day(s)"
                  label-for="delivery-days"
                  :state="true"
                >
                  <b-form-checkbox-group
                    buttons
                    v-model="storeSettings.delivery_days"
                    class="storeFilters"
                    @change="val => onChangeDeliveryDays(val)"
                    :options="[
                      { value: 'sun', text: 'Sunday' },
                      { value: 'mon', text: 'Monday' },
                      { value: 'tue', text: 'Tuesday' },
                      { value: 'wed', text: 'Wednesday' },
                      { value: 'thu', text: 'Thursday' },
                      { value: 'fri', text: 'Friday' },
                      { value: 'sat', text: 'Saturday' }
                    ]"
                  ></b-form-checkbox-group>
                  <img
                    v-b-popover.hover="
                      'These are the day(s) you plan on delivering your order or allowing pickup to your customers and will show up as options on the checkout page for the customer. Please choose at least one day to allow orders on your menu.'
                    "
                    title="Delivery / Pickup Day(s)"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </b-form-group>
                <b-modal
                  size="md"
                  v-model="showCutoffModal"
                  title="Warning"
                  hide-footer
                >
                  <h6 class="center-text mt-3">
                    You potentially have active subscriptions that are charged
                    and turned into orders at this cutoff period.
                  </h6>
                  <p class="center-text mt-3">
                    If you change your cutoff time, those active subscriptions
                    won't be adjusted. They will still turn into orders at the
                    time of your old cutoff.
                  </p>
                  <p class="center-text mt-3">
                    If this is an issue, please either keep this cutoff period,
                    or you can individually cancel subscriptions on the
                    Subscriptions page.
                  </p>
                  <b-btn
                    class="center"
                    variant="primary"
                    @click="showCutoffModal = false"
                    >Confirm</b-btn
                  >
                </b-modal>
                <b-modal
                  size="xl"
                  ref="deliveryDaysModal"
                  title="Warning"
                  hide-footer
                >
                  <h6 class="center-text mt-3">
                    There are active subscriptions associated with this delivery
                    day. Please choose one of the three options below to
                    continue.
                  </h6>
                  <div class="row mt-5">
                    <div class="col-sm-4">
                      <p class="center-text">
                        Cancel my action and keep this delivery day active for
                        future subscriptions & orders.
                      </p>
                    </div>
                    <div class="col-sm-4">
                      <p class="center-text">
                        Remove this delivery day, but I will still fulfill meal
                        plan orders attached to this day.
                      </p>
                    </div>
                    <div class="col-sm-4">
                      <p class="center-text">
                        Remove this delivery day for future orders, and cancel
                        all my subscriptions attached to this day.
                      </p>
                    </div>
                  </div>
                  <div class="row mb-5">
                    <div class="col-sm-4">
                      <b-btn
                        variant="success"
                        class="center"
                        @click="hideDeliveryDaysModal"
                        >Keep Day</b-btn
                      >
                    </div>
                    <div class="col-sm-4">
                      <b-btn
                        variant="warning"
                        class="center"
                        @click="removeDeliveryDay"
                        >Remove Day & Honor</b-btn
                      >
                    </div>
                    <div class="col-sm-4">
                      <b-btn
                        variant="danger"
                        class="center"
                        @click="cancelMealPlans"
                        >Remove Day & Cancel</b-btn
                      >
                    </div>
                  </div>
                </b-modal>

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
                      { value: 'zipcodes', text: 'Zip Codes' }
                    ]"
                  ></b-form-radio-group>
                  <img
                    v-b-popover.hover="
                      'As you do local delivery, you may have a certain cutoff distance. Here you can set this distance by radius by the number of miles around you or by zip codes separated by commas. If you offer pickup, and the customer chooses pickup, this will not apply to them.'
                    "
                    title="Delivery Distance Type"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
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
                    @input="
                      e => {
                        updateZips(e);
                      }
                    "
                    class="form-control"
                    placeholder="Zip Codes"
                  ></textarea>
                </b-form-group>

                <b-form-group
                  label="Cut Off Period Type"
                  label-for="cut-off-period-type"
                  :state="true"
                  inline
                >
                  <b-form-radio-group
                    v-model="storeSettings.cutoff_type"
                    :options="[
                      { text: 'Timed', value: 'timed' },
                      { text: 'Single Day', value: 'single_day' }
                    ]"
                  >
                    <img
                      v-b-popover.hover="
                        'If you only have one delivery/pickup day, then choose either option and it will work the same way. Timed Example: Your delivery days are Sunday and Wednesday, and you set the Cut Off Period to 1 day and 12 hours. This will lock in orders for Sunday on Friday at 12 PM and lock in orders for Wednesday on Monday at 12 PM. Single Day Example: Your delivery days are Sunday and Wednesday, and you set the Cut Off Day to Friday at 12 PM. This locks in orders for BOTH Sunday & Wednesday on Friday at 12 PM.'
                      "
                      title="Cut Off Type"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </b-form-radio-group>
                </b-form-group>

                <b-form-group
                  :label="
                    storeSettings.cutoff_type === 'timed'
                      ? 'Cut Off Period'
                      : 'Cut Off Day'
                  "
                  label-for="cut-off-period"
                  :state="true"
                  inline
                >
                  <b-select
                    v-model="storeSettings.cutoff_days"
                    class="d-inline w-auto mr-1"
                    :options="cutoffDaysOptions"
                    @change.native="checkCutoffMealPlans"
                  ></b-select>
                  <b-select
                    v-model="storeSettings.cutoff_hours"
                    class="d-inline w-auto mr-1 custom-select"
                    :options="cutoffHoursOptions"
                    @change.native="checkCutoffMealPlans"
                  ></b-select>
                  <img
                    v-b-popover.hover="
                      'This is the amount of time you want to lock in orders before a specific delivery day. For example - you set the cut off period to 1 day, and it is currently Tuesday. If you have a Wednesday delivery day, your customer will not see Wednesday as a delivery day option. They will see the next available delivery day. This prevents you from getting new orders right before your delivery day and possibly already after you prepped your orders for that day.'
                    "
                    title="Cut Off Period"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </b-form-group>

                <b-form-group>
                  <b-form-radio-group
                    v-model="storeSettings.minimumOption"
                    :options="minimumOptions"
                  ></b-form-radio-group>
                </b-form-group>

                <b-form-group
                  :state="true"
                  v-if="storeSettings.minimumOption === 'price'"
                >
                  <p>
                    <span class="mr-1">Minimum Price Requirement</span>
                    <img
                      v-b-popover.hover="
                        'Here you can set a minimum price required before a customer can place an order. Leave it at 0 if you have no minimum requirement.'
                      "
                      title="Minimum Price Requirement"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-input
                    type="text"
                    v-model="storeSettings.minimumPrice"
                    placeholder="Minimum Price"
                    required
                  ></b-form-input>
                </b-form-group>
                <b-form-group
                  :state="true"
                  v-if="storeSettings.minimumOption === 'meals'"
                >
                  <p>
                    <span class="mr-1">Minimum Items Requirement</span>
                    <img
                      v-b-popover.hover="
                        'Here you can set a minimum number of items required before a customer can place an order. Leave it at 0 if you have no minimum requirement.'
                      "
                      title="Minimum Items Requirement"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-input
                    type="text"
                    v-model="storeSettings.minimumMeals"
                    placeholder="Minimum Number of Items"
                    required
                  ></b-form-input>
                </b-form-group>
                <b-form-group :state="true">
                  <p>
                    <span class="mr-1">Weekly Subscription Discount</span>
                    <img
                      v-b-popover.hover="
                        'Give your customers an incentive to create a weekly subscription with you by offering a discount percentage. Please keep in mind the customer can still cancel at any time after their first order.'
                      "
                      title="Weekly Subscription Discount"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
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
                    placeholder="Weekly Subscription Discount %"
                    required
                  ></b-form-input>
                </b-form-group>
                <b-form-group :state="true">
                  <p>
                    <span class="mr-1">Delivery Fee</span>
                    <img
                      v-b-popover.hover="
                        'Here you can apply an optional delivery fee paid for by your customers.'
                      "
                      title="Delivery Fee"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.applyDeliveryFee"
                  />

                  <div v-if="storeSettings.applyDeliveryFee">
                    <p>
                      <span class="mr-1">Delivery Fee Type</span>
                      <img
                        v-b-popover.hover="
                          'Either choose to apply a flat fee no matter how far the customer is, or a fee based on the distance of the customer in miles.'
                        "
                        title="Delivery Fee Type"
                        src="/images/store/popover.png"
                        class="popover-size"
                      />
                    </p>
                    <b-form-radio-group v-model="storeSettings.deliveryFeeType">
                      <b-form-radio name="flat" value="flat">Flat</b-form-radio>
                      <b-form-radio name="mileage" value="mileage"
                        >Mileage</b-form-radio
                      >
                    </b-form-radio-group>

                    <b-form-input
                      class="mt-3"
                      v-if="
                        storeSettings.applyDeliveryFee &&
                          storeSettings.deliveryFeeType === 'flat'
                      "
                      type="text"
                      v-model="storeSettings.deliveryFee"
                      placeholder="Delivery Fee"
                      required
                    ></b-form-input>
                    <div class="row">
                      <div class="col-md-6">
                        <b-form-input
                          class="mt-3"
                          v-if="
                            storeSettings.applyDeliveryFee &&
                              storeSettings.deliveryFeeType === 'mileage'
                          "
                          type="text"
                          v-model="storeSettings.mileageBase"
                          placeholder="Base Amount"
                          required
                        ></b-form-input>
                      </div>
                      <div class="col-md-6">
                        <b-form-input
                          class="mt-3"
                          v-if="
                            storeSettings.applyDeliveryFee &&
                              storeSettings.deliveryFeeType === 'mileage'
                          "
                          type="text"
                          v-model="storeSettings.mileagePerMile"
                          placeholder="Per Mile"
                          required
                        ></b-form-input>
                      </div>
                    </div>
                  </div>
                </b-form-group>
                <b-form-group :state="true">
                  <p>
                    <span class="mr-1">Processing Fee</span>
                    <img
                      v-b-popover.hover="
                        'Here you can apply an optional processing fee paid for by your customers.'
                      "
                      title="Processing Fee"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.applyProcessingFee"
                  />
                </b-form-group>

                <div v-if="storeSettings.applyProcessingFee">
                  <p>
                    <span class="mr-1">Processing Fee Type</span>
                    <img
                      v-b-popover.hover="
                        'Either choose to apply a flat processing fee or a percentage amount based off of the subtotal.'
                      "
                      title="Processing Fee Type"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-radio-group
                    v-model="storeSettings.processingFeeType"
                    class="mt-2 mb-2"
                  >
                    <b-form-radio name="flat" value="flat">Flat</b-form-radio>
                    <b-form-radio name="percent" value="percent"
                      >Percent</b-form-radio
                    >
                  </b-form-radio-group>
                  <b-form-input
                    class="mb-4"
                    type="text"
                    v-model="storeSettings.processingFee"
                    placeholder="Processing Fee"
                    required
                  ></b-form-input>
                </div>

                <b-form-group label="I Will Be:">
                  <b-form-checkbox-group
                    v-model="transferSelected"
                    :options="transferOptions"
                  ></b-form-checkbox-group>
                </b-form-group>
                <p v-if="transferTypeCheckDelivery">Delivery Instructions:</p>
                <b-form-textarea
                  v-if="transferTypeCheckDelivery"
                  type="text"
                  rows="3"
                  v-model="storeSettings.deliveryInstructions"
                  placeholder="Please include delivery instructions to your customers (time window, how long your driver will wait, etc.) This will be shown on the checkout page as well as email notifications the customer receives."
                  class="mb-2"
                ></b-form-textarea>
                <p v-if="transferTypeCheckPickup">Pickup Instructions:</p>
                <b-form-textarea
                  v-if="transferTypeCheckPickup"
                  type="text"
                  rows="3"
                  v-model="storeSettings.pickupInstructions"
                  placeholder="Please include pickup instructions to your customers (pickup address, phone number, and time). This will be shown on the checkout page as well as email notifications the customer receives."
                ></b-form-textarea>

                <p class="mt-2">
                  <span class="mr-1">Notes For Customer</span>
                  <img
                    v-b-popover.hover="
                      'Here you can optionally add any notes that you want to communicate to your customer on your packing slips and new order email notifications. Some examples include heating instructions, expiration periods of your meals, or any personalized message. This will be shown on your packing slips as well as email notifications the customer receives.'
                    "
                    title="Notes For Customer"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <b-form-textarea
                  type="text"
                  rows="3"
                  v-model="storeSettings.notesForCustomer"
                  placeholder="E.G. Heating instructions, meal expiration periods, or any personalized message. This goes on your packing slips & email notifications to your customers."
                ></b-form-textarea>

                <b-button type="submit" variant="primary" class="mt-3"
                  >Save</b-button
                >
              </b-form>
            </b-tab>
            <b-tab title="Menu">
              <p>
                <span class="mr-1">Show Nutrition Facts</span>
                <img
                  v-b-popover.hover="
                    'Nutrition facts are generated based on the ingredients you enter in for each meal on your Menu page. The nutrition is then shown to your customers if they click on any of your meals when ordering from you.'
                  "
                  title="Show Nutrition Facts"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
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
              <p>
                <span class="mr-1">Show Macros</span>
                <img
                  v-b-popover.hover="
                    'Enables input fields for your to add your meal\'s calories, carbs, protein, and fat. This then shows up underneath your meal titles on your menu page. If you have Nutrition Facts enabled as well, please keep the numbers the same as your customers will see any differences.'
                  "
                  title="Show Macros"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.showMacros"
                    @change.native="updateStoreSettings"
                  />
                </b-form-group>
              </b-form>
              <p>
                <span class="mr-1">Show Ingredients</span>
                <img
                  v-b-popover.hover="
                    'Ingredients of your meals are shown for each meal on your menu if the user clicks on the meal. You can choose to show or hide them with this option.'
                  "
                  title="Show Ingredients"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <b-form @submit.prevent="updateStoreSettings">
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

              <p>
                <span class="mr-1">Allow Meal Packages</span>
                <img
                  v-b-popover.hover="
                    'Enables a button on your menu page which lets you create meal packages. These are groupings of individual meals and quantities on your menu and are displayed to your customer on your menu through a scrolling carousel. Enabling this also adds a Packages category to your menu.'
                  "
                  title="Allow Meal Packages"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.meal_packages"
                    @change.native="updateStoreSettings"
                  />
                </b-form-group>
              </b-form>

              <p>
                <span class="mr-1">Allow Weekly Subscriptions</span>
                <img
                  v-b-popover.hover="
                    'Shows a section on your bag/checkout page that lets the customer opt in for a weekly subscription for an optional discount. The customer will then be charged every week. They can pause, cancel, or change meals in their subscriptions.'
                  "
                  title="Allow Weekly Subscriptions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.allowMealPlans"
                    @change.native="updateStoreSettings"
                  />
                </b-form-group>
              </b-form>

              <p>
                <span class="mr-1">Enable Meal Instructions</span>
                <img
                  v-b-popover.hover="
                    'Adds a new form field on each of your meals on the Menu page on your dashboard. Within that box you can type in special instructions specific to that particular meal such as heating instructions. If your customer orders that particular meal, these instructions will then be shown in their packing slips & email receipts. This is similar to the \'Notes for Customer\' option above, but for particular meals.'
                  "
                  title="Enable Meal Instructions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.mealInstructions"
                    @change.native="updateStoreSettings"
                  />
                </b-form-group>
              </b-form>

              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group :state="true" v-if="!storeModules.hideBranding">
                  <p>
                    <span class="mr-1">Menu Brand Color</span>
                    <img
                      v-b-popover.hover="
                        'Set the main color to show on your menu for buttons & the top navigation area. Try to match this to the main color of your logo. If you would like a color not shown in the color picker, please contact us.'
                      "
                      title="Menu Brand Color"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <swatches v-model="color"></swatches>
                </b-form-group>
                <b-form-group :state="true" v-if="!storeModules.hideBranding">
                  <p>
                    <span class="mr-1">Main Website URL</span>
                    <img
                      v-b-popover.hover="
                        'Optionally link up your main website to your menu and checkout page. If your customer clicks your logo they will be redirected back to your main website.'
                      "
                      title="Main Website URL"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-input
                    id="website"
                    v-model="storeSettings.website"
                    placeholder="Example: http://goprep.com"
                  ></b-form-input>
                </b-form-group>
                <p>Menu Style</p>
                <b-form-radio-group
                  buttons
                  v-model="storeSettings.menuStyle"
                  class="storeFilters"
                  @input.native="updateStoreSettings"
                  :options="[
                    { value: 'image', text: 'Image Based' },
                    { value: 'text', text: 'Text Based' }
                  ]"
                ></b-form-radio-group>
                <img
                  v-b-popover.hover="
                    'Choose image based if you have images of your meals and want to showcase your meals visually. Choose text based if you don\'t have many images and want to emphasize descriptions more. Text based still shows images of your meals but in a much smaller size.'
                  "
                  title="Menu Style"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
                <br /><br />
                <router-link
                  v-if="!storeURLcheck"
                  :to="'/store/menu/preview'"
                  class="btn btn-warning btn-md"
                  tag="button"
                  >Preview Menu</router-link
                >
                <a
                  :href="storeURL"
                  v-if="storeURLcheck"
                  class="btn btn-warning btn-md"
                  tag="button"
                  >Preview Menu</a
                >
                <br /><br />
                <b-button type="submit" variant="primary mt-2">Save</b-button>
              </b-form>
            </b-tab>
            <b-tab title="Coupons">
              <p>
                <span class="mr-1">Coupons</span>
                <img
                  v-b-popover.hover="
                    'Add a coupon that your customers can use on your checkout page. Choose the type - either an overall percentage of the bag or a flat amount.'
                  "
                  title="Coupons"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <v-client-table
                :columns="columns"
                :data="tableData"
                :options="{
                  orderBy: {
                    column: 'id',
                    ascending: true
                  },
                  headings: {
                    freeDelivery: 'Free Delivery',
                    oneTime: 'One Time'
                  },
                  filterable: false
                }"
              >
                <div slot="beforeTable" class="mb-2">
                  <b-form @submit.prevent="saveCoupon">
                    <b-form-group id="coupon">
                      <div class="row">
                        <div class="col-md-2">
                          <b-form-input
                            id="coupon-code"
                            v-model="coupon.code"
                            required
                            placeholder="Enter Coupon Code"
                          ></b-form-input>
                        </div>
                        <div class="col-md-2">
                          <b-form-radio-group v-model="coupon.type">
                            <div class="row">
                              <div class="col-md-6 pt-2">
                                <b-form-radio name="coupon-type" value="flat"
                                  >Flat</b-form-radio
                                >
                              </div>
                              <div class="col-md-6 pt-2">
                                <b-form-radio name="coupon-type" value="percent"
                                  >Percent</b-form-radio
                                >
                              </div>
                            </div>
                          </b-form-radio-group>
                        </div>
                        <div class="col-md-2">
                          <b-form-input
                            id="coupon-code"
                            v-model="coupon.amount"
                            placeholder="Enter Amount"
                          ></b-form-input>
                        </div>
                        <div class="col-md-2">
                          <b-form-checkbox
                            v-model="coupon.freeDelivery"
                            value="1"
                            unchecked-value="0"
                            class="pt-2"
                          >
                            Free Delivery
                          </b-form-checkbox>
                        </div>
                        <div class="col-md-2">
                          <b-form-checkbox
                            v-model="coupon.oneTime"
                            value="1"
                            unchecked-value="0"
                            class="pt-2"
                          >
                            One Time
                          </b-form-checkbox>
                        </div>
                        <div class="col-md-1">
                          <b-button type="submit" variant="success"
                            >Add</b-button
                          >
                        </div>
                      </div>
                    </b-form-group>
                  </b-form>
                </div>

                <div slot="freeDelivery" slot-scope="props">
                  <p v-if="props.row.freeDelivery" class="text-success">✓</p>
                  <p v-if="!props.row.freeDelivery" class="red">X</p>
                </div>

                <div slot="oneTime" slot-scope="props">
                  <p v-if="props.row.oneTime" class="text-success">✓</p>
                  <p v-if="!props.row.oneTime" class="red">X</p>
                </div>

                <div
                  slot="actions"
                  slot-scope="props"
                  v-if="props.row.id !== -1"
                >
                  <b-btn
                    variant="danger"
                    size="sm"
                    @click="e => deleteCoupon(props.row.id)"
                    >Delete</b-btn
                  >
                </div>
              </v-client-table>
            </b-tab>
            <b-tab title="Notifications">
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

                <b-form-group label="New Subscriptions" :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.notifications.new_subscription"
                    @change.native="updateStoreSettings"
                  />
                </b-form-group>

                <b-form-group label="Cancelled Subscriptions" :state="true">
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
              </b-form>
            </b-tab>
            <b-tab title="Other">
              <p>https://{{ storeDetails.domain }}.goprep.com</p>
              <b-form
                @submit.prevent="updateStoreLogo"
                v-if="!storeModules.hideBranding"
              >
                <b-form-group label="Logo" :state="true">
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
                <div class="mt-3">
                  <b-button type="submit" variant="primary">Save</b-button>
                </div>
              </b-form>
              <div v-if="!storeSettings.stripe_id">
                <b-form-group :state="true">
                  <b-button variant="primary" :href="stripeConnectUrl"
                    >Connect Bank Account</b-button
                  >
                </b-form-group>
              </div>
              <div v-else class="mt-2">
                <b-form-group :state="true"
                  >ID: {{ storeSettings.stripe_id }}</b-form-group
                >
                <a :href="payments_url" target="_blank">
                  <b-button type="submit" variant="primary"
                    >View Stripe Account</b-button
                  >
                </a>
              </div>
              <b-form @submit.prevent="updateStoreSettings">
                <p class="mt-3">
                  <span class="mr-1">Sales Tax</span>
                  <img
                    v-b-popover.hover="
                      'Here you can turn sales tax on or off for your customers.'
                    "
                    title="Enable Sales Tax"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeSettings.enableSalesTax"
                />
                <div v-if="storeSettings.enableSalesTax">
                  <p class="mt-3 mb-0 pb-0">
                    <span class="mr-1">Sales Tax %</span>
                    <img
                      v-b-popover.hover="
                        'Our system figures out your sales tax using the state you signed up with. You can override the number in the field below.'
                      "
                      title="Sales Tax"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-group :state="true">
                    <b-form-input
                      label="Sales Tax"
                      class="mt-3"
                      type="text"
                      v-model="salesTax"
                      required
                    ></b-form-input>
                  </b-form-group>
                </div>
                <p class="mt-2">
                  <span class="mr-1">Google Analytics Code</span>
                  <img
                    v-b-popover.hover="
                      'Create a Google Analytics account and paste in your tracking code below. You\'ll then be able to see all kinds of traffic reports about who viewed your menu page. Please follow the exact format that is shown to you which looks like this: UA-00000000-00. If you need help setting up your Google Analytics account, please contact us and we\'ll be glad to set it up for you.'
                    "
                    title="Google Analytics"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <b-form-group :state="true" class="mt-2">
                  <b-form-input
                    type="text"
                    v-model="storeSettings.gaCode"
                    placeholder="UA-00000000-00"
                    required
                  ></b-form-input>
                </b-form-group>
                <b-button type="submit" variant="primary">Save</b-button>
              </b-form>
              <b-form @submit.prevent="closeStore" v-if="canOpen">
                <p class="mt-2">
                  <span class="mr-1 mt-2">Open</span>
                  <img
                    v-b-popover.hover="
                      'You can toggle this off to stop accepting new orders from customers for any reason. Please be sure to fill out the reason below to communicate to your customers.'
                    "
                    title="Open or Closed"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeSettings.open"
                  @change.native="checkTOAforModal"
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
            </b-tab>
          </b-tabs>
        </b-col>
      </b-row>

      <b-modal
        v-model="showTOAModal"
        title="Service Agreement"
        size="xl"
        @ok="allowOpen"
        @cancel="allowOpen"
        @hidden="allowOpen"
      >
        <termsOfAgreement></termsOfAgreement>
        <center>
          <b-form-checkbox
            v-model="acceptedTOAcheck"
            value="1"
            unchecked-value="0"
            >I accept these terms.</b-form-checkbox
          >
        </center>
      </b-modal>

      <b-modal v-model="showMealPlansModal" title="Warning">
        <p>
          You currently have at least one active subscription with your
          customers. If you temporarily close your store, all of your customer's
          subscriptions will be automatically paused and will not resume when
          you re-open. These customers will be notified via email.
        </p>
        <p class="center-text">Continue anyway?</p>
        <b-btn variant="danger" class="center" @click="pauseMealPlans"
          >Continue</b-btn
        >
      </b-modal>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.VueTables__search {
  display: none;
}
</style>

<style>
.nav-tabs .nav-item {
  margin-bottom: -10px;
  margin-right: 2px;
}
</style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import timezones from "../../../data/timezones.js";
import currencies from "../../../data/currencies.js";
import Swatches from "vue-swatches";
import "vue-swatches/dist/vue-swatches.min.css";
import fs from "../../../lib/fs.js";
import TermsOfService from "../../TermsOfService";
import TermsOfAgreement from "../../TermsOfAgreement";
import SalesTax from "sales-tax";

export default {
  components: {
    cSwitch,
    Swatches,
    TermsOfService,
    TermsOfAgreement
  },
  data() {
    return {
      acceptedTOA: 0,
      acceptedTOAcheck: 0,
      showTOAModal: 0,
      showMealPlansModal: false,
      color: "",
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
      view_delivery_days: 1,
      payments_url: "",
      coupon: { type: "flat", freeDelivery: 0, oneTime: 0 },
      columns: ["code", "type", "amount", "freeDelivery", "oneTime", "actions"],
      deselectedDeliveryDay: null,
      showCutoffModal: false,
      stripeConnectUrl: null,
      salesTax: 0
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "store",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories",
      storeSubscriptions: "storeSubscriptions",
      storeCoupons: "storeCoupons",
      storeModules: "storeModules"
    }),
    tableData() {
      if (this.storeCoupons.length > 0) return this.storeCoupons;
      else return [];
    },
    storeDetails() {
      return this.storeDetail;
    },
    // storeDetail(){
    //     return this.store.store_detail;
    // }
    cutoffDaysOptions() {
      let options = [];
      for (let i = 0; i <= 7; i++) {
        if (this.storeSettings.cutoff_type === "timed") {
          options.push({ value: i, text: i + " Days" });
        } else if (this.storeSettings.cutoff_type === "single_day" && i < 7) {
          options.push({ value: i, text: moment(i, "e").format("dddd") });
        }
      }
      return options;
    },
    cutoffHoursOptions() {
      let options = [];
      for (let i = 0; i <= 23; i++) {
        if (this.storeSettings.cutoff_type === "timed") {
          options.push({ value: i, text: i + " Hours" });
        } else if (this.storeSettings.cutoff_type === "single_day") {
          options.push({ value: i, text: moment(i, "H").format("HH:00") });
        }
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
    transferTypeCheckDelivery() {
      if (_.includes(this.transferSelected, "delivery")) {
        return true;
      }
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferSelected, "pickup")) {
        return true;
      }
    },
    timezoneOptions() {
      return timezones.selectOptions();
    },
    currencyOptions() {
      return currencies.selectOptions();
    },
    canOpen() {
      return (
        this.storeSettings.cutoff_days + this.storeSettings.cutoff_hours > 0 &&
        this.storeSettings.delivery_days.length > 0 &&
        this.storeSettings.delivery_distance_radius > 0 &&
        !_.isEmpty(this.storeSettings.stripe_id)
      );
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
  created() {
    axios.get("/api/me/stripe/connect/url").then(resp => {
      this.stripeConnectUrl = resp.data;
    });
  },
  mounted() {
    if (this.storeSettings.salesTax >= 0) {
      this.salesTax = this.storeSettings.salesTax;
    } else {
      this.getSalesTax(this.storeDetail.state);
    }

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

    this.checkAcceptedTOA();
  },
  methods: {
    ...mapActions([
      "refreshCategories",
      "refreshStoreSettings",
      "refreshStoreCoupons"
    ]),
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
      settings.salesTax = this.salesTax;
      settings.transferType = this.transferTypes;
      settings.delivery_distance_zipcodes = this.zipCodes;
      settings.color = this.color;

      axios
        .patch("/api/me/settings", settings)
        .then(response => {
          this.refreshStoreSettings();
          this.$toastr.s("Your settings have been saved.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        });
    },
    saveCoupon() {
      this.spliceCharacters();
      axios
        .post("/api/me/coupons", this.coupon)
        .then(response => {
          this.coupon = {
            type: "flat",
            freeDelivery: 0,
            oneTime: 0
          };
          this.$toastr.s("Coupon Added", "Success");
        })
        .catch(response => {
          this.$toastr.e("Failed to add coupon.", "Error");
        })
        .finally(() => {
          this.refreshStoreCoupons();
        });
    },
    deleteCoupon(id) {
      axios
        .delete("/api/me/coupons/" + id)
        .then(response => {
          this.$toastr.s("Coupon Deleted", "Success");
        })
        .finally(() => {
          this.refreshStoreCoupons();
        });
    },
    closeStore() {
      let activeSubscriptions = false;

      this.storeSubscriptions.forEach(subscription => {
        if (subscription.status === "active") activeSubscriptions = true;
      });

      if (this.storeSettings.open === false && activeSubscriptions) {
        this.showMealPlansModal = true;
        return;
      }

      let settings = { ...this.storeSettings };

      axios
        .patch("/api/me/settings", settings)
        .then(response => {
          this.refreshStoreSettings();
          this.$toastr.s("Your settings have been saved.", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        });
    },
    updateStoreLogo() {
      let data = { ...this.storeDetails };
      axios.patch("/api/me/updateLogo", data);
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

      if (this.storeSettings.mileageBase != null) {
        let mileageBase = this.storeSettings.mileageBase;
        if (mileageBase.toString().includes("$")) {
          let intToString = mileageBase.toString();
          let newFee = intToString.replace("$", "");
          this.storeSettings.mileageBase = newFee;
        }
      }

      if (this.storeSettings.mileagePerMile != null) {
        let mileagePerMile = this.storeSettings.mileagePerMile;
        if (mileagePerMile.toString().includes("$")) {
          let intToString = mileagePerMile.toString();
          let newFee = intToString.replace("$", "");
          this.storeSettings.mileagePerMile = newFee;
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

      if (this.coupon.amount != null) {
        let couponAmount = this.coupon.amount;
        if (this.coupon.amount.toString().includes("$")) {
          let intToString = this.coupon.amount.toString();
          let newPrice = intToString.replace("$", "");
          this.coupon.amount = newPrice;
        }
      }

      if (this.coupon.amount != null) {
        let couponAmount = this.coupon.amount;
        if (this.coupon.amount.toString().includes("%")) {
          let intToString = this.coupon.amount.toString();
          let newPrice = intToString.replace("%", "");
          this.coupon.amount = newPrice;
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
    toast(type) {
      switch (type) {
        case "s":
          this.$toastr.s("message", "Success");
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
    onChangeDeliveryDays(days) {
      // Get unselected day
      let diff = _.difference(this.storeSettings.delivery_days, days);

      if (_.isEmpty(diff)) {
        return;
      }

      const deselected = diff[0];

      // Deselected day has active subscriptions
      if (_.includes(this.storeSettings.subscribed_delivery_days, deselected)) {
        // Add deselected day back for now
        this.$nextTick(() => {
          this.storeSettings.delivery_days = [
            ...this.storeSettings.delivery_days,
            deselected
          ];
        });

        // Store deselected day for later
        this.deselectedDeliveryDay = deselected;

        // Show modal
        this.$refs.deliveryDaysModal.show();
      }
    },
    removeDeliveryDay() {
      let day = this.deselectedDeliveryDay;
      let index = this.storeSettings.delivery_days.indexOf(day);
      this.storeSettings.delivery_days.splice(index, 1);
      this.$refs.deliveryDaysModal.hide();
    },
    updateZips(e) {
      this.zipCodes = e.target.value.split(",");
    },
    async updateLogo(logo) {
      let b64 = await fs.getBase64(this.$refs.storeImageInput.file);
      this.storeDetail.logo = b64;
    },
    checkAcceptedTOA() {
      axios.get("/api/me/getAcceptedTOA").then(resp => {
        this.acceptedTOA = resp.data;
      });
    },
    checkTOAforModal() {
      if (this.acceptedTOA === 0) {
        this.showTOAModal = 1;
      }
    },
    allowOpen() {
      if (this.acceptedTOAcheck === "1") {
        axios.get("/api/me/acceptedTOA");
        this.storeSettings.open = true;
      } else this.storeSettings.open = false;
    },
    pauseMealPlans() {
      axios.post("/api/me/pauseMealPlans", {
        closedReason: this.storeSettings.closedReason
      });
      this.showMealPlansModal = false;
      this.$toastr.s("Your settings have been saved.", "Success");
    },
    cancelMealPlans() {
      this.removeDeliveryDay();
      axios.post("/api/me/cancelMealPlans", {
        deliveryDay: this.deselectedDeliveryDay
      });
      this.$refs.deliveryDaysModal.hide();
      this.$toastr.s("Your settings have been saved.", "Success");
    },
    hideDeliveryDaysModal() {
      this.$refs.deliveryDaysModal.hide();
    },
    checkCutoffMealPlans() {
      if (this.storeSubscriptions.length > 0) {
        this.showCutoffModal = true;
      }
    },
    getSalesTax(state) {
      SalesTax.getSalesTax("US", state).then(tax => {
        this.setSalesTax(tax.rate);
      });
    },
    setSalesTax(rate) {
      this.salesTax = rate * 100;
    }
  }
};
</script>
