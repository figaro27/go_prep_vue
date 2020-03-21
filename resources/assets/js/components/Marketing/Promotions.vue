<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <v-client-table
        :columns="columns"
        :data="tableData"
        :options="{
          headings: {}
        }"
      >
      </v-client-table>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      columns: [
        "active",
        "rewardType",
        "rewardAmount",
        "conditionType",
        "conditionAmount",
        "endDate"
      ]
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized",
      promotions: "storePromotions"
    }),
    tableData() {
      return Object.values(this.promotions);
    }
  },
  methods: {
    ...mapActions(["refreshStorePromotions"]),
    formatMoney: format.money
  }
};
</script>
