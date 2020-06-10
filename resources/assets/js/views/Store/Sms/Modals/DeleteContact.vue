<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <h5 class="center-text p-2 mt-2">
        Are you sure you want to delete this contact?
      </h5>
      <div class="d-flex pt-2" style="justify-content:center">
        <b-btn
          class="d-inline mr-2"
          variant="secondary"
          @click="$emit('cancel')"
          >Cancel</b-btn
        >
        <b-btn class="d-inline" variant="danger" @click="$emit('deleteContact')"
          >Delete</b-btn
        >
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../../mixins/deliveryDates";
import format from "../../../../lib/format";
import store from "../../../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      tableData: [],
      contact: {},
      columns: ["name", "membersCount", "actions"]
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money
  }
};
</script>
