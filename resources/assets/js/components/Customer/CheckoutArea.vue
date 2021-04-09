<template>
  <div class="checkoutArea">
    <ul class="list-group">
      <li class="bag-item" v-if="activePromotions.length > 0">
        <div
          class="col-md-12"
          v-for="promotion in activePromotions"
          :key="promotion.id"
        >
          <b-alert
            variant="success"
            show
            v-if="
              promotion.conditionType === 'meals' &&
                totalBagQuantity < promotion.conditionAmount
            "
          >
            <p class="center-text strong">
              Add {{ promotion.conditionAmount - totalBagQuantity }} more meals
              to receive a discount of
              <span v-if="promotion.promotionType === 'flat'">{{
                format.money(promotion.promotionAmount, storeSettings.currency)
              }}</span>
              <span v-else>{{ promotion.promotionAmount }}%</span>
            </p>
          </b-alert>
          <b-alert
            variant="success"
            show
            v-if="
              promotion.conditionType === 'subtotal' &&
                subtotal < promotion.conditionAmount
            "
          >
            <p class="center-text strong">
              Add
              {{
                format.money(
                  promotion.conditionAmount - subtotal,
                  storeSettings.currency
                )
              }}
              more to receive
              <span v-if="promotion.promotionAmount > 0">
                <span v-if="promotion.promotionType === 'flat'">{{
                  format.money(
                    promotion.promotionAmount,
                    storeSettings.currency
                  )
                }}</span>
                <span v-else>{{ promotion.promotionAmount }}%</span>
                off your order.
              </span>
              <span
                v-if="promotion.promotionAmount === 0 && promotion.freeDelivery"
              >
                free delivery.
              </span>
            </p>
          </b-alert>
          <b-alert
            variant="success"
            show
            v-if="
              promotion.conditionType === 'orders' &&
                loggedIn &&
                getRemainingPromotionOrders(promotion) !== 0 &&
                !user.storeOwner
            "
          >
            <p class="center-text strong">
              Order {{ getRemainingPromotionOrders(promotion) }} more times to
              receive a discount of
              <span v-if="promotion.promotionType === 'flat'">{{
                format.money(promotion.promotionAmount, storeSettings.currency)
              }}</span>
              <span v-else>{{ promotion.promotionAmount }}%</span>
            </p>
          </b-alert>

          <b-alert
            variant="success"
            show
            v-if="
              promotion.promotionType === 'points' && promotionPointsAmount > 0
            "
          >
            <p class="center-text strong">
              You will earn {{ promotionPointsAmount }}
              {{ promotionPointsName }} on this order.
            </p>
          </b-alert>
        </div>
      </li>
      <li
        class="bag-item"
        v-if="
          (storeSettings.allowWeeklySubscriptions ||
            storeSettings.allowMonthlySubscriptions) &&
            $route.params.subscriptionId === undefined &&
            $parent.orderId === undefined &&
            store.modules.frequencyItems
        "
      >
        <div class="row">
          <div class="col-md-12 pb-1" v-if="bagHasMultipleFrequencyItems">
            <b-form-radio-group
              buttons
              class="filters frequencyFilters mb-3"
              v-model="frequencyType"
              :options="[
                { value: null, text: 'One Time Order' },
                { value: 'sub', text: 'Subscription' }
              ]"
              @input="val => setFrequencySubscription(val)"
            ></b-form-radio-group>
          </div>
          <div class="col-md-12 pb-1" v-if="bagHasOnlyOrderItems">
            <h4>One Time Order</h4>
          </div>
          <div class="col-md-12 pb-1" v-if="bagHasOnlySubItems">
            <h4>Subscription</h4>
          </div>
        </div>
      </li>
      <li
        class="bag-item"
        v-if="
          (storeSettings.allowWeeklySubscriptions ||
            storeSettings.allowBiWeeklySubscriptions ||
            storeSettings.allowMonthlySubscriptions ||
            storeSettings.prepaidSubscriptions) &&
            $route.params.subscriptionId === undefined &&
            $parent.orderId === undefined &&
            !subscriptionId &&
            !store.modules.frequencyItems &&
            user.user_role_id !== 4
        "
      >
        <div class="row" v-if="!store.modules.subscriptionOnly">
          <div class="col-md-12 pb-1">
            <h3>
              <img
                v-if="!mobile && !manualOrder"
                v-b-popover.hover.bottom="
                  'Choose a subscription instead of a one time order and items will be given to you on a recurring basis.'
                "
                title="Subscription"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
              <img
                v-if="mobile && !manualOrder"
                v-b-popover.click.top="
                  'Choose a subscription instead of a one time order and items will be given to you on a recurring basis.'
                "
                title="Subscription"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
              <strong
                >Subscribe
                <span
                  v-if="
                    storeSettings.mealPlanDiscount > 0 &&
                      storeSettings.applyMealPlanDiscount
                  "
                  >& Save {{ storeSettings.mealPlanDiscount }}%</span
                >
              </strong>
              <c-switch
                color="success"
                variant="pill"
                size="lg"
                class="pt-2"
                v-model="bagMealPlan"
                @change="
                  val => {
                    setWeeklySubscriptionValue(val);
                    updateParentData();
                    setBagMealPlan(val);
                    syncDiscounts();
                    removePromos();
                  }
                "
              />
            </h3>
          </div>
        </div>
        <div
          v-if="
            weeklySubscriptionValue &&
              !manualOrder &&
              bagSubscriptionInterval !== 'select'
          "
        >
          <p v-if="bagSubscriptionInterval !== 'prepaid'">
            A subscription is a {{ bagSubscriptionInterval }}ly recurring order.
            Your card will be automatically charged and new orders will
            continuously be created for you on a {{ bagSubscriptionInterval }}ly
            basis. You can change the items in your subscription and you can
            cancel the subscription
            <span v-if="storeSettings.minimumSubWeeks > 1"
              >after
              {{ storeSettings.minimumSubWeeks }}
              orders</span
            >.
          </p>
          <p v-else>
            A prepaid is a subscription that will charge you now all at once for
            the next {{ storeSettings.prepaidWeeks }} weeks of orders.
          </p>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          weeklySubscription &&
            (hasMultipleSubscriptionIntervals ||
              store.settings.prepaidSubscriptions) &&
            !adjusting
        "
      >
        <div class="d-inline">
          <div
            class="d-inline"
            v-if="
              hasMultipleSubscriptionIntervals ||
                (storeSettings.prepaidSubscriptions &&
                  storeSettings.allowWeeklySubscriptions)
            "
          >
            <img
              v-if="!mobile"
              v-b-popover.hover.bottom="
                'Choose the frequency in which to be charged and receive your orders.'
              "
              title="Order Frequency"
              src="/images/store/popover.png"
              class="popover-size ml-1"
            />
            <img
              v-if="mobile"
              v-b-popover.click.top="
                'Choose the frequency in which to be charged and receive your orders.'
              "
              title="Order Frequency"
              src="/images/store/popover.png"
              class="popover-size ml-1"
            />
            <strong>Frequency:</strong>
          </div>
          <p class="strong pb-5" v-if="!storeSettings.allowWeeklySubscriptions">
            Prepaid {{ store.settings.prepaidWeeks }} Week Subscription
          </p>
          <div class="d-inline pl-3">
            <!-- <span
              v-if="bagSubscriptionInterval === 'select'"
              class="red mr-2"
              style="font-size:25px;position:relative;top:8px"
              >*</span
            > -->
            <b-select
              v-if="!store.settings.prepaidSubscriptions"
              style="font-size:16px"
              :value="bagSubscriptionInterval"
              class="mb-1 delivery-select"
              @input="val => setBagSubscriptionInterval(val)"
            >
              <option value="select"><strong>Select</strong></option>
              <option value="week" v-if="storeSettings.allowWeeklySubscriptions"
                ><strong>Weekly</strong></option
              >
              <option
                value="biweek"
                v-if="storeSettings.allowBiWeeklySubscriptions"
                ><strong>Bi-Weekly</strong></option
              >
              <option
                value="month"
                v-if="storeSettings.allowMonthlySubscriptions"
                ><strong>Monthly</strong></option
              >
            </b-select>
            <!-- If a store winds up not wanting to offer single week subs along with prepaid subscriptions, make a new field in store_settings for this. Checkbox on settings page "Also allow weekly" -->
            <b-select
              v-if="
                storeSettings.prepaidSubscriptions &&
                  storeSettings.allowWeeklySubscriptions
              "
              style="font-size:16px"
              :value="bagSubscriptionInterval"
              class="mb-1 delivery-select"
              @input="val => setBagSubscriptionInterval(val)"
            >
              <option value="select"><strong>Select</strong></option>
              <option value="week"><strong>Weekly</strong></option>
              <option value="prepaid"
                ><strong
                  >Prepaid {{ store.settings.prepaidWeeks }} Weeks</strong
                ></option
              >
            </b-select>
          </div>
        </div>
      </li>

      <li class="checkout-item">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Subtotal</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(subtotal, storeSettings.currency) }}
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="couponApplied">
        <div class="row">
          <div class="col-6 col-md-4">
            <span
              class="d-inline mr-2"
              @click="removeCoupon"
              v-if="couponApplied"
            >
              <i class="fas fa-times-circle clear-meal dark-gray pt-1"></i>
            </span>
            <span class="text-success" v-if="coupon && couponApplied"
              >Coupon ({{ coupon.code }})</span
            >
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success" v-if="couponReduction > 0"
              >({{
                format.money(couponReduction, storeSettings.currency)
              }})</span
            >
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="mealPlanDiscount > 0">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Subscription Discount:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5 text-success">
            ({{ format.money(mealPlanDiscount, storeSettings.currency) }})
          </div>
        </div>
      </li>

      <!-- Show sales tax after subtotal if salesTaxAfterFees is turned off -->
      <li
        class="checkout-item"
        v-if="storeSettings.enableSalesTax && !storeSettings.salesTaxAfterFees"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Sales Tax </strong
            ><!-- {{ salesTax * 100 }}% -->
            <p
              v-if="$route.params.adjustOrder && order.customSalesTax"
              class="small"
            >
              Custom Amount Entered
            </p>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span v-if="editingSalesTax">
              <b-form-input
                type="number"
                min="0"
                v-model="customSalesTax"
                class="d-inline width-70"
                style="font-size:16px"
              ></b-form-input>
              <i
                class="fas fa-check-circle text-primary pt-2 pl-1"
                @click="editingSalesTax = false"
              ></i>
            </span>
            <span v-else>
              {{ format.money(tax, storeSettings.currency) }}
            </span>
            <i
              v-if="($route.params.storeView || storeOwner) && !editingSalesTax"
              @click="editSalesTax"
              class="fa fa-edit text-warning"
            ></i>
            <i
              class="fas fa-undo-alt text-secondary"
              v-if="customSalesTax !== null && editingSalesTax === false"
              @click="
                customSalesTax = null;
                editingSalesTax = false;
              "
            ></i>
            <i
              class="fas fa-undo-alt"
              v-if="$route.params.adjustOrder && order.customSalesTax"
              @click="order.customSalesTax = 0"
            ></i>
          </div>
        </div>
      </li>

      <li
        class="checkout-item"
        v-if="bagDeliverySettings.applyDeliveryFee === true && pickup === 0"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>{{ selectedTransferType }} Fee</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span v-if="!couponFreeDelivery && !promotionFreeDelivery">
              <span v-if="editingDeliveryFee">
                <b-form-input
                  type="number"
                  v-model="customDeliveryFee"
                  class="d-inline width-70"
                  style="font-size:16px"
                ></b-form-input>
                <i
                  style="flex-basis:30%"
                  class="fas fa-check-circle text-primary pt-2 pl-1"
                  @click="editingDeliveryFee = false"
                ></i>
              </span>
              <span v-else>{{
                format.money(deliveryFeeAmount, storeSettings.currency)
              }}</span>
              <i
                v-if="
                  ($route.params.storeView || storeOwner) && !editingDeliveryFee
                "
                @click="editDeliveryFee"
                class="fa fa-edit text-warning"
              ></i>
              <i
                class="fas fa-undo-alt text-secondary"
                v-if="
                  customDeliveryFee !== null && editingDeliveryFee === false
                "
                @click="
                  customDeliveryFee = null;
                  editingDeliveryFee = false;
                "
              ></i>
            </span>
            <span v-else>
              <p class="text-success">
                {{ format.money(0, storeSettings.currency) }}
              </p>
            </span>
          </div>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="storeSettings.applyProcessingFee && !cashOrder"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Processing Fee:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(processingFeeAmount, storeSettings.currency) }}
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="purchasedGiftCardApplied">
        <div class="row">
          <div class="col-6 col-md-4">
            <span class="d-inline mr-2" @click="removePurchasedGiftCard">
              <i class="fas fa-times-circle clear-meal dark-gray pt-1"></i>
            </span>
            <span class="text-success"
              >Gift Card ({{ purchasedGiftCard.code }})</span
            >
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success" v-if="purchasedGiftCardReduction > 0"
              >({{
                format.money(
                  purchasedGiftCardReduction,
                  storeSettings.currency
                )
              }})</span
            >
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="referralReduction > 0">
        <div class="row">
          <div class="col-6 col-md-4">
            <span class="d-inline mr-2" @click="removeReferral">
              <i class="fas fa-times-circle clear-meal dark-gray pt-1"></i>
            </span>
            <span class="text-success">Referral ({{ referral.code }})</span>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success"
              >({{
                format.money(referralReduction, storeSettings.currency)
              }})</span
            >
          </div>
        </div>
      </li>

      <li
        class="checkout-item"
        v-if="promotionReduction > 0 && !removePromotions"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <!-- <span
              class="d-inline mr-2"
              @click="removePromotions = true"
              v-if="
                ($route.params.storeView === true || storeOwner) &&
                  removePromotions === false
              "
            >
              <i class="fas fa-times-circle clear-meal dark-gray pt-1"></i>
            </span> -->
            <span class="text-success">Promotional Discount</span>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success"
              >({{
                format.money(promotionReduction, storeSettings.currency)
              }})</span
            >
          </div>
        </div>
      </li>

      <li
        class="checkout-item"
        v-if="promotionPointsReduction > 0 && !removePromotions"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <span
              class="d-inline mr-2"
              @click="removePromotions = true"
              v-if="
                ($route.params.storeView === true || storeOwner) &&
                  removePromotions === false
              "
            >
              <i class="fas fa-times-circle clear-meal dark-gray pt-1"></i>
            </span>
            <span class="text-success">{{ promotionPointsName }}</span>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success"
              >({{
                format.money(promotionPointsReduction, storeSettings.currency)
              }})</span
            >
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="storeModules.gratuity">
        <div class="row">
          <div class="col-6 col-md-4 d-flex d-inilne">
            <strong>Tip</strong>
            <b-form-select
              :options="gratuityOptions"
              :value="bagGratuityPercent"
              class="ml-2 w-100px"
              @input="val => setBagGratuityPercent(val)"
            ></b-form-select>
          </div>
          <div class="col-6 col-md-3 offset-md-5 d-flex">
            <b-form-input
              min="0"
              type="number"
              :value="bagCustomGratuity"
              placeholder="0"
              class="w-80px"
              v-if="bagGratuityPercent == 'custom'"
              @input="val => setBagCustomGratuity(val)"
              style="font-size:16px"
            ></b-form-input>
            <span v-else>{{ format.money(tip, storeSettings.currency) }}</span>
          </div>
        </div>
      </li>

      <li class="checkout-item" v-if="storeModules.cooler && !bagPickup">
        <div class="row">
          <div class="col-6 col-md-4">
            <span>
              <strong>Cooler Bag Deposit</strong>
              <img
                v-b-popover.hover="
                  'This deposit will be refunded to you upon return of the cooler.'
                "
                title="Cooler Bag Deposit"
                src="/images/store/popover.png"
                class="popover-size"
              />
              <b-form-checkbox
                v-model="includeCooler"
                @input="coolerDepositChanged = true"
                v-if="storeModuleSettings.coolerOptional"
                >Include</b-form-checkbox
              >
            </span>
          </div>
          <div class="col-6 col-md-3 offset-md-5 d-flex">
            <span v-if="includeCooler">
              {{ format.money(coolerDeposit, storeSettings.currency) }}
            </span>
            <span v-else>
              {{ format.money(0, storeSettings.currency) }}
            </span>
          </div>
        </div>
      </li>

      <!-- Show sales tax after fees if salesTaxAfterFees is turned on -->
      <li
        class="checkout-item"
        v-if="storeSettings.enableSalesTax && storeSettings.salesTaxAfterFees"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Sales Tax </strong
            ><!-- {{ salesTax * 100 }}% -->
            <p
              v-if="$route.params.adjustOrder && order.customSalesTax"
              class="small"
            >
              Custom Amount Entered
            </p>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span v-if="editingSalesTax">
              <b-form-input
                type="number"
                v-model="customSalesTax"
                class="d-inline width-70"
                style="font-size:16px"
              ></b-form-input>
              <i
                class="fas fa-check-circle text-primary pt-2 pl-1"
                @click="editingSalesTax = false"
              ></i>
            </span>
            <span v-else>
              {{ format.money(tax, storeSettings.currency) }}
            </span>
            <i
              v-if="($route.params.storeView || storeOwner) && !editingSalesTax"
              @click="editSalesTax"
              class="fa fa-edit text-warning"
            ></i>
            <i
              class="fas fa-undo-alt text-secondary"
              v-if="customSalesTax !== null && editingSalesTax === false"
              @click="
                customSalesTax = null;
                editingSalesTax = false;
              "
            ></i>
            <i
              class="fas fa-undo-alt"
              v-if="$route.params.adjustOrder && order.customSalesTax"
              @click="order.customSalesTax = 0"
            ></i>
          </div>
        </div>
      </li>

      <li class="checkout-item">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Total</strong>
            <span v-if="prepaid">
              <br />{{ format.money(grandTotal / 4, storeSettings.currency) }} x
              {{ storeSettings.prepaidWeeks }} weekly orders.
            </span>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <strong>{{
              format.money(grandTotal, storeSettings.currency)
            }}</strong>
          </div>
        </div>
      </li>

      <!-- Coupon Area -->
      <li
        v-if="
          store.hasPromoCodes &&
            showDiscounts.coupons &&
            (!store.modules.noPromosOnSubscriptions ||
              (store.modules.noPromosOnSubscriptions &&
                !weeklySubscriptionValue))
        "
      >
        <h5 v-if="!loggedIn">Promo codes entered during checkout.</h5>
        <div class="row">
          <div class="col-xs-6 pl-3">
            <b-form-group id="coupon">
              <b-form-input
                style="font-size:16px"
                id="coupon-code"
                v-model="discountCode"
                required
                placeholder="Enter Promotional Code"
                v-if="loggedIn"
              ></b-form-input>
            </b-form-group>
          </div>
          <div class="col-xs-6 pl-2">
            <b-btn variant="primary" @click="applyDiscountCode" v-if="loggedIn"
              >Apply</b-btn
            >
          </div>
        </div>
      </li>
      <li
        v-if="store.modules.noPromosOnSubscriptions && weeklySubscriptionValue"
      >
        <b-alert variant="warning" show class="pb-4">
          <center>
            <span class="strong"
              >Promo codes aren't available on subscriptions.</span
            >
          </center>
        </b-alert>
      </li>

      <li
        v-if="
          availablePromotionPoints &&
            availablePromotionPoints > 0 &&
            !$route.params.adjustMealPlan &&
            !subscriptionId
        "
      >
        <div class="row">
          <div class="col-sm-12 pl-3">
            <b-alert variant="info" show class="pb-4"
              >You have
              <span class="strong"
                >{{ availablePromotionPoints }} {{ promotionPointsName }}</span
              >
              available. Would you like to use your points on this order?
              <c-switch
                color="primary"
                variant="pill"
                size="lg"
                v-model="usePromotionPoints"
                class="pt-3"
                @change.native="applyPromotionPoints"
            /></b-alert>
          </div>
        </div>
      </li>
    </ul>

    <li
      class="checkout-item"
      v-if="
        transferTypeCheckDelivery &&
          transferTypeCheckPickup &&
          (!storeModules.hideDeliveryOption ||
            $route.params.storeView === true ||
            storeOwner) &&
          store.delivery_day_zip_codes.length == 0 &&
          !transferTypes.both
      "
    >
      <b-alert
        v-if="
          storeSettings.preventNextWeekOrders &&
            storeSettings.next_orderable_delivery_dates.length === 0 &&
            storeSettings.next_orderable_pickup_dates.length === 0
        "
        variant="warning center-text"
        show
        >Orders are closed until {{ storeSettings.menuReopening }}.</b-alert
      >
      <b-form-group>
        <b-form-radio-group v-model="bagPickup" @change="changePickup">
          <b-form-radio
            :value="0"
            v-if="
              storeSettings.next_orderable_delivery_dates.length > 0 ||
                $route.params.storeView ||
                storeOwner
            "
          >
            <strong>{{ deliveryShipping }}</strong>
          </b-form-radio>
          <b-form-radio
            :value="1"
            v-if="
              (storeSettings.next_orderable_pickup_dates.length > 0 ||
                $route.params.storeView ||
                storeOwner) &&
                deliveryShipping !== 'Shipping'
            "
          >
            <strong>Pickup</strong>
          </b-form-radio>
        </b-form-radio-group>
      </b-form-group>
    </li>
    <li
      :class="pickupLocationClass"
      v-if="storeModules.pickupLocations && pickup"
    >
      <div>
        <strong>Pickup Location</strong>
        <b-select
          style="font-size:16px"
          v-model="selectedPickupLocation"
          :options="pickupLocationOptions"
          @input="val => setBagPickupLocation(val)"
          class="delivery-select ml-2"
          required
        ></b-select>
      </div>
      <p
        v-if="selectedPickupLocationAddress && selectedPickupLocation"
        class="pt-3"
      >
        {{ selectedPickupLocationAddress }}
      </p>
    </li>
    <div
      v-if="
        !storeModules.hideTransferOptions ||
          $route.params.storeView ||
          storeOwner
      "
    >
      <li
        class="checkout-item"
        v-if="
          ($route.params.storeView || storeOwner) &&
            !isMultipleDelivery &&
            !$route.params.adjustMealPlan &&
            !subscriptionId
        "
      >
        <div class="d-flex">
          <strong class="d-inline pt-1">{{ selectedTransferType }} Day</strong>
          <b-select
            style="font-size:16px"
            v-model="deliveryDay"
            :options="deliveryDateOptionsStoreView"
            :value="bagDeliveryDate"
            @input="changeDeliveryDay"
            class="delivery-select ml-2 d-inilne"
            required
          >
            <option slot="top" disabled>-- Select delivery day --</option>
          </b-select>
          <b-form-checkbox v-model="backdate" class="d-inline pt-1 ml-2"
            >Show Past Dates</b-form-checkbox
          >
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          deliveryDateOptions.length > 1 &&
            $route.params.subscriptionId === undefined &&
            !$route.params.storeView &&
            !storeOwner &&
            (!bagDeliveryDate || !store.modules.category_restrictions) &&
            !isMultipleDelivery &&
            (pickup === 0 ||
              !store.modules.pickupLocations ||
              (store.modules.pickupLocations &&
                selectedPickupLocation !== null)) &&
            !subscriptionId
        "
      >
        <div>
          <strong
            v-if="
              pickup === 0 &&
                deliveryDateOptions.length > 1 &&
                !storeModules.hideDeliveryOption
            "
          >
            {{ selectedTransferType }} Day
          </strong>
          <strong
            v-if="
              pickup === 1 &&
                deliveryDateOptions.length > 1 &&
                (pickup === 0 ||
                  !store.modules.pickupLocations ||
                  (store.modules.pickupLocations &&
                    selectedPickupLocation !== null))
            "
          >
            Pickup Day
          </strong>
          <b-select
            style="font-size:16px"
            v-if="
              deliveryDateOptions.length > 1 &&
                (pickup === 0 ||
                  !store.modules.pickupLocations ||
                  (store.modules.pickupLocations &&
                    selectedPickupLocation !== null))
            "
            :options="deliveryDateOptions"
            :value="bagDeliveryDate"
            @input="changeDeliveryDay"
            class="delivery-select ml-2"
            required
          >
            <option slot="top" disabled
              >-- Select {{ selectedTransferType.toLowerCase() }} day --</option
            >
          </b-select>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          store.modules.category_restrictions &&
            bagDeliveryDate &&
            !$route.params.storeView
        "
      >
        <div>
          <strong>
            Pickup Day: {{ moment(bagDeliveryDate).format("dddd, MMM Do") }}
            <!-- Add Delivery/Pickup option when next store uses category_restrictions -->
          </strong>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          deliveryDateOptions.length === 1 &&
            $route.params.subscriptionId === undefined &&
            !$route.params.storeView &&
            !storeOwner &&
            !isMultipleDelivery &&
            !subscriptionId
        "
      >
        <div>
          <strong v-if="pickup === 0">
            {{ selectedTransferType }} Day: {{ deliveryDateOptions[0].text }}
          </strong>
          <strong v-if="pickup === 1">
            Pickup Day: {{ deliveryDateOptions[0].text }}
          </strong>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          (storeModules.pickupHours || storeModules.deliveryHours) &&
            deliveryDay !== undefined &&
            $route.params.subscriptionId === undefined
        "
      >
        <div>
          <strong>{{ selectedTransferType }} Time</strong>
          <b-form-select
            style="font-size:16px"
            :class="transferTimeClass"
            v-model="transferTime"
            :value="transferTime"
            :options="transferTimeOptions"
            @input="changeDeliveryTime"
          ></b-form-select>
          <!-- <b-form-checkbox
            class="d-inline ml-2"
            style="position:relative;top:6px"
            v-if="storeModuleSettings.transferTimeRange"
            v-model="hourInterval"
            >Hour Intervals</b-form-checkbox
          > -->
        </div>
      </li>

      <!-- <li
        class="checkout-item"
        v-if="
          $route.params.storeView &&
            (storeModules.deliveryHours || storeModules.pickupHours)
        "
      >
        <div class="d-inline">
          <p class="strong d-inline">Custom Time</p>
          <b-form-input
            class="delivery-select ml-2 d-inline"
            v-model="transferTime"
            :value="transferTime"
          ></b-form-input>
        </div>
      </li> -->
    </div>
    <li
      class="transfer-instruction mt-2"
      v-if="
        !$route.params.storeView &&
          !storeOwner &&
          loggedIn &&
          transferInstructions
      "
    >
      <p class="strong">{{ selectedTransferType }} Instructions</p>
      <p v-html="transferInstructions"></p>
    </li>

    <li v-if="loggedIn">
      <div
        v-if="
          ($route.params.storeView || storeOwner) && store.modules.showStaff
        "
      >
        <h4 class="mt-2 mb-3">
          Staff Member
        </h4>
        <v-select
          label="name"
          :options="staff"
          v-model="staffMember"
          placeholder="Staff member taking the order."
          :filterable="false"
          :reduce="staff => staff.id"
          class="mb-2"
          @input="changeStaff"
        >
        </v-select>
      </div>
      <div>
        <div v-if="context === 'store' && !adjusting">
          <b-form-group>
            <h4 class="mt-2 mb-3">Customer</h4>
            <!--<v-select
              label="text"
              :options="customers"
              :reduce="customer => customer.value"
              v-model="customerModel"
              :value="customer"
              @input="getCards"
            >!-->

            <!-- Hide regular customer dropdown & show search customer dropdown if store has 250 or more customers -->
            <!-- <v-select
              v-if="
                store.id === 108 ||
                  store.id === 109 ||
                  store.id === 110 ||
                  store.id === 278
              "
              placeholder="Search customer name"
              label="text"
              :options="customers"
              :value="customerModel"
              @input="val => changeCustomer(val)"
            >
            </v-select> -->

            <v-select
              label="text"
              :options="customerOptions"
              @search="onSearchCustomer"
              @input="val => changeCustomer(val)"
              placeholder="Or search by email, phone, or address."
              :filterable="false"
              :value="customerModel"
            >
            </v-select>
          </b-form-group>

          <b-btn variant="primary" v-if="store.id === 3" @click="setSample"
            >GO!</b-btn
          >

          <b-btn
            variant="primary"
            v-if="storeModules.manualCustomers"
            @click="showAddCustomerModal"
            >Add New Customer</b-btn
          >
        </div>
        <div v-if="!hidePaymentArea && showPaymentMethod">
          <h4 class="mt-2 mb-3">
            Payment Method
          </h4>

          <div
            v-if="
              storeModules.cashOrders &&
                (storeModuleSettings.cashAllowedForCustomer ||
                  context === 'store')
            "
          >
            <b-form-checkbox
              v-model="cashOrder"
              class="pb-2 mediumCheckbox mt-1 mb-1"
            >
              {{ storeModuleSettings.cashOrderWording }}
            </b-form-checkbox>
            <b-form-checkbox
              v-if="context === 'store'"
              v-model="noBalance"
              class="pb-2 mediumCheckbox mt-1 mb-1"
            >
              No Balance
            </b-form-checkbox>

            <!-- <p
              v-if="
                storeModuleSettings.cashAllowedForCustomer && cashOrder && creditCardList.length === 0 && creditCardId === null
              "
            >
              Please add a credit card on file in order to proceed with a cash
              order. In the event that cash is not paid, your credit card will be
              charged.
            </p> -->
          </div>

          <!-- <div
            v-if="
              store.settings.payment_gateway === 'authorize' &&
                !$route.params.storeView &&
                loggedIn &&
                !addedBillingAddress
            "
            class="pb-2"
          >
            <p class="strong">
              Does your billing address below match your delivery address?
            </p>
            <p>
              <b>Billing Address:</b> {{ user.user_detail.address }},
              {{ user.user_detail.city }}, {{ user.user_detail.state }},
              {{ user.user_detail.zip }}
            </p>
            <div class="d-inline">
              <b-btn
                variant="danger"
                class="d-inline"
                @click="showBillingAddressModal = true"
                >No</b-btn
              >
              <b-btn
                variant="success"
                class="d-inline"
                @click="billingAddressVerified = true"
                >Yes</b-btn
              >
            </div>
          </div> -->
          <!-- <div
            v-if="
              store.settings.payment_gateway === 'authorize' &&
                !$route.params.storeView &&
                loggedIn &&
                addedBillingAddress
            "
            class="pb-2"
          >
            <p>
              <b>Billing Address:</b> {{ currentBillingAddress }}
            </p>
          </div> -->
          <card-picker
            v-if="!cashOrder && storeSettings.payment_gateway !== 'cash'"
            :selectable="true"
            :creditCards="creditCardList"
            :checkoutPage="true"
            v-model="card"
            class="mb-2"
            ref="cardPicker"
            :gateway="gateway"
          ></card-picker>

          <b-form-group
            v-if="
              ($route.params.storeView || storeOwner) && storeModules.deposits
            "
            horizontal
            label="Deposit"
          >
            <b-form-input
              v-model="deposit"
              type="number"
              required
              placeholder="$0.00"
              style="font-size:16px"
            ></b-form-input>
          </b-form-group>
        </div>

        <div
          v-if="
            hasActiveSubscription &&
              store.modules.allowMultipleSubscriptions &&
              weeklySubscription
          "
          class="alert alert-warning"
          role="alert"
        >
          You already have an active subscription with us and may have already
          been charged for an order.
        </div>
        <!-- <b-alert
          show
          variant="warning"
          class="center-text pt-2"
          v-if="
            ($route.params.storeView || storeOwner) &&
              card === null &&
              cashOrder === null &&
              customerModel != null
          "
          >Please choose a payment method.</b-alert
        > -->
        <div
          v-if="
            ($route.params.adjustOrder || $route.params.manualOrder) &&
              !customerEmail.includes('noemail')
          "
        >
          <b-form-checkbox
            v-if="store.modules.showHotCheckbox"
            v-model="hot"
            class="pb-2 mediumCheckbox mt-1 mb-1"
          >
            Hot
          </b-form-checkbox>
          <b-form-checkbox
            v-model="emailCustomer"
            class="pb-2 mediumCheckbox mt-1 mb-1"
          >
            Email Customer
          </b-form-checkbox>
          <b-form-checkbox
            v-if="$route.params.adjustOrder"
            v-model="dontAffectBalance"
            class="pb-2 mediumCheckbox mt-1 mb-1 pl-5"
            >Don't Adjust Balance
          </b-form-checkbox>
        </div>

        <b-alert
          show
          v-if="
            store.modules.frequencyItems &&
              bagHasMultipleFrequencyItems &&
              !adjustingSubscription &&
              !adjustingOrder
          "
          variant="secondary"
        >
          <p class="center-text strong">
            Checking out will create both a one time order and a subscription.
          </p>
        </b-alert>

        <li v-if="loggedIn && invalidCheckout && !adjustingOrder">
          <b-alert variant="warning" show class="pb-0 mb-0">
            <p class="strong center-text font-14">{{ invalidCheckout }}</p>
            <div class="d-flex d-center">
              <b-btn
                class="mb-3"
                variant="warning"
                v-if="
                  context !== 'store' &&
                    !this.willDeliver &&
                    this.bagPickup === 0
                "
                @click="showDeliveryAreaModal = true"
                >View Delivery Area</b-btn
              >
            </div>
          </b-alert>
        </li>

        <!-- <li v-if="adjustingOrder && invalidCheckout">
          <b-btn
            @click="blockedCheckoutMessage()"
            class="menu-bag-btn"
            style="background:#a7a7a7 !important"
            >UPDATE ORDER</b-btn
          >
        </li> -->

        <li v-if="adjustingOrder">
          <b-btn @click="adjust" class="menu-bag-btn">UPDATE ORDER</b-btn>
        </li>

        <li v-if="loggedIn && invalidCheckout && adjustingSubscription">
          <b-btn
            @click="blockedCheckoutMessage()"
            class="menu-bag-btn"
            style="background:#a7a7a7 !important"
            >UPDATE SUBSCRIPTION</b-btn
          >
        </li>

        <div
          v-if="
            !invalidCheckout &&
              subscriptionId &&
              (minimumMet || context == 'store')
          "
        >
          <b-btn
            class="menu-bag-btn update-meals-btn"
            @click="updateSubscriptionMeals"
            >UPDATE SUBSCRIPTION</b-btn
          >
        </div>
        <li v-if="!invalidCheckout && !adjusting">
          <b-btn
            @click="checkout"
            :disabled="checkingOut"
            class="menu-bag-btn mb-4"
            >CHECKOUT</b-btn
          >
        </li>

        <li v-if="loggedIn && invalidCheckout && !adjusting">
          <b-btn
            @click="blockedCheckoutMessage()"
            class="menu-bag-btn"
            style="background:#a7a7a7 !important"
            >CHECKOUT</b-btn
          >
        </li>
      </div>
    </li>

    <li v-else>
      <div class="row" v-if="!adjusting">
        <b-btn @click="showAuthModal()" class="menu-bag-btn mb-4"
          >CONTINUE CHECKOUT</b-btn
        >
      </div>
    </li>

    <add-customer-modal
      :addCustomerModal="addCustomerModal"
    ></add-customer-modal>

    <b-modal
      size="sm"
      title="Delivery Area"
      v-model="showDeliveryAreaModal"
      v-if="showDeliveryAreaModal"
      no-fade
    >
      <p
        class="strong pt-2"
        v-if="store.settings.delivery_distance_type === 'radius'"
      >
        Our delivery radius is
        {{ store.settings.delivery_distance_radius }} miles from postal code:
        {{ store.details.zip }}
      </p>
      <span v-if="store.settings.delivery_distance_type === 'zipcodes'">
        <p class="strong pt-2">We deliver to the following postal codes:</p>
        <span
          v-for="(zipcode, index) in store.settings.delivery_distance_zipcodes"
        >
          {{ zipcode
          }}<span
            v-if="store.settings.delivery_distance_zipcodes.length - index > 1"
            >,
          </span>
        </span>
      </span>
    </b-modal>

    <b-modal
      size="md"
      title="Please Add Billing Address"
      v-model="showBillingAddressModal"
      v-if="showBillingAddressModal"
      hide-footer
      no-fade
    >
      <b-form @submit.prevent="addBillingAddress" class="mt-4">
        <b-form-group horizontal label="Billing Address">
          <b-form-input
            v-model="form.billingAddress"
            type="text"
            required
            placeholder="Billing Address"
            style="font-size:16px"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Billing City">
          <b-form-input
            v-model="form.billingCity"
            type="text"
            required
            placeholder="Billing City"
            style="font-size:16px"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Billing State">
          <v-select
            v-model="form.billingState"
            label="name"
            :options="stateNames"
            @keypress.enter.native.prevent=""
            style="font-size:16px"
          ></v-select>
        </b-form-group>
        <b-form-group horizontal label="Billing Zip">
          <b-form-input
            v-model="form.billingZip"
            type="text"
            required
            placeholder="Billing Zip"
            style="font-size:16px"
          ></b-form-input>
        </b-form-group>
        <b-button type="submit" variant="primary" class="float-right"
          >Add</b-button
        >
      </b-form>
    </b-modal>

    <v-style>
      .frequencyFilters .active{ background-color:
      {{ store.settings.color }} !important; }
    </v-style>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import SalesTax from "sales-tax";
import CardPicker from "../../components/Billing/CardPicker";
import { createToken } from "vue-stripe-elements-plus";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";
import states from "../../data/states.js";
import format from "../../lib/format";

export default {
  components: {
    CardPicker,
    AddCustomerModal
  },
  data() {
    return {
      overrideCardId: null,
      fillingOutCard: false,
      backdate: false,
      showDeliveryAreaModal: false,
      showDiscounts: {
        subscriptionDiscount: true,
        coupons: true,
        promotions: true
      },
      applySubDiscountOnOrder: false,
      doubleCheckout: false,
      customGratuity: 0,
      lastUsedDiscountCode: null,
      hourInterval: false,
      coolerDepositChanged: false,
      includeCooler: true,
      customerOptions: [],
      coupons: [],
      purchasedGiftCards: [],
      pointsReduction: 0,
      promotionPoints: null,
      usePromotionPoints: false,
      removePromotions: false,
      hasWeeklySubscriptionItems: false,
      hasMonthlySubscriptionItems: false,
      hasPrepaidSubscriptionItems: false,
      form: {
        billingState: null
      },
      dontAffectBalance: false,
      showBillingAddressModal: false,
      billingAddressVerified: false,
      customSalesTax: null,
      customDeliveryFee: null,
      editingSalesTax: false,
      editingDeliveryFee: false,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      deposit: null,
      creditCardId: null,
      discountCode: "",
      addCustomerModal: false,
      weeklySubscriptionValue: null,
      existingCustomerAdded: false,
      emailCustomer: true,
      // selectedPickupLocation:
      //   this.order && this.order.pickup_location_id
      //     ? this.order.pickup_location_id
      //     : null,
      minimumDeliveryDayAmount: 0
    };
  },
  updated() {
    // this.creditCardId = this.card;

    // this.$eventBus.$on("chooseCustomer", () => {
    //   this.chooseCustomer();
    // });
    if (this.bagPickupSet) {
      this.$parent.pickup = this.bagPickup;
    }

    if (this.coupon && this.coupon.minimum > 0) {
      if (this.coupon.minimum > this.totalBagPricePreFees) {
        this.removeCoupon();
      }
    }
  },
  props: {
    order: null,
    customer: null,
    preview: false,
    manualOrder: false,
    forceValue: false,
    cashOrder: false,
    noBalance: false,
    hot: false,
    mobile: false,
    salesTax: 0,
    creditCardList: null,
    orderId: null,
    deliveryDay: null,
    transferTime: null,
    pickup: 0,
    orderLineItems: null,
    checkoutData: null,
    gateway: {
      type: String,
      default: "stripe"
    },
    adjustMealPlan: null,
    availablePromotionPoints: null
  },
  watch: {
    customer: function(val) {
      if (!this.existingCustomerAdded) {
        if (val) {
          // this.setBagCustomerModel(this.getCustomerObject(val));
          if (
            this.context == "store" &&
            this.store.settings.deliveryFeeType == "mileage"
          ) {
            axios
              .post("/api/me/getDistanceFrom", { id: this.customerModel.value })
              .then(resp => {
                this.store.distance = resp.data;
              });
          }
        } else {
          this.setBagCustomerModel(null);
        }
        if (this.$route.params.manualOrder) {
          this.getCards();
        }
      }
    },
    customerModel: function(val) {
      this.getCards();
      this.updateParentData();
    },
    customDeliveryFee: function(val) {
      this.setBagDeliveryFee(val);
    },
    bagHasOnlySubItems: function(val) {
      if (val === true) {
        this.setFrequencySubscription("sub");
      }
    },
    bagHasOnlyOrderItems: function(val) {
      if (val === true) {
        this.setFrequencySubscription(null);
      }
    }
  },
  mounted: function() {
    // this.refreshStoreCustomers();

    this.setOrderFrequency();

    if (this.store.modules.frequencyItems) {
      if (this.bagHasMultipleFrequencyItems || this.bagHasOnlySubItems) {
        this.setFrequencySubscription("sub");
      } else {
        this.setFrequencySubscription(null);
      }
    }

    if (this.customer) {
      this.setBagCustomerModel(this.getCustomerObject(this.customer));
    } else {
      this.setBagCustomerModel(null);
    }

    if (this.forceValue) {
      if (this.customer) {
        this.getCards();
      }
    }

    // if (this.$route.params.subscription){
    //   let customerModel = { text: this.$route.params.subscription.user.name, value: this.$route.params.subscription.customer_id };
    //   this.$parent.customer = this.$route.params.subscription.customer_id;
    //   this.setBagCustomerModel(customerModel);
    // }

    let stateAbr = this.store.details.state;
    let state = this.stateNames.filter(stateName => {
      return stateName.value.toLowerCase() === stateAbr.toLowerCase();
    });

    this.form.billingState = state[0];

    // if (this.adjustingSubscription) {
    //   this.setSubscriptionCoupon();
    // }

    if (
      this.$route.params.adjustOrder &&
      this.order.purchasedGiftCardReduction > 0
    ) {
      axios
        .post("/api/me/findPurchasedGiftCard", {
          store_id: this.store.id,
          purchasedGiftCardCode: this.order.purchased_gift_card_code
        })
        .then(resp => {
          this.setBagPurchasedGiftCard(resp.data);
        });
    }

    if (this.$route.params.adjustOrder) {
      if (this.order.coolerDeposit === "0.00") {
        this.includeCooler = false;
      }
    }
    if (this.$route.params.adjustMealPlan) {
      if (this.$route.params.subscription.coolerDeposit === "0.00") {
        this.includeCooler = false;
      }
    }
    this.setSubscription();

    if (this.isMultipleDelivery) {
      this.minimumDeliveryDayAmount = this.store.delivery_days[0].minimum;
    }

    if (this.deliveryShipping == "Shipping") {
      this.setBagPickup(0);
    }

    if (!this.hasDeliveryOption) {
      this.setBagPickup(1);
    }

    if (this.$route.params.adjustOrder) {
      this.setBagGratuityPercent("custom");
      this.customGratuity = this.order.gratuity > 0 ? this.order.gratuity : 0;
    }

    if (this.$parent.$route.params.subscription) {
      this.getCards();
      this.setBagGratuityPercent("custom");
      this.customGratuity =
        this.$parent.$route.params.subscription.gratuity > 0
          ? this.$parent.$route.params.subscription.gratuity
          : 0;
    }
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      creditCards: "cards",
      store: "viewedStore",
      storeModules: "viewedStoreModules",
      storeModuleSettings: "viewedStoreModuleSettings",
      storeCustomers: "storeCustomers",
      total: "bagQuantity",
      bag: "bagItems",
      bagDeliveryDate: "bagDeliveryDate",
      coupon: "bagCoupon",
      purchasedGiftCard: "bagPurchasedGiftCard",
      referral: "bagReferral",
      deliveryPlan: "bagMealPlan",
      mealPlan: "bagMealPlan",
      hasMeal: "bagHasMeal",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPricePreFeesBothTypes: "totalBagPricePreFeesBothTypes",
      totalBagPrice: "totalBagPrice",
      willDeliver: "viewedStoreWillDeliver",
      isLoading: "isLoading",
      storeLogo: "viewedStoreLogo",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      pickupLocations: "viewedStorePickupLocations",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _orders: "orders",
      subscriptions: "subscriptions",
      user: "user",
      storeCoupons: "storeCoupons",
      bagDeliverySettings: "bagDeliverySettings",
      deliveryDays: "viewedStoreDeliveryDays",
      referrals: "viewedStoreReferrals",
      promotions: "viewedStorePromotions",
      deliveryFeeZipCodes: "viewedStoreDeliveryFeeZipCodes",
      holidayTransferTimes: "viewedStoreHolidayTransferTimes",
      bagPickup: "bagPickup",
      bagPickupSet: "bagPickupSet",
      staff: "storeStaff",
      staffMember: "bagStaffMember",
      customerModel: "bagCustomerModel",
      context: "context",
      deliveryFee: "bagDeliveryFee",
      frequencyType: "bagFrequencyType",
      mealMixItems: "mealMixItems",
      selectedPickupLocation: "bagPickupLocation",
      bagGratuityPercent: "bagGratuityPercent",
      bagCustomGratuity: "bagCustomGratuity",
      bagSubscriptionInterval: "bagSubscriptionInterval",
      distance: "viewedStoreDistance",
      bagNotes: "bagNotes",
      bagPublicNotes: "bagPublicNotes",
      bagMealPlan: "bagMealPlan"
    }),
    prepaid() {
      if (
        this.weeklySubscriptionValue &&
        this.storeSettings.prepaidSubscriptions &&
        this.bagSubscriptionInterval === "prepaid"
      ) {
        return true;
      } else {
        return false;
      }
    },
    hasMultipleSubscriptionIntervals() {
      let count = 0;
      if (this.storeSettings.allowWeeklySubscriptions) {
        count++;
      }
      if (this.storeSettings.allowBiWeeklySubscriptions) {
        count++;
      }
      if (this.storeSettings.allowMonthlySubscriptions) {
        count++;
      }
      return count > 1 ? true : false;
    },
    adjusting() {
      if (this.adjustingOrder || this.adjustingSubscription) {
        return true;
      }
    },
    adjustingOrder() {
      if (this.$route.params.adjustOrder || this.$route.params.orderId) {
        return true;
      }
    },
    adjustingSubscription() {
      if (
        this.subscriptionId ||
        this.$route.params.adjustMealPlan ||
        this.adjustMealPlan ||
        this.$route.query.sub === "true"
      ) {
        return true;
      }
    },
    bagHasMultipleFrequencyItems() {
      let hasSubItems = this.bag.some(item => {
        return item.meal.frequencyType === "sub";
      });
      let hasOrderItems = this.bag.some(item => {
        return item.meal.frequencyType !== "sub";
      });

      if (hasSubItems && hasOrderItems) {
        return true;
      } else {
        return false;
      }
    },
    bagHasOnlySubItems() {
      if (
        this.bag.some(item => {
          return item.meal.frequencyType == "sub";
        }) &&
        !this.bag.some(item => {
          return item.meal.frequencyType !== "sub";
        })
      ) {
        return true;
      } else {
        return false;
      }
    },
    bagHasOnlyOrderItems() {
      if (
        this.bag.some(item => {
          return item.meal.frequencyType !== "sub";
        }) &&
        !this.bag.some(item => {
          return item.meal.frequencyType === "sub";
        })
      ) {
        return true;
      } else {
        return false;
      }
    },
    hasDeliveryOption() {
      return (
        this.store.settings.transferType.includes("delivery") ||
        ((this.store.modules.customDeliveryDays ||
          this.store.modules.multipleDeliveryDays) &&
          this.store.delivery_days.some(day => {
            return day.type == "delivery";
          }))
      );
    },
    deliveryShipping() {
      let {
        applyDeliveryFee,
        deliveryFee,
        deliveryFeeType,
        mileageBase,
        mileagePerMile
      } = this.bagDeliverySettings;

      if (this.order && this.order.shipping) {
        return "Shipping";
      }
      if (
        this.$route.params.subscription &&
        this.$route.params.subscription.shipping
      ) {
        return "Shipping";
      }

      if (deliveryFeeType == "zip" && this.loggedIn) {
        let userZip = this.customerModel
          ? this.customerModel.zip.replace(/\s/g, "")
          : this.user.user_detail.zip.replace(/\s/g, "");
        let zip = this.deliveryFeeZipCodes.find(dfzc => {
          dfzc.zip_code = dfzc.zip_code.replace(/\s/g, "");
          return dfzc.zip_code === userZip;
        });
        if (zip && zip.shipping) {
          return "Shipping";
        }
      }

      return "Delivery";
    },
    selectedTransferType() {
      let {
        applyDeliveryFee,
        deliveryFee,
        deliveryFeeType,
        mileageBase,
        mileagePerMile
      } = this.bagDeliverySettings;

      if (!this.bagPickup) {
        if (deliveryFeeType == "zip" && this.loggedIn) {
          let userZip = this.customerModel
            ? this.customerModel.zip.replace(/\s/g, "")
            : this.user.user_detail.zip.replace(/\s/g, "");
          let zip = this.deliveryFeeZipCodes.find(dfzc => {
            dfzc.zip_code = dfzc.zip_code.replace(/\s/g, "");
            return dfzc.zip_code === userZip;
          });
          if (zip && zip.shipping) {
            return "Shipping";
          }
        }

        return "Delivery";
      } else {
        return "Pickup";
      }
    },
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.day_friendly;
              if (!groupedDD[key]) {
                groupedDD[key] = {
                  items: [],
                  delivery_day: bagItem.delivery_day
                };
              }

              groupedDD[key].items.push(bagItem);
            }
          });

          if (JSON.stringify(groupedDD) != "{}") {
            for (let i in groupedDD) {
              grouped.push(groupedDD[i]);
            }
          }

          // Add all delivery days
          if (this.selectedDeliveryDay) {
            let included = false;
            grouped.forEach(group => {
              if (group.delivery_day.id === this.selectedDeliveryDay.id) {
                included = true;
              }
            });
            if (!included) {
              grouped.push({
                items: [],
                delivery_day: this.selectedDeliveryDay
              });
            }
          }
        } else {
          grouped.push({
            items: this.bag
          });
        }
      }

      return grouped;
    },
    transferTypes() {
      let hasDelivery = false;
      let hasPickup = false;

      this.store.delivery_days.forEach(day => {
        if (day.type == "delivery") {
          hasDelivery = true;
        }
        if (day.type == "pickup") {
          hasPickup = true;
        }
      });

      let hasBoth =
        this.store.modules.multipleDeliveryDays && hasDelivery && hasPickup
          ? true
          : false;

      return {
        delivery: hasDelivery,
        pickup: hasPickup,
        both: hasBoth
      };
    },
    gratuityOptions() {
      return [
        { value: 0, text: "None" },
        { value: "custom", text: "Custom" },
        { value: 2, text: "2%" },
        { value: 4, text: "4%" },
        { value: 6, text: "6%" },
        { value: 8, text: "8%" },
        { value: 10, text: "10%" },
        { value: 12, text: "12%" },
        { value: 14, text: "14%" },
        { value: 16, text: "16%" },
        { value: 18, text: "18%" },
        { value: 20, text: "20%" }
      ];
    },
    prefix() {
      if (this.loggedIn) {
        return "/api/me/";
      } else {
        return "/api/guest/";
      }
    },
    activePromotions() {
      let promotions = [];
      this.promotions.forEach(promotion => {
        if (promotion.active) {
          promotions.push(promotion);
        }
      });
      return promotions;
    },
    promotionPointsAmount() {
      let promotion = this.promotions.find(promotion => {
        return promotion.promotionType === "points";
      });
      if (promotion) {
        return (
          (promotion.promotionAmount / 100) *
          this.subtotal *
          100
        ).toFixed(0);
      }
      return 0;
    },
    promotionPointsName() {
      let promotion = this.promotions.find(promotion => {
        return promotion.promotionType === "points";
      });
      return promotion.pointsName;
    },
    bagURL() {
      let referralUrl = this.$route.query.r ? "?r=" + this.$route.query.r : "";
      return "/customer/bag" + referralUrl;
    },
    hasMultipleSubscriptionItems() {
      let subscriptionItemTypes = 0;
      if (this.hasWeeklySubscriptionItems) subscriptionItemTypes += 1;
      if (this.hasMonthlySubscriptionItems) subscriptionItemTypes += 1;
      if (this.hasPrepaidSubscriptionItems) subscriptionItemTypes += 1;
      if (subscriptionItemTypes > 1) return true;
    },
    deliveryValue() {
      if (
        this.store.settings.next_orderable_delivery_dates.length === 0 &&
        this.store.settings.next_orderable_pickup_dates.length > 0
      ) {
        return 0;
      } else {
        return 1;
      }
    },
    pickupValue() {
      if (
        this.store.settings.next_orderable_delivery_dates.length === 0 &&
        this.store.settings.next_orderable_pickup_dates.length > 0
      ) {
        return 1;
      } else {
        return 0;
      }
    },
    pickupLocationClass() {
      if (this.selectedPickupLocationAddress && this.selectedPickupLocation)
        return "checkout-item h-90";
      else return "checkout-item";
    },
    selectedPickupLocationAddress() {
      if (this.selectedPickupLocation) {
        let selectedLocation = _.find(this.pickupLocations, location => {
          return location.id === this.selectedPickupLocation;
        });
        let selectedLocationAddress =
          selectedLocation.address +
          ", " +
          selectedLocation.city +
          ", " +
          selectedLocation.state +
          ", " +
          selectedLocation.zip;
        return selectedLocationAddress;
      }
    },
    isMultipleDelivery() {
      return this.storeModules.multipleDeliveryDays == 1 ? true : false;
    },
    isManualOrder() {
      return this.$route.params.manualOrder;
    },
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.id;
              if (!groupedDD[key]) {
                groupedDD[key] = {
                  items: [],
                  delivery_day: bagItem.delivery_day
                };
              }

              groupedDD[key].items.push(bagItem);
            }
          });

          if (JSON.stringify(groupedDD) != "{}") {
            for (let i in groupedDD) {
              grouped.push(groupedDD[i]);
            }
          }

          grouped.sort((a, b) => {
            const ddA = a.delivery_day.day;
            const ddB = b.delivery_day.day;

            return moment(ddA).unix() - moment(ddB).unix();
          });
        } else {
          grouped.push({
            items: this.bag
          });
        }
      }
      return grouped;
    },
    stateNames() {
      return states.selectOptions("US");
    },
    currentBillingAddress() {
      if (this.form.billingAddress !== undefined) {
        return (
          this.form.address +
          ", " +
          this.form.city +
          ", " +
          this.form.state.value +
          ", " +
          this.form.zip
        );
      } else {
        let details = this.user.user_detail;
        return details.billingAddress
          ? details.billingAddress
          : details.address + ", " + details.billingCity
          ? details.billingCity
          : details.city + ", " + details.billingState
          ? details.billingState
          : details.state + ", " + details.billingZip
          ? details.billingZip
          : details.zip;
      }
    },
    addedBillingAddress() {
      return (
        this.billingAddressVerified ||
        (this.user.user_detail.billingAddress != null &&
          this.user.user_detail.billingAddress.length > 0)
      );
    },
    inSub() {
      return this.$route.params.inSub;
    },
    storeOwner() {
      let flag = false;
      if (this.user && this.user.storeOwner) {
        flag = true;
      }

      return flag;
    },
    storeSettings() {
      return this.store.settings;
    },
    couponFreeDelivery() {
      return this.coupon ? this.coupon.freeDelivery : 0;
    },
    couponApplied() {
      return !_.isNull(this.coupon);
    },
    purchasedGiftCardApplied() {
      return !_.isNull(this.purchasedGiftCard);
    },
    customers() {
      if (this.customerOptions.length > 0) {
        return this.customerOptions;
      }
      let customers = this.storeCustomers;
      if (_.isEmpty(customers)) {
        return [];
      }

      let grouped = {};
      customers.forEach(customer => {
        grouped[customer.user_id] = {
          value: customer.id,
          text: customer.name
        };
      });

      let sorted = Object.values(grouped).sort((a, b) =>
        a.text.localeCompare(b.text)
      );

      return sorted;
    },
    storeId() {
      return this.store.id;
    },
    storeWebsite() {
      if (!this.storeSettings.website) {
        return null;
      } else {
        let website = this.storeSettings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        return website;
      }
    },
    deliveryInstructions() {
      if (this.storeSettings.deliveryInstructions != null) {
        return this.storeSettings.deliveryInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.deliveryInstructions;
    },
    shippingInstructions() {
      if (this.storeSettings.shippingInstructions != null) {
        return this.storeSettings.shippingInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.shippingInstructions;
    },
    pickupInstructions() {
      if (this.selectedPickupLocation !== null) {
        return _.find(this.pickupLocationOptions, loc => {
          return loc.value === this.selectedPickupLocation;
        }).instructions;
      }
      if (this.storeSettings.pickupInstructions != null) {
        return this.storeSettings.pickupInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.pickupInstructions;
    },
    transferInstructions() {
      switch (this.selectedTransferType) {
        case "Pickup":
          return this.pickupInstructions;
          break;
        case "Delivery":
          return this.deliveryInstructions;
          break;
        case "Shipping":
          return this.shippingInstructions;
          break;
      }
    },
    pickupLocationOptions() {
      return this.pickupLocations.map(loc => {
        return {
          value: loc.id,
          text: loc.name,
          instructions: loc.instructions
        };
      });
    },
    cards() {
      if (this.$route.params.manualOrder) {
        return this.creditCardList;
      }
      if (this.creditCard != null) return [this.creditCard];
      else return this.creditCards;
    },
    card() {
      if (this.overrideCardId) {
        return this.overrideCardId;
      }

      if (this.$route.params.subscription) {
        return this.$route.params.subscription.card_id;
      }

      if (this.creditCardId !== null) {
        return this.creditCardId;
      }

      if (this.creditCards.length != 1) {
        return null;
      } else {
        return this.creditCards[0].id;
      }
    },
    transferTimeClass() {
      let day = this.holidayTransferTimes.find(day => {
        return day.holiday_date == this.bagDeliveryDate;
      });
      return this.storeModuleSettings.transferTimeRange ||
        (day && day.transferTimeRange)
        ? "delivery-select ml-2"
        : "custom-select ml-2";
    },
    transferTimeOptions() {
      let options = [];

      let day = this.holidayTransferTimes.find(day => {
        return day.holiday_date == this.bagDeliveryDate;
      });

      let pickupStartTime = day
        ? day.pickupStartTime
        : this.storeModuleSettings.pickupStartTime;
      let pickupEndTime = day
        ? day.pickupEndTime
        : this.storeModuleSettings.pickupEndTime;
      let deliveryStartTime = day
        ? day.deliveryStartTime
        : this.storeModuleSettings.deliveryStartTime;
      let deliveryEndTime = day
        ? day.deliveryEndTime
        : this.storeModuleSettings.deliveryEndTime;

      let start = this.pickup ? pickupStartTime : deliveryStartTime;
      let end = this.pickup ? pickupEndTime : deliveryEndTime;

      let interval = this.hourInterval
        ? 60
        : day
        ? day.transferTimeMinutesInterval
        : this.storeModuleSettings.transferTimeMinutesInterval;

      while (start < end) {
        options.push(start);
        start = moment(start, "HH:mm:ss")
          .add(interval, "minutes")
          .format("HH:mm A");
      }

      options.pop();

      if (
        !this.storeModuleSettings.transferTimeRange ||
        (day && !day.transferTimeRange)
      ) {
        options.pop();
      }

      options = options.map(option => {
        if (
          this.storeModuleSettings.transferTimeRange ||
          (day && day.transferTimeRange)
        ) {
          return (
            moment(option, "HH:mm:ss").format("h:mm A") +
            " - " +
            moment(option, "HH:mm:ss")
              .add(interval, "minutes")
              .format("h:mm A")
          );
        } else {
          return moment(option, "HH:mm:ss").format("h:mm A");
        }
      });

      return options;
    },
    // transferTimeOptions() {
    //   let startTime = null;
    //   let endTime = null;
    //   if (this.pickup === 1) {
    //     startTime = parseInt(
    //       this.storeModuleSettings.pickupStartTime.substr(0, 2)
    //     );
    //     endTime = parseInt(this.storeModuleSettings.pickupEndTime.substr(0, 2));
    //   } else {
    //     startTime = parseInt(
    //       this.storeModuleSettings.deliveryStartTime.substr(0, 2)
    //     );
    //     endTime = parseInt(
    //       this.storeModuleSettings.deliveryEndTime.substr(0, 2)
    //     );
    //   }

    //   let hourOptions = [];

    //   let omittedTransferTimes = this.storeModuleSettings.omittedTransferTimes;
    //   let selectedDeliveryDay = moment(this.deliveryDay).format("YYYY-MM-DD");
    //   let omit = [];

    //   omittedTransferTimes.forEach(dateTime => {
    //     let date = Object.keys(dateTime)[0];
    //     let time = Object.values(dateTime)[0];

    //     if (date === selectedDeliveryDay) {
    //       let hour = parseInt(time.substr(0, 1));
    //       if (hour < 12) hour += 12;
    //       omit.push(hour);
    //     }
    //   });

    //   while (startTime <= endTime) {
    //     if (!omit.includes(startTime)) {
    //       hourOptions.push(startTime);
    //     }
    //     startTime++;
    //   }

    //   let transferTimeRange = this.storeModuleSettings.transferTimeRange;
    //   let newHourOptions = [];

    //   hourOptions.forEach(option => {
    //     if (option < 12) {
    //       option = option.toString();
    //       let period = " AM";
    //       if (parseInt(option) === 11) {
    //         period = " PM";
    //       }
    //       let hour = 1;
    //       let newOption = option.concat(" AM");
    //       if (transferTimeRange) {
    //         newOption.concat(" - " + (parseInt(option) + hour) + period);
    //         let finalOption = newOption.concat(
    //           " - " + (parseInt(option) + hour) + period
    //         );
    //         newHourOptions.push(finalOption);
    //       } else newHourOptions.push(newOption);
    //     } else {
    //       if (option > 12) {
    //         option = option - 12;
    //       }
    //       let hour = 1;
    //       let period = " PM";
    //       if (parseInt(option) === 11) {
    //         period = " AM";
    //       }
    //       if (parseInt(option) === 12) {
    //         hour = -11;
    //       }
    //       option = option.toString();
    //       let newOption = option.concat(" PM");
    //       if (transferTimeRange) {
    //         newOption.concat(" - " + (parseInt(option) + hour) + period);
    //         let finalOption = newOption.concat(
    //           " - " + (parseInt(option) + hour) + period
    //         );
    //         newHourOptions.push(finalOption);
    //       } else newHourOptions.push(newOption);
    //     }
    //   });

    //   // Temporary fix for Livoti's to limit Christmas day hours until hour by pickup day feature is ready
    //   // if (
    //   //   this.bagDeliveryDate === "2019-12-24 00:00:00" &&
    //   //   (this.storeId === 108 || this.storeId === 109 || this.storeId === 110)
    //   // ) {
    //   //   for (let i = 0; i <= 3; i++) newHourOptions.pop();
    //   //   if (this.storeId === 110) {
    //   //     newHourOptions.unshift("8 AM - 9 AM");
    //   //     newHourOptions.unshift("7 AM - 8 AM");
    //   //   }
    //   // }

    //   // Livoti's Thanksgiving 2020
    //   if (
    //     this.bagDeliveryDate === "2020-11-25 00:00:00" &&
    //     (this.storeId === 108 || this.storeId === 109 || this.storeId === 110)
    //   ) {
    //     newHourOptions.pop();
    //     if (this.storeId === 110) {
    //       newHourOptions.unshift("8 AM - 9 AM");
    //     }
    //   }

    //   if (
    //     this.bagDeliveryDate === "2020-11-26 00:00:00" &&
    //     (this.storeId === 108 || this.storeId === 109 || this.storeId === 110)
    //   ) {
    //     for (let i = 0; i <= 5; i++) {
    //       newHourOptions.pop();
    //     }
    //     if (this.storeId === 110) {
    //       newHourOptions.unshift("8 AM - 9 AM");
    //     }
    //     newHourOptions.unshift("7 AM - 8 AM");
    //   }

    //   return newHourOptions;
    // },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheckDelivery() {
      if (_.includes(this.transferType, "delivery")) return true;
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferType, "pickup")) return true;
    },
    deliveryDateOptions() {
      let options = [];
      let dates = this.pickup
        ? this.storeSettings.next_orderable_pickup_dates
        : this.storeSettings.next_orderable_delivery_dates;
      let deliveryDays = this.store.delivery_days;

      // If no cutoff, add today's date
      if (
        this.storeSettings.cutoff_days === 0 &&
        this.storeSettings.cutoff_hours === 0 &&
        !this.weeklySubscriptionValue
      ) {
        dates = this.storeSettings.next_delivery_dates;
      }

      /*
use next_delivery_dates

      if(this.storeModules.customDeliveryDays) {
        return _(this.deliveryDays)
          .filter({ type: this.bagPickup ? 'pickup' : 'delivery'})
          .map(dday => {
            return {
              value:
            }
          })
          .value();
      }
*/

      if (
        this.storeModules.ignoreCutoff &&
        (this.$route.params.storeView || this.storeOwner)
      )
        dates = this.storeSettings.next_delivery_dates;

      if (this.storeModules.customDeliveryDays && this.pickup) {
        dates = _.filter(dates, date => {
          const deliveryDay = _.find(this.deliveryDays, {
            day: date.week_index.toString(),
            type: "pickup"
          });

          if (!deliveryDay) {
            return false;
          }

          // Change to check if store has delivery_day_pickup_locations instead of just checking for pickup locations
          if (
            this.pickupLocations.length > 0 &&
            deliveryDay.pickup_location_ids.length > 0
          ) {
            return deliveryDay.pickup_location_ids.includes(
              this.selectedPickupLocation
            );
          } else {
            return deliveryDay;
          }
        });
      }

      dates.forEach(date => {
        options.push({
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        });
      });

      // only one option. Set as bag date
      if (options.length === 1 && this.bagDeliveryDate !== options[0].value) {
        this.$nextTick(() => {
          this.setBagDeliveryDate(options[0].value);
        });
      }

      return options;
    },
    deliveryDateOptionsStoreView() {
      let options = [];
      let today = new Date();

      let year = today.getFullYear();
      let month = today.getMonth();
      let date = today.getDate();

      let start = this.backdate ? -60 : 0;

      for (start; start < 180; start++) {
        let day = new Date(year, month, date + start);
        options.push({
          value: moment(day).format("YYYY-MM-DD 00:00:00"),
          text: moment(day).format("dddd MMM Do")
        });
      }
      return options;
    },
    minimumOption() {
      return this.minOption;
    },
    minimumMeals() {
      return this.minMeals;
    },
    minimumPrice() {
      return this.minPrice;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    subtotal() {
      let totalLineItemsPrice = 0;
      if (this.orderLineItems != null && this.orderLineItems.length > 0) {
        this.orderLineItems.forEach(orderLineItem => {
          totalLineItemsPrice += orderLineItem.price * orderLineItem.quantity;
        });
      }
      let preFeeTotal =
        this.adjustingSubscription || this.adjustingOrder
          ? this.totalBagPricePreFeesBothTypes
          : this.totalBagPricePreFees;
      let subtotal = preFeeTotal + totalLineItemsPrice;

      return subtotal;
    },
    couponReduction() {
      if (!this.couponApplied) {
        return 0;
      }
      let coupon = this.coupon;
      let subtotal = this.subtotal;
      // if (coupon && coupon.fromSub) {
      //   return coupon.amount;
      // }
      if (coupon && coupon.type === "flat") {
        return coupon.amount < subtotal ? coupon.amount : subtotal;
      } else if (coupon && coupon.type === "percent") {
        return (coupon.amount / 100) * subtotal;
      }
    },
    afterCoupon() {
      let subtotal = this.subtotal;
      if (this.couponApplied) {
        subtotal -= this.couponReduction;
      }

      return subtotal;
    },
    mealPlanDiscount() {
      if (
        (this.subscriptionId ||
          this.weeklySubscription ||
          this.inSub ||
          this.adjustMealPlan ||
          this.$route.query.sub === "true" ||
          this.mealPlan ||
          this.applySubDiscountOnOrder) &&
        this.storeSettings.applyMealPlanDiscount
      ) {
        return this.subtotal * (this.storeSettings.mealPlanDiscount / 100);
      }
      return 0;
    },
    subscribeAndSaveAmount() {
      return this.subtotal * (this.storeSettings.mealPlanDiscount / 100);
    },
    removeDeliveryFee() {
      // Checks if the bag is empty or if the bag contains ONLY a gift card.
      if (this.bag.length === 0) {
        return true;
      }
      let remove = true;
      this.bag.forEach(item => {
        if (!item.meal.hasOwnProperty("gift_card")) {
          remove = false;
        }
      });
      return remove;
    },
    afterDiscount() {
      if (
        (this.applyMealPlanDiscount && this.weeklySubscription) ||
        this.inSub ||
        this.adjustMealPlan ||
        this.$route.query.sub === "true" ||
        this.mealPlan ||
        this.subscriptionId
      ) {
        return this.afterCoupon - this.mealPlanDiscount;
      } else return this.afterCoupon;
    },
    deliveryFeeAmount() {
      // Hard coding temporary solution for The Produce Box
      if (this.store.id === 172) {
        return this.storeSettings.deliveryFee * this.bag.length;
      }
      if (this.pickup === 0) {
        let {
          applyDeliveryFee,
          deliveryFee,
          deliveryFeeType,
          mileageBase,
          mileagePerMile
        } = this.bagDeliverySettings;

        if (this.deliveryFee) {
          return parseFloat(this.deliveryFee);
        }
        if (this.customDeliveryFee !== null) {
          return parseFloat(this.customDeliveryFee);
        }
        if (this.$route.params.adjustOrder) {
          return this.order.deliveryFee;
        }

        if (this.removeDeliveryFee) {
          return 0;
        }

        if (
          !this.couponFreeDelivery &&
          !this.promotionFreeDelivery &&
          this.pickup === 0
        ) {
          if (applyDeliveryFee) {
            let fee = 0;
            if (deliveryFeeType === "flat") {
              fee = deliveryFee;
            } else if (deliveryFeeType === "zip") {
              if (!this.loggedIn) {
                fee = deliveryFee;
              } else {
                let userZip = this.customerModel
                  ? this.customerModel.zip.replace(/\s/g, "")
                  : this.user.user_detail.zip.replace(/\s/g, "");
                let zip = this.deliveryFeeZipCodes.find(dfzc => {
                  dfzc.zip_code = dfzc.zip_code.replace(/\s/g, "");
                  return dfzc.zip_code === userZip;
                });
                fee = zip ? zip.delivery_fee : deliveryFee;
              }
            } else if (deliveryFeeType === "mileage") {
              let distance = this.store.distance
                ? parseFloat(this.store.distance)
                : 0;
              fee =
                parseFloat(mileageBase) + parseFloat(mileagePerMile) * distance;
            } else if (deliveryFeeType === "range") {
              if (!this.loggedIn) {
                fee =
                  this.store.delivery_fee_ranges.length > 0
                    ? this.store.delivery_fee_ranges[0].price
                    : deliveryFee;
              } else {
                let distance = this.store.distance
                  ? Math.ceil(parseFloat(this.store.distance))
                  : 0;
                let range = this.store.delivery_fee_ranges.find(range => {
                  return (
                    distance >= range.starting_miles &&
                    distance < range.ending_miles
                  );
                });

                fee = range ? parseFloat(range.price) : parseFloat(deliveryFee);
              }
            }

            if (
              this.storeModules.multipleDeliveryDays &&
              deliveryFeeType !== "range" &&
              deliveryFeeType !== "zip"
            ) {
              let mddFee = 0;
              this.groupBag.forEach(item => {
                if (item.delivery_day && item.items && item.items.length > 0) {
                  if (item.delivery_day.feeType == "flat") {
                    mddFee += parseFloat(item.delivery_day.fee);
                  }
                  if (item.delivery_day.feeType == "mileage") {
                    let distance = this.store.distance
                      ? parseFloat(this.store.distance)
                      : 0;
                    mddFee +=
                      parseFloat(item.delivery_day.mileageBase) +
                      parseFloat(item.delivery_day.mileagePerMile) * distance;
                  }
                }
              });
              return mddFee;
            }
            fee = fee * this.groupBag.length;

            if (this.storeModules.frequencyItems) {
              if (this.bagHasOnlySubItems && !this.frequencyType) {
                return 0;
              }
              if (this.bagHasOnlyOrderItems && this.frequencyType === "sub") {
                return 0;
              }
            }

            return fee;
          } else return 0;
        } else return 0;
      } else return 0;
    },
    processingFeeAmount() {
      if (this.cashOrder || this.totalBagPricePreFees == 0) {
        return 0;
      }
      if (this.storeSettings.applyProcessingFee) {
        if (this.storeSettings.processingFeeType === "flat") {
          return this.storeSettings.processingFee;
        } else if (this.storeSettings.processingFeeType === "percent") {
          return (this.storeSettings.processingFee / 100) * this.afterDiscount;
        }
      }
    },
    afterFees() {
      let {
        applyDeliveryFee,
        deliveryFee,
        deliveryFeeType,
        mileageBase,
        mileagePerMile
      } = this.bagDeliverySettings;

      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let processingFee = this.processingFeeAmount;
      let subtotal = this.afterDiscount;

      if (applyDeliveryFee && this.pickup === 0 && this.deliveryFeeAmount) {
        subtotal += this.deliveryFeeAmount;
      }
      if (applyProcessingFee) {
        subtotal += processingFee;
      }

      return subtotal;
    },
    afterFeesAndTax() {
      return this.afterFees + this.tax;
    },
    purchasedGiftCardReduction() {
      if (
        this.$route.params.adjustOrder &&
        this.order.purchasedGiftCardReduction > 0
      ) {
        return this.order.purchasedGiftCardReduction;
      }
      if (!this.purchasedGiftCardApplied) {
        return 0;
      }

      let grandTotal =
        this.afterFeesAndTax -
        this.totalNonGiftCardDiscountReduction +
        this.coolerDeposit +
        this.tip;

      if (parseFloat(this.purchasedGiftCard.balance) > grandTotal) {
        return grandTotal;
      } else {
        return parseFloat(this.purchasedGiftCard.balance);
      }
    },
    referralReduction() {
      if (!this.referral) {
        return 0;
      }
      if (this.$route.params.adjustOrder) {
        return this.order.referralReduction;
      }

      return parseFloat(this.referral.balance);
    },
    promotionReduction() {
      if (this.removePromotions) {
        return 0;
      }
      let promotions = this.promotions;
      let condSubtotalReduction = 0;
      let condMealsReduction = 0;
      let condOrdersReduction = 0;
      let condNoneReduction = 0;

      promotions.forEach(promotion => {
        let promotionReduction =
          promotion.promotionType === "flat"
            ? promotion.promotionAmount
            : (promotion.promotionAmount / 100) * this.subtotal;
        if (promotion.active) {
          if (
            promotion.conditionType === "subtotal" &&
            this.subtotal >= promotion.conditionAmount
          ) {
            condSubtotalReduction +=
              condSubtotalReduction < promotionReduction
                ? -condSubtotalReduction + promotionReduction
                : promotionReduction;
          }
          if (
            promotion.conditionType === "meals" &&
            this.totalBagQuantity >= promotion.conditionAmount
          ) {
            condMealsReduction +=
              condMealsReduction < promotionReduction
                ? -condMealsReduction + promotionReduction
                : promotionReduction;
          }
          if (
            promotion.conditionType === "orders" &&
            this.getRemainingPromotionOrders(promotion) === 0
          ) {
            condOrdersReduction +=
              condOrdersReduction < promotionReduction
                ? -condOrdersReduction + promotionReduction
                : promotionReduction;
          }
          if (promotion.conditionType === "none") {
            condNoneReduction +=
              condNoneReduction < promotionReduction
                ? -condNoneReduction + promotionReduction
                : promotionReduction;
          }
        }
      });
      return (
        condSubtotalReduction +
        condMealsReduction +
        condOrdersReduction +
        condNoneReduction
      );
    },
    promotionPointsReduction() {
      if (
        this.removePromotions ||
        !this.usePromotionPoints ||
        this.afterFeesAndTax <= 0
      ) {
        return 0;
      }
      return this.pointsReduction;
    },
    tip() {
      let customGratuity =
        this.bagCustomGratuity !== null ? this.bagCustomGratuity : 0;
      let tip =
        this.bagGratuityPercent && this.bagGratuityPercent === "custom"
          ? parseFloat(customGratuity)
          : parseFloat(
              (this.bagGratuityPercent / 100) * this.totalBagPricePreFees
            );
      return tip;
    },
    coolerDeposit() {
      if (this.bagPickup == 1) {
        return 0;
      }
      if (
        this.$route.params.adjustOrder &&
        !this.coolerDepositChanged &&
        this.order.coolerDeposit
      ) {
        return parseFloat(this.order.coolerDeposit);
      }
      if (
        this.$route.params.adjustMealPlan &&
        !this.coolerDepositChanged &&
        this.$route.params.subscription.coolerDeposit
      ) {
        return parseFloat(this.$route.params.subscription.coolerDeposit);
      }
      if (this.storeModules.cooler) {
        if (this.includeCooler) {
          return this.storeModuleSettings.coolerDeposit;
        } else {
          return 0;
        }
      } else {
        return 0;
      }
      return 0;
    },
    grandTotal() {
      let total =
        this.afterFeesAndTax -
        this.totalDiscountReduction +
        this.coolerDeposit +
        this.tip;
      if (total < 0) {
        total = 0;
      }
      // Update this in the future. Separate bag subscription interval from prepaid subscriptions
      if (this.prepaid) {
        return total * this.store.settings.prepaidWeeks;
      }
      return total;
    },
    totalNonGiftCardDiscountReduction() {
      return (
        this.referralReduction +
        this.promotionReduction +
        this.promotionPointsReduction
      );
    },
    totalDiscountReduction() {
      return (
        this.purchasedGiftCardReduction +
        this.referralReduction +
        this.promotionReduction +
        this.promotionPointsReduction
      );
    },
    promotionFreeDelivery() {
      let promotions = this.promotions;
      let freeDelivery = false;

      promotions.forEach(promotion => {
        if (promotion.active) {
          if (
            promotion.conditionType === "subtotal" &&
            this.subtotal >= promotion.conditionAmount &&
            promotion.freeDelivery
          ) {
            freeDelivery = true;
          }
          if (
            promotion.conditionType === "meals" &&
            this.totalBagQuantity >= promotion.conditionAmount &&
            promotion.freeDelivery
          ) {
            freeDelivery = true;
          }
          if (
            promotion.conditionType === "orders" &&
            this.user.orderCount % promotion.conditionAmount === 0 &&
            promotion.freeDelivery
          ) {
            freeDelivery = true;
          }
          if (promotion.conditionType === "none" && promotion.freeDelivery) {
            freeDelivery = true;
          }
        }
      });
      return freeDelivery;
    },
    totalBagQuantity() {
      let quantity = 0;
      this.bag.forEach(item => {
        if (item.meal_package) {
          quantity += this.getItemMeals(item);
        } else {
          quantity += item.quantity;
        }
      });
      return quantity;
    },
    // Prevents the box from showing if a customer has a gift card in their bag.

    // hasCoupons() {
    //   let bagHasGiftCard = false;
    //   this.bag.forEach(item => {
    //     if (item.meal.gift_card) {
    //       bagHasGiftCard = true;
    //     }
    //   });
    //   if (this.store.hasPromoCodes && !bagHasGiftCard) {
    //     return true;
    //   } else {
    //     return false;
    //   }
    // },
    applyMealPlanDiscount() {
      return this.storeSettings.applyMealPlanDiscount;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "items";
      }
      return "item";
    },
    singOrPluralTotal() {
      if (this.total > 1) {
        return "Items";
      }
      return "item";
    },
    weeklySubscription() {
      if (this.hasSubscriptionOnlyItems) {
        return true;
      }
      if (this.store.modules.subscriptionOnly) {
        return true;
      }
      if (
        this.checkoutData &&
        this.checkoutData.hasOwnProperty("weeklySubscriptionValue")
      ) {
        return this.checkoutData.weeklySubscriptionValue;
      }

      if (this.$route.params.subscriptionId != null) {
        return true;
      } else {
        return this.deliveryPlan;
      }
    },
    deliveryPlanText() {
      if (this.weeklySubscription) return "Prepared Weekly";
      else return "Prepared Once";
    },
    tax() {
      if (this.customSalesTax !== null && this.customSalesTax !== "") {
        return parseFloat(this.customSalesTax);
      }
      if (this.$route.params.adjustOrder && this.order.customSalesTax) {
        return parseFloat(this.order.salesTax);
      }
      // Custom Sales Tax Per Meal
      let removableItemAmount = 0;
      let customSalesTaxAmount = 0;
      this.bag.forEach(item => {
        if (!item.meal.gift_card === true && !item.free) {
          // Remove the meal from the total amount of the bag, and then add it back in using its custom sales tax rate.
          if (!item.meal_package) {
            if (item.meal.salesTax !== null) {
              removableItemAmount += item.price * item.quantity;
              customSalesTaxAmount +=
                item.price * item.quantity * item.meal.salesTax;
            }
            if (
              item.size !== null &&
              item.size.salesTax !== null &&
              item.size.salesTax !== undefined
            ) {
              removableItemAmount += item.size.price * item.quantity;
              customSalesTaxAmount += item.quantity * item.size.salesTax;
            }
          } else {
            if (item.meal && item.meal.meals) {
              item.meal.meals.forEach(meal => {
                if (meal.salesTax !== null) {
                  removableItemAmount += meal.price * meal.quantity;
                  customSalesTaxAmount +=
                    meal.price * meal.quantity * meal.salesTax;
                }
              });
            }
            if (item.meal && item.meal.sizes) {
              if (item.meal.sizes.length > 0) {
                item.meal.sizes.forEach(size => {
                  if (size.meals) {
                    size.meals.forEach(meal => {
                      if (meal.salesTax !== null) {
                        removableItemAmount += meal.price * meal.quantity;
                        customSalesTaxAmount +=
                          meal.price * meal.quantity * meal.salesTax;
                      }
                    });
                  }
                });
              }
            }

            if (item.addons !== null) {
              if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addonItem => {
                  if (addonItem.meal.salesTax !== null) {
                    removableItemAmount += addonItem.price * addonItem.quantity;
                    customSalesTaxAmount +=
                      addonItem.price *
                      addonItem.quantity *
                      addonItem.meal.salesTax;
                  }
                });
              }
            }
            if (item.components !== null) {
              if (Object.entries(item.components).length > 0) {
                Object.values(item.components).forEach(component => {
                  Object.values(component).forEach(componentOption => {
                    if (componentOption.length > 0) {
                      if (componentOption[0].meal.salesTax !== null) {
                        removableItemAmount += componentOption[0].price;
                        customSalesTaxAmount +=
                          componentOption[0].price *
                          componentOption[0].quantity *
                          componentOption[0].meal.salesTax;
                      }
                    }
                  });
                });
              }
            }
          }
        }
        if (item.meal.gift_card) {
          removableItemAmount += item.price * item.quantity;
        }
      });

      let taxableAmount = 0;
      if (!this.storeSettings.salesTaxAfterFees) {
        taxableAmount =
          this.afterDiscount - removableItemAmount + customSalesTaxAmount;
      } else {
        taxableAmount =
          this.afterFees - removableItemAmount + customSalesTaxAmount;
      }

      if (taxableAmount < 0) {
        taxableAmount = 0;
      }
      if (
        this.storeSettings.enableSalesTax === 0 ||
        this.storeSettings.enableSalesTax === false
      ) {
        return 0;
      }
      if (this.storeSettings.salesTax > 0) {
        return parseFloat(
          ((this.storeSettings.salesTax / 100) * taxableAmount).toFixed(2)
        );
      } else {
        return parseFloat((this.salesTax * taxableAmount).toFixed(2));
      }
    },
    subscriptionId() {
      if (this.$route.query.subscriptionId) {
        return this.$route.query.subscriptionId;
      }
      return this.$route.params.subscriptionId;
    },
    hidePaymentArea() {
      let params = this.$route.params;
      if (
        params.preview != null ||
        params.adjustOrder ||
        (this.context == "store" &&
          !this.adjusting &&
          this.customerModel == null)
      )
        return true;
      else return false;
    },
    showPaymentMethod() {
      if (
        (this.loggedIn && this.grandTotal > 0) ||
        (this.loggedIn && this.grandTotal == 0 && this.weeklySubscriptionValue)
      ) {
        return true;
      } else {
        return false;
      }
    },
    hasActiveSubscription() {
      let hasActiveSub = false;
      if (this.subscriptions) {
        this.subscriptions.forEach(subscription => {
          if (
            subscription.store_id === this.store.id &&
            subscription.status === "active"
          )
            hasActiveSub = true;
        });
      }
      return hasActiveSub;
    },
    customerEmail() {
      return "";
      let customerEmail = "";
      let customers = this.storeCustomers;
      if (customers) {
      }
      customers.forEach(customer => {
        if (customer.id === this.customer) {
          customerEmail = customer.email;
          return;
        }
      });
      return customerEmail;
    },
    invalidCheckout() {
      if (
        this.context !== "store" &&
        !this.willDeliver &&
        this.bagPickup === 0
      ) {
        return (
          "You are outside of the " +
          this.selectedTransferType.toLowerCase() +
          " area."
        );
      }
      if (this.context !== "store" && !this.minimumMet) {
        return this.addMore;
      }
      if (
        this.bagPickup === 1 &&
        this.store.modules.pickupLocations &&
        this.pickupLocationOptions.length > 0 &&
        !this.selectedPickupLocation
      ) {
        return "Please select a pickup location from the dropdown.";
      }
      if (
        !this.isMultipleDelivery &&
        !this.store.modules.hideTransferOptions &&
        !this.adjustingSubscription
      ) {
        if (!this.bagDeliveryDate) {
          if (this.bagPickup === 1) {
            return "Please select a pickup date from the dropdown.";
          }
          if (this.bagPickup === 0) {
            return "Please select a delivery date from the dropdown.";
          }
        }
      }
      if (
        !this.adjustingSubscription &&
        this.store.modules.pickupHours &&
        this.bagPickup === 1 &&
        this.transferTime === null
      ) {
        return "Please select a pickup time from the dropdown.";
      }

      if (
        !this.adjustingSubscription &&
        this.store.modules.deliveryHours &&
        this.bagPickup === 0 &&
        this.transferTime === null
      ) {
        return "Please select a delivery time from the dropdown.";
      }

      if (
        this.staffMember == null &&
        this.store.modules.showStaff &&
        this.$route.params.manualOrder
      ) {
        return "Please select a staff member.";
      }

      if (
        this.context === "store" &&
        this.customerModel === null &&
        this.$route.params.manualOrder
      ) {
        return "Please choose a customer.";
      }

      if (this.showPaymentMethod && !this.card && !this.cashOrder) {
        return "Please enter a payment method.";
      }

      if (
        this.loggedIn &&
        this.weeklySubscriptionValue &&
        this.bagSubscriptionInterval === "select" &&
        !this.adjustingSubscription
      ) {
        return "Please select the order frequency of your subscription.";
      }

      if (
        (this.weeklySubscriptionValue || this.adjustingSubscription) &&
        this.bag.some(item => {
          return item.meal.gift_card === true;
        })
      ) {
        return "Gift cards are not allowed on subscriptions. Please remove the gift card.";
      }

      if (this.hasMultipleSubscriptionItems) {
        return "You have multiple subscription types in your bag (e.g weekly & monthly). Please checkout one subscription type at a time.";
      }

      return null;
    }
  },
  created() {},
  methods: {
    ...mapActions([
      "refreshSubscriptions",
      "refreshStoreSubscriptions",
      "refreshCustomerOrders",
      "refreshOrders",
      "refreshOrdersToday",
      "refreshStoreSubscriptions",
      "refreshUpcomingOrders",
      "refreshUpcomingOrdersWithoutItems",
      "refreshStoreCustomers",
      "refreshStorePurchasedGiftCards",
      "refreshCards",
      "refreshStoreCustomers"
    ]),
    ...mapActions("resources", ["refreshResource"]),
    ...mapMutations([
      "emptyBag",
      "setBagMealPlan",
      "setBagCoupon",
      "setBagPurchasedGiftCard",
      "setBagReferral",
      "setBagDeliveryDate",
      "setBagStaffMember",
      "clearBagDeliveryDate",
      "clearBagTransferTime",
      "clearBagStaffMember",
      "clearBagCustomerModel",
      "clearBagDeliveryFee",
      "setBagPickup",
      "setBagTransferTime",
      "setBagCustomerModel",
      "setBagDeliveryFee",
      "setBagFrequencyType",
      "setBagPickupLocation",
      "setBagGratuityPercent",
      "setBagCustomGratuity",
      "setBagSubscriptionInterval",
      "setBagNotes",
      "setBagPublicNotes"
    ]),
    setOrderFrequency() {
      // this.setBagSubscriptionInterval(null)
      let week = this.storeSettings.allowWeeklySubscriptions;
      let biweek = this.storeSettings.allowBiWeeklySubscriptions;
      let month = this.storeSettings.allowMonthlySubscriptions;

      if (!this.storeSettings.prepaidSubscriptions) {
        if (week && !biweek && !month) {
          this.setBagSubscriptionInterval("week");
          return;
        }
        if (!week && biweek && !month) {
          this.setBagSubscriptionInterval("biweek");
          return;
        }
        if (!week && !biweek && month) {
          this.setBagSubscriptionInterval("month");
          return;
        }
        if (this.bagSubscriptionInterval == null) {
          this.setBagSubscriptionInterval("select");
        }
      } else {
        if (!week) {
          this.setBagSubscriptionInterval("prepaid");
          return;
        }
      }
    },
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    getCustomerObject(id) {
      return _.find(this.customers, ["value", id]);
    },
    async applyCoupon() {
      let coupon = {};
      await axios
        .post(this.prefix + "findCoupon", {
          store_id: this.store.id,
          couponCode: this.couponCode
        })
        .then(resp => {
          coupon = resp.data;
        });

      if (
        coupon &&
        this.couponCode.toUpperCase() === coupon.code.toUpperCase()
      ) {
        if (coupon.oneTime) {
          let oneTimePass = this.oneTimeCouponCheck(coupon.id);
          if (oneTimePass === "login") {
            this.$toastr.w(
              "This is a one-time coupon. Please log in or create an account to check if it has already been used."
            );
            return;
          }
          if (!oneTimePass) {
            this.$toastr.w(
              "This was a one-time coupon that has already been used.",
              'Coupon Code: "' + this.couponCode + '"'
            );
            this.couponCode = "";
            return;
          }
        }
      }
    },
    async applyDiscountCode() {
      let checkNext = true;
      if (
        this.bag.some(item => {
          return item.meal.gift_card === true;
        })
      ) {
        this.$toastr.w(
          "Promo codes are disabled when purchasing gift cards. Please order the gift card separately."
        );
        return;
      }
      if (this.discountCode === "sub") {
        this.applySubDiscountOnOrder = true;
        this.discountCode = "";
        this.$toastr.s("Subscription discount added.");
        return;
      }
      this.lastUsedDiscountCode = this.discountCode;
      let coupon = null;
      await axios
        .post(this.prefix + "findCoupon", {
          store_id: this.store.id,
          couponCode: this.discountCode,
          user_id: this.user.id
        })
        .then(resp => {
          coupon = resp.data;
          if (
            coupon &&
            this.discountCode.toUpperCase() === coupon.code.toUpperCase()
          ) {
            if (
              coupon.minimum > 0 &&
              coupon.minimum >= this.totalBagPricePreFees
            ) {
              this.$toastr.w(
                "This coupon requires a minimum amount of " +
                  this.store.settings.currency_symbol +
                  coupon.minimum +
                  " to be used."
              );
              return;
            }
            if (coupon.oneTime) {
              let oneTimePass = this.oneTimeCouponCheck(coupon.id);
              if (oneTimePass === "login") {
                this.$toastr.w(
                  "This is a one-time coupon. Please log in or create an account to check if it has already been used."
                );
                return;
              }
              if (!oneTimePass) {
                this.$toastr.w(
                  "This was a one-time coupon that has already been used.",
                  'Coupon Code: "' + this.discountCode + '"'
                );
                this.discountCode = "";
                return;
              }
            }
            this.coupon = coupon;
            this.setBagCoupon(coupon);
            this.discountCode = "";
            this.$toastr.s("Coupon Applied.", "Success");
            return;
          }
        })
        .catch(e => {
          checkNext = false;
          this.$toastr.w(e.response.data);
          return;
        });

      if (!checkNext || coupon) {
        return;
      }

      // if (this.weeklySubscriptionValue) {
      //   this.$toastr.w("Gift cards are allowed on one time orders only.");
      //   return;
      // }

      let purchasedGiftCard = {};
      await axios
        .post(this.prefix + "findPurchasedGiftCard", {
          store_id: this.store.id,
          purchasedGiftCardCode: this.discountCode
        })
        .then(resp => {
          purchasedGiftCard = resp.data;
        });

      if (
        purchasedGiftCard &&
        this.discountCode.toUpperCase() === purchasedGiftCard.code.toUpperCase()
      ) {
        if (purchasedGiftCard.balance === "0.00") {
          this.$toastr.w("There are no more funds left on this gift card.");
          return;
        }
        if (this.grandTotal === 0) {
          this.$toastr.w(
            "The total price of this order is 0. It can't be discounted any more."
          );
          return;
        }

        this.purchasedGiftCard = purchasedGiftCard;

        this.setBagPurchasedGiftCard(purchasedGiftCard);
        this.discountCode = "";
        this.$toastr.s("Gift Card Applied.", "Success");
        return;
      }
      // this.discountCode = "";
      // this.$toastr.w("Promo code not found.");

      let referral = {};
      await axios
        .post(this.prefix + "findReferralCode", {
          store_id: this.store.id,
          referralCode: this.discountCode
        })
        .then(resp => {
          referral = resp.data;
        });

      if (
        referral &&
        this.discountCode.toUpperCase() === referral.code.toUpperCase()
      ) {
        if (referral.balance === "0.00") {
          this.$toastr.w("There are no more funds left on your referral code.");
          return;
        }
        if (this.grandTotal === 0) {
          this.$toastr.w(
            "The total price of this order is 0. It can't be discounted any more."
          );
          return;
        }
        if (referral.balance > this.grandTotal) {
          referral.balance = this.grandTotal;
        }

        this.referral = referral;

        this.setBagReferral(referral);
        this.discountCode = "";
        this.$toastr.s("Referral Code Applied.", "Success");
        return;
      }
      this.discountCode = "";
      this.$toastr.w("Promo code not found.");
    },
    oneTimeCouponCheck(couponId) {
      if (this.$route.params.storeView || this.storeOwner) {
        return true;
      }
      if (!this.loggedIn) {
        return "login";
      }
      let couponCheck = true;
      this._orders.forEach(order => {
        if (couponId === order.coupon_id) {
          couponCheck = false;
        }
      });
      return couponCheck;
    },
    changeDeliveryDay(val) {
      this.setBagDeliveryDate(val);
      this.updateParentData();
    },
    changeDeliveryTime(val) {
      this.setBagTransferTime(val);
      this.updateParentData();
    },
    changePickup(val) {
      this.setBagPickup(val);
      this.updateParentData();
    },
    changeStaff(val) {
      this.setBagStaffMember(val);
      this.updateParentData();
    },
    changeCustomer(val) {
      this.setBagCustomerModel(val);
      this.updateParentData();
      if (this.deliveryShipping == "Shipping") {
        this.setBagPickup(0);
      }
    },
    setWeeklySubscriptionValue(v) {
      this.weeklySubscriptionValue = v;
    },
    updateParentData() {
      this.$emit("updateData", {
        customer: this.customerModel ? this.customerModel.value : this.customer,
        weeklySubscriptionValue: this.weeklySubscriptionValue,
        pickup: this.pickup,
        transferTime: this.transferTime,
        deliveryDay: this.deliveryDay,
        cashOrder: this.cashOrder,
        creditCardList: this.creditCardList,
        lineItemOrders: this.orderLineItems
      });
    },
    getCards() {
      if (this.store.modules.cashOrderAutoSelect) {
        return;
      }
      if (this.context !== "store") {
        this.refreshCards();
      } else {
        this.$nextTick(() => {
          axios
            .post("/api/me/getCards", { id: this.customer })
            .then(response => {
              this.$parent.creditCardList = response.data;
              if (response.data.length) {
                this.creditCardId = response.data[0].id;
                this.creditCard = response.data[0];
                if (this.$refs.cardPicker) {
                  this.$refs.cardPicker.setCard(response.data[0].id);
                }
              }
            });
        });
      }

      this.updateParentData();
      window.localStorage.clear();
    },
    getCustomer() {
      if (this.customerModel) {
        return this.customerModel.value;
      }
      if (this.customer) {
        return this.customer;
      }
      if (this.$route.params.subscription) {
        return this.$route.params.subscription.customer_id;
      }
    },
    async adjust() {
      if (!this.isMultipleDelivery) {
        if (this.bagDeliveryDate === null) {
          this.$toastr.w("Please select a delivery/pickup date.");
          return;
        }
      }
      if (
        this.pickup === 1 &&
        this.store.modules.pickupLocations &&
        this.pickupLocationOptions.length > 0 &&
        !this.selectedPickupLocation
      ) {
        this.$toastr.w("Please select a pickup location from the dropdown.");
        return;
      }
      let deposit = this.deposit;
      if (deposit !== null && deposit.toString().includes("%")) {
        deposit.replace("%", "");
        deposit = parseInt(deposit);
      }

      let weeklySubscriptionValue = this.storeSettings.allowWeeklySubscriptions
        ? this.weeklySubscriptionValue
        : 0;

      axios
        .post(`/api/me/orders/adjustOrder`, {
          notes: this.bagNotes,
          publicNotes: this.bagPublicNotes,
          orderId: this.$parent.orderId,
          deliveryDate: this.deliveryDay,
          isMultipleDelivery: this.isMultipleDelivery,
          pickup: this.pickup,
          shipping: this.selectedTransferType == "Shipping" ? 1 : 0,
          transferTime: this.transferTime,
          subtotal: this.subtotal,
          mealPlanDiscount: this.mealPlanDiscount,
          afterDiscount: this.afterDiscount,
          deliveryFee: this.deliveryFeeAmount,
          processingFee: this.processingFeeAmount,
          bag: this.bag,
          plan: weeklySubscriptionValue,
          store_id: this.store.id,
          salesTax: this.tax,
          coupon_id: this.couponApplied ? this.coupon.id : null,
          couponReduction: this.couponReduction,
          couponCode: this.couponApplied ? this.coupon.code : null,
          purchased_gift_card_id: this.purchasedGiftCardApplied
            ? this.purchasedGiftCard.id
            : null,
          purchasedGiftCardReduction: this.purchasedGiftCardReduction,
          applied_referral_id: this.referral ? this.referral.id : null,
          referralReduction: this.referralReduction,
          promotionReduction: this.promotionReduction,
          pickupLocation: this.selectedPickupLocation,
          customer: this.customerModel
            ? this.customerModel.value
            : this.customer,
          deposit: deposit,
          cashOrder: this.cashOrder,
          lineItemsOrder: this.orderLineItems,
          gratuity: this.tip,
          grandTotal: this.grandTotal,
          emailCustomer: this.emailCustomer,
          customSalesTax: this.customSalesTax !== null ? 1 : 0,
          dontAffectBalance: this.dontAffectBalance,
          hot: this.hot ? this.hot : 0,
          pointsReduction: this.promotionPointsReduction,
          coolerDeposit: this.coolerDeposit
        })
        .then(resp => {
          if (this.purchasedGiftCard !== null) {
            this.purchasedGiftCard.balance -= this.purchasedGiftCardReduction;
          }
          this.$toastr.s("Order Adjusted");
          this.$router.push({
            name: "store-orders",
            params: {
              autoPrintPackingSlip: this.storeModules.autoPrintPackingSlip,
              orderId: resp.data
            }
          });
          this.setBagMealPlan(false);
          this.setBagCoupon(null);
          this.setBagPurchasedGiftCard(null);
          this.setBagReferral(null);
          this.setBagNotes(null);
          this.setBagPublicNotes(null);
          this.emptyBag();
          this.refreshResource("orders");
          this.refreshUpcomingOrders();
          this.refreshUpcomingOrdersWithoutItems();
          this.clearBagDeliveryDate();
          this.clearBagTransferTime();
          this.clearBagStaffMember();
          this.clearBagCustomerModel();
          this.refreshStorePurchasedGiftCards();
          this.clearBagDeliveryFee();
          // Always select pickup for Livotis
          if (
            this.store.id === 108 ||
            this.store.id === 109 ||
            this.store.id === 110 ||
            this.store.id === 278
          ) {
            this.setBagPickup(1);
          }
        })
        .catch(async response => {
          let error = response.response.data.message;
          this.checkingOut = false;
          this.$toastr.w(error);
        });
    },
    mounted() {
      this.creditCardId = this.card;

      if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

      this.selectedPickupLocation = this.pickupLocationOptions[0].value;
    },
    checkout() {
      if (this.checkingOut) {
        return;
      }

      if (this.showCheckoutErrorToasts()) {
        return;
      }

      this.checkingOut = true;
      this.subscriptionItemsCheck();

      if (this.bagDeliveryDate && !this.deliveryDay) {
        this.deliveryDay = this.bagDeliveryDate.value
          ? this.bagDeliveryDate.value
          : this.bagDeliveryDate;
      }

      // Filter the bag for one time order items & subscription items in frequencyType stores.
      let bag = this.bag;
      if (this.storeModules.frequencyItems) {
        if (this.bagHasMultipleFrequencyItems) {
          this.setFrequencySubscription(null);
          if (!this.doubleCheckout) {
            bag = this.bag.filter(item => {
              return item.meal.frequencyType !== "sub";
            });
          } else {
            this.setFrequencySubscription("sub");
            bag = this.bag.filter(item => {
              return item.meal.frequencyType === "sub";
            });
          }
        }
      }

      let endPoint = this.$route.params.manualOrder
        ? "/api/me/checkout"
        : "/api/bag/checkout";

      axios
        .post(endPoint, {
          notes: this.bagNotes,
          publicOrderNotes: this.bagPublicNotes,
          subtotal: this.subtotal,
          mealPlanDiscount: this.mealPlanDiscount,
          afterDiscount: this.afterDiscount,
          bag: bag,
          plan: this.weeklySubscriptionValue ? this.weeklySubscriptionValue : 0,
          plan_interval: this.bagSubscriptionInterval,
          prepaid: this.prepaid,
          pickup: this.pickup,
          shipping: this.selectedTransferType == "Shipping" ? 1 : 0,
          isMultipleDelivery: this.isMultipleDelivery,
          delivery_day: this.deliveryDay
            ? this.deliveryDay
            : this.deliveryDateOptions[0].value,
          card_id: !this.cashOrder && this.grandTotal > 0 ? this.card : 0,
          store_id: this.store.id,
          salesTax: this.tax,
          customSalesTax: this.customSalesTax !== null ? 1 : 0,
          couponCode: this.couponApplied ? this.coupon.code : null,
          coupon_id: this.couponApplied ? this.coupon.id : null,
          couponReduction: this.couponReduction,
          purchased_gift_card_id: this.purchasedGiftCardApplied
            ? this.purchasedGiftCard.id
            : null,
          purchasedGiftCardReduction: this.purchasedGiftCardReduction,
          applied_referral_id: this.referral ? this.referral.id : null,
          referralReduction: this.referralReduction,
          promotionReduction: this.promotionReduction,
          deliveryFee: this.deliveryFeeAmount,
          processingFee: this.processingFeeAmount,
          pickupLocation:
            this.pickup === 1 ? this.selectedPickupLocation : null,
          customer: this.customerModel
            ? this.customerModel.value
            : this.customer,
          deposit: this.deposit,
          cashOrder: this.cashOrder ? this.cashOrder : 0,
          noBalance: this.noBalance,
          hot: this.hot ? this.hot : false,
          transferTime: this.transferTime,
          lineItemsOrder: this.orderLineItems,
          gratuity: this.tip,
          grandTotal: this.grandTotal,
          emailCustomer: this.emailCustomer,
          referralUrl: this.$route.query.r,
          promotionPointsAmount: this.promotionPointsAmount,
          pointsReduction: this.promotionPointsReduction,
          staff: this.staffMember,
          coolerDeposit: this.coolerDeposit,
          distance: this.distance
        })
        .then(async resp => {
          // Check if the order contains subscription & order items in frequencyTypes stores and if so, checkout again (first order then subscription)

          if (
            this.storeModules.frequencyItems &&
            this.bagHasMultipleFrequencyItems &&
            !this.doubleCheckout
          ) {
            this.doubleCheckout = true;
            this.checkingOut = false;
            this.checkout();
          } else {
            // See if this is needed

            if (this.purchasedGiftCard !== null) {
              this.purchasedGiftCard.balance -= this.purchasedGiftCardReduction;
            }

            // Clear everything

            this.setBagMealPlan(false);
            this.setBagCoupon(null);
            this.setBagPurchasedGiftCard(null);
            this.setBagReferral(null);
            this.setBagNotes(null);
            this.setBagPublicNotes(null);
            this.clearBagDeliveryDate();
            this.clearBagTransferTime();
            this.clearBagStaffMember();
            this.clearBagCustomerModel();
            this.clearBagDeliveryFee();
            this.refreshCards();
            this.emptyBag();

            // Always select pickup for Livotis
            if (
              this.store.id === 108 ||
              this.store.id === 109 ||
              this.store.id === 110 ||
              this.store.id === 278
            ) {
              this.setBagPickup(1);
            }

            // Redirect

            this.redirectAfterCheckoutOrAdjust(resp);

            // Facebook pixel log

            if (this.store.settings.fbPixel) {
              Vue.analytics.fbq.event("track", "Purchase");
            }

            this.loading = false;
          }
        })
        .catch(async response => {
          let error = response.response.data.message;

          if (response.response.data.error === "past_delivery") {
            this.deliveryDay = null;
            this.clearBagDeliveryDate();
          }

          if (response.response.data.error === "inactive_coupon") {
            this.setBagCoupon(null);
          }

          if (response.response.data.removeableMeal) {
            let removeableMeal = response.response.data.removeableMeal;
            this.bag.forEach(item => {
              if (item.meal.id === removeableMeal.id) {
                this.clearMealFullQuantity(
                  item.meal,
                  item.meal_package,
                  item.size,
                  item.components,
                  item.addons,
                  item.special_instructions
                );
              }
            });
          }

          this.checkingOut = false;
          this.$toastr.w(error);
        });
    },
    inputCustomer(id) {
      this.existingCustomerAdded = false;
      this.getCards();
    },
    setCustomer(user) {
      if (user.existing) {
        this.existingCustomerAdded = true;
        this.customer = user.id;
        this.setBagCustomerModel({ text: user.name, value: user.id });
      } else {
        this.setBagCustomerModel({ text: user.name, value: user.id });
        this.$parent.setCustomer(user.id);
      }
    },
    removeCoupon() {
      this.coupon = {};
      this.setBagCoupon(null);
      this.discountCode = "";
    },
    removeReferral() {
      this.referral = {};
      this.setBagReferral(null);
    },
    removePurchasedGiftCard() {
      this.purchasedGiftCard = {};
      this.setBagPurchasedGiftCard(null);
      //edit
      this.discountCode = "";
    },
    editDeliveryFee() {
      this.editingDeliveryFee = true;
    },
    editSalesTax() {
      this.editingSalesTax = true;
    },
    addBillingAddress() {
      axios
        .post("/api/me/addBillingAddress", {
          billingAddress: this.form.billingAddress,
          billingCity: this.form.billingCity,
          billingState: this.form.billingState.value,
          billingZip: this.form.billingZip
        })
        .then(resp => {
          this.showBillingAddressModal = false;
          this.billingAddressVerified = true;
          this.$toastr.s("Billing address added. Please add your credit card.");
        });
    },
    subscriptionItemsCheck() {
      this.hasWeeklySubscriptionItems = false;
      this.hasMonthlySubscriptionItems = false;
      this.hasPrepaidSubscriptionItems = false;
      let subscriptionItemTypeCount = 0;
      this.bag.forEach(item => {
        if (item.meal.subscriptionInterval) {
          switch (item.meal.subscriptionInterval) {
            case "weekly":
              this.hasWeeklySubscriptionItems = true;
              subscriptionItemTypeCount += 1;
              break;
            case "monthly":
              this.hasMonthlySubscriptionItems = true;
              subscriptionItemTypeCount += 1;
              break;
            case "monthly-prepay":
              this.hasPrepaidSubscriptionItems = true;
              subscriptionItemTypeCount += 1;
              break;
          }
        }
      });
      if (subscriptionItemTypeCount > 0) {
        this.weeklySubscriptionValue = 1;
        this.plan = 1;
      }
      return subscriptionItemTypeCount;
    },
    checkPickup() {
      if (
        this.store.settings.next_orderable_delivery_dates.length === 0 &&
        this.store.settings.next_orderable_pickup_dates.length > 0
      ) {
        return true;
      } else {
        return false;
      }
    },
    getRemainingPromotionOrders(promotion) {
      let conditionAmount = promotion.conditionAmount;
      if (conditionAmount > this.user.orderCount) {
        return conditionAmount - this.user.orderCount;
      } else {
        let increment = conditionAmount;
        while (conditionAmount < this.user.orderCount) {
          conditionAmount += increment;
        }
        return conditionAmount - this.user.orderCount;
      }
    },
    applyPromotionPoints() {
      if (this.usePromotionPoints) {
        if (this.grandTotal === 0) {
          this.$toastr.w(
            "The total price of this order is 0. It can't be discounted any more."
          );
          return;
        }
        if (this.availablePromotionPoints / 100 > this.grandTotal) {
          this.pointsReduction = this.grandTotal;
        } else {
          this.pointsReduction = this.availablePromotionPoints / 100;
        }
      }
    },
    onSearchCustomer(search, loading) {
      if (search !== "") {
        this.customerOptions = [];
        loading(true);
        this.search(loading, search, this);
      }
    },
    showAuthModal() {
      this.$eventBus.$emit("showAuthModal");
    },
    setSubscription() {
      if (
        this.weeklySubscription ||
        this.inSub ||
        this.adjustMealPlan ||
        this.$route.query.sub === "true"
      ) {
        this.setBagMealPlan(true);
      }
    },
    setSample() {
      let customer = {
        text: "Sample Customer",
        value: 42,
        zip: "11209"
      };
      this.changeCustomer(customer);
      this.setBagDeliveryDate(this.deliveryDateOptionsStoreView[3].value);
      if (this.bagDeliveryDate && !this.deliveryDay) {
        this.deliveryDay = this.bagDeliveryDate.value
          ? this.bagDeliveryDate.value
          : this.bagDeliveryDate;
      }
      this.updateParentData();
    },
    // setSubscriptionCoupon() {
    //   let coupon_id = null;
    //   if (this.coupon) {
    //     coupon_id = this.coupon.id;
    //   }
    //   if (this.$parent.subscription && this.$parent.subscription.coupon_id) {
    //     coupon_id = this.$parent.subscription.coupon_id;
    //   }
    //   if (
    //     this.subscriptions &&
    //     this.subscriptions.length > 0 &&
    //     this.subscriptionId
    //   ) {
    //     let sub = this.subscriptions.find(sub => {
    //       return sub.id === this.subscriptionId;
    //     });
    //     if (sub) {
    //       coupon_id = sub.coupon_id;
    //     }
    //   }
    //   if (!coupon_id) {
    //     return;
    //   }
    //   axios
    //     .post(this.prefix + "findCouponById", {
    //       store_id: this.store.id,
    //       couponId: this.coupon
    //         ? this.coupon.id
    //         : this.$parent.subscription && this.$parent.subscription.coupon_id
    //         ? this.$parent.subscription.coupon_id
    //         : this.subscriptions
    //     })
    //     .then(resp => {
    //       if (resp.data) {
    //         this.setBagCoupon(resp.data);
    //       }
    //     });
    // },
    showAddCustomerModal() {
      if (
        this.storeSettings.payment_gateway === "stripe" &&
        !this.storeSettings.stripe_id
      ) {
        this.$toastr.w(
          "You must connect to Stripe before being able to add customers & create orders. Visit the Settings page to connect to Stripe."
        );
        return;
      }
      this.addCustomerModal = true;
    },
    syncDiscounts() {
      this.$nextTick(() => {
        switch (this.store.settings.discountSyncType) {
          case 1:
            if (this.mealPlanDiscount > 0) {
              this.showDiscounts.coupons = false;
              this.removeDiscounts();
            } else {
              this.showDiscounts.coupons = true;
            }
            break;
        }
      });
    },
    removePromos() {
      if (
        this.storeModules.noPromosOnSubscriptions &&
        this.weeklySubscriptionValue
      ) {
        this.removePromotions = true;
        this.removeDiscounts();
      }
    },
    removeDiscounts() {
      this.removeCoupon();
      this.removePurchasedGiftCard();
      this.removeReferral();
    },
    setFrequencySubscription(val) {
      this.setBagFrequencyType(val);
      val = val == "sub" ? true : false;
      this.setBagMealPlan(val);
      val = val == true ? 1 : 0;
      this.setWeeklySubscriptionValue(val);
      this.updateParentData();
      this.syncDiscounts();
    },
    blockedCheckoutMessage() {
      if (
        this.fillingOutCard &&
        this.invalidCheckout === "Please enter a payment method."
      ) {
        this.$refs.cardPicker.onClickCreateCard();
        return;
      }
      this.$toastr.w(this.invalidCheckout);
    },
    getItemMeals(item) {
      const mealPackage = !!item.meal_package;

      if (!mealPackage || !item.meal) {
        return [];
      }

      const pkg = this.getMealPackage(item.meal.id, item.meal);
      //const size = pkg && item.size ? pkg.getSize(item.size.id) : null;
      const size = pkg && item.size ? item.size : null;

      const packageMeals = size ? size.meals : pkg ? pkg.meals : null;

      let mealQuantities = _.mapValues(
        _.keyBy(packageMeals, pkgMeal => {
          return JSON.stringify({
            mealId: pkgMeal.id,
            sizeId: pkgMeal.meal_size_id,
            itemId: pkgMeal.item_id
          });
        }),
        mealItem => {
          return {
            quantity: mealItem.quantity,
            meal: mealItem,
            special_instructions: mealItem.special_instructions
          };
        }
      );

      // Add on component option selections
      _(item.components).forEach((options, componentId) => {
        const component = pkg.getComponent(componentId);
        const optionIds = mealPackage ? Object.keys(options) : options;

        _.forEach(optionIds, optionId => {
          const option = pkg.getComponentOption(component, optionId);
          if (!option) {
            return null;
          }

          //if (option.selectable) {
          _.forEach(options[option.id], item => {
            const mealId = item.meal_id;
            const sizeId = item.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += item.quantity;
            } else if (item.meal) {
              mealQuantities[guid] = {
                quantity: item.quantity,
                meal: item.meal,
                special_instructions: item.special_instructions
              };
            }
          });
          /*} else {
            _.forEach(option.meals, mealItem => {
              const mealId = mealItem.meal_id;
              const sizeId = mealItem.meal_size_id;
              const guid = JSON.stringify({ mealId, sizeId });

              if (mealQuantities[guid]) {
                mealQuantities[guid].quantity += mealItem.quantity;
              } else if (item.meal) {
                mealQuantities[guid] = {
                  quantity: item.quantity,
                  meal: item.meal,
                  special_instructions: item.special_instructions
                };
              }
            });
          }*/
        });
      });

      _(item.addons).forEach((addonItems, addonId) => {
        const addon = pkg.getAddon(addonId);

        if (addon.selectable) {
          _.forEach(addonItems, addonItem => {
            const mealId = addonItem.meal_id;
            const sizeId = addonItem.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += addonItem.quantity;
            } else if (addonItem.meal) {
              mealQuantities[guid] = {
                quantity: addonItem.quantity,
                meal: addonItem.meal,
                special_instructions: addonItem.special_instructions
              };
            }
          });
        } else {
          _.forEach(addonItems, addonItem => {
            const mealId = addonItem.meal_id;
            const sizeId = addonItem.meal_size_id;
            const guid = JSON.stringify({ mealId, sizeId });

            if (mealQuantities[guid]) {
              mealQuantities[guid].quantity += addonItem.quantity;
            } else if (addonItem.meal) {
              mealQuantities[guid] = {
                quantity: addonItem.quantity,
                meal: addonItem.meal,
                special_instructions: addonItem.special_instructions
              };
            }
          });
        }
      });

      const meals = _(mealQuantities)
        .map((item, guid) => {
          if (
            !item.hasOwnProperty("quantity") ||
            !item.hasOwnProperty("meal")
          ) {
            return null;
          }

          const { mealId, sizeId } = JSON.parse(guid);
          const meal = this.getMeal(mealId, item.meal);

          if (!meal) return null;

          //const size = meal && sizeId ? meal.getSize(sizeId) : null;
          const size =
            meal && meal.meal_size
              ? meal.meal_size
              : meal && sizeId
              ? meal.getSize(sizeId)
              : null;
          const title = size ? size.full_title : meal.full_title;

          const special_instructions = item.special_instructions;

          return {
            meal,
            size,
            quantity: item.quantity,
            title,
            special_instructions
          };
        })
        .filter()
        .value();

      let totalQuantity = 0;
      meals.forEach(meal => {
        totalQuantity += meal.quantity;
      });
      return totalQuantity;
    },
    search: _.debounce((loading, search, vm) => {
      axios
        .post("/api/me/searchCustomer", {
          query: search
        })
        .then(response => {
          response.data.forEach(customer => {
            if (customer) {
              vm.customerOptions.push({
                text: customer.name,
                value: parseInt(customer.id),
                zip: customer.zip
              });
            }
          });
        })
        .finally(() => {
          loading(false);
        });
    }, 600)
  }
};
</script>
