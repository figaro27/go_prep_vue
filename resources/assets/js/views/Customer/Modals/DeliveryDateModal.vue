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
    <h4>Please select the delivery/pickup date for your order.</h4>
    <b-form class="mt-2 text-center" @submit.prevent="changeDeliveryDate">
      <b-form-group :state="true">
        <b-select
          v-if="deliveryDateOptions.length >= 1"
          :options="deliveryDateOptions"
          :value="date"
          class="delivery-select ml-2 width-140"
          required
        >
          <option slot="top" disabled>-- Select delivery day --</option>
        </b-select>
      </b-form-group>

      <b-btn variant="primary" size="lg" type="submit" class="brand-color"
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
    }
  }
};
</script>

<style lang="scss" scoped></style>
