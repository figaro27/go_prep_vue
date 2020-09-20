<template>
  <b-modal
    v-if="!multDDZipCode"
    v-model="visible"
    size="md"
    no-fade
    no-close-on-backdrop
    no-close-on-esc
    hide-footer
    hide-header
  >
    <h5 class="mb-3 mt-3 center-text" v-if="noAvailableDays">
      Unfortunately we do not deliver to your zip code.
    </h5>
    <center>
      <b-btn
        v-if="noAvailableDays"
        size="lg"
        class="brand-color white-text d-inline"
        @click="setPickup()"
        >Pickup</b-btn
      >
      <b-btn
        size="lg"
        @click="reset"
        class="brand-color white-text d-inline"
        v-if="noAvailableDays"
        >Enter Different Zip Code</b-btn
      >
    </center>
    <h5 class="mb-3 mt-3 center-text" v-if="delivery && !bagZipCode">
      Please enter your delivery zip code.
    </h5>
    <h5 class="mb-3 mt-3 center-text" v-if="!delivery && transferTypes.both">
      Are you ordering for pickup or delivery?
    </h5>
    <b-form class="mt-2 text-center" @submit.prevent="setZipCode">
      <center>
        <b-form-group :state="true" class="d-flex">
          <b-form-input
            v-if="delivery && !bagZipCode"
            placeholder="Zip Code"
            v-model="zipCode"
            class="width-50"
          ></b-form-input>
        </b-form-group>
        <b-btn
          v-if="transferTypes.both && !delivery"
          size="lg"
          class="brand-color white-text d-inline"
          @click="setPickup()"
          >Pickup</b-btn
        >
        <b-btn
          v-if="transferTypes.both && !delivery"
          size="lg"
          class="brand-color white-text d-inline"
          @click="setDelivery()"
          >Delivery</b-btn
        >
        <b-btn
          size="lg"
          type="submit"
          class="brand-color white-text d-inline"
          :disabled="!zipCodeSet"
          v-if="delivery && !noAvailableDays"
          >View Menu</b-btn
        >
      </center>
    </b-form>
  </b-modal>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  props: {
    deliverySelected: {
      default: false
    }
  },
  data() {
    return {
      visible: true,
      zipCode: null,
      delivery: false,
      selected: false,
      noAvailableDays: false,
      updated: false
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      multDDZipCode: "bagMultDDZipCode",
      bagZipCode: "bagZipCode",
      context: "context",
      bagPickup: "bagPickup"
    }),
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
    },
    sortedDeliveryDays() {
      // If delivery_days table has the same day of the week for both pickup & delivery, only show the day once
      let baseDeliveryDays = this.store.delivery_days;
      let deliveryWeeks = this.store.settings.deliveryWeeks;
      let storeDeliveryDays = [];

      for (let i = 0; i <= deliveryWeeks; i++) {
        baseDeliveryDays.forEach(day => {
          let m = moment(day.day_friendly);
          let newDate = moment(m).subtract(i, "week");
          let newDay = { ...day };
          newDay.day_friendly = newDate.format("YYYY-MM-DD");
          storeDeliveryDays.push(newDay);
        });
      }

      storeDeliveryDays = storeDeliveryDays.reverse();

      let sortedDays = [];

      if (this.store.delivery_day_zip_codes.length === 0) {
        sortedDays = _.uniqBy(storeDeliveryDays, "day_friendly");
      } else {
        sortedDays = storeDeliveryDays;
      }

      // If the store only serves certain zip codes on certain delivery days
      if (this.store.delivery_day_zip_codes.length > 0) {
        let deliveryDayIds = [];
        this.store.delivery_day_zip_codes.forEach(ddZipCode => {
          if (ddZipCode.zip_code === parseInt(this.bagZipCode)) {
            deliveryDayIds.push(ddZipCode.delivery_day_id);
          }
        });
        sortedDays = sortedDays.filter(day => {
          if (deliveryDayIds.includes(day.id) || this.bagPickup) {
            return true;
          }
          // return deliveryDayIds.includes(day.id);
        });
      }

      if (this.bagPickup) {
        sortedDays = sortedDays.filter(day => {
          return day.type === "pickup";
        });
      } else {
        sortedDays = sortedDays.filter(day => {
          return day.type === "delivery";
        });
      }

      sortedDays.sort(function(a, b) {
        return new Date(a.day_friendly) - new Date(b.day_friendly);
      });

      return sortedDays;
    }
  },
  created() {},
  mounted() {
    if (this.transferTypes.both) {
      this.hasBothTransferTypes = true;
    } else if (!this.transferTypes.both && this.transferTypes.delivery) {
      this.delivery = true;
    }

    if (this.deliverySelected) {
      this.delivery = true;
    }
  },
  updated() {
    if (
      !this.updated &&
      !this.transferTypes.both &&
      this.transferTypes.pickup
    ) {
      this.visible = false;
    }
  },
  destroyed() {
    this.setMultDDZipCode(1);
  },
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
      this.delivery = true;
      this.setBagZipCode(null);

      if (this.bagZipCode || this.store.delivery_day_zip_codes.length == 0) {
        this.visible = false;
        this.$emit("setAutoPickUpcomingMultDD");
      }
    },
    setZipCode() {
      this.setBagZipCode(this.zipCode);
      this.$emit("setAutoPickUpcomingMultDD");
      if (this.sortedDeliveryDays.length === 0 && !this.bagPickup) {
        this.noAvailableDays = true;
        return;
      }
      this.visible = false;
    },
    reset() {
      this.noAvailableDays = false;
      this.delivery = true;
      this.zipCode = null;
      this.setBagZipCode(null);
    }
  }
};
</script>
