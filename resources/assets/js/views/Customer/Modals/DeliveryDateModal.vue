<template>
  <b-modal
    v-model="visible"
    title="Select delivery date"
    size="md"
    @ok="$emit('ok')"
    @cancel="$emit('cancel')"
    @hidden="$emit('hidden')"
    @hide="editing = false"
    no-fade
  >
    <b-form-group :state="true">
      <b-form class="mt-2" @submit.prevent="onAddCategory" inline>
        <b-select
          v-if="deliveryDateOptions.length > 1"
          :options="deliveryDateOptions"
          :value="bagDeliveryDate"
          @input="changeDeliveryDate"
          class="delivery-select ml-2 width-140"
          required
        >
          <option slot="top" disabled>-- Select delivery day --</option>
        </b-select>

        <b-button type="submit" variant="primary ml-2">Create</b-button>
      </b-form>
    </b-form-group>
  </b-modal>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  data() {
    return {
      visible: true
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
  created() {},
  methods: {
    ...mapActions({
      refreshCategories: "refreshCategories"
    }),
    changeDeliveryDate() {}
  }
};
</script>

<style lang="scss" scoped></style>
