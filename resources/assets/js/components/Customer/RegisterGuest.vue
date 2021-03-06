<template>
  <div class="mt-4">
    <div v-if="isTosVisible">
      <button @click="isTosVisible = false" class="btn btn-primary back-button">
        Back
      </button>
      <termsOfService></termsOfService>
    </div>
    <b-form v-else @submit.prevent="submit" autocomplete="off" ref="form">
      <div class="d-flex" style="flex-wrap:wrap">
        <b-form-group
          :state="state(0, 'firstname')"
          style="flex-grow:1"
          class="mr-2"
        >
          <b-input
            placeholder="First Name"
            v-model="form[0].firstname"
            type="text"
            @input="
              $v.form[0].firstname.$touch();
              clearFeedback(0, 'firstname');
            "
            :state="state(0, 'firstname')"
            autocomplete="new-password"
            style="font-size:16px"
          ></b-input>
        </b-form-group>

        <b-form-group :state="state(0, 'lastname')" style="flex-grow:1">
          <b-input
            placeholder="Last Name"
            v-model="form[0].lastname"
            type="text"
            @input="
              $v.form[0].lastname.$touch();
              clearFeedback(0, 'lastname');
            "
            :state="state(0, 'lastname')"
            autocomplete="new-password"
            style="font-size:16px"
          ></b-input>
        </b-form-group>
      </div>
      <b-form-group :state="state(0, 'phone')">
        <b-input
          placeholder="Phone Number"
          v-model="form[0].phone"
          type="tel"
          @input="asYouType()"
          :state="state(0, 'phone')"
          autocomplete="new-password"
          style="font-size:16px"
        ></b-input>
      </b-form-group>

      <b-form-group :state="state(1, 'address')">
        <b-input
          placeholder="Address"
          v-model="form[1].address"
          type="text"
          @input="
            $v.form[1].address.$touch();
            clearFeedback(1, 'address');
          "
          :state="state(1, 'address')"
          autocomplete="new-password"
          style="font-size:16px"
        ></b-input>
      </b-form-group>
      <div class="d-flex" style="flex-wrap:wrap">
        <b-form-group
          :state="state(1, 'city')"
          style="flex-grow:1"
          class="mr-2"
        >
          <b-input
            :placeholder="cityLabel"
            v-model="form[1].city"
            type="text"
            @input="
              $v.form[1].city.$touch();
              clearFeedback(1, 'city');
            "
            :state="state(1, 'city')"
            autocomplete="new-password"
            style="font-size:16px"
          ></b-input>
        </b-form-group>

        <b-form-group
          :state="state(1, 'state')"
          v-if="showStatesBox"
          style="flex-grow:1"
          class="mr-2"
        >
          <b-select
            :placeholder="stateLabel"
            :options="getStateNames(form[1].country)"
            v-model="form[1].state"
            :on-change="val => changeState(val, 1)"
            class="w-100"
            style="font-size:16px"
          ></b-select>
        </b-form-group>

        <b-form-group :state="state(1, 'zip')" style="flex-grow:1" class="mr-2">
          <b-input
            :placeholder="postalLabel"
            v-model="form[1].zip"
            type="text"
            @input="
              $v.form[1].zip.$touch();
              clearFeedback(1, 'zip');
            "
            :state="state(1, 'zip')"
            autocomplete="new-password"
            style="font-size:16px"
          ></b-input>
        </b-form-group>

        <b-form-group :state="state(1, 'country')" style="flex-grow:1">
          <b-select
            placeholder="Country"
            label="name"
            :options="countryNames"
            v-model="form[1].country"
            class="w-100"
            style="font-size:16px"
          ></b-select>
        </b-form-group>
      </div>
      <b-form-group :state="state(1, 'delivery')">
        <b-input
          placeholder="Delivery Instructions (Optional)"
          v-model="form[1].delivery"
          type="text"
          @input="
            $v.form[1].delivery.$touch();
            clearFeedback(1, 'delivery');
          "
          :state="state(1, 'delivery')"
          autocomplete="new-password"
          style="font-size:16px"
        ></b-input>
      </b-form-group>
      <p class="font-italic">
        By checking out you agree to our
        <span
          class="strong"
          @click.stop.prevent="isTosVisible = true"
          @touch.stop.prevent="isTosVisible = true"
          >terms of service.</span
        >
      </p>
      <b-button type="submit" :disabled="$v.form[1].$invalid" variant="primary"
        >Continue</b-button
      >
    </b-form>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { required, minLength, email, sameAs } from "vuelidate/lib/validators";
import validators from "../../validators";
import auth from "../../lib/auth";
import TermsOfService from "../../views/TermsOfService";
import TermsOfAgreement from "../../views/TermsOfAgreement";
import countries from "../../data/countries.js";
import currencies from "../../data/currencies.js";
import states from "../../data/states.js";
import { AsYouType } from "libphonenumber-js";

export default {
  components: {
    TermsOfService,
    TermsOfAgreement
  },
  data() {
    return {
      showStatesBox: true,
      isTosVisible: false,
      redirect: null,
      step: 0,

      form: {
        0: {
          guest: true,
          role: "customer",
          email: null,
          password: null,
          password_confirmation: null,
          firstname: null,
          lastname: null,
          phone: null
        },
        1: {
          address: null,
          city: null,
          state: null,
          zip: null,
          country: "US",
          delivery: "",
          accepted_tos: 0
        },
        2: {
          store_name: null,
          domain: null,
          //currency: "USD",
          address: null,
          city: null,
          state: null,
          zip: null,
          country: "US",
          accepted_tos: 0
          //accepted_toa: 0
        }
      },
      feedback: {
        invalid: {},
        valid: {}
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    }),
    cityLabel() {
      if (this.form[1].country === "BH") {
        return "Town";
      } else {
        return "City";
      }
    },
    stateLabel() {
      switch (this.form[1].country) {
        case "GB":
          return "County";
          break;
        case "CA":
          return "Province";
          break;
        default:
          return "State";
      }
    },
    postalLabel() {
      switch (this.form[1].country) {
        case "US":
          return "Zip Code";
          break;
        case "BH":
          return "Block";
          break;
        default:
          return "Postal Code";
      }
    },
    countryNames() {
      return countries.selectOptions();
    },
    currencyOptions() {
      return currencies.selectOptions();
    }
  },
  validations: {
    form: {
      0: {
        role: validators.required,
        firstname: validators.firstname,
        lastname: validators.lastname,
        phone: validators.phone
      },
      1: {
        address: validators.address,
        city: validators.city,
        zip: validators.zip,
        country: validators.required,
        delivery: validators.delivery,
        accepted_tos: validators.required
      }
    },
    validationGroup: ["form[0]", "form[1]"]
  },
  created() {
    if (!_.isEmpty(this.$route.query.redirect)) {
      this.redirect = this.$route.query.redirect;
    }
  },
  mounted() {
    this.form[0].role = "guest";
    this.form[0].email =
      "noemail-guest-" + this.store.id + "-" + this.uniqid() + "@goprep.com";
    this.form[0].password = this.uniqid();
    this.form[0].password_confirmation = this.form[0].password;

    if (this.store.details) {
      this.form[1].state = this.store.details.state;
      this.form[1].country = this.store.details.country;
      let stateAbr = this.store.details.state;
      let state = this.stateNames.filter(stateName => {
        return stateName.value.toLowerCase() === stateAbr.toLowerCase();
      });

      this.form[1].state = state[0] ? state[0].value : null;
    }
  },
  methods: {
    ...mapActions(["init", "setToken"]),
    getStateNames(country = "US") {
      if (country == "BH") {
        this.showStatesBox = false;
      }
      return states.selectOptions(country);
    },
    state(step, key) {
      if (
        !_.isEmpty(this.form[step][key]) &&
        this.$v.form[step][key] &&
        this.$v.form[step][key].$dirty
      ) {
        if (
          this.$v.form[step][key].$error ||
          !_.isNull(this.invalidFeedback(step, key))
        ) {
          return false;
        }
        return true;
      } else return null;
    },
    invalidFeedback(step, key) {
      try {
        if (_.isArray(this.feedback.invalid[step][key])) {
          const message = this.feedback.invalid[step][key].join(" ");
          return message;
        }
      } catch {}
      return null;
    },
    validFeedback(step, key) {
      try {
        if (_.isArray(this.feedback.valid[step][key])) {
          const message = this.feedback.valid[step][key].join(" ");
          return message;
        }
      } catch {}
      return null;
    },
    clearFeedback(step, key) {
      try {
        this.feedback.invalid[step][key] = null;
        this.feedback.valid[step][key] = null;
      } catch (e) {}
    },
    async validate(step) {
      try {
        await axios.post(
          `/api/auth/register/validate/${step}`,
          this.form[step]
        );
        return true;
      } catch (e) {
        const resp = e.response.data;
        if (!_.isEmpty(resp.errors)) {
          this.$set(this.feedback.invalid, step, resp.errors);
          this.$forceUpdate();
        }
        return false;
      }
    },
    async next() {
      if (!this.$v.form[this.step].$invalid) {
        if (await this.validate(this.step)) {
          if (this.form[0].role === "customer") {
            this.step++;
          } else {
            this.step += 2;
          }
        } else if (this.form[1].accepted_tos === 0) {
          this.$toastr.w(
            "Please accept the terms of service.",
            "Registration failed"
          );
        } else this.$toastr.w("Please try again.", "Registration failed");

        this.$v.form.$touch();

        this.$nextTick(() => {
          window.scrollTo(0, 1);
          window.scrollTo(0, 0);
        });
      }
    },
    async submit() {
      if (!(await this.validate(this.step))) {
        return;
      }

      this.asYouType();

      let data = {
        user: this.form[0],
        user_details: this.form[1]
      };

      if (data.user.role === "store") {
        data.user_details = { ...data.store };
      }

      axios
        .post("/api/auth/register", data)
        .then(async response => {
          let jwt = response.data;

          if (jwt.access_token) {
            auth.setToken(jwt);

            if (this.$route.path === "/customer/bag") {
              this.redirect = "/customer/bag";
            }

            if (this.redirect) {
              await this.init();
              this.$router.replace(this.redirect);
            } else if (jwt.redirect) {
              window.location = jwt.redirect;
            } else {
              await this.init();
              switch (jwt.user.user_role_id) {
                case 1:
                  this.$router.replace("/customer/home");
                  break;

                case 2:
                  this.$router.replace("/store/orders");
                  break;
              }
            }
          }
        })
        .catch(e => {
          this.$toastr.w("Please try again.", "Registration failed");
        });
    },
    changeState(state, formNumber) {
      this.form[formNumber].state = state.abbreviation;
    },
    changeCountry(country, formNumber) {
      this.form[formNumber].country = country;
    },
    asYouType() {
      let country =
        this.store && this.store.details ? this.store.details.country : "US";
      this.form[0].phone = this.form[0].phone.replace(/[^\d.-]/g, "");
      this.form[0].phone = new AsYouType(country).input(this.form[0].phone);
    },
    uniqid(a = "", b = false) {
      const c = Date.now() / 1000;
      let d = c
        .toString(16)
        .split(".")
        .join("");
      while (d.length < 14) d += "0";
      let e = "";
      if (b) {
        e = ".";
        e += Math.round(Math.random() * 100000000);
      }
      return a + d + e;
    }
  }
};
</script>

<style lang="scss" scoped>
.back-button {
  position: sticky;
  top: 1.5rem;
  float: right;
}
</style>
