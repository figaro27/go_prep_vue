<template>
  <b-modal
    v-model="visible"
    title=""
    size="md"
    @ok="$emit('ok')"
    @cancel="$emit('cancel')"
    @hidden="$emit('hidden')"
    @hide="editing = false"
    no-fade
    no-close-on-backdrop
    no-close-on-esc
    hide-footer
    hide-header
  >
    <h5 class="mb-2 mt-3 center-text">
      Please select the pickup date for your order.
    </h5>
    <b-form class="mt-2 text-center" @submit.prevent="changeDeliveryDate">
      <b-form-group :state="true">
        <b-select
          v-if="deliveryDateOptions.length >= 1"
          v-model="date"
          :options="deliveryDateOptions"
          class="delivery-select ml-2 width-140"
          required
        >
          <option slot="top" disabled>-- Select delivery day --</option>
        </b-select>
      </b-form-group>

      <b-btn size="lg" type="submit" class="brand-color white-text"
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
      date: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      bagDeliveryDate: "bagDeliveryDate",
      storeSettings: "viewedStoreSettings",
      storeModules: "viewedStoreModules"
    })
  },
  created() {
    this.date = this.deliveryDateOptions[0];
  },
  methods: {
    ...mapActions({
      refreshCategories: "refreshCategories"
    }),
    ...mapMutations({
      setBagDeliveryDate: "setBagDeliveryDate"
    }),
    changeDeliveryDate() {
      this.setBagDeliveryDate(this.date);
      this.visible = false;
    }
  }
};
</script>

<style lang="scss" scoped></style>
