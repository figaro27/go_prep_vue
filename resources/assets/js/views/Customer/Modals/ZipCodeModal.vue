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
    <h5 class="mb-3 mt-3 center-text" v-if="delivery && !bagZipCode">
      Please enter your delivery zip code.
    </h5>
    <h5 class="mb-3 mt-3 center-text" v-if="!delivery">
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
          v-if="!delivery"
          size="lg"
          class="brand-color white-text d-inline"
          @click="setPickup()"
          >Pickup</b-btn
        >
        <b-btn
          v-if="!delivery"
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
          v-if="delivery"
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
      selected: false
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      multDDZipCode: "bagMultDDZipCode",
      bagZipCode: "bagZipCode",
      context: "context"
    }),
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
    if (this.deliverySelected) {
      this.delivery = true;
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
      if (this.context == "store") {
        this.setBagZipCode(null);
      }
      if (this.bagZipCode) {
        this.visible = false;
        this.$emit("setAutoPickUpcomingMultDD");
      }
    },
    setZipCode() {
      this.setBagZipCode(this.zipCode);
      this.visible = false;
      this.$emit("setAutoPickUpcomingMultDD");
    }
  }
};
</script>
