<template>
  <div
    v-if="$parent.showMealsArea"
    style="min-height: 100%;"
    v-bind:class="
      storeSettings.menuStyle === 'image'
        ? 'left-right-box-shadow main-customer-container'
        : 'left-right-box-shadow main-customer-container gray-background'
    "
  >
    <b-alert
      show
      variant="danger"
      v-if="store.settings.open === false && !$parent.storeView"
    >
      <h4 class="center-text">
        We are currently not accepting orders.
      </h4>
      <p class="center-text mt-3">
        {{ store.settings.closedReason }}
      </p>
    </b-alert>

    <b-alert show variant="success" v-if="$route.query.sub === true">
      <h5 class="center-text">
        Weekly Subscription
      </h5>
      <p class="center-text">
        You have an active weekly subscription with us. Update your meals for
        your next renewal on
        {{ moment(subscriptions[0].next_renewal).format("dddd, MMM Do") }}.
      </p>
    </b-alert>

    <b-alert
      variant="warning"
      show
      v-if="isAdjustOrder() && bagContainsGiftCard"
    >
      This order adjustment shows the gift card(s) purchased for the sake of
      keeping the order amount consistent. However removing the gift card won't
      remove the purchased gift card code the customer has access to. And if you
      want to add a new gift card, a new separate order needs to be created.
    </b-alert>

    <outside-delivery-area
      v-if="
        !$route.params.storeView &&
          !$parent.storeView &&
          !store.modules.hideDeliveryOption
      "
      :storeView="$parent.storeView"
    ></outside-delivery-area>

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
        <h6 class="center-text">
          Add {{ promotion.conditionAmount - totalBagQuantity }} more meals to
          receive a discount of
          <span v-if="promotion.promotionType === 'flat'">{{
            format.money(promotion.promotionAmount, storeSettings.currency)
          }}</span>
          <span v-else>{{ promotion.promotionAmount }}%</span>
        </h6>
      </b-alert>
      <b-alert
        variant="success"
        show
        v-if="
          promotion.conditionType === 'subtotal' &&
            totalBagPricePreFees < promotion.conditionAmount
        "
      >
        <h6 class="center-text">
          Add
          {{
            format.money(
              promotion.conditionAmount - totalBagPricePreFees,
              storeSettings.currency
            )
          }}
          more to receive
          <span v-if="promotion.promotionAmount > 0">
            <span v-if="promotion.promotionType === 'flat'">{{
              format.money(promotion.promotionAmount, storeSettings.currency)
            }}</span>
            <span v-else>{{ promotion.promotionAmount }}%</span>
            off your order.
          </span>
          <span
            v-if="promotion.promotionAmount === 0 && promotion.freeDelivery"
          >
            free delivery.
          </span>
        </h6>
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
        <h6 class="center-text">
          Order {{ getRemainingPromotionOrders(promotion) }} more times to
          receive a discount of
          <span v-if="promotion.promotionType === 'flat'">{{
            format.money(promotion.promotionAmount, storeSettings.currency)
          }}</span>
          <span v-else>{{ promotion.promotionAmount }}%</span>
        </h6>
      </b-alert>
      <b-alert
        variant="success"
        show
        v-if="promotion.promotionType === 'points' && promotionPointsAmount > 0"
      >
        <h6 class="center-text">
          You will earn {{ promotionPointsAmount }}
          {{ promotion.pointsName }} on this order.
        </h6>
      </b-alert>
    </div>

    <div
      class="alert alert-success"
      role="alert"
      v-if="
        store &&
          store.referral_settings &&
          store.referral_settings.enabled &&
          store.referral_settings.showInMenu &&
          user.referralUrlCode
      "
    >
      <h5 class="center-text">Referral Program</h5>
      <p class="center-text">
        Give out your referral link to customers and if they order
        <span v-if="referralSettings.frequency == 'urlOnly'">
          using your link
        </span>
        you will receive {{ referralSettings.amountFormat }} credit
        <span v-if="referralSettings.frequency == 'firstOrder'">
          on the first order they place.
        </span>
        <span v-else>
          on every future order they place.
        </span>
        <br />Your referral link is
        <a :href="referralUrl">{{ referralUrl }}</a>
      </p>
    </div>

    <p v-html="store.details.description" v-if="store.details.description"></p>

    <meal-package-components-modal
      ref="packageComponentModal"
      :packageTitle="packageTitle"
    ></meal-package-components-modal>

    <div
      v-for="(group, catIndex) in meals"
      :key="'category_' + group.category + '_' + catIndex"
      :id="group.category_id"
      :target="'categorySection_' + group.category_id"
      :class="container"
      style="margin-bottom: 20px;"
      v-if="group.meals.length > 0 && isCategoryVisible(group)"
    >
      <div
        v-observe-visibility="
          (isVisible, entry) => $parent.onCategoryVisible(isVisible, group)
        "
      >
        <div v-if="storeSettings.menuStyle === 'image'">
          <h2 class="text-center mb-2 dbl-underline">
            {{ group.category }}
          </h2>
          <h5 v-if="group.subtitle !== null" class="text-center mb-4">
            {{ group.subtitle }}
          </h5>
          <div class="row">
            <div
              class="item col-sm-6 col-md-6 col-lg-6 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border pb-2 mb-2"
              v-for="(meal, index) in group.meals"
              v-if="!meal.hideFromMenu && assignedToDeliveryDay(meal)"
              :key="
                meal.meal_package
                  ? 'meal_package_' +
                    meal.id +
                    '_' +
                    group.category_id +
                    '_' +
                    index
                  : 'meal_' + meal.id + '_' + group.category_id + '_' + index
              "
            >
              <div :class="card">
                <div :class="cardBody">
                  <div class="item-wrap">
                    <div class="title d-md-none">
                      <p v-html="getMealTitle(meal.title)"></p>
                      <strong
                        v-if="store.id === 148"
                        style="position:relative;bottom:10px"
                        >{{
                          format.money(meal.price, storeSettings.currency)
                        }}</strong
                      >
                    </div>

                    <div class="image">
                      <thumbnail
                        v-if="meal.image != null && meal.image.url_medium"
                        :src="meal.image.url_medium"
                        class="menu-item-img"
                        width="100%"
                        style="background-color:#ffffff"
                        @click="showMeal(meal, group)"
                      ></thumbnail>
                      <!-- Hard coding price difference for now for Eat Fresh until new menu design table is required-->
                      <div
                        class="price"
                        v-if="store.id !== 148 && store.id !== 178"
                      >
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </div>
                    </div>

                    <div class="meta">
                      <div
                        class="title d-none d-md-block center-text pt-2 pb-1"
                      >
                        <p
                          v-html="getMealTitle(meal.title)"
                          style="line-height:normal;"
                        ></p>
                        <!-- Hard coding price difference for now for Eat Fresh until new menu design table is required-->
                        <strong
                          v-if="store.id === 148"
                          style="position:relative;bottom:10px"
                          >{{
                            format.money(meal.price, storeSettings.currency)
                          }}</strong
                        >
                      </div>

                      <!-- Show more & show less buttons instead of linking to meal page on mobile -->

                      <!-- <div
                        class="description d-md-none"
                        @click="readMore(meal)"
                      >
                        <span
                          v-if="
                            meal.description && meal.description.length > 150
                          "
                        >
                          <span v-if="!showFullDescription[meal.id]">
                            {{ truncate(meal.description, 150, "...") }} Show
                            more</span
                          >
                          <span v-if="showFullDescription[meal.id]">
                            {{ meal.description }} Show less</span
                          >
                        </span>
                        <span v-else>
                          {{ meal.description }}
                        </span>
                      </div> -->

                      <div
                        class="description d-md-none"
                        @click="showMeal(meal)"
                      >
                        <span>
                          {{ truncate(meal.description, 150, "...") }} Show
                          more</span
                        >
                      </div>

                      <div
                        class="title macrosArea d-flex d-center"
                        v-if="meal.macros && storeSettings.showMacros"
                      >
                        <div class="d-inline mr-4">
                          <p>
                            {{ getMacroTitle().calories }}<br />
                            <span>{{ getMacros(meal, "calories") }}</span>
                          </p>
                        </div>
                        <div class="d-inline mr-4">
                          <p>
                            {{ getMacroTitle().carbs }}<br />
                            <span>{{ getMacros(meal, "carbs") }}</span>
                          </p>
                        </div>
                        <div class="d-inline mr-4">
                          <p>
                            {{ getMacroTitle().protein }}<br />
                            <span>{{ getMacros(meal, "protein") }}</span>
                          </p>
                        </div>
                        <div class="d-inline mr-4">
                          <p>
                            {{ getMacroTitle().fat }}<br />
                            <span>{{ getMacros(meal, "fat") }}</span>
                          </p>
                        </div>
                      </div>

                      <div class="actions">
                        <div
                          class="d-flex justify-content-between align-items-center mt-1"
                        >
                          <b-btn
                            @click.stop="minusMixOne(meal)"
                            class="plus-minus gray"
                            v-if="meal.gift_card"
                          >
                            <i>-</i>
                          </b-btn>
                          <b-form-input
                            v-if="meal.gift_card"
                            type="number"
                            name
                            id
                            class="quantity"
                            placeholder="0"
                            v-model="giftCardQuantities[meal.id]"
                            @change="
                              val => setItemQuantity('giftCard', meal, val)
                            "
                          ></b-form-input>

                          <b-btn
                            v-if="meal.gift_card"
                            @click.stop="addMeal(meal, null)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>

                          <b-btn
                            @click.stop="minusMixOne(meal)"
                            class="plus-minus gray"
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                !meal.hasVariations
                            "
                          >
                            <i>-</i>
                          </b-btn>
                          <b-form-input
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                !meal.hasVariations
                            "
                            type="number"
                            name
                            id
                            class="quantity"
                            placeholder="0"
                            v-model="mealQuantities[meal.id]"
                            @change="val => setItemQuantity('meal', meal, val)"
                          ></b-form-input>

                          <b-btn
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                !meal.hasVariations
                            "
                            @click.stop="addMeal(meal, null)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>

                          <b-dropdown
                            right
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                meal.hasVariations &&
                                meal.sizes.length > 0
                            "
                            toggle-class="brand-color"
                            :ref="
                              'dropdown_' + meal.id + '_' + group.category_id
                            "
                            class="mx-auto"
                            size="lg"
                          >
                            <span class="white-text" slot="button-content"
                              >Select</span
                            >
                            <b-dropdown-item
                              @click="addMeal(meal, false)"
                              class="variation-dropdown"
                            >
                              {{ meal.default_size_title || "Regular" }} -
                              {{
                                format.money(meal.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              class="variation-dropdown"
                              v-for="(size, index) in meal.sizes"
                              :key="'size_' + size.id + '_' + index"
                              @click.stop="addMeal(meal, false, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>

                          <b-btn
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                meal.hasVariations &&
                                meal.sizes.length === 0
                            "
                            @click.stop="addMeal(meal, null)"
                            size="lg"
                            class="mx-auto variation-dropdown brand-color white-text"
                            style="height:45px"
                          >
                            Select
                          </b-btn>

                          <!-- <b-btn
                            v-if="
                              meal.meal_package &&
                                (!meal.sizes || meal.sizes.length === 0)
                            "
                            @click="addMeal(meal, false)"
                            class="plus-minus menu-bag-btn"
                          >
                            <i>+</i>
                          </b-btn> -->

                          <b-btn
                            slot="button-content"
                            class="brand-color mx-auto white-text"
                            size="lg"
                            v-if="
                              meal.meal_package &&
                                !meal.gift_card &&
                                (!meal.sizes || meal.sizes.length === 0)
                            "
                            @click="addMeal(meal, true)"
                            >Select</b-btn
                          >

                          <b-dropdown
                            v-if="
                              meal.meal_package &&
                                meal.sizes &&
                                meal.sizes.length > 0
                            "
                            toggle-class="brand-color"
                            :ref="
                              'dropdown_' + meal.id + '_' + group.category_id
                            "
                            class="mx-auto"
                            size="lg"
                            right
                          >
                            <span class="white-text" slot="button-content"
                              >Select</span
                            >

                            <b-dropdown-item
                              @click="addMeal(meal, true)"
                              class="variation-dropdown"
                            >
                              {{ meal.default_size_title || "Regular" }} -
                              {{
                                format.money(meal.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              class="variation-dropdown"
                              v-for="(size, index) in meal.sizes"
                              :key="'size_' + size.id + '_' + index"
                              @click="addMealPackage(meal, true, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="storeSettings.menuStyle === 'text'">
          <h2 class="text-center mb-3 dbl-underline">
            {{ group.category }}
          </h2>
          <div class="row">
            <div
              class="item item-text col-sm-6 col-md-6 col-lg-12 col-xl-6"
              v-for="(meal, index) in group.meals"
              v-if="!meal.hideFromMenu && assignedToDeliveryDay(meal)"
              :key="'meal_' + meal.id + '_' + index"
              style="margin-bottom: 10px !important;"
            >
              <div
                class="card card-text-menu border-light p-3 mr-1"
                @click="showMeal(meal, group)"
                style="height: 100%;"
              >
                <!--<div class="bag-item-quantity row">!-->
                <div
                  class="bag-item-quantity"
                  style="display: flex; min-height: 128px !important;"
                >
                  <!--<div class="col-md-1">!-->
                  <div class="button-area" style="position: relative;">
                    <!-- <div
                      @click.stop="addMeal(item, null)"
                      class="bag-plus-minus small-buttons brand-color white-text"
                    >
                      <i>+</i>
                    </div> -->

                    <b-btn
                      v-if="!meal.meal_package && !meal.hasVariations"
                      @click.stop="addMeal(meal, null)"
                      class="menu-bag-btn small-buttons plus-minus"
                    >
                      <i>+</i>
                    </b-btn>

                    <b-dropdown
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          meal.sizes.length > 0
                      "
                      toggle-class="brand-color"
                      :ref="'dropdown_' + meal.id + '_' + group.category_id"
                      class="mx-auto"
                      size="lg"
                    >
                      <span class="white-text" slot="button-content"
                        >Select</span
                      >
                      <b-dropdown-item
                        @click.stop="addMeal(meal, false)"
                        class="variation-dropdown"
                      >
                        {{ meal.default_size_title || "Regular" }} -
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </b-dropdown-item>
                      <b-dropdown-item
                        class="variation-dropdown"
                        v-for="(size, index) in meal.sizes"
                        :key="'size_' + size.id + '_' + index"
                        @click.stop="addMeal(meal, false, size)"
                      >
                        {{ size.title }} -
                        {{ format.money(size.price, storeSettings.currency) }}
                      </b-dropdown-item>
                    </b-dropdown>
                    <b-btn
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          meal.hasVariations &&
                          meal.sizes.length === 0
                      "
                      @click.stop="addMeal(meal, null)"
                      size="lg"
                      class="mx-auto variation-dropdown brand-color white-text"
                      style="height:45px"
                    >
                      Select
                    </b-btn>
                    <b-btn
                      slot="button-content"
                      class="brand-color mx-auto white-text"
                      size="lg"
                      v-if="
                        meal.meal_package &&
                          !meal.gift_card &&
                          !meal.hasVariations &&
                          meal.sizes.length === 0
                      "
                      @click="addMeal(meal, true)"
                      >Select</b-btn
                    >

                    <b-dropdown
                      v-if="
                        meal.meal_package && meal.sizes && meal.sizes.length > 0
                      "
                      toggle-class="brand-color"
                      :ref="'dropdown_' + meal.id + '_' + group.category_id"
                      class="mx-auto"
                      size="lg"
                    >
                      <span class="white-text" slot="button-content"
                        >Select</span
                      >

                      <b-dropdown-item
                        @click="addMeal(meal, true)"
                        class="variation-dropdown"
                      >
                        {{ meal.default_size_title || "Regular" }} -
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </b-dropdown-item>
                      <b-dropdown-item
                        class="variation-dropdown"
                        v-for="(size, index) in meal.sizes"
                        :key="'size_' + size.id + '_' + index"
                        @click="addMealPackage(meal, true, size)"
                      >
                        {{ size.title }} -
                        {{ format.money(size.price, storeSettings.currency) }}
                      </b-dropdown-item>
                    </b-dropdown>

                    <b-form-input
                      v-if="meal.gift_card"
                      name
                      id
                      class="small-quantity mt-1 mb-1"
                      placeholder="0"
                      :value="mealMixQuantity(meal)"
                      readonly
                    ></b-form-input>
                    <b-form-input
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          !meal.hasVariations
                      "
                      name
                      id
                      class="small-quantity mt-1 mb-1"
                      placeholder="0"
                      :value="mealMixQuantity(meal)"
                      readonly
                    ></b-form-input>

                    <!-- Clicking on the input box to edit the quantity takes you to the meal page because it is within the clickable box. click.stop doesn't work, @clicking to a method which hides the meal page and show meals area doesn't work. Will revisit if needed -->

                    <!-- <b-form-input
                      v-if="meal.gift_card"
                      name
                      id
                      class="small-quantity mt-1 mb-1"
                      placeholder="0"
                      v-model="giftCardQuantities[meal.id]"
                      @change="val => setItemQuantity('giftCard', meal, val)"
                      @click.stop=""
                    ></b-form-input>
                    <b-form-input
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          !meal.hasVariations
                      "
                      name
                      id
                      class="small-quantity mt-1 mb-1"
                      placeholder="0"
                      v-model="mealQuantities[meal.id]"
                      @change="val => setItemQuantity('meal', meal, val)"
                      @click.stop=""
                    ></b-form-input> -->
                    <div
                      @click.stop="minusMixOne(meal)"
                      class="bag-plus-minus small-buttons gray white-text"
                      v-if="!meal.meal_package && !meal.hasVariations"
                    >
                      <i>-</i>
                    </div>
                  </div>

                  <!--<div v-if="meal.image != null" class="col-md-8">!-->
                  <div
                    v-if="meal.image != null"
                    class="content-area"
                    style="position: relative;"
                  >
                    <div class="image-area" style="position: relative;">
                      <thumbnail
                        class="text-menu-image"
                        v-if="meal.image != null"
                        :src="meal.image.url_thumb"
                        :spinner="false"
                      ></thumbnail>
                      <div class="price" style="right: 5px !important;">
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </div>
                    </div>

                    <div class="content-text-wrap">
                      <p
                        style="word-break: break-all;"
                        v-html="getMealTitle(meal.title)"
                      ></p>
                      <div class="mt-1 content-text">
                        {{ meal.description }}
                      </div>
                      <div class="title" v-if="storeSettings.showMacros">
                        <div class="title macrosArea d-flex ">
                          <div class="d-inline mr-4">
                            <p>
                              Calories<br />
                              <span>{{ getMacros(meal, "calories") }}</span>
                            </p>
                          </div>
                          <div class="d-inline mr-4">
                            <p>
                              Carbs<br />
                              <span>{{ getMacros(meal, "carbs") }}</span>
                            </p>
                          </div>
                          <div class="d-inline mr-4">
                            <p>
                              Protein<br />
                              <span>{{ getMacros(meal, "protein") }}</span>
                            </p>
                          </div>
                          <div class="d-inline">
                            <p>
                              Fat<br />
                              <span>{{ getMacros(meal, "fat") }}</span>
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="content-area" style="position: relative;">
                    <div class="content-text-wrap d-flex">
                      <!--<div v-else class="col-md-11">!-->
                      <div style="flex-basis:75%">
                        <p v-html="getMealTitle(meal.title)"></p>
                        <span class="content-text">
                          {{ meal.description }}
                          <div
                            class="title"
                            v-if="meal.macros && storeSettings.showMacros"
                          >
                            <div
                              class="title macrosArea d-flex d-center"
                              v-if="meal.macros && storeSettings.showMacros"
                            >
                              <div class="d-inline mr-4">
                                <p>
                                  Calories<br />
                                  <span>{{ getMacros(meal, "calories") }}</span>
                                </p>
                              </div>
                              <div class="d-inline mr-4">
                                <p>
                                  Carbs<br />
                                  <span>{{ getMacros(meal, "carbs") }}</span>
                                </p>
                              </div>
                              <div class="d-inline mr-4">
                                <p>
                                  Protein<br />
                                  <span>{{ getMacros(meal, "protein") }}</span>
                                </p>
                              </div>
                              <div class="d-inline">
                                <p>
                                  Fat<br />
                                  <span>{{ getMacros(meal, "fat") }}</span>
                                </p>
                              </div>
                            </div>
                          </div>
                        </span>
                      </div>
                      <div style="flex-basis:25%">
                        <div
                          class="price-no-bg"
                          style="top: 0 !important; right: 0 !important;"
                        >
                          {{ format.money(meal.price, storeSettings.currency) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!--<div v-if="meal.image != null" class="col-md-3">!-->
                  <!--<div
                    v-if="meal.image != null"
                    class="image-area"
                    style="position: relative; width: 128px;"
                  >
                    <thumbnail
                      class="text-menu-image"
                      v-if="meal.image != null"
                      :src="meal.image.url_thumb"
                      :spinner="false"
                    ></thumbnail>
                    <div
                      class="price"
                      style="top: 5px !important; right: 5px !important;"
                    >
                      {{ format.money(meal.price, storeSettings.currency) }}
                    </div>
                  </div>!-->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import MenuBag from "../../mixins/menuBag";
import { mapGetters, mapActions } from "vuex";
import OutsideDeliveryArea from "../../components/Customer/OutsideDeliveryArea";
import MealPackageComponentsModal from "../../components/Modals/MealPackageComponentsModal";
import store from "../../store";

export default {
  data() {
    return {
      mealQuantities: [],
      giftCardQuantities: [],
      packageTitle: null,
      showFullDescription: {}
    };
  },
  components: {
    MealPackageComponentsModal,
    OutsideDeliveryArea
  },
  props: {
    meals: "",
    card: "",
    cardBody: "",
    filters: null,
    search: "",
    filteredView: false,
    adjustOrder: false
  },
  mounted() {
    if ("item" in this.$route.query) {
      if (this.context !== "store") {
        axios.post("/api/addViewToMeal", {
          store_id: this.store.id,
          meal_id: this.$route.query.item
        });
      }
      // axios
      //   .post("/api/refreshByTitle", {
      //     store_id: this.store.id,
      //     meal_title: this.$route.query.item
      //   })
      //   .then(resp => {
      //     this.$parent.showMealPage(resp.data.meal);
      //   });
      axios.get("/api/refresh/meal/" + this.$route.query.item).then(resp => {
        this.$parent.showMealPage(resp.data.meal);
      });
    }
    if ("package" in this.$route.query) {
      axios
        .get("/api/refresh/meal_package/" + this.$route.query.package)
        .then(resp => {
          this.$parent.showMealPackagePage(resp.data.package, null);
        });
    }
    if ("package_size" in this.$route.query) {
      axios
        .get(
          "/api/refresh/meal_package_with_size/" +
            this.$route.query.package_size
        )
        .then(resp => {
          this.$parent.showMealPackagePage(
            resp.data.package,
            resp.data.package_size
          );
        });
    }

    // Fix packages first
    // if (this.$route.query.package){
    //   axios.post('/api/refreshPackageByTitle', {store_id: this.store.id, package_title: this.$route.query.package, package_size_title: this.$route.query.package_size})
    //   .then(resp => {
    //     this.showMealPackage(resp.data.package, resp.data.packageSize)
    //   })
    // }

    window.scrollTo(0, 0);

    // Remove first category if it has no items.
    if (this.meals[0].meals.length === 0) {
      this.$parent.finalCategories.shift();
    }
  },
  watch: {
    subscriptions: function() {
      if (
        this.user.id &&
        this.subscriptions.length > 0 &&
        !this.$route.params.id &&
        !this.store.modules.allowMultipleSubscriptions
      ) {
        this.$router.push({
          path: "/customer/subscriptions/" + this.subscriptions[0].id,
          params: { subscriptionId: this.subscriptions[0].id },
          query: { sub: true }
        });
      }
    }
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      context: "context",
      //total: "bagQuantity",
      //hasMeal: "bagHasMeal",
      //minOption: "minimumOption",
      //minMeals: "minimumMeals",
      //minPrice: "minimumPrice",
      bag: "bagItems",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _categories: "viewedStoreCategories",
      user: "user",
      subscriptions: "subscriptions",
      promotions: "viewedStorePromotions",
      loggedIn: "loggedIn",
      totalBagPricePreFees: "totalBagPricePreFees",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice"
    }),
    referralSettings() {
      return this.store.referral_settings;
    },
    smallScreen() {
      const width =
        window.innerWidth ||
        document.documentElement.clientWidth ||
        document.body.clientWidth;
      if (width < 1750) {
        return true;
      } else {
        return false;
      }
    },
    totalBagQuantity() {
      let quantity = 0;
      this.bag.forEach(item => {
        quantity += item.quantity;
      });
      return quantity;
    },
    bagContainsGiftCard() {
      let containsGiftCard = false;
      this.bag.forEach(item => {
        if (item.meal.gift_card) {
          containsGiftCard = true;
        }
      });
      return containsGiftCard;
    },
    isMultipleDelivery() {
      return this.store.modules.multipleDeliveryDays == 1 ? true : false;
    },
    storeSettings() {
      return this.store.settings;
    },
    container() {
      if (this.storeSettings.menuStyle === "image") {
        return "categorySection customer-menu-container";
      } else {
        return "categorySection customer-menu-container";
      }
    },
    referralAmount() {
      return this.store.referral_settings.amountFormat;
    },
    referralUrl() {
      return this.store.referral_settings.url + this.user.referralUrlCode;
    },
    referralFrequency() {
      return this.store.referral_settings.frequency;
    },
    promotionPointsAmount() {
      let promotion = this.promotions.find(promotion => {
        return promotion.promotionType === "points";
      });
      return (
        (promotion.promotionAmount / 100) *
        this.totalBagPricePreFees *
        100
      ).toFixed(0);
    },
    activePromotions() {
      let promotions = [];
      this.promotions.forEach(promotion => {
        if (promotion.active) {
          promotions.push(promotion);
        }
      });
      return promotions;
    }
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    truncate(text, length, suffix) {
      if (text) {
        return text.substring(0, length) + suffix;
      }
    },
    existInBagItem(meal, meal_size, item) {
      const mealPackage = !!item.meal_package;

      if (!mealPackage || !item.meal) {
        return false;
      }

      const meal_size_id = meal_size ? meal_size.id : null;

      let found = false;
      const pkg = this.getMealPackage(item.meal.id, item.meal);
      const size = pkg && item.size ? item.size : null;
      const packageMeals = size ? size.meals : pkg ? pkg.meals : null;

      if (packageMeals) {
        packageMeals.forEach(pkgMeal => {
          if (
            pkgMeal &&
            meal.id == pkgMeal.id &&
            meal_size_id == pkgMeal.meal_size_id &&
            !found
          ) {
            found = true;
          }
        });
      }

      if (!found) {
        _(item.components).forEach((options, componentId) => {
          const component = pkg.getComponent(componentId);
          const optionIds = mealPackage ? Object.keys(options) : options;

          _.forEach(optionIds, optionId => {
            const option = pkg.getComponentOption(component, optionId);
            if (!option) {
              return null;
            }

            if (option.selectable) {
              _.forEach(options[option.id], optionItem => {
                if (
                  optionItem &&
                  optionItem.meal_id == meal.id &&
                  optionItem.meal_size_id == meal_size_id &&
                  !found
                ) {
                  found = true;
                }
              });
            } else {
              _.forEach(option.meals, mealItem => {
                if (
                  mealItem &&
                  mealItem.meal_id == meal.id &&
                  mealItem.meal_size_id == meal_size_id &&
                  !found
                ) {
                  found = true;
                }
              });
            }
          });
        });
      }

      if (!found) {
        _(item.addons).forEach((addonItems, addonId) => {
          const addon = pkg.getAddon(addonId);

          if (addon.selectable) {
            _.forEach(addonItems, addonItem => {
              if (
                addonItem &&
                addonItem.meal_id == meal.id &&
                addonItem.meal_size_id == meal_size_id &&
                !found
              ) {
                found = true;
              }
            });
          } else {
            _.forEach(addonItems, addonItem => {
              if (
                addonItem &&
                addonItem.meal_id == meal.id &&
                addonItem.meal_size_id == meal_size_id &&
                !found
              ) {
                found = true;
              }
            });
          }
        });
      }

      return found;
    },
    getPackageBagItems() {
      const items = [];
      const bag = this.bag;

      if (bag) {
        bag.forEach(item => {
          if (item.meal_package) {
            if (!this.isMultipleDelivery) {
              items.push(item);
            } else {
              if (
                item.delivery_day &&
                this.store.delivery_day &&
                item.delivery_day.id == this.store.delivery_day.id
              ) {
                items.push(item);
              }
            }
          }
        });
      }

      return items;
    },
    getRelatedBagItems(meal, size) {
      const items = [];
      const bag = this.bag;

      if (bag) {
        bag.forEach(item => {
          if (this.existInBagItem(meal, size, item)) {
            items.push(item);
          }
        });
      }
      return items;
    },
    isManualOrder() {
      if (
        this.manualOrder ||
        this.$route.params.manualOrder ||
        this.$route.name == "store-manual-order"
      ) {
        return true;
      }
      return false;
    },
    isAdjustOrder() {
      if (
        this.adjustOrder ||
        this.$route.params.adjustOrder ||
        this.$route.name == "store-adjust-order"
      ) {
        return true;
      }
      return false;
    },
    mealMixQuantity(meal) {
      if (meal.meal_package) {
        return this.quantity(meal, true);
      } else {
        return this.mealQuantity(meal);
      }
    },
    async minusMixOne(
      meal,
      condition,
      size,
      components,
      addons,
      special_instructions
    ) {
      if (this.$parent.selectedDeliveryDay) {
        meal.delivery_day = this.$parent.selectedDeliveryDay;
      }
      if (meal.meal_package) {
        this.minusOne(meal, true);
      } else {
        if (
          (meal.sizes && meal.sizes.length > 0) ||
          (meal.components && meal.components.length > 0) ||
          (meal.addons && meal.addons.length > 0)
        ) {
          this.$toastr.e("Please remove the meal from the bag.");
        } else {
          this.minusOne(meal, false, null, null, [], null);
        }
      }
    },
    hasVariations(meal, size) {
      let hasVar = false;
      if (size == null || size == undefined) {
        if (meal.components.length > 0) {
          meal.components.forEach(component => {
            component.options.forEach(option => {
              if (option.meal_size_id == null) {
                hasVar = true;
              }
            });
          });
        }

        if (meal.addons.length > 0) {
          meal.addons.forEach(addon => {
            if (addon.meal_size_id == null) {
              hasVar = true;
            }
          });
        }
      } else {
        meal.addons.forEach(addon => {
          if (addon.meal_size_id == size.id) {
            hasVar = true;
          }
        });
        meal.components.forEach(component => {
          component.options.forEach(option => {
            if (option.meal_size_id == size.id) {
              hasVar = true;
            }
          });
        });
      }

      return hasVar;
    },
    async addMealPackage(mealPackage, condition = false, size) {
      if (size === undefined) {
        size = null;
      }

      if (size) {
        this.packageTitle = mealPackage.title + " - " + size.title;
      } else {
        if (mealPackage.default_size_title) {
          this.packageTitle =
            mealPackage.title + " - " + mealPackage.default_size_title;
        } else {
          this.packageTitle = mealPackage.title;
        }
      }

      if (size) {
        mealPackage.selectedSizeId = size.id;
      } else {
        mealPackage.selectedSizeId = undefined;
      }
      /* Refresh Meal Package */
      // if (!this.store.refreshed_package_ids.includes(mealPackage.id)) {
      this.$parent.forceShow = true;

      if (!mealPackage.delivery_day) {
        mealPackage.delivery_day = this.$parent.finalDeliveryDay;
      }

      if (!("package" in this.$route.query)) {
        mealPackage = await store.dispatch(
          "refreshStoreMealPackage",
          mealPackage
        );
      }

      this.$parent.forceShow = false;
      // } else {
      //   mealPackage = this.getMealPackage(mealPackage.id);
      // }
      /* Refresh Meal Package End */

      /* Show Detail Page or not */
      let showDetail = false;
      let sizeId = size ? size.id : null;
      let sizeCriteria = { meal_package_size_id: sizeId };

      if (mealPackage.sizes && mealPackage.sizes.length && sizeId) {
        size = _.find(mealPackage.sizes, { id: sizeId });
      }

      if (
        mealPackage.components &&
        mealPackage.components.length &&
        _.maxBy(mealPackage.components, "minimum") &&
        _.find(mealPackage.components, component => {
          return _.find(component.options, sizeCriteria);
        })
      ) {
        showDetail = true;
      }

      if (
        mealPackage.addons &&
        mealPackage.addons.length &&
        _.find(mealPackage.addons, sizeCriteria)
      ) {
        showDetail = true;
      }

      if (showDetail) {
        this.showMealPackage(mealPackage, size);
        return false;
      }

      /* Show Detail Page or not end */

      this.$parent.mealPackageModal = false;
      if (this.$parent.showBagClass.includes("hidden-right")) {
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass += " area-scroll";
      } else if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass -= " area-scroll";
      }
      this.$parent.search = "";

      if (this.store.modules.multipleDeliveryDays) {
        if (
          mealPackage.meals &&
          mealPackage.meals.some(meal => {
            return meal.delivery_day_id;
          })
        ) {
          // Splits the meals in the meal package into separate delivery days in the bag
          let count = [
            ...new Set(mealPackage.meals.map(meal => meal.delivery_day_id))
          ].length;
          let deliveryDayIds = this.store.delivery_days.map(day => {
            return day.id;
          });
          deliveryDayIds.forEach(dayId => {
            let deliveryDay = this.store.delivery_days.find(day => {
              return day.id == dayId;
            });
            let newMealPackage = { ...mealPackage };
            newMealPackage.meals = [];
            mealPackage.meals.forEach(meal => {
              if (meal.delivery_day_id == dayId) {
                newMealPackage.meals.push(meal);
              }
            });
            if (newMealPackage.meals.length > 0) {
              newMealPackage.delivery_day = deliveryDay;
              newMealPackage.customTitle =
                newMealPackage.title + " - " + deliveryDay.day_long;
              newMealPackage.title =
                newMealPackage.title + " - " + deliveryDay.day_long;
              newMealPackage.price = newMealPackage.price / count;
              this.addOne(newMealPackage, true, size);
            }
          });
          return;
        }
        if (size) {
          if (
            size.meals.some(meal => {
              return meal.delivery_day_id;
            })
          ) {
            // Splits the meals in the meal package into separate delivery days in the bag
            let count = [
              ...new Set(size.meals.map(meal => meal.delivery_day_id))
            ].length;
            let deliveryDayIds = this.store.delivery_days.map(day => {
              return day.id;
            });
            deliveryDayIds.forEach(dayId => {
              let deliveryDay = this.store.delivery_days.find(day => {
                return day.id == dayId;
              });
              let newMealPackageSize = { ...size };
              newMealPackageSize.meals = [];
              size.meals.forEach(meal => {
                if (meal.delivery_day_id == dayId) {
                  newMealPackageSize.meals.push(meal);
                }
              });
              if (newMealPackageSize.meals.length > 0) {
                newMealPackageSize.delivery_day = deliveryDay;
                newMealPackageSize.customTitle =
                  newMealPackageSize.title + " - " + deliveryDay.day_long;
                newMealPackageSize.title =
                  newMealPackageSize.title + " - " + deliveryDay.day_long;
                newMealPackageSize.price = newMealPackageSize.price / count;
                this.addOne(mealPackage, true, newMealPackageSize);
              }
            });
            return;
          }
        }
        mealPackage.customTitle =
          mealPackage.title +
          " - " +
          moment(this.$parent.selectedDeliveryDay.day_friendly).format("dddd");
        mealPackage.title =
          mealPackage.title +
          " - " +
          moment(this.$parent.selectedDeliveryDay.day_friendly).format("dddd");
      }

      this.addOne(mealPackage, true, size);
    },
    async addMeal(meal, mealPackage, size) {
      meal.quantity = this.mealQuantities[meal.id];
      if (this.$parent.selectedDeliveryDay) {
        meal.delivery_day = this.$parent.selectedDeliveryDay;
      }
      if (meal.gift_card) {
        meal.quantity = this.giftCardQuantities[meal.id];
        this.addOne(meal);
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
        this.giftCardQuantities.splice(meal.id, 1);

        return;
      }
      if (meal.meal_package) {
        this.addMealPackage(meal, true);
      } else {
        if (
          meal.sizes & (meal.sizes.length > 0) &&
          meal.components &&
          meal.components.length === 0 &&
          meal.addons &&
          meal.addons.length === 0
        ) {
          if (this.isAdjustOrder() || this.isManualOrder()) {
            //const items = this.getRelatedBagItems(meal, null);
            const items = this.getPackageBagItems();

            if (items && items.length > 0) {
              this.$parent.showAdjustModal(meal, null, null, [], null, items);
              return;
            } else {
              this.addOne(meal, false, null, null, [], null);
            }
          } else {
            this.addOne(meal, false, null, null, [], null);
          }
        }

        if (this.hasVariations(meal, size)) {
          let data = {};
          data.meal = meal;
          data.sizeId = size ? size.id : null;
          this.$emit("showVariations", data);
          // this.showVariationsModal = true;
          // this.showMeal(meal, null, size);
          return;
        } else {
          if (size === undefined) {
            size = null;
          }

          if (this.isAdjustOrder() || this.isManualOrder()) {
            //const items = this.getRelatedBagItems(meal, size);
            const items = this.getPackageBagItems();

            if (items && items.length > 0) {
              this.$parent.showAdjustModal(meal, size, null, [], null, items);
              return;
            } else {
              this.addOne(meal, false, size, null, [], null);
            }
          } else {
            this.addOne(meal, false, size, null, [], null);
          }
        }

        if (this.$parent.showBagClass.includes("hidden-right")) {
          this.$parent.showBagClass = "shopping-cart show-right bag-area";
        }
        if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass += " area-scroll";
        } else if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass -= " area-scroll";
        }
      }
      this.$parent.search = "";
      this.mealQuantities.splice(meal.id, 1);
    },
    showMealPackage(mealPackage, size) {
      $([document.documentElement, document.body]).scrollTop(0);
      this.$parent.showMealPackagePage(mealPackage, size);
      this.$parent.showMealsArea = false;
      this.$parent.showMealPackagesArea = false;
      this.$parent.search = "";
    },
    async showMeal(meal, group, size) {
      if (meal.gift_card) {
        return;
      }

      if (size) {
        meal.selectedSizeId = size.id;
      } else {
        meal.selectedSizeId = undefined;
      }

      if (meal.meal_package) {
        let mealPackage = await store.dispatch("refreshStoreMealPackage", meal);
        this.showMealPackage(mealPackage, size);
      } else {
        $([document.documentElement, document.body]).scrollTop(0);
        this.$parent.showMealPage(meal, size ? size.id : null);
        this.$parent.showMealsArea = false;
        this.$parent.showMealPackagesArea = false;
      }
      this.$parent.search = "";
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
    getMealTitle(title) {
      return title.replace(/(\r\n|\n|\r)/gm, "<br />");
    },
    getMacroTitle() {
      let macros = {};
      if (!this.smallScreen) {
        macros.calories = "Calories";
        macros.carbs = "Carbs";
        macros.protein = "Protein";
        macros.fat = "Fat";
      } else {
        macros.calories = "Cal";
        macros.carbs = "C";
        macros.protein = "P";
        macros.fat = "F";
      }

      return macros;
    },
    logImg(meal) {
      console.log(meal.image.url_thumb);
    },
    readMore(meal) {
      let status = this.showFullDescription[meal.id];
      this.$set(this.showFullDescription, meal.id, !status);
    },
    assignedToDeliveryDay(meal) {
      if (
        !this.store.hasDeliveryDayItems ||
        !this.store.modules.multipleDeliveryDays ||
        meal.gift_card
      ) {
        return true;
      }
      if (meal.delivery_day_ids && this.$parent.finalDeliveryDay) {
        if (
          meal.delivery_day_ids.length === 0 ||
          meal.delivery_day_ids.includes(this.$parent.finalDeliveryDay.id) ||
          this.$parent.finalDeliveryDay == null
        ) {
          return true;
        }
      }
    },
    setItemQuantity(type, item, val) {
      switch (type) {
        case "meal":
          this.mealQuantities[item.id] = parseInt(val);
          break;
        case "giftCard":
          this.giftCardQuantities[item.id] = parseInt(val);
          break;
      }
    },
    getMacros(meal, macro) {
      switch (macro) {
        case "calories":
          return meal.macros && meal.macros.calories
            ? meal.macros.calories
            : this.$parent.getNutritionFacts(meal.ingredients, meal)
                .valueCalories;
          break;
        case "carbs":
          return meal.macros && meal.macros.carbs
            ? meal.macros.carbs
            : this.$parent.getNutritionFacts(meal.ingredients, meal)
                .valueTotalCarb;
          break;
        case "protein":
          return meal.macros && meal.macros.protein
            ? meal.macros.protein
            : this.$parent.getNutritionFacts(meal.ingredients, meal)
                .valueProteins;
          break;
        case "fat":
          return meal.macros && meal.macros.fat
            ? meal.macros.fat
            : this.$parent.getNutritionFacts(meal.ingredients, meal)
                .valueTotalFat;
          break;
      }
    }
  }
};
</script>
<style>
.quantity::placeholder {
  padding-left: 14px;
}
</style>
