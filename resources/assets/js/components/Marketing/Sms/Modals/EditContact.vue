<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-form-input
        v-model="contact.firstName"
        placeholder="First Name"
      ></b-form-input>
      <b-form-input
        v-model="contact.lastName"
        placeholder="Last Name"
      ></b-form-input>
      <b-form-input v-model="contact.phone" placeholder="Phone"></b-form-input>
      <b-btn variant="warning" @click="$emit('updateContact', contact)"
        >Update</b-btn
      >
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
      columns: ["name", "membersCount", "actions"]
    };
  },
  props: {
    contact: null
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
