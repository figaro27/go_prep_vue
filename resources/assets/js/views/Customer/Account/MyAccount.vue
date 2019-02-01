<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="updateCustomer">
            <b-form-input type="text" v-model="userDetail.firstname" placeholder="First Name"></b-form-input>
            <hr>
            <b-form-input
              type="text"
              v-model="userDetail.lastname"
              placeholder="Last Name"
              required
            ></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.phone" placeholder="Phone" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.address" placeholder="Address" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.city" placeholder="City" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.state" placeholder="State" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.zip" placeholder="Zip Code" required></b-form-input>
            <hr>
            <b-form-input type="text" v-model="userDetail.delivery" placeholder="Delivery" required></b-form-input>
            <b-button type="submit" variant="primary">Submit</b-button>
          </b-form>
        </div>
      </div>
      <div class="card">
        <div class="card-header">Payment Methods</div>
        <div class="card-body">
          <card-picker :selectable="false"></card-picker>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>

</style>

<script>
import CardPicker from '../../../components/Billing/CardPicker';
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    CardPicker
  },
  data() {
    return {
      userDetail: {
        firstname: "",
        lastname: "",
        phone: "",
        address: "",
        city: "",
        state: "",
        zip: null,
        delivery: ""
      },
    };
  },
  computed: {
    ...mapGetters({
      cards: "cards"
    })
  },
  mounted() {
    this.getCustomer();
  },
  methods: {
    ...mapActions(['refreshUser']),
    getCustomer() {
      axios.get("/getCustomer").then(response => {
        this.userDetail = response.data;
      });
    },
    updateCustomer() {
      this.spliceZip();
      axios.post("/updateCustomer", this.userDetail)
        .then(response => {
          this.$toastr.s('Profile updated.');
          this.refreshUser();
        })
        .catch(e => {
          this.$toastr.e('Failed to update profile.')
        });
    },
    spliceZip() {
      if (this.userDetail.zip.toString().length > 5) {
        let reducedZip = this.userDetail.zip.toString();
        this.userDetail.zip = parseInt(reducedZip.substring(0, 5));
      }
    },
  }
};
</script>