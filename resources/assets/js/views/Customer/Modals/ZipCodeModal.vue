<template>
  <b-modal
    v-model="visible"
    size="md"
    no-fade
    no-close-on-backdrop
    no-close-on-esc
    hide-footer
    hide-header
  >
    <h5 class="mb-3 mt-3 center-text">
      Please enter your delivery zip code.
    </h5>
    <b-form class="mt-2 text-center" @submit.prevent="setZipCode">
      <center>
        <b-form-group :state="true" class="d-flex">
          <b-form-input
            placeholder="Zip Code"
            v-model="zipCode"
            class="width-50"
          ></b-form-input>
        </b-form-group>
      </center>
      <b-btn
        size="lg"
        type="submit"
        class="brand-color white-text"
        :disabled="!zipCode"
        >View Menu</b-btn
      >
    </b-form>
  </b-modal>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  data() {
    return {
      visible: true,
      zipCode: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    })
  },
  created() {},
  methods: {
    ...mapMutations({
      setBagZipCode: "setBagZipCode"
    }),
    setZipCode() {
      this.setBagZipCode(this.zipCode);
      this.visible = false;
      this.$parent.autoPickUpcomingMultDD(this.$parent.sortedDeliveryDays);
    }
  }
};
</script>
