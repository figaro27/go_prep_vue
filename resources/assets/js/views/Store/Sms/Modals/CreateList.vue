<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <div class="d-flex">
        <b-form-input
          v-model="list.name"
          placeholder="List Name (Optional)"
          style="width:350px"
        ></b-form-input>

        <b-button
          @click="$emit('addList', list)"
          variant="primary"
          class="mb-2 float-left ml-4"
          >Save</b-button
        >
      </div>
      <v-client-table
        :columns="columns"
        :data="SMSContactsData"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            add: 'Add To List',
            firstName: 'First Name',
            lastName: 'Last Name'
          },
          filterable: false
        }"
      >
        <div slot="add" slot-scope="props">
          <b-form-checkbox
            type="checkbox"
            :value="1"
            :unchecked-value="0"
            @change="val => addContact(props.row.id, val)"
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
      columns: ["add", "firstName", "lastName", "phone"],
      list: {
        name: "",
        contacts: []
      }
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
    }),
    SMSContactsData() {
      if (_.isArray(this.SMSContacts)) {
        return this.SMSContacts;
      } else {
        return [];
      }
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    addContact(id, val) {
      if (val === 1) {
        this.list.contacts.push(id);
      } else {
        this.list.contacts.pop(id);
      }
    }
  }
};
</script>
