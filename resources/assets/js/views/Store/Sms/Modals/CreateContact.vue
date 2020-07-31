<template>
  <div class="row mt-3">
    <div class="col-md-12 d-flex pb-3">
      <Spinner v-if="isLoading" />
      <b-form @submit.prevent="$emit('addContact', contact)" class="d-flex">
        <b-form-input
          v-model="contact.firstName"
          placeholder="First Name"
          class="d-inline mr-1"
          required
        ></b-form-input>
        <b-form-input
          v-model="contact.lastName"
          placeholder="Last Name"
          class="d-inline mr-1"
          required
        ></b-form-input>
        <b-form-input
          v-model="contact.phone"
          placeholder="Phone"
          class="d-inline mr-1"
          required
        ></b-form-input>
        <b-btn variant="primary" type="submit">Save</b-btn>
      </b-form>
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
      initialized: "initialized"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money
  }
};
</script>
