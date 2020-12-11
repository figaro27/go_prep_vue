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
              <b-alert
                show
                dismissible
                style="width:650px;background-color:#EBFAFF"
                class="mb-2"
              >
                <p class="strong pt-3">
                  Update - There is no longer a Save button. Every setting will
                  now save automatically.
                </p>
              </b-alert>
              <div v-if="!storeSettings.stripe_id">
                <b-form-group :state="true">
                  <b-button variant="primary" :href="stripeConnectUrl"
                    >Connect Bank Account</b-button
                  >
                </b-form-group>
              </div>
              <div v-else class="mt-3">
                <!-- <b-form-group :state="true"
                    >ID: {{ storeSettings.stripe_id }}</b-form-group
                  > -->
                <a :href="payments_url" target="_blank">
                  <b-button variant="primary">View Stripe Account</b-button>
                </a>
              </div>

              <b-form-group :state="true" class="mt-4">
                <picture-input
                  :ref="`storeImageInput`"
                  :prefill="logoPrefill"
                  @prefill="$refs[`storeImageInput`].onResize()"
                  :alertOnError="false"
                  :autoToggleAspectRatio="true"
                  margin="0"
                  size="10"
                  button-class="btn"
                  style="width: 180px; height: 180px; margin: 0;"
                  @change="val => updateLogo(val)"
                ></picture-input>
              </b-form-group>
              <br /><br /><br />
              <b-form @submit.prevent="updateStoreSettings(true)">
                <b-form-group label="Transfer Type" v-if="!customDeliveryDays">
                  <b-form-checkbox-group
                    v-model="transferSelected"
                    :options="transferOptions"
                    @input="updateStoreSettings()"
                  ></b-form-checkbox-group>
                </b-form-group>

                <b-form-group
                  v-if="!customDeliveryDays"
                  label-for="delivery-days"
                  :state="true"
                  class="mt-4"
                >
                  <p>
                    Delivery / Pickup Days
                    <img
                      v-b-popover.hover="
                        'These are the day(s) you plan on delivering your order or allowing pickup to your customers and will show up as options on the checkout page for the customer. At least one day needs to be set to allow ordering.'
                      "
                      title="Delivery / Pickup Day(s)"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-checkbox-group
                    buttons
                    v-model="delivery_days"
                    class="storeFilters"
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
                    @click="(showCutoffModal = false), updateStoreSettings()"
                    >Confirm</b-btn
                  >
                </b-modal>

                <b-modal
                  size="md"
                  v-model="deliveryFeeZipCodeModal"
                  title="Delivery Fee By Zip Code"
                  @ok="updateDeliveryFeeZipCodes"
                  @cancel="deliveryFeeZipCodes = []"
                >
                  <center>
                    <p class="strong">Add By City</p>
                  </center>
                  <div
                    class="d-flex m-3"
                    style="justify-content:space-between;"
                  >
                    <b-form-input
                      v-model="deliveryFeeCity.city"
                      placeholder="City"
                      class="mr-1"
                    ></b-form-input>
                    <b-select
                      label="State"
                      :options="getStateNames('US')"
                      v-model="deliveryFeeCity.state"
                      class="mr-1"
                    ></b-select>
                    <b-form-input
                      v-model="deliveryFeeCity.rate"
                      type="number"
                      class="mr-1"
                      placeholder="Rate"
                    ></b-form-input>
                    <b-form-checkbox
                      class="pt-1 mr-2"
                      v-if="store.id == 148 || store.id == 3"
                      v-model="deliveryFeeCity.shipping"
                      >Shipping</b-form-checkbox
                    >
                    <b-btn variant="primary" @click="addDeliveryFeeCity"
                      >Add</b-btn
                    >
                  </div>
                  <center>
                    <p class="strong">Add By Individual Postal Code</p>
                  </center>
                  <div
                    class="d-flex m-3"
                    style="justify-content:space-between;"
                  >
                    <b-form-input
                      v-model="deliveryFeeZipCode.code"
                      placeholder="Postal Code"
                      class="mr-1"
                    ></b-form-input>
                    <b-form-input
                      v-model="deliveryFeeZipCode.rate"
                      type="number"
                      class="mr-1"
                      placeholder="Rate"
                    ></b-form-input>
                    <b-form-checkbox
                      class="pt-1 mr-2"
                      v-if="store.id == 148 || store.id == 3"
                      v-model="deliveryFeeZipCode.shipping"
                      >Shipping</b-form-checkbox
                    >
                    <b-btn variant="primary" @click="addDeliveryFeeZipCode"
                      >Add</b-btn
                    >
                  </div>

                  <li
                    v-for="(dfzc, i) in deliveryFeeZipCodes"
                    class="mb-1 mt-2"
                  >
                    <div class="row d-flex" style="justify-content:center;">
                      <div class="col-md-3 d-flex">
                        <span class="strong pt-1">{{ dfzc.zip_code }}</span>
                      </div>
                      <div class="col-md-3 d-flex">
                        <span class="d-inline pt-2 pr-1">{{
                          storeSettings.currency_symbol
                        }}</span
                        ><b-form-input
                          v-model="dfzc.delivery_fee"
                          type="number"
                          class="d-inline"
                        ></b-form-input>
                      </div>
                      <div
                        class="col-md-3 d-flex"
                        v-if="store.id == 148 || store.id == 3"
                      >
                        <b-form-checkbox
                          v-model="dfzc.shipping"
                        ></b-form-checkbox>
                      </div>
                    </div>
                    <hr />
                  </li>
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
                        Remove this delivery day, but I will still fulfill item
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
                        @click="cancelSubscriptionsByDeliveryDay"
                        >Remove Day & Cancel</b-btn
                      >
                    </div>
                  </div>
                </b-modal>

                <b-form-group label-for="delivery-distance-type" :state="true">
                  <p>
                    Delivery Distance Type
                    <img
                      v-b-popover.hover="
                        'As you do local delivery, you may have a certain cutoff distance. Here you can set this distance by radius by the number of miles around you or by zip codes separated by commas. If you offer pickup, and the customer chooses pickup, this will not apply to them.'
                      "
                      title="Delivery Distance Type"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-radio-group
                    buttons
                    v-model="storeSettings.delivery_distance_type"
                    class="storeFilters"
                    :options="[
                      { value: 'radius', text: 'Radius' },
                      { value: 'zipcodes', text: 'Zip Codes' }
                    ]"
                    @input="updateStoreSettings()"
                  ></b-form-radio-group>
                </b-form-group>
                <b-form-group
                  v-if="storeSettings.delivery_distance_type === 'radius'"
                  label-for="delivery-distance-radius"
                  :state="true"
                >
                  <p>
                    Delivery Distance Radius
                    <img
                      v-b-popover.hover="
                        'Add the radius in miles. The system looks the difference between your postal code and the customer\'s postal code.'
                      "
                      title="Delivery Distance Radius"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <b-form-input
                    type="number"
                    v-model="storeSettings.delivery_distance_radius"
                    placeholder="Radius (miles)"
                    required
                    @input="updateStoreSettings()"
                  ></b-form-input>
                </b-form-group>
                <b-form-group
                  v-if="storeSettings.delivery_distance_type === 'zipcodes'"
                  label="Delivery Zip Codes"
                  label-for="delivery-distance-zipcodes"
                  description="Separate zip codes by comma."
                  :state="true"
                >
                  <textarea
                    v-model="storeSettings.delivery_distance_zipcodes"
                    class="form-control w-600"
                    placeholder="Zip Codes"
                    @input="formatZips()"
                  ></textarea>
                </b-form-group>

                <b-form-group
                  class="mt-2"
                  v-if="!customDeliveryDays"
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
                    @input="updateStoreSettings()"
                  >
                    <img
                      v-b-popover.hover="
                        'If you only have one delivery/pickup day, then choose either option and it will work the same way. Timed Example: Your delivery days are Sunday and Wednesday, and you set the Cut Off Period to 1 day and 12 hours. This will lock in orders for Sunday on Friday at 12 PM and lock in orders for Wednesday on Monday at 12 PM. Single Day Example: Your delivery days are Sunday and Wednesday, and you set the Cut Off Day to Friday at 12 PM. This locks in orders for BOTH Sunday & Wednesday on Friday at 12 PM. Count backwards from the delivery/pickup day to figure out the best cutoff time. The day begins at 12 AM.'
                      "
                      title="Cut Off Type"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </b-form-radio-group>
                </b-form-group>

                <b-form-group
                  class="mt-2"
                  v-if="!customDeliveryDays"
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

                <b-form-group class="mt-4 mb-4">
                  <b-form-radio-group
                    v-model="storeSettings.minimumOption"
                    :options="minimumOptions"
                    @input="updateStoreSettings()"
                    class="mt-2"
                  ></b-form-radio-group>
                </b-form-group>

                <b-form-group
                  :state="true"
                  v-if="storeSettings.minimumOption === 'price'"
                  class="mt-2"
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
                    v-model="storeSettings.minimumPrice"
                    placeholder="Minimum Price"
                    required
                    @input="updateStoreSettings()"
                    class="w-180"
                    type="number"
                    min="0"
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
                    v-model="storeSettings.minimumMeals"
                    placeholder="Minimum Number of Items"
                    required
                    @input="updateStoreSettings()"
                    type="number"
                    min="0"
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
                    @change.native="updateStoreSettings()"
                  />

                  <div v-if="storeSettings.applyDeliveryFee">
                    <p>
                      <span class="mr-1">Delivery Fee Type</span>
                      <img
                        v-b-popover.hover="
                          'Either choose to apply a flat fee no matter how far the customer is, a flat fee based on the zip code, or a fee based on the distance of the customer in miles. If you choose mileage, you set the base amount first and then the amount per mile. For example - Base amount - $3.00 and then .75 cents per mile. The base amount guarantees you at least receive that amount before the per mile fee gets added.'
                        "
                        title="Delivery Fee Type"
                        src="/images/store/popover.png"
                        class="popover-size"
                      />
                    </p>
                    <b-form-radio-group
                      v-model="storeSettings.deliveryFeeType"
                      @input="updateStoreSettings()"
                    >
                      <b-form-radio name="flat" value="flat">Flat</b-form-radio>
                      <b-form-radio name="zip" value="zip"
                        >Flat By Zip</b-form-radio
                      >
                      <b-form-radio name="mileage" value="mileage"
                        >Mileage</b-form-radio
                      >
                    </b-form-radio-group>

                    <b-form-input
                      class="mt-3 w-180"
                      v-if="
                        storeSettings.applyDeliveryFee &&
                          storeSettings.deliveryFeeType === 'flat'
                      "
                      v-model="storeSettings.deliveryFee"
                      placeholder="Delivery Fee"
                      required
                      @input="updateStoreSettings()"
                      type="number"
                      min="0"
                    ></b-form-input>
                    <b-btn
                      variant="primary"
                      class="mt-3"
                      v-if="
                        storeSettings.applyDeliveryFee &&
                          storeSettings.deliveryFeeType === 'zip'
                      "
                      @click="setDeliveryFeeZipCodes()"
                      >Set Rates</b-btn
                    >
                    <div class="row">
                      <div class="col-md-6">
                        <b-form-input
                          class="mt-3"
                          v-if="
                            storeSettings.applyDeliveryFee &&
                              storeSettings.deliveryFeeType === 'mileage'
                          "
                          type="number"
                          min="0"
                          v-model="storeSettings.mileageBase"
                          placeholder="Base Amount"
                          required
                          @input="updateStoreSettings()"
                        ></b-form-input>
                      </div>
                      <div class="col-md-6">
                        <b-form-input
                          class="mt-3"
                          v-if="
                            storeSettings.applyDeliveryFee &&
                              storeSettings.deliveryFeeType === 'mileage'
                          "
                          type="number"
                          min="0"
                          v-model="storeSettings.mileagePerMile"
                          placeholder="Per Mile"
                          required
                          @input="updateStoreSettings()"
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
                    @change.native="updateStoreSettings()"
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
                      @input="updateStoreSettings()"
                    />
                  </p>
                  <b-form-radio-group
                    v-model="storeSettings.processingFeeType"
                    class="mt-2 mb-2"
                    @input="updateStoreSettings()"
                  >
                    <b-form-radio name="flat" value="flat">Flat</b-form-radio>
                    <b-form-radio name="percent" value="percent"
                      >Percent</b-form-radio
                    >
                  </b-form-radio-group>
                  <b-form-input
                    class="mb-4 w-180"
                    type="number"
                    min="0"
                    v-model="storeSettings.processingFee"
                    placeholder="Processing Fee"
                    required
                    @input="updateStoreSettings()"
                  ></b-form-input>
                </div>

                <!-- Adding for GoEatFresh for now until full shipping feature is completed -->
                <p v-if="store.id == 148 || store.id == 3">
                  Shipping Instructions:
                </p>
                <b-form-textarea
                  v-if="store.id == 148 || store.id == 3"
                  type="text"
                  rows="3"
                  v-model="storeSettings.shippingInstructions"
                  placeholder="Please include shipping instructions to your customers. This will be shown on the checkout page as well as email notifications the customer receives."
                  class="mb-2 w-600"
                  @input="saveTextInput()"
                ></b-form-textarea>

                <p v-if="transferTypeCheckDelivery">Delivery Instructions:</p>
                <b-form-textarea
                  v-if="transferTypeCheckDelivery"
                  type="text"
                  rows="3"
                  v-model="storeSettings.deliveryInstructions"
                  placeholder="Please include delivery instructions to your customers (time window, how long your driver will wait, etc.) This will be shown on the checkout page as well as email notifications the customer receives."
                  class="mb-2 w-600"
                  @input="saveTextInput()"
                ></b-form-textarea>
                <p v-if="transferTypeCheckPickup">Pickup Instructions:</p>
                <b-form-textarea
                  v-if="transferTypeCheckPickup"
                  type="text"
                  rows="3"
                  v-model="storeSettings.pickupInstructions"
                  placeholder="Please include pickup instructions to your customers (pickup address, phone number, and time). This will be shown on the checkout page as well as email notifications the customer receives."
                  class="mb-2 w-600"
                  @input="saveTextInput()"
                ></b-form-textarea>

                <p class="mt-2">
                  <span class="mr-1">Notes For Customer</span>
                  <img
                    v-b-popover.hover="
                      'Here you can optionally add any notes or customized message that you want to communicate to your customer on your packing slips and new order email notifications. This is usually some general information or a thank you of some kind.'
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
                  placeholder="Thank you for your order."
                  class="w-600"
                  @input="saveTextInput()"
                ></b-form-textarea>

                <!-- <b-button type="submit" variant="primary" class="mt-3"
                  >Save</b-button
                > -->

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
                    @change.native="updateStoreSettings()"
                  />
                  <div v-if="storeSettings.enableSalesTax">
                    <p class="mt-3 mb-0 pb-0">
                      <span class="mr-1">Sales Tax %</span>
                      <img
                        v-b-popover.hover="
                          'Our system figures out your sales tax rate using the state you signed up with. You can override the number in the field below.'
                        "
                        title="Sales Tax"
                        src="/images/store/popover.png"
                        class="popover-size"
                      />
                    </p>
                    <b-form-group :state="true">
                      <b-form-input
                        label="Sales Tax"
                        class="mt-3 w-180"
                        type="number"
                        min="0"
                        v-model="salesTax"
                        required
                        @input="updateStoreSettings()"
                      ></b-form-input>
                    </b-form-group>
                  </div>

                  <div v-if="storeSettings.enableSalesTax">
                    <p class="mt-4 mb-0 pb-0">
                      <span class="mr-1">Apply Sales Tax After Fees</span>
                      <img
                        v-b-popover.hover="
                          'Apply sales tax on the subtotal or on the subtotal plus any additional fees.'
                        "
                        title="Sales Tax Order"
                        src="/images/store/popover.png"
                        class="popover-size"
                      />
                    </p>

                    <c-switch
                      class="mt-3 mb-2"
                      color="success"
                      variant="pill"
                      size="lg"
                      v-model="storeSettings.salesTaxAfterFees"
                      @change.native="updateStoreSettings()"
                    />
                  </div>

                  <p class="mt-2">Timezone</p>
                  <b-select
                    :options="timezoneOptions"
                    v-model="storeSettings.timezone"
                    class="d-inline w-auto mr-1"
                    @input="updateStoreSettings()"
                  >
                  </b-select>
                </b-form>

                <p class="mt-3">
                  <span class="mr-1 mt-2">Open</span>
                  <img
                    v-b-popover.hover="
                      'You can toggle this off to stop accepting new orders from customers for any reason. Please fill out the reason for being closed below to communicate to your customers.'
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
                  @change.native="toggleCloseOpen"
                />

                <b-form-input
                  v-if="!storeSettings.open"
                  type="text"
                  v-model="storeSettings.closedReason"
                  placeholder="Here you can indicate the reason to your customers for not accepting orders."
                  required
                  @input="updateStoreSettings()"
                ></b-form-input>

                <!-- <div class="mt-3">
                    <b-button type="submit" variant="primary">Save</b-button>
                  </div> -->
              </b-form>
            </b-tab>
            <b-tab title="Menu">
              <b-form @submit.prevent="updateStoreSettings">
                <p>https://{{ storeDetails.domain }}.goprep.com</p>
                <b-form
                  @submit.prevent="updateStoreLogo"
                  v-if="!storeModules.hideBranding"
                >
                  <!-- <b-form-group label="Logo" :state="true">
                    <picture-input
                      :ref="`storeImageInput`"
                      :prefill="logoPrefill"
                      @prefill="$refs[`storeImageInput`].onResize()"
                      :alertOnError="false"
                      :autoToggleAspectRatio="true"
                      margin="0"
                      size="10"
                      button-class="btn"
                      style="width: 180px; height: 180px; margin: 0;"
                      @change="val => updateLogo(val)"
                    ></picture-input>
                  </b-form-group> -->
                  <!-- <div class="mt-3">
                    <b-button type="submit" variant="primary" class="mb-3"
                      >Save</b-button
                    >
                  </div> -->
                </b-form>
              </b-form>

              <p>
                Menu Style
                <img
                  v-b-popover.hover="
                    'Choose image based if you have images of your items and want to showcase your items visually. Choose text based if you don\'t have many images and want to emphasize descriptions more. Text based still shows images of your items but in a smaller size.'
                  "
                  title="Menu Style"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <b-form-radio-group
                buttons
                v-model="storeSettings.menuStyle"
                class="storeFilters mb-2"
                @input="updateStoreSettings()"
                :options="[
                  { value: 'image', text: 'Image Based' },
                  { value: 'text', text: 'Text Based' }
                ]"
              ></b-form-radio-group>

              <p>
                <span class="mr-1">Show Nutrition Facts</span>
                <img
                  v-b-popover.hover="
                    'Nutrition facts are generated based on the ingredients you enter in for each item on your Menu page, or you enter nutritional info directly. The nutrition is then shown to your customers if they click on any of your items when ordering from you.'
                  "
                  title="Show Nutrition Facts"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showNutrition"
                @change.native="updateStoreSettings()"
              />
              <p>
                <span class="mr-1">Show Macros</span>
                <img
                  v-b-popover.hover="
                    'Enables input fields for your to add your item\'s calories, carbs, protein, and fat. Macros are shown on your menu. If this is turned on, and no macros are manually entered, the macros will be pulled from your nutrition facts even if nutrition facts is not enabled.'
                  "
                  title="Show Macros"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showMacros"
                @change.native="updateStoreSettings()"
              />
              <p>
                <span class="mr-1">Show Ingredients</span>
                <img
                  v-b-popover.hover="
                    'Ingredients of your items are shown for each item on your menu if the user clicks on the item. You can choose to show or hide them with this option.'
                  "
                  title="Show Ingredients"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.showIngredients"
                @change.native="updateStoreSettings()"
              />

              <p>
                <span class="mr-1">Enable Item Instructions</span>
                <img
                  v-b-popover.hover="
                    'Adds a new form field on each of your items on the Menu page on your dashboard. Within that box you can type in special instructions specific to that particular item such as heating instructions. If your customer orders that particular item, these instructions will then be shown in their packing slips & email receipts.'
                  "
                  title="Enable Item Instructions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.mealInstructions"
                @change.native="updateStoreSettings()"
              />

              <p class="mt-2">
                <span class="mr-1">Google Analytics Code</span>
                <img
                  v-b-popover.hover="
                    'Create a Google Analytics account and paste in your tracking code below. You\'ll then be able to see all kinds of traffic reports about who viewed your menu page. Please follow the exact format that is shown to you which looks like this: UA-00000000-00.'
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
                  @input="updateStoreSettings()"
                  class="w-180"
                ></b-form-input>
              </b-form-group>

              <p class="mt-2">
                <span class="mr-1">Facebook Pixel Code</span>
                <img
                  v-b-popover.hover="
                    'If you are running a Facebook advertising campaign, you can paste your Facebook pixel code here in order to track traffic and orders from Facebook.'
                  "
                  title="Facebook Pixel"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>

              <b-form-group :state="true" class="mt-2">
                <b-form-input
                  type="text"
                  v-model="storeSettings.fbPixel"
                  @input="updateStoreSettings()"
                  class="w-180"
                ></b-form-input>
              </b-form-group>

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
                  <swatches
                    v-model="color"
                    @input="updateStoreSettings()"
                  ></swatches>
                </b-form-group>
                <b-form-group :state="true" v-if="!storeModules.hideBranding">
                  <p>
                    <span class="mr-1">Main Website URL</span>
                    <img
                      v-b-popover.hover="
                        'Optionally link up your main website to your menu and checkout page. If your customer clicks your logo they will be redirected back to your main website. This will also show the website URL on your packing slips and labels.'
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
                    @input="updateStoreSettings()"
                    class="w-600"
                  ></b-form-input>
                </b-form-group>
                <b-form-group
                  label="About (Shown at the top of your menu)"
                  :state="true"
                >
                  <wysiwyg class="w-600" v-model="description" />
                </b-form-group>

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
                <!-- <br /><br /> -->
                <!-- <b-button type="submit" variant="primary mt-2">Save</b-button> -->
              </b-form>
            </b-tab>
            <b-tab title="Subscriptions">
              <p>
                <span class="mr-1">Allow Weekly Subscriptions</span>
                <img
                  v-b-popover.hover="
                    'Shows a section on your bag/checkout page that lets the customer opt in for a weekly subscription for an optional discount. The customer will then be charged every week. They can cancel, or change items in their subscriptions.'
                  "
                  title="Allow Weekly Subscriptions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.allowWeeklySubscriptions"
                @change.native="updateStoreSettings()"
              />
              <b-form-group
                :state="true"
                v-if="storeSettings.allowWeeklySubscriptions"
              >
                <p class="mt-3">
                  <span class="mr-1">Minimum Subscription Weeks</span>
                  <img
                    v-b-popover.hover="
                      'Type in the number of weeks of orders your customer will be locked into when creating a weekly subscription. The Cancel button will only show for them after they meet this minimum requirement. They would have to contact you directly if they want to cancel. This is a way to prevent abuse of the subscription discount if you have one.'
                    "
                    title="Minimum Subscription Weeks"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <b-form-input
                  v-model="storeSettings.minimumSubWeeks"
                  required
                  @input="updateStoreSettings()"
                  class="w-180"
                  type="number"
                  min="0"
                ></b-form-input>
              </b-form-group>
              <p>
                <span class="mr-1">Allow Bi-Weekly Subscriptions</span>
                <img
                  v-b-popover.hover="
                    'Shows a section on your bag/checkout page that lets the customer opt in for a bi-weekly subscription for an optional discount. The customer will then be charged every 2 weeks. They can cancel, or change items in their subscriptions.'
                  "
                  title="Allow Bi-Weekly Subscriptions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.allowBiWeeklySubscriptions"
                @change.native="updateStoreSettings()"
              />
              <!-- <p v-if="storeSettings.allowWeeklySubscriptions || storeSettings.allowMonthlySubscriptions" class="mt-3">
                <span class="mr-1">Weekly Subscriptions Paid Monthly</span>
                <img
                  v-b-popover.hover="
                    'Subscription orders are created every week just like a regular weekly subscription, but customers prepay all at once for 4 weeks and are locked in for 4 weeks. If they cancel the subscription on week 2 for example, the cancellation applies to next month\'s renewal and they will still receive orders for weeks 3 and 4.'
                  "
                  title="Weekly Subscriptions Paid Monthly"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
                <c-switch
                  v-if="storeSettings.allowWeeklySubscriptions || storeSettings.allowMonthlySubscriptions"
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeSettings.monthlyPrepaySubscriptions"
                  @change.native="updateStoreSettings()"
                /> -->
              <p class="mt-3">
                <span class="mr-1">Allow Monthly Subscriptions</span>
                <img
                  v-b-popover.hover="
                    'Shows a section on your bag/checkout page that lets the customer opt in for a monthly subscription for an optional discount. The customer will then be charged once every 4 weeks and receive 1 order every 4 weeks. They can cancel, or change items in their subscriptions.'
                  "
                  title="Allow Monthly Subscriptions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.allowMonthlySubscriptions"
                @change.native="updateStoreSettings()"
              />
              <p class="mt-3">
                <span class="mr-1">Allow Multiple Subscriptions</span>
                <img
                  v-b-popover.hover="
                    'Allow a single customer to create multiple subscriptions. As a caveat, some customers may forget about their existing subscription and create another one despite the alert messages we show them about their existing subscription. They will then unintentionally be charged twice and have two orders created for the week and ask for a cancellation/refund on one of them.'
                  "
                  title="Allow Multiple Subscriptions"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </p>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                v-model="storeSettings.allowMultipleSubscriptions"
                @change.native="updateStoreSettings()"
              />
              <b-form-group
                :state="true"
                v-if="
                  storeSettings.allowWeeklySubscriptions ||
                    storeSettings.allowMonthlySubscriptions
                "
              >
                <p class="mt-3">
                  <span class="mr-1">Subscription Discount</span>
                  <img
                    v-b-popover.hover="
                      'Give your customers an incentive to create a subscription with you by offering a discount percentage. The customer is locked into at least 2 orders before they can cancel their subscription through our system in order to prevent users from abusing the discount. They would have to contact you if they want to cancel the subscription before at least 2 orders in which you can cancel it for them if you agree.'
                    "
                    title="Subscription Discount"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeSettings.applyMealPlanDiscount"
                  @change.native="updateStoreSettings()"
                />
                <b-form-input
                  v-if="storeSettings.applyMealPlanDiscount"
                  id="meal-plan-discount"
                  v-model="storeSettings.mealPlanDiscount"
                  placeholder="Subscription Discount %"
                  required
                  @input="updateStoreSettings()"
                  type="number"
                  min="0"
                  class="w-180"
                ></b-form-input>
              </b-form-group>

              <b-form-group
                :state="true"
                class="mt-4 mb-4"
                v-if="
                  storeSettings.allowWeeklySubscriptions ||
                    storeSettings.allowMonthlySubscriptions
                "
              >
                <p>
                  <span class="mr-1">Subscription Renewal Timing</span>
                  <img
                    v-b-popover.hover="
                      'Choose \'Renew At Cutoff\' for the subscription renewal date to be moved to the cutoff day for the delivery day the customer chooses. Choose \'Renew Now\' for your customer\'s subscription renewal/lock in day to be the day they created the subscription. The first option gives your customer all the way until your cutoff to update their subscription, but their card is not charged and order is not created and shown until your cutoff occurs. The second option renews & creates the order immediately and on the same day each week the subscription was created, but there will be a gap between the renewal time and your cutoff time in which your customer is able to update their subscription.'
                    "
                    title="Subscription Renewal Timing"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <b-form-radio-group
                  v-model="storeSettings.subscriptionRenewalType"
                  :options="subscriptionRenewalTypeOptions"
                  @input="updateStoreSettings()"
                ></b-form-radio-group>
              </b-form-group>
            </b-tab>
            <b-tab title="Notifications">
              <b-form @submit.prevent="updateStoreSettings">
                <b-form-group label="New Orders" :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.notifications.new_order"
                    @change.native="updateStoreSettings()"
                  />
                </b-form-group>

                <b-form-group label="New Subscriptions" :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.notifications.new_subscription"
                    @change.native="updateStoreSettings()"
                  />
                </b-form-group>

                <b-form-group label="Cancelled Subscriptions" :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.notifications.cancelled_subscription"
                    @change.native="updateStoreSettings()"
                  />
                </b-form-group>

                <b-form-group label="Ready to Print" :state="true">
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeSettings.notifications.ready_to_print"
                    @change.native="updateStoreSettings()"
                  />
                </b-form-group>
              </b-form>
            </b-tab>
            <b-tab title="Advanced">
              <b-form @submit.prevent="updateStoreModules(true)">
                <p class="mt-2">
                  <span class="mr-1 mt-2">Production Groups</span>
                  <img
                    v-b-popover.hover="
                      'Divide up your production report into separate production groups. Add new groups on the Production page and then assign each item to a group on the Menu page.'
                    "
                    title="Production Groups"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.productionGroups"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Special Instructions</span>
                  <img
                    v-b-popover.hover="
                      'Allow your customers to type in special instructions when ordering from you.'
                    "
                    title="Special Instructions"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.specialInstructions"
                  @change.native="updateStoreModules()"
                />
                <span v-if="storeModules.specialInstructions">
                  <p class="mt-2">
                    <span class="mr-1 mt-2"
                      >Store Only Special Instructions</span
                    >
                    <img
                      v-b-popover.hover="
                        'Make special instructions available on manual orders you create only - not for your customer\'s online orders.'
                      "
                      title="Store Only Special Instructions"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </p>
                  <c-switch
                    color="success"
                    variant="pill"
                    size="lg"
                    v-model="storeModuleSettings.specialInstructionsStoreOnly"
                    @change.native="updateStoreModules()"
                  />
                </span>
                <p class="mt-2">
                  <span class="mr-1 mt-2">Stock Management</span>
                  <img
                    v-b-popover.hover="
                      'Set the stock of each meal on the Menu page. When the item is out of stock, the item will automatically become inactive until you activate it again and set the updated stock.'
                    "
                    title="Stock Management"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.stockManagement"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Auto Print Packing Slips</span>
                  <img
                    v-b-popover.hover="
                      'After creating a manual order, this automatically pops up the packing slip prompt of the order so you can print it right away.'
                    "
                    title="Auto Print Packing Slips"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.autoPrintPackingSlip"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Cash Orders for Customers</span>
                  <img
                    v-b-popover.hover="
                      'Allow your customers to check a box on the checkout page which indicates they want to place a cash on delivery order. The credit card area will then be hidden and they will not be charged.'
                    "
                    title="Cash Orders for Customers"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModuleSettings.cashAllowedForCustomer"
                  @change.native="updateStoreModules(false, true)"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">No Balance on Cash Orders</span>
                  <img
                    v-b-popover.hover="
                      'Enable this if you create manual cash on delivery orders but you don\'t want the order to show a balance due.'
                    "
                    title="No Balance on Cash Orders"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.cashOrderNoBalance"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Item Expiration</span>
                  <img
                    v-b-popover.hover="
                      'Set expiration periods on your items and show expiration dates on your menu & labels.'
                    "
                    title="Item Expiration"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.mealExpiration"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Gratuity</span>
                  <img
                    v-b-popover.hover="
                      'Add an area on the checkout page for customer\'s to optionally add a tip to their order.'
                    "
                    title="Gratuity"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.gratuity"
                  @change.native="updateStoreModules()"
                />

                <p class="mt-2">
                  <span class="mr-1 mt-2">Cooler Bag Deposit</span>
                  <img
                    v-b-popover.hover="
                      'Apply a deposit on your customer\'s orders for a cooler bag which can then be refunded upon return of the bag..'
                    "
                    title="Cooler Bag Deposit"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModules.cooler"
                  @change.native="updateStoreModules()"
                />
                <p class="mt-2 ml-5" v-if="storeModules.cooler">
                  <span class="mr-1 mt-2">Optional</span>
                  <img
                    v-b-popover.hover="
                      'Make the cooler bag deposit optional for the customer. They will see a checkbox instead of it being forced.'
                    "
                    title="Optional"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <c-switch
                  v-if="storeModules.cooler"
                  class="ml-5"
                  color="success"
                  variant="pill"
                  size="lg"
                  v-model="storeModuleSettings.coolerOptional"
                  @change.native="updateStoreModules()"
                />
                <p class="mt-2 ml-5" v-if="storeModules.cooler">
                  <span class="mr-1 mt-2">Cooler Bag Deposit Amount</span>
                  <img
                    v-b-popover.hover="
                      'Enter the amount of the cooler bag deposit the customer has to pay.'
                    "
                    title="Cooler Bag Deposit"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </p>
                <b-form-input
                  v-if="storeModules.cooler"
                  class="w-80px ml-5"
                  v-model="storeModuleSettings.coolerDeposit"
                  type="number"
                  @change.native="updateStoreModules(false, true)"
                ></b-form-input>
              </b-form>
            </b-tab>
          </b-tabs>
        </b-col>
      </b-row>

      <b-modal
        v-model="showTOAModal"
        title="Service Agreement"
        size="xl"
        @ok="allowOpen"
        @cancel="(showTOAModal = false), (storeSettings.open = false)"
        hide-header
        no-close-on-backdrop
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

      <b-modal
        v-model="showSubscriptionsModal"
        hide-header
        hide-footer
        no-close-on-backdrop
      >
        <p class="mt-3">
          You have active subscriptions. What would you like to do with them?
        </p>
        <div class="d-flex d-inline">
          <b-btn variant="warning" class="center" @click="pauseAllSubscriptions"
            >Pause All</b-btn
          >
          <b-btn variant="danger" class="center" @click="cancelAllSubscriptions"
            >Cancel All</b-btn
          >
          <b-btn
            variant="success"
            class="center"
            @click="showSubscriptionsModal = false"
            >Keep Them</b-btn
          >
        </div>
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
import PictureInput from "vue-picture-input";
import states from "../../../data/states.js";

export default {
  components: {
    cSwitch,
    Swatches,
    TermsOfService,
    TermsOfAgreement,
    PictureInput
  },
  watch: {
    delivery_days() {
      this.storeSettings.delivery_days = this.delivery_days;
      this.updateStoreSettings();
    },
    description: _.debounce(function(val) {
      this.storeDetail.description = val;
      this.updateStoreDetails();
    }, 1000)
  },
  data() {
    return {
      description: null,
      delivery_days: null,
      deliveryFeeCity: {},
      deliveryFeeZipCode: {},
      deliveryFeeZipCodeModal: false,
      deliveryFeeZipCodes: [],
      logoUpdated: false,
      acceptedTOA: 0,
      acceptedTOAcheck: 0,
      showTOAModal: false,
      showSubscriptionsModal: false,
      color: "",
      transferSelected: [],
      transferOptions: [
        { text: "Delivery", value: "delivery" },
        { text: "Pickup", value: "pickup" }
      ],
      subscriptionRenewalTypeOptions: [
        { text: "Renew At Cutoff", value: "cutoff" },
        { text: "Renew Now", value: "now" }
      ],
      minimumSelected: "price",
      minimumOptions: [
        { text: "Require Minimum Price", value: "price" },
        { text: "Require Minimum Items", value: "meals" }
      ],
      loginAlertSuccess: false,
      loginAlertFail: false,
      zipCodes: [],
      view_delivery_days: 1,
      payments_url: "",
      deselectedDeliveryDay: null,
      showCutoffModal: false,
      stripeConnectUrl: null,
      salesTax: 0
    };
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "viewedStore",
      storeDetail: "storeDetail",
      storeSetting: "storeSetting",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories",
      storeSubscriptions: "storeSubscriptions",
      storeModules: "storeModules",
      storeModuleSettings: "storeModuleSettings",
      storeDeliveryFeesZipCodes: "storeDeliveryFeeZipCodes",
      isLoading: "isLoading"
    }),
    storeDetails() {
      return this.storeDetail;
    },
    customDeliveryDays() {
      if (
        this.storeModules.customDeliveryDays ||
        this.storeModules.multipleDeliveryDays
      ) {
        return true;
      } else {
        return false;
      }
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
        this.storeSettings.delivery_days.length > 0 &&
        ((this.storeSettings.payment_gateway === "stripe" &&
          !_.isEmpty(this.storeSettings.stripe_id)) ||
          (this.storeSettings.payment_gateway === "authorize" &&
            !_.isEmpty(this.storeSettings.authorize_login_id)))
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
    },
    storeURLcheck() {
      let URL = window.location.href;
      let subdomainCheck = URL.substr(0, URL.indexOf("."));
      if (subdomainCheck.includes("goprep")) return true;
      else return false;
    }
  },
  created() {
    axios.get("/api/me/stripe/connect/url").then(resp => {
      this.stripeConnectUrl = resp.data;
    });
  },
  mounted() {
    this.disableSpinner();
    if (this.storeSettings.salesTax !== null) {
      this.salesTax = this.storeSettings.salesTax;
    } else {
      this.salesTax = this.getSalesTax(this.storeDetail.state);
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

    this.delivery_days = this.storeSettings.delivery_days;
    this.description = this.storeDetails.description;
  },
  destroyed() {
    this.enableSpinner();
  },
  methods: {
    ...mapActions([
      "refreshCategories",
      "refreshStoreSettings",
      "refreshStoreModules",
      "refreshStoreModuleSettings",
      "refreshStoreDeliveryFeeZipCodes",
      "disableSpinner",
      "enableSpinner"
    ]),
    updateStoreSettings(toast = false) {
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
      settings.color = this.color;

      if (this.logoUpdated) {
        this.updateStoreLogo();
      }

      axios
        .patch("/api/me/settings", settings)
        .then(response => {
          // this.refreshStoreSettings();
          if (toast) {
            this.$toastr.s("Your settings have been saved.", "Success");
          }
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        });
    },
    updateStoreDetails() {
      axios.patch("/api/me/details", { ...this.storeDetails });
    },
    updateStoreModules(toast = false, moduleSettings = false) {
      let modules = { ...this.storeModules };

      axios
        .post("/api/me/updateModules", modules)
        .then(response => {
          // this.refreshStoreModules();
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        });

      if (moduleSettings) {
        let moduleSettings = { ...this.storeModuleSettings };

        axios
          .post("/api/me/updateModuleSettings", moduleSettings)
          .then(response => {
            // this.refreshStoreModuleSettings();
            if (toast) {
              this.$toastr.s("Your settings have been saved.", "Success");
            }
          })
          .catch(response => {
            let error = _.first(Object.values(response.response.data.errors));
            error = error.join(" ");
            this.$toastr.e(error, "Error");
          });
      }
    },
    toggleCloseOpen() {
      if (this.storeSettings.open) {
        if (this.store.accepted_toa === 0) {
          this.showTOAModal = true;
          return;
        }
      } else {
        if (
          this.storeSubscriptions.some(sub => {
            return sub.status === "active" || sub.status === "paused";
          })
        ) {
          this.showSubscriptionsModal = true;
        }
      }
      this.updateStoreSettings();
    },
    // closeStore() {
    //   let activeSubscriptions = false;

    //   this.storeSubscriptions.forEach(subscription => {
    //     if (subscription.status === "active") activeSubscriptions = true;
    //   });

    //   console.log(activeSubscriptions);

    //   if (this.storeSettings.open === false && activeSubscriptions) {
    //     this.showSubscriptionsModal = true;
    //     return;
    //   }

    //   let settings = { ...this.storeSettings };

    //   axios
    //     .patch("/api/me/settings", settings)
    //     .then(response => {
    //       this.refreshStoreSettings();
    //       this.$toastr.s("Your settings have been saved.", "Success");
    //     })
    //     .catch(response => {
    //       let error = _.first(Object.values(response.response.data.errors));
    //       error = error.join(" ");
    //       this.$toastr.e(error, "Error");
    //     });
    // },
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
        if (
          processingFee.toString().includes("$") ||
          processingFee.toString().includes("%")
        ) {
          let intToString = processingFee.toString();
          let newFee = intToString.replace("$", "");
          let finalFee = newFee.replace("%", "");
          this.storeSettings.processingFee = finalFee;
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
    onChangeDeliveryDays(days) {
      // Get unselected day
      console.log(days);
      console.log(this.storeSettings.delivery_days);
      let diff = _.difference(this.storeSettings.delivery_days, days);
      console.log(diff);

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
      this.updateStoreSettings();
    },
    removeDeliveryDay() {
      let day = this.deselectedDeliveryDay;
      let index = this.storeSettings.delivery_days.indexOf(day);
      this.storeSettings.delivery_days.splice(index, 1);
      this.$refs.deliveryDaysModal.hide();
    },
    async updateLogo(logo) {
      this.logoUpdated = true;
      let b64 = await fs.getBase64(this.$refs.storeImageInput.file);
      this.storeDetail.logo = b64;
      this.updateStoreDetails();
    },
    checkAcceptedTOA() {
      axios.get("/api/me/getAcceptedTOA").then(resp => {
        this.acceptedTOA = resp.data;
      });
    },
    allowOpen() {
      if (this.acceptedTOAcheck === "1") {
        this.updateStoreSettings();
      } else {
        this.$toastr.w("Please accept the terms of agreement.");
        this.storeSettings.open = false;
        return;
      }
    },
    pauseAllSubscriptions() {
      axios.get("/api/me/pauseAllSubscriptions");
      this.showSubscriptionsModal = false;
    },
    cancelAllSubscriptions() {
      axios.get("/api/me/cancelAllSubscriptions");
      this.showSubscriptionsModal = false;
    },
    cancelSubscriptionsByDeliveryDay() {
      this.removeDeliveryDay();
      axios.post("/api/me/cancelSubscriptionsByDeliveryDay", {
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
      } else {
        this.updateStoreSettings();
      }
    },
    getSalesTax(state) {
      SalesTax.getSalesTax("US", state).then(tax => {
        this.setSalesTax(tax.rate);
      });
    },
    setSalesTax(rate) {
      this.salesTax = rate * 100;
    },
    async setDeliveryFeeZipCodes() {
      this.deliveryFeeCity.state = this.storeDetails.state;
      this.deliveryFeeZipCodeModal = true;

      await this.refreshStoreDeliveryFeeZipCodes();

      if (_.isArray(this.storeDeliveryFeesZipCodes)) {
        this.storeDeliveryFeesZipCodes.forEach(dfzc => {
          this.deliveryFeeZipCodes.push({
            zip_code: dfzc.zip_code,
            delivery_fee: dfzc.delivery_fee,
            shipping: dfzc.shipping
          });
        });
      }
      this.storeSettings.delivery_distance_zipcodes.forEach(zipCode => {
        let contains = false;
        this.deliveryFeeZipCodes.forEach(dfzc => {
          if (dfzc.zip_code === zipCode) {
            contains = true;
          }
        });
        if (!contains && zipCode != null) {
          this.deliveryFeeZipCodes.push({
            zip_code: zipCode,
            delivery_fee: this.storeSettings.deliveryFee,
            shipping: zipCode.shipping
          });
        }
      });
    },
    updateDeliveryFeeZipCodes() {
      axios
        .post("/api/me/updateDeliveryFeeZipCodes", this.deliveryFeeZipCodes)
        .then(() => {
          this.setDeliveryFeeZipCodes();
          this.updateStoreSettings();
          this.deliveryFeeZipCode = {};
        });
    },
    addDeliveryFeeCity() {
      if (this.deliveryFeeCity.rate == null) {
        this.$toastr.w(
          "Please add the rate you want to charge for the city/town."
        );
        return;
      }
      axios
        .post("/api/me/addDeliveryFeeCity", { dfc: this.deliveryFeeCity })
        .then(() => {
          this.setDeliveryFeeZipCodes();
          this.updateStoreSettings();
          this.deliveryFeeCity = {};
        });
    },
    addDeliveryFeeZipCode() {
      if (this.deliveryFeeZipCode.code == null) {
        this.$toastr.w("Please add the postal code.");
        return;
      }
      if (this.deliveryFeeZipCode.rate == null) {
        this.$toastr.w(
          "Please add the rate you want to charge for the postal code."
        );
        return;
      }
      this.deliveryFeeZipCodes.push({
        delivery_fee: this.deliveryFeeZipCode.rate,
        zip_code: this.deliveryFeeZipCode.code,
        shipping: this.deliveryFeeZipCode.shipping
      });
      this.updateDeliveryFeeZipCodes();
    },
    getStateNames(country = "US") {
      return states.selectOptions(country);
    },
    formatZips: _.debounce(function() {
      this.storeSettings.delivery_distance_zipcodes = this.storeSettings.delivery_distance_zipcodes
        .toString()
        .replace('"', "")
        .split(",");
      this.updateStoreSettings();
    }, 1000),
    saveTextInput: _.debounce(function() {
      this.updateStoreSettings();
    }, 1000)
  }
};
</script>
