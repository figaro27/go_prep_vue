<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <div class="d-flex">
        <b-form-input v-model="list.name" style="width:350px"></b-form-input>
        <!-- <b-button
          @click="$emit('updateList', { list: list, contacts: SMSContacts })"
          variant="warning"
          class="mb-2 float-left ml-4"
          >Update</b-button
        > -->
      </div>
      <v-client-table
        :columns="columns"
        :data="SMSContacts"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            firstName: 'First Name',
            lastName: 'Last Name'
          },
          filterable: false
        }"
      >
        <div slot="included" slot-scope="props">
          <b-form-checkbox
            v-model="props.row.included"
            type="checkbox"
            :value="true"
            :unchecked-value="false"
            @change="val => updateIncluded(props.row.id, val)"
          ></b-form-checkbox>
        </div>
      </v-client-table>
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
      columns: ["firstName", "lastName", "phone"],
      includedContacts: []
    };
  },
  props: {
    list: null
  },
  created() {},
  mounted() {
    axios
      .post("/api/me/showContactsInList", { id: this.list.id })
      .then(resp => {
        this.includedContacts = resp.data;
        this.SMSContacts.forEach(contact => {
          if (
            this.includedContacts.some(
              includedContact => includedContact.id === contact.id
            )
          ) {
            contact.included = true;
          } else {
            contact.included = false;
          }
        });
        this.columns.unshift("included");
      });
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      SMSContacts: "SMSContacts"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    updateIncluded(id, val) {
      this.SMSContacts.forEach(row => {
        if (row.id === id) {
          row.included = val;
        }
      });
      this.$emit("updateList", { list: this.list, contacts: this.SMSContacts });
    }
  }
};
</script>
