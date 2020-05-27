<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />

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
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-primary btn-md mb-2 mb-sm-0"
            @click="selectAll"
          >
            Select All
          </button>
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="$emit('insertContacts', selectedContacts)"
            :disabled="selectedContacts.length === 0"
          >
            Insert Contacts
          </button>
        </div>

        <div slot="included" slot-scope="props">
          <b-form-checkbox
            v-model="props.row.included"
            type="checkbox"
            :value="true"
            :unchecked-value="false"
            @change="val => addToSelectedContacts(props.row, val)"
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
      columns: ["included", "firstName", "lastName", "phone"],
      selectedContacts: [],
      allSelected: false
    };
  },
  created() {},
  mounted() {},
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
    ...mapActions({
      refreshSMSContacts: "refreshSMSContacts"
    }),
    formatMoney: format.money,
    addToSelectedContacts(contact, val) {
      if (val === true) {
        if (!this.selectedContacts.includes(contact)) {
          this.selectedContacts.push(contact);
        }
      } else {
        this.selectedContacts.pop(contact);
      }
    },
    selectAll() {
      this.allSelected = !this.allSelected;
      this.selectedContacts = [];
      this.SMSContacts.forEach(contact => {
        if (this.allSelected) {
          contact.included = true;
          this.selectedContacts.push(contact);
        } else {
          contact.included = false;
          this.selectedContacts.pop(contact);
        }
      });
    }
  }
};
</script>
