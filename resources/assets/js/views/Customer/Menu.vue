<template>
  <div>
    <floating-action-button class="d-sm-none" to="/customer/bag">
      <i class="fa fa-shopping-bag text-white"></i>
    </floating-action-button>
    <!-- <div class="menu ml-auto mr-auto"> -->
    <div class="menu ml-auto mr-auto">
      <div v-if="!willDeliver && !preview && loggedIn">
        <b-alert variant="danger center-text" show
          >You are outside of the delivery area.</b-alert
        >
      </div>

      <div v-if="storeSettings.open === false">
        <div class="row">
          <div class="col-sm-12 mt-3">
            <div class="card">
              <div class="card-body">
                <h5 class="center-text">
                  This company will not be taking new orders at this time.
                </h5>
                <p class="center-text mt-3">
                  <strong>Reason:</strong>
                  {{ storeSettings.closedReason }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-basic">
        <b-modal
          :title="store.details.name"
          size="lg"
          v-model="showDescriptionModal"
          v-if="showDescriptionModal"
        >
          <p v-html="description"></p>
        </b-modal>
      </div>

      <div class="modal-basic">
        <b-modal
          size="lg"
          v-model="viewFilterModal"
          v-if="viewFilterModal"
          hide-header
        >
          <div>
            <h4 class="center-text mb-5 mt-5">Hide Meals That Contain</h4>
          </div>
          <div class="row mb-4">
            <div
              v-for="allergy in allergies"
              :key="`allergy-${allergy.id}`"
              class="filters col-6 col-sm-4 col-md-3 mb-3"
            >
              <b-button
                :pressed="active[allergy.id]"
                @click="filterByAllergy(allergy.id)"
                >{{ allergy.title }}</b-button
              >
            </div>
          </div>
          <hr />
          <div>
            <h4 class="center-text mb-5">Show Meals With</h4>
          </div>
          <div class="row">
            <div
              v-for="tag in tags"
              :key="`tag-${tag}`"
              class="filters col-6 col-sm-4 col-md-3 mb-3"
            >
              <b-button :pressed="active[tag]" @click="filterByTag(tag)">
                {{ tag }}
              </b-button>
            </div>
          </div>
          <b-button
            @click="clearFilters"
            class="center mt-4 brand-color white-text"
            >Clear All</b-button
          >
        </b-modal>
      </div>

      <div class="row">
        <div class="col-sm-12 mt-3">
          <div class="card">
            <div class="card-body">
              <b-modal
                ref="mealModal"
                size="lg"
                :title="meal.title"
                v-model="mealModal"
                v-if="mealModal"
              >
                <div class="row mt-3">
                  <div class="col-lg-6 modal-meal-image">
                    <thumbnail
                      v-if="meal.image.url"
                      :src="meal.image.url"
                      :aspect="false"
                      width="100%"
                    ></thumbnail>
                    <img v-else :src="meal.featured_image" />
                    <p v-if="storeSettings.showNutrition">
                      {{ meal.description }}
                    </p>
                    <div
                      class="row mt-3 mb-5"
                      v-if="storeSettings.showNutrition"
                    >
                      <div class="col-lg-6">
                        <h5>Tags</h5>
                        <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                      </div>
                      <div class="col-lg-6">
                        <h5>Contains</h5>
                        <li v-for="allergy in meal.allergy_titles">
                          {{ allergy }}
                        </li>
                      </div>
                    </div>

                    <div class="row mt-5" v-if="storeSettings.showNutrition">
                      <div class="col-lg-5 mt-3">
                        <h5>{{ format.money(meal.price) }}</h5>
                      </div>
                      <div class="col-lg-7">
                        <b-btn
                          v-if="meal.sizes.length === 0"
                          @click="addOne(meal)"
                          class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                        <b-dropdown v-else toggle-class="menu-bag-btn">
                          <span slot="button-content">+ ADD</span>
                          <b-dropdown-item @click="addOne(meal)">
                            {{ meal.default_size_title }} -
                            {{ format.money(meal.item_price) }}
                          </b-dropdown-item>
                          <b-dropdown-item
                            v-for="size in meal.sizes"
                            :key="size.id"
                            @click="addOne(meal, false, size)"
                            >{{ size.title }} -
                            {{ format.money(size.price) }}</b-dropdown-item
                          >
                        </b-dropdown>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6" v-if="storeSettings.showNutrition">
                    <div
                      id="nutritionFacts"
                      ref="nutritionFacts"
                      class="mt-2 mt-lg-0"
                    ></div>
                  </div>
                  <div class="col-lg-6" v-if="!storeSettings.showNutrition">
                    <p>{{ meal.description }}</p>
                    <div class="row">
                      <div class="col-lg-6">
                        <h5>Tags</h5>
                        <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                      </div>
                      <div class="col-lg-6">
                        <h5>Contains</h5>
                        <li v-for="allergy in meal.allergy_titles">
                          {{ allergy }}
                        </li>
                      </div>
                    </div>
                    <div
                      class="row mt-3 mb-3"
                      v-if="storeSettings.showIngredients"
                    >
                      <div class="col-lg-12">
                        <h5>Ingredients</h5>
                        {{ ingredients }}
                      </div>
                    </div>
                    <div class="row mt-5" v-if="storeSettings.showNutrition">
                      <div class="col-lg-8">
                        <h5>{{ format.money(meal.price) }}</h5>
                      </div>
                      <div class="col-lg-4">
                        <b-btn @click="addOne(meal)" class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                      </div>
                    </div>
                    <div class="row mt-5" v-if="!storeSettings.showNutrition">
                      <div class="col-lg-6">
                        <h5>{{ format.money(meal.price) }}</h5>
                      </div>
                      <div class="col-lg-6">
                        <b-btn
                          v-if="meal.sizes.length === 0"
                          @click="addOne(meal)"
                          class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                        <b-dropdown v-else toggle-class="menu-bag-btn">
                          <span slot="button-content">+ ADD</span>
                          <b-dropdown-item @click="addOne(meal)">
                            {{ meal.default_size_title }} -
                            {{ format.money(meal.item_price) }}
                          </b-dropdown-item>
                          <b-dropdown-item
                            v-for="size in meal.sizes"
                            :key="size.id"
                            @click="addOne(meal, false, size)"
                            >{{ size.title }} -
                            {{ format.money(size.price) }}</b-dropdown-item
                          >
                        </b-dropdown>
                      </div>
                    </div>
                  </div>
                </div>
              </b-modal>

              <b-modal
                ref="mealPackageModal"
                size="lg"
                :title="mealPackage.title"
                v-model="mealPackageModal"
                v-if="mealPackageModal"
                @shown="$forceUpdate()"
                :hide-footer="true"
              >
                <carousel
                  ref="carousel"
                  :perPage="1"
                  @mounted="
                    () => {
                      loaded = true;
                    }
                  "
                >
                  <slide>
                    <div v-if="loaded" class="row">
                      <div class="col-lg-6 modal-meal-image">
                        <thumbnail
                          v-if="mealPackage.image.url"
                          :src="mealPackage.image.url"
                          :aspect="false"
                          width="100%"
                          :spinner="false"
                          :lazy="false"
                        ></thumbnail>
                        <img v-else :src="mealPackage.featured_image" />
                      </div>
                      <div class="col-lg-6">
                        <div class="modal-meal-package-description mt-2">
                          <p>{{ mealPackage.description }}</p>
                        </div>
                        <div class="modal-meal-package-meals">
                          <h5 class="mt-2">Meals</h5>

                          <li v-for="meal in mealPackage.meals" :key="meal.id">
                            {{ meal.title }} x {{ meal.quantity }}
                          </li>

                          <div class="modal-meal-package-price">
                            <h5 class="mt-3 mb-3">
                              {{ format.money(mealPackage.price) }}
                            </h5>
                          </div>
                          <b-btn
                            @click="addOne(mealPackage)"
                            class="menu-bag-btn width-80"
                            >+ ADD</b-btn
                          >
                        </div>
                      </div>
                    </div>
                  </slide>

                  <!-- Text slides with image -->
                  <slide
                    v-for="meal in mealPackage.meals"
                    :key="meal.id"
                    :caption="meal.title"
                  >
                    <div class="row">
                      <div class="col-lg-6 modal-meal-image">
                        <h4 class="center-text">{{ meal.title }}</h4>
                        <thumbnail
                          v-if="meal.image.url"
                          :src="meal.image.url"
                          :aspect="false"
                          width="100%"
                          :lazy="false"
                          :spinner="false"
                        ></thumbnail>
                        <img v-else :src="meal.featured_image" />
                        <p v-if="storeSettings.showNutrition">
                          {{ meal.description }}
                        </p>
                        <div
                          class="row mt-3 mb-5"
                          v-if="storeSettings.showNutrition"
                        >
                          <div class="col-lg-6">
                            <h5>Tags</h5>
                            <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                          </div>
                          <div class="col-lg-6">
                            <h5>Contains</h5>
                            <li v-for="allergy in meal.allergy_titles">
                              {{ allergy }}
                            </li>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6" v-if="storeSettings.showNutrition">
                        <div
                          :id="`nutritionFacts${meal.id}`"
                          :ref="`nutritionFacts${meal.id}`"
                          class="mt-2 mt-lg-0"
                        ></div>
                        <b-btn
                          @click="addOne(mealPackage)"
                          class="menu-bag-btn width-80 mt-3"
                          >+ ADD PACKAGE</b-btn
                        >
                      </div>
                      <div class="col-lg-6" v-if="!storeSettings.showNutrition">
                        <p>{{ meal.description }}</p>
                        <div class="row">
                          <div class="col-lg-6">
                            <h5>Tags</h5>
                            <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                          </div>
                          <div class="col-lg-6">
                            <h5>Contains</h5>
                            <li v-for="allergy in meal.allergy_titles">
                              {{ allergy }}
                            </li>
                          </div>
                        </div>
                        <div
                          class="row mt-3 mb-3"
                          v-if="storeSettings.showIngredients"
                        >
                          <div class="col-lg-12">
                            <h5>Ingredients</h5>
                            {{ ingredients }}
                          </div>
                        </div>
                        <b-btn
                          @click="addOne(mealPackage)"
                          class="menu-bag-btn width-80 mt-3"
                          >+ ADD PACKAGE</b-btn
                        >
                      </div>
                    </div>
                  </slide>
                </carousel>
              </b-modal>

              <div class="row">
                <div class="col-sm-12">
                  <div class="row">
                    <div class="col-sm-12 store-logo-area">
                      <img
                        v-if="storeLogo"
                        class="store-logo"
                        :src="storeLogo"
                        alt="Company Logo"
                      />
                    </div>
                    <div class="col-sm-12" v-if="store.details.description">
                      <b-btn
                        @click="showDescription"
                        class="brand-color white-text center mt-3"
                        >About</b-btn
                      >
                    </div>
                    <div class="col-sm-12 category-area">
                      <div class="filter-area">
                        <b-button
                          @click="viewFilters"
                          class="brand-color white-text"
                        >
                          <i class="fa fa-filter"></i>
                          <span class="d-none d-sm-inline">&nbsp;Filters</span>
                        </b-button>
                        <b-button @click="clearFilters" class="gray white-text">
                          <i class="fa fa-eraser"></i>
                          <span class="d-none d-sm-inline"
                            >&nbsp;Clear Filters</span
                          >
                        </b-button>
                      </div>

                      <ul>
                        <li
                          v-for="category in categories"
                          :key="category"
                          @click="goToCategory(category)"
                          class="ml-4"
                        >
                          {{ category }}
                        </li>
                      </ul>

                      <div>
                        <b-btn
                          class="gray white-text pull-right"
                          @click="clearAll"
                        >
                          <i class="fa fa-eraser"></i>&nbsp;Clear Bag
                        </b-btn>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div :class="`col-md-9 main-menu-area`">
                  <Spinner v-if="!meals.length" position="absolute" />
                  <div
                    v-for="group in meals"
                    :key="group.category"
                    :id="group.category"
                    class="categories"
                  >
                    <h2 class="text-center mb-3 dbl-underline">
                      {{ group.category }}
                    </h2>
                    <div class="row">
                      <div
                        class="col-sm-6 col-lg-4 col-xl-3"
                        v-for="(meal, i) in group.meals"
                        :key="meal.id"
                      >
                        <thumbnail
                          v-if="meal.image.url_medium"
                          :src="meal.image.url_medium"
                          class="menu-item-img"
                          width="100%"
                          @click="showMealModal(meal)"
                          style="background-color:#ffffff"
                        ></thumbnail>
                        <div
                          class="d-flex justify-content-between align-items-center mb-2 mt-1"
                        >
                          <b-btn
                            @click="minusOne(meal)"
                            class="plus-minus gray"
                          >
                            <i>-</i>
                          </b-btn>
                          <!-- <img src="/images/customer/minus.jpg" @click="minusOne(meal)" class="plus-minus"> -->
                          <b-form-input
                            type="text"
                            name
                            id
                            class="quantity"
                            :value="quantity(meal)"
                            readonly
                          ></b-form-input>
                          <b-btn
                            v-if="meal.sizes.length === 0"
                            @click="addOne(meal)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>
                          <b-dropdown
                            v-else
                            toggle-class="menu-bag-btn plus-minus"
                            :right="i > 0 && (i + 1) % 4 === 0"
                          >
                            <i slot="button-content">+</i>
                            <b-dropdown-item @click="addOne(meal)">
                              {{ meal.default_size_title }} -
                              {{ format.money(meal.item_price) }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              v-for="size in meal.sizes"
                              :key="size.id"
                              @click="addOne(meal, false, size)"
                              >{{ size.title }} -
                              {{ format.money(size.price) }}</b-dropdown-item
                            >
                          </b-dropdown>

                          <!-- <img src="/images/customer/plus.jpg" @click="addOne(meal)" class="plus-minus"> -->
                        </div>
                        <p class="center-text strong featured">
                          {{ meal.title }}
                        </p>
                        <p class="center-text featured">
                          {{ format.money(meal.price) }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    v-if="storeSettings.meal_packages && mealPackages.length"
                    id="Packages"
                  >
                    <h2 class="text-center mb-3 dbl-underline">Packages</h2>

                    <div class="row">
                      <div
                        class="col-sm-6 col-lg-4 col-xl-3"
                        v-for="mealPkg in mealPackages"
                        :key="mealPkg.id"
                      >
                        <thumbnail
                          v-if="mealPkg.image.url_medium"
                          :src="mealPkg.image.url_medium"
                          class="menu-item-img"
                          width="100%"
                          @click="showMealPackageModal(mealPkg)"
                          style="background-color:#ffffff"
                        ></thumbnail>
                        <div
                          class="d-flex justify-content-between align-items-center mb-2 mt-1"
                        >
                          <b-btn
                            @click="minusOne(mealPkg, true)"
                            class="plus-minus gray"
                          >
                            <i>-</i>
                          </b-btn>
                          <!-- <img src="/images/customer/minus.jpg" @click="minusOne(meal)" class="plus-minus"> -->
                          <b-form-input
                            type="text"
                            name
                            id
                            class="quantity"
                            :value="quantity(mealPkg, true)"
                            readonly
                          ></b-form-input>
                          <b-btn
                            @click="addOne(mealPkg, true)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>
                          <!-- <img src="/images/customer/plus.jpg" @click="addOne(meal)" class="plus-minus"> -->
                        </div>
                        <p class="center-text strong featured">
                          {{ mealPkg.title }}
                        </p>
                        <p class="center-text featured">
                          {{ format.money(mealPkg.price) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-sm-5 col-md-3 bag-area">
                  <ul class="list-group">
                    <li
                      v-for="(item, mealId) in bag"
                      :key="`bag-${mealId}`"
                      class="bag-item"
                    >
                      <div
                        v-if="item && item.quantity > 0"
                        class="d-flex align-items-center"
                      >
                        <div class="bag-item-quantity mr-2">
                          <div
                            @click="addOne(item.meal, false, item.size)"
                            class="bag-plus-minus brand-color white-text"
                          >
                            <i>+</i>
                          </div>
                          <p class="bag-quantity">{{ item.quantity }}</p>
                          <div
                            @click="minusOne(item.meal, false, item.size)"
                            class="bag-plus-minus gray white-text"
                          >
                            <i>-</i>
                          </div>
                        </div>
                        <div class="bag-item-image mr-2">
                          <thumbnail
                            :src="item.meal.image.url_thumb"
                            :spinner="false"
                            width="80px"
                          ></thumbnail>
                        </div>
                        <div class="flex-grow-1 mr-2">
                          <div v-if="item.size">
                            {{ item.size.full_title }}
                          </div>
                          <div v-else>{{ item.meal.item_title }}</div>
                        </div>
                        <div class="flex-grow-0">
                          <img
                            src="/images/customer/x.png"
                            @click="clearMeal(item.meal, false, item.size)"
                            class="clear-meal"
                          />
                        </div>
                      </div>
                    </li>
                  </ul>

                  <p
                    class="align-right"
                    v-if="
                      minOption === 'meals' &&
                        total < minimumMeals &&
                        !manualOrder
                    "
                  >
                    Please add {{ remainingMeals }} {{ singOrPlural }} to
                    continue.
                  </p>

                  <p class="align-right">
                    <strong>Subtotal:&nbsp;</strong>
                    {{ format.money(preFeePreDiscount) }}
                  </p>

                  <div
                    v-if="
                      minOption === 'meals' &&
                        total >= minimumMeals &&
                        !preview &&
                        !manualOrder
                    "
                  >
                    <router-link to="/customer/bag" v-if="!subscriptionId">
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                    <b-btn
                      v-else
                      class="menu-bag-btn"
                      @click="updateSubscriptionMeals"
                      >UPDATE MEALS</b-btn
                    >
                  </div>
                  <div
                    v-if="
                      minOption === 'price' &&
                        totalBagPricePreFees < minPrice &&
                        !manualOrder
                    "
                  >
                    <p class="align-right">
                      Please add {{ format.money(remainingPrice) }} more to
                      continue.
                    </p>
                  </div>
                  <div
                    v-if="
                      minOption === 'price' &&
                        totalBagPrice >= minPrice &&
                        !preview &&
                        !manualOrder
                    "
                  >
                    <router-link to="/customer/bag" v-if="!subscriptionId">
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                    <b-btn
                      v-else
                      class="menu-bag-btn"
                      @click="updateSubscriptionMeals"
                      >UPDATE MEALS</b-btn
                    >
                  </div>
                  <div v-if="manualOrder">
                    <div
                      v-if="transferTypeCheck === 'both'"
                      class="center-text"
                    >
                      <b-form-group>
                        <b-form-radio-group v-model="pickup" name="pickup">
                          <b-form-radio :value="0" @click="pickup = 0">
                            <strong>Delivery</strong>
                          </b-form-radio>
                          <b-form-radio :value="1" @click="pickup = 1">
                            <strong>Pickup</strong>
                          </b-form-radio>
                        </b-form-radio-group>
                      </b-form-group>
                    </div>
                    <div>
                      <p
                        v-if="pickup === 0 && transferTypeCheck !== 'pickup'"
                        class="center-text"
                      >
                        Delivery Day
                      </p>
                      <p
                        v-if="pickup === 1 || transferTypeCheck === 'pickup'"
                        class="center-text"
                      >
                        Pickup Day
                      </p>
                      <b-form-group
                        v-if="deliveryDaysOptions.length > 1"
                        description
                      >
                        <b-select
                          :options="deliveryDaysOptions"
                          v-model="deliveryDay"
                          class="bag-select"
                          required
                        >
                          <option slot="top" disabled
                            >-- Select delivery day --</option
                          >
                        </b-select>
                      </b-form-group>
                      <div v-else-if="deliveryDaysOptions.length === 1">
                        <p>Delivery day: {{ deliveryDaysOptions[0].text }}</p>
                      </div>
                    </div>

                    <b-form-group description>
                      <p class="center-text mt-3">Choose Customer</p>
                      <b-select
                        :options="customers"
                        v-model="customer"
                        class="bag-select"
                        required
                      >
                        <option slot="top" disabled
                          >-- Select Customer --</option
                        >
                      </b-select>
                    </b-form-group>

                    <b-btn
                      variant="success"
                      v-if="storeSettings.applyDeliveryFee"
                      @click="addDeliveryFee = true"
                      class="center"
                      >Add Delivery Fee</b-btn
                    >
                    <b-btn
                      variant="success"
                      v-if="storeSettings.applyProcessingFee"
                      @click="addProcessingFee = true"
                      class="center mt-2"
                      >Add Processing Fee</b-btn
                    >

                    <div>
                      <h6
                        class="mt-2 center-text"
                        v-if="!addDeliveryFee && !addProcessingFee"
                      >
                        Total: {{ format.money(totalBagPriceBeforeFees) }}
                      </h6>
                      <p
                        class="mt-2 center-text"
                        v-if="addDeliveryFee || addProcessingFee"
                      >
                        Subtotal: {{ format.money(totalBagPriceBeforeFees) }}
                      </p>
                      <p class="mt-2 center-text" v-if="addDeliveryFee">
                        Delivery Fee:
                        {{ format.money(storeSettings.deliveryFee) }}
                      </p>
                      <p class="mt-2 center-text" v-if="addProcessingFee">
                        Processing Fee:
                        {{ format.money(storeSettings.processingFee) }}
                      </p>
                      <h6
                        class="mt-2 center-text"
                        v-if="addDeliveryFee || addProcessingFee"
                      >
                        Total: {{ format.money(totalBagPriceAfterFees) }}
                      </h6>
                    </div>
                    <b-btn class="menu-bag-btn mt-2" @click="checkout"
                      >Create Manual Order</b-btn
                    >
                  </div>
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
import { mapGetters, mapActions, mapMutations } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";
import Spinner from "../../components/Spinner";
import units from "../../data/units";
import nutrition from "../../data/nutrition";
import format from "../../lib/format";
import SalesTax from "sales-tax";
import keyboardJS from "keyboardjs";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

export default {
  components: {
    Spinner
  },
  props: {
    preview: {
      default: false
    },
    manualOrder: {
      default: false
    },
    subscriptionId: {
      default: null
    },
    SalesTax
  },
  data() {
    return {
      loaded: false,
      salesTaxRate: 0,
      active: {},
      loading: false,
      pickup: 0,
      deliveryDay: undefined,
      deliveryPlan: false,
      addDeliveryFee: false,
      addProcessingFee: false,
      customer: undefined,
      viewFilterModal: false,
      showDescriptionModal: false,
      filteredView: false,
      filters: {
        tags: [],
        allergies: []
      },
      //bag: {},
      meal: null,
      mealPackage: null,
      ingredients: "",
      mealModal: false,
      mealPackageModal: false,
      calories: null,
      totalfat: null,
      satfat: null,
      transfat: null,
      cholesterol: null,
      sodium: null,
      totalcarb: null,
      fibers: null,
      sugars: null,
      proteins: null,
      vitamind: null,
      potassium: null,
      calcium: null,
      iron: null,
      addedsugars: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice"
    }),
    description() {
      return this.store.details.description;
    },
    nutrition() {
      return nutrition;
    },
    storeSettings() {
      return this.store.settings;
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
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "meals";
      }
      return "meal";
    },
    totalBagPriceBeforeFees() {
      let deliveryFee = this.storeSettings.deliveryFee;
      let processingFee = this.storeSettings.processingFee;
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;

      if (applyDeliveryFee && applyProcessingFee) {
        return this.totalBagPrice - deliveryFee - processingFee;
      } else if (applyDeliveryFee && !applyProcessingFee) {
        return this.totalBagPrice - deliveryFee;
      } else if (applyProcessingFee && !applyDeliveryFee) {
        return this.totalBagPrice - processingFee;
      } else return this.totalBagPrice;
    },
    totalBagPriceAfterFees() {
      let deliveryFee = this.storeSettings.deliveryFee;
      let processingFee = this.storeSettings.processingFee;

      if (this.addDeliveryFee && this.addProcessingFee) {
        return this.totalBagPriceBeforeFees + deliveryFee + processingFee;
      } else if (this.addDeliveryFee && !this.addProcessingFee) {
        return this.totalBagPriceBeforeFees + deliveryFee;
      } else if (this.addProcessingFee && !this.addDeliveryFee) {
        return this.totalBagPriceBeforeFees + processingFee;
      } else return this.totalBagPriceBeforeFees;
    },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheck() {
      if (
        _.includes(this.transferType, "delivery") &&
        _.includes(this.transferType, "pickup")
      ) {
        return "both";
      }
      if (
        !_.includes(this.transferType, "delivery") &&
        _.includes(this.transferType, "pickup")
      ) {
        return "pickup";
      }
    },
    deliveryDaysOptions() {
      return this.storeSetting("next_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        };
      });
    },
    meals() {
      let meals = this.store.meals;
      let filters = this.filters;
      let grouped = {};

      if (!_.isArray(meals)) {
        return [];
      }

      meals = _.filter(meals, meal => {
        return meal.active;
      });

      if (this.filteredView) {
        meals = _.filter(meals, meal => {
          let skip = false;

          if (!skip && filters.tags.length > 0) {
            let hasAllTags = _.reduce(
              filters.tags,
              (has, tag) => {
                if (!has) return false;
                let x = _.includes(meal.tag_titles, tag);

                return x;
              },
              true
            );

            skip = !hasAllTags;
          }

          if (!skip && filters.allergies.length > 0) {
            let hasAllergy = _.reduce(
              meal.allergy_ids,
              (has, allergyId) => {
                if (has) return true;
                let x = _.includes(filters.allergies, allergyId);

                return x;
              },
              false
            );

            skip = hasAllergy;
          }
          return !skip;
        });
      }

      meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (!_.has(grouped, category.category)) {
            grouped[category.category] = [meal];
          } else {
            grouped[category.category].push(meal);
          }
        });
      });

      // Find store-defined category sorting
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      // Structure
      grouped = _.map(grouped, (meals, cat) => {
        return {
          category: cat,
          meals,
          order: sorting[cat] || 9999
        };
      });

      // Sort
      return _.orderBy(grouped, "order");
    },
    mealPackages() {
      return (
        _.map(this.store.packages, mealPackage => {
          mealPackage.meal_package = true;
          return mealPackage;
        }) || []
      );
    },
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (!_.includes(grouped, category.category)) {
            grouped.push(category.category);
          }
        });
      });

      let categories = _.orderBy(grouped, cat => {
        return cat in sorting ? sorting[cat] : 9999;
      });

      if (this.storeSettings.meal_packages && this.mealPackages.length) {
        categories.push("Packages");
      }

      return categories;
    },
    tags() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.tags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      });
      return grouped;
    },
    customers() {
      let customers = this.storeCustomers;
      let grouped = {};
      customers.forEach(customer => {
        grouped[customer.id] = customer.name;
      });
      return grouped;
    },
    showIngredients() {
      return this.storeSettings.showIngredients;
    },
    preFeePreDiscount() {
      let subtotal = this.totalBagPricePreFees;
      return subtotal;
    }
  },
  mounted() {
    try {
      this.getSalesTax(this.store.details.state);
    } catch (e) {}
    keyboardJS.bind("left", () => {
      if (this.$refs.carousel) {
        console.log(this.$refs.carousel);
        this.$refs.carousel.handleNavigation("backward");
      }
    });
    keyboardJS.bind("right", () => {
      if (this.$refs.carousel) {
        this.$refs.carousel.handleNavigation("forward");
      }
    });
  },
  beforeDestroy() {
    this.showActiveFilters();
  },
  methods: {
    ...mapActions(["refreshSubscriptions", "emptyBag"]),
    showActiveFilters() {
      let tags = this.tags;
      this.active = tags.reduce((acc, tag) => {
        acc[tag] = false;
        return acc;
      }, {});

      let allergies = this.allergies;
      this.active = _.reduce(
        allergies,
        (acc, allergy) => {
          acc[allergy] = false;
          return acc;
        },
        {}
      );
    },
    quantity(meal, mealPackage = false, size = null) {
      const qty = this.$store.getters.bagItemQuantity(meal, mealPackage, size);
      return qty;
    },
    addOne(meal, mealPackage = false, size = null) {
      this.$store.commit("addToBag", { meal, quantity: 1, mealPackage, size });
      this.mealModal = false;
      this.mealPackageModal = false;
    },
    minusOne(meal, mealPackage = false, size = null) {
      this.$store.commit("removeFromBag", {
        meal,
        quantity: 1,
        mealPackage,
        size
      });
    },
    clearMeal(meal, mealPackage = false, size = null) {
      let quantity = this.quantity(meal, mealPackage, size);
      this.$store.commit("removeFromBag", {
        meal,
        quantity,
        mealPackage,
        size
      });
    },
    clearAll() {
      this.$store.commit("emptyBag");
    },
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    showMealModal(meal) {
      this.meal = meal;
      this.mealModal = true;

      this.$nextTick(() => {
        this.getNutritionFacts(this.meal.ingredients, this.meal);
      });
    },
    showMealPackageModal(mealPackage) {
      this.mealPackage = { ...mealPackage };
      this.mealPackageModal = true;

      this.$nextTick(() => {
        this.mealPackage.meals.forEach(meal => {
          this.getNutritionFacts(
            meal.ingredients,
            meal,
            this.$refs[`nutritionFacts${meal.id}`]
          );
        });
      });

      //this.$nextTick(() => {
      //  this.getNutritionFacts(this.meal.ingredients, this.meal);
      //});
    },
    getNutritionFacts(ingredients, meal, ref = null) {
      const nutrition = this.nutrition.getTotals(ingredients);
      const ingredientList = this.nutrition.getIngredientList(ingredients);

      if (!ref) {
        ref = this.$refs.nutritionFacts;
      }

      $(ref).html("");

      $(ref).nutritionLabel({
        showServingUnitQuantity: false,
        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: this.showIngredients,

        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,

        allowFDARounding: true,
        decimalPlacesForNutrition: 2,

        showPolyFat: false,
        showMonoFat: false,

        valueCalories: nutrition.calories,
        valueFatCalories: nutrition.fatCalories,
        valueTotalFat: nutrition.totalFat,
        valueSatFat: nutrition.satFat,
        valueTransFat: nutrition.transFat,
        valueCholesterol: nutrition.cholesterol,
        valueSodium: nutrition.sodium,
        valueTotalCarb: nutrition.totalCarb,
        valueFibers: nutrition.fibers,
        valueSugars: nutrition.sugars,
        valueProteins: nutrition.proteins,
        valueVitaminD: (nutrition.vitaminD / 20000) * 100,
        valuePotassium_2018: (nutrition.potassium / 4700) * 100,
        valueCalcium: (nutrition.calcium / 1300) * 100,
        valueIron: (nutrition.iron / 18) * 100,
        valueAddedSugars: nutrition.addedSugars,
        showLegacyVersion: false
      });
    },
    addBagItems(bag) {
      this.$store.commit("addBagItems", bag);
    },
    filterByCategory(category) {
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.categories, cat => {
        return cat === category;
      });

      i === -1
        ? this.filters.categories.push(category)
        : Vue.delete(this.filters.categories, i);
    },
    filterByTag(tag) {
      this.active[tag] = !this.active[tag];
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.tags, _tag => {
        return tag === _tag;
      });

      i === -1 ? this.filters.tags.push(tag) : Vue.delete(this.filters.tags, i);
    },
    filterByAllergy(allergyId) {
      Vue.set(this.active, allergyId, !this.active[allergyId]);
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.allergies, _allergyId => {
        return _allergyId === allergyId;
      });

      if (i === -1) {
        let allergies = [...this.filters.allergies];
        allergies.push(allergyId);
        Vue.set(this.filters, "allergies", allergies);
      } else {
        Vue.delete(this.filters.allergies, i);
      }
    },
    goToCategory(category) {
      window.location.href = "#" + category;
    },
    viewFilters() {
      this.viewFilterModal = true;
    },
    clearFilters() {
      let allergies = this.filters.allergies;
      _.remove(allergies, allergy => _.includes(allergies, allergy));

      let tags = this.filters.tags;
      _.remove(tags, tag => _.includes(tags, tag));

      this.active = _.mapValues(this.active, () => false);
      this.filteredView = false;
    },
    checkout() {
      this.loading = true;
      axios
        .post("/api/bag/checkout", {
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: this.card,
          store_id: this.store.id
        })
        .then(async resp => {
          this.emptyBag();
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors)) || [
            "Please try again"
          ];
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async updateSubscriptionMeals() {
      try {
        const { data } = await axios.post(
          `/api/me/subscriptions/${this.subscriptionId}/meals`,
          { bag: this.bag, salesTaxRate: this.salesTaxRate }
        );
        await this.refreshSubscriptions();
        this.emptyBag();
        this.$router.push({
          path: "/customer/meal-plans",
          query: {
            updated: true
          }
        });
      } catch (e) {
        if (!_.isEmpty(e.response.data.error)) {
          this.$toastr.e(e.response.data.error);
        } else {
          this.$toastr.e(
            "Please try again or contact our support team",
            "Failed to update meals!"
          );
        }
        return;
      }
    },
    getSalesTax(state) {
      SalesTax.getSalesTax("US", state).then(tax => {
        this.setSalesTax(tax.rate);
      });
    },
    setSalesTax(rate) {
      this.salesTaxRate = rate;
    },
    showDescription() {
      this.showDescriptionModal = true;
    }
  }
};
</script>
