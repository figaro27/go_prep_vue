<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-12">
        <div style="position: relative">
          <center>
            <div v-if="showOtherDaysMessage">
              <h4 class="mb-3">
                Would you like to add items to any other days before proceeding?
              </h4>
              <div class="d-flex" style="justify-content:center">
                <b-btn
                  variant="primary"
                  size="lg"
                  class="mb-5 mt-3 mr-3"
                  @click="addMoreDays = true"
                  >Add Day</b-btn
                >
                <b-btn
                  variant="success"
                  size="lg"
                  class="mb-5 mt-3"
                  @click="$emit('continueToCheckout', true)"
                  >Continue To Checkout</b-btn
                >
              </div>
            </div>

            <b-form-radio-group
              v-if="
                isMultipleDelivery &&
                  hasBothTranserTypes &&
                  !showOtherDaysMessage
              "
              buttons
              class="storeFilters mb-2"
              v-model="transferDayType"
              :options="[
                { value: 1, text: 'Pickup' },
                { value: 0, text: 'Delivery' }
              ]"
              @change="val => changeTransferType(val)"
            ></b-form-radio-group>
          </center>
          <h4
            class="center-text mt-2 mb-2"
            v-if="
              (sortedDeliveryDays.length > 0 && !showOtherDaysMessage) ||
                addMoreDays
            "
          >
            Select Day
          </h4>

          <Spinner
            v-if="isLoadingDeliveryDays"
            position="relative"
            style="left: 0;"
          />
          <div
            class="delivery_day_wrap mt-3"
            v-if="!showOtherDaysMessage || addMoreDays"
          >
            <div
              @click="$emit('changeDeliveryDay', day)"
              v-for="day in sortedDeliveryDays"
              v-bind:class="
                selectedDeliveryDay &&
                selectedDeliveryDay.day_friendly == day.day_friendly
                  ? 'delivery_day_item active'
                  : 'delivery_day_item'
              "
              :style="getBrandColor(day)"
            >
              <center>
                ({{ day.type.charAt(0).toUpperCase() + day.type.slice(1) }})
                {{ moment(day.day_friendly).format("dddd, MMM Do YYYY") }}
              </center>
            </div>
            <div
              v-if="
                sortedDeliveryDays.length === 0 &&
                  store.delivery_day_zip_codes.length > 0
              "
            ></div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="hasDeliveryDayZipCodes && !bagPickup">
      <h5 class="mb-3 center-text" v-if="noAvailableDays && !showPostalCodeBox">
        Unfortunately we do not deliver to your postal code.
      </h5>
      <div v-if="!loggedIn">
        <center>
          <b-btn
            variant="primary"
            v-if="bagZipCode && !bagPickup && !showPostalCodeBox"
            @click="changeZipCode()"
            class="mt-3 mb-2"
            >Change Postal Code</b-btn
          >
        </center>

        <h5 class="mb-1 center-text" v-if="!bagPickup && !bagZipCode">
          Please enter your delivery postal code.
        </h5>

        <b-form class="mt-2 text-center" @submit.prevent="setZipCode">
          <center>
            <b-form-group :state="true" class="d-flex d-center">
              <b-form-input
                v-if="(!bagPickup && !bagZipCode) || showPostalCodeBox"
                placeholder="Postal Code"
                v-model="zipCode"
                class="width-100px mt-1"
              ></b-form-input>
              <b-btn
                v-if="
                  (zipCode && zipCode.length >= 0 && !bagZipCode) ||
                    showPostalCodeBox
                "
                variant="primary"
                class="mt-2"
                @click="setZipCode"
                >Submit</b-btn
              >
            </b-form-group>
          </center>
        </b-form>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  watch: {
    bagZipCode: function(val) {
      this.zipCode = val;
    }
  },
  mixins: [MenuBag],
  props: {
    selectedDeliveryDay: {
      default: null
    },
    deliverySelected: {
      default: false
    },
    showOtherDaysMessage: {
      default: false
    }
  },
  data() {
    return {
      addMoreDays: false,
      zipCode: null,
      selected: false,
      updated: false
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      multDDZipCode: "bagMultDDZipCode",
      bagZipCode: "bagZipCode",
      context: "context",
      bagPickup: "bagPickup",
      loggedIn: "loggedIn"
    }),
    showPostalCodeBox() {
      if (!this.bagPickup && !this.bagZipCode) {
        return true;
      }
      return false;
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

      let hasBoth = hasDelivery && hasPickup ? true : false;

      return {
        delivery: hasDelivery,
        pickup: hasPickup,
        both: hasBoth
      };
    },
    zipCodeSet() {
      if (this.zipCode && this.zipCode.length == 5) {
        return true;
      } else {
        return false;
      }
    }
  },
  created() {},
  mounted() {
    this.zipCode = this.bagZipCode;
  },
  updated() {},
  methods: {
    ...mapMutations({
      setBagZipCode: "setBagZipCode",
      setBagPickup: "setBagPickup",
      setMultDDZipCode: "setMultDDZipCode"
    }),
    setPickup() {
      this.$store.commit("emptyBag");
      this.setBagPickup(1);
      this.setZipCode();
    },
    setDelivery() {
      this.$store.commit("emptyBag");
      this.setBagPickup(0);
      this.setBagZipCode(null);

      if (this.bagZipCode || this.store.delivery_day_zip_codes.length == 0) {
        this.$emit("autoPickUpcomingMultDD");
      }
    },
    setZipCode() {
      this.setBagZipCode(this.zipCode);
      if (this.noAvailableDays) {
        return;
      } else {
        this.$emit("autoPickUpcomingMultDD");
        this.$emit("closeDeliveryDayModal");
      }
    },
    reset() {
      this.zipCode = null;
      this.setBagZipCode(null);
    },
    changeZipCode() {
      this.setBagZipCode(null);
      this.zipCode = null;
    },
    changeTransferType(val) {
      this.$store.commit("emptyBag");
      this.setBagPickup(val);

      if (
        !this.bagZipCode &&
        val == 0 &&
        this.store.delivery_day_zip_codes.length > 0
      ) {
      }
      this.$emit("setAutoPickUpcomingMultDD");
    }
  }
};
</script>
