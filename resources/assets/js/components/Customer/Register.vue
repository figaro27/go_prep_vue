<template>
  <div class="mt-4">
    <b-modal id="tos" size="xl" ref="tos" no-fade>
      <termsOfService></termsOfService>
    </b-modal>
    <b-modal id="toa" size="xl" ref="toa">
      <termsOfAgreement></termsOfAgreement>
    </b-modal>
    <b-form @submit.prevent="submit" autocomplete="off" ref="form">
      <div v-if="step === 0">
        <div class="d-flex" style="flex-wrap:wrap">
          <b-form-group
            :state="state(0, 'first_name')"
            style="flex-grow:1"
            class="mr-2"
          >
            <b-input
              placeholder="First Name"
              v-model="form[0].first_name"
              type="text"
              @input="
                $v.form[0].first_name.$touch();
                clearFeedback(0, 'first_name');
              "
              :state="state(0, 'first_name')"
              autocomplete="new-password"
            ></b-input>
          </b-form-group>

          <b-form-group :state="state(0, 'last_name')" style="flex-grow:1">
            <b-input
              placeholder="Last Name"
              v-model="form[0].last_name"
              type="text"
              @input="
                $v.form[0].last_name.$touch();
                clearFeedback(0, 'last_name');
              "
              :state="state(0, 'last_name')"
              autocomplete="new-password"
            ></b-input>
          </b-form-group>
        </div>
        <b-form-group
          :state="state(0, 'email')"
          :invalid-feedback="invalidFeedback(0, 'email')"
          :valid-feedback="validFeedback(0, 'email')"
        >
          <b-input
            placeholder="Email Address"
            v-model="form[0].email"
            type="email"
            @input="
              $v.form[0].email.$touch();
              clearFeedback(0, 'email');
            "
            :state="state(0, 'email')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <b-form-group :state="state(0, 'password')">
          <b-input
            placeholder="Password"
            v-model="form[0].password"
            type="password"
            @input="
              $v.form[0].password.$touch();
              clearFeedback(0, 'password');
            "
            :state="state(0, 'password')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <b-form-group
          :state="state(0, 'password') && state(0, 'password_confirmation')"
          :invalid-feedback="invalidFeedback(0, 'password')"
          :valid-feedback="validFeedback(0, 'password')"
        >
          <b-input
            placeholder="Confirm Password"
            v-model="form[0].password_confirmation"
            type="password"
            @input="
              $v.form[0].password_confirmation.$touch();
              clearFeedback(0, 'password');
            "
            :state="state(0, 'password') && state(0, 'password_confirmation')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <b-form-group :state="state(0, 'phone')">
          <b-input
            placeholder="Phone Number"
            v-model="form[0].phone"
            type="tel"
            @input="asYouType()"
            :state="state(0, 'phone')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <b-form-group>
          <b-button
            @click="next()"
            :disabled="$v.form[0].$invalid"
            variant="primary"
            >Next</b-button
          >
        </b-form-group>
      </div>

      <div v-if="step === 1">
        <h4>Account Details</h4>

        <b-form-group :state="state(1, 'country')">
          <b-select
            placeholder="Country"
            :options="countryNames"
            v-model="form[1].country"
            class="w-100"
          ></b-select>
        </b-form-group>

        <b-form-group :state="state(1, 'address')">
          <b-input
            placeholder="Delivery Address"
            v-model="form[1].address"
            type="text"
            @input="
              $v.form[1].address.$touch();
              clearFeedback(1, 'address');
            "
            :state="state(1, 'address')"
            autocomplete="new-password"
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
            ></b-select>
          </b-form-group>

          <b-form-group :state="state(1, 'zip')" style="flex-grow:1">
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
            ></b-input>
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
          ></b-input>
        </b-form-group>

        <b-form-group>
          <b-form-checkbox
            id="accepted-tos"
            name="accepted-tos"
            v-model="form[1].accepted_tos"
            :value="1"
            :unchecked-value="0"
            :state="state(1, 'accepted_tos')"
          >
            I accept the
            <!-- <span
                class="strong"
                @click.stop.prevent="$refs.tos.show()"
                @touch.stop.prevent="$refs.tos.show()"
                >terms of service</span
              > -->
            <span
              class="strong"
              @click.stop.prevent="$refs.tos.show()"
              @touch.stop.prevent="$refs.tos.show()"
              >terms of service</span
            >
          </b-form-checkbox>
        </b-form-group>

        <b-form-group v-if="form[0].role === 'store'">
          <b-button
            @click="next()"
            :disabled="$v.form[1].$invalid"
            variant="primary"
            >Next</b-button
          >
        </b-form-group>

        <b-form-group v-else>
          <b-button
            type="submit"
            :disabled="$v.form[1].$invalid"
            variant="primary"
            >Submit</b-button
          >
        </b-form-group>
      </div>

      <div v-if="step === 2">
        <h4>Store Details</h4>
        <p class="mb-3">
          You can change these details later in My Account before you go live.
        </p>
        <b-form-group
          :state="state(2, 'store_name')"
          :invalid-feedback="invalidFeedback(2, 'store_name')"
          :valid-feedback="validFeedback(2, 'store_name')"
        >
          <b-input
            placeholder="Store Name"
            v-model="form[2].store_name"
            type="text"
            @input="
              $v.form[2].store_name.$touch();
              clearFeedback(2, 'store_name');
            "
            :state="state(2, 'store_name')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <b-form-group
          :state="state(2, 'domain')"
          :invalid-feedback="invalidFeedback(2, 'domain')"
          :valid-feedback="validFeedback(2, 'domain')"
          class="w-100"
        >
          <div class="input-group">
            <b-input
              placeholder="Store Domain"
              v-model="form[2].domain"
              type="text"
              @input="
                $v.form[2].domain.$touch();
                clearFeedback(2, 'domain');
              "
              :state="state(2, 'domain')"
              autocomplete="new-password"
            ></b-input>
            <div class="input-group-append">
              <span class="input-group-text">.goprep.com</span>
            </div>
          </div>
        </b-form-group>

        <b-form-group :state="state(2, 'country')">
          <b-select
            placeholder="Country"
            :options="countryNames"
            v-model="form[2].country"
            class="w-100"
          ></b-select>
        </b-form-group>

        <b-form-group
          :state="state(2, 'address')"
          :invalid-feedback="invalidFeedback(2, 'address')"
          :valid-feedback="validFeedback(2, 'address')"
        >
          <b-input
            placeholder="Address"
            v-model="form[2].address"
            type="text"
            @input="
              $v.form[2].address.$touch();
              clearFeedback(2, 'address');
            "
            :state="state(2, 'address')"
            autocomplete="new-password"
          ></b-input>
        </b-form-group>

        <div class="d-flex">
          <b-form-group
            class="d-inline mr-3"
            :state="state(2, 'city')"
            :invalid-feedback="invalidFeedback(2, 'city')"
            :valid-feedback="validFeedback(2, 'city')"
          >
            <b-input
              placeholder="City"
              v-model="form[2].city"
              type="text"
              @input="
                $v.form[2].city.$touch();
                clearFeedback(2, 'city');
              "
              :state="state(2, 'city')"
              autocomplete="new-password"
            ></b-input>
          </b-form-group>

          <b-form-group :state="state(2, 'state')" class="d-inline mr-3">
            <b-select
              placeholder="State"
              label="name"
              :options="getStateNames(form[2].country)"
              v-model="form[2].state"
              :on-change="val => changeState(val, 2)"
              class="w-100"
            ></b-select>
          </b-form-group>

          <b-form-group
            class="d-inline"
            :state="state(2, 'zip')"
            :invalid-feedback="invalidFeedback(2, 'zip')"
            :valid-feedback="validFeedback(2, 'zip')"
          >
            <b-input
              placeholder="Postal Code"
              v-model="form[2].zip"
              type="text"
              @input="
                $v.form[2].zip.$touch();
                clearFeedback(1, 'zip');
              "
              :state="state(2, 'zip')"
              autocomplete="new-password"
            ></b-input>
          </b-form-group>
        </div>
        <b-form-group :state="state(2, 'accepted_tos')">
          <b-form-checkbox
            id="accepted-tos2"
            name="accepted-tos2"
            v-model="form[2].accepted_tos"
            :value="1"
            :unchecked-value="0"
            :state="state(2, 'accepted_tos')"
          >
            I accept the
            <span
              class="strong"
              @click.stop.prevent="$refs.tos.show()"
              @touch.stop.prevent="$refs.tos.show()"
              >terms of service</span
            >
          </b-form-checkbox>
        </b-form-group>

        <!--<b-form-group :state="state(2, 'accepted_toa')">
                <b-form-checkbox
                  id="accepted-toa2"
                  name="accepted-toa2"
                  v-model="form[2].accepted_toa"
                  :value="1"
                  :unchecked-value="0"
                  :state="state(2, 'accepted_toa')"
                >
                  I accept the
                  <span
                    class="strong"
                    @click.stop.prevent="$refs.toa.show()"
                    @touch.stop.prevent="$refs.toa.show()"
                    >terms of agreement</span
                  >
                </b-form-checkbox>
              </b-form-group>-->

        <b-form-group>
          <b-button
            type="submit"
            v-if="!manualOrder"
            :disabled="$v.form[2].$invalid"
            variant="primary"
            >Submit</b-button
          >
        </b-form-group>
        <b-form-group>
          <b-button
            type="submit"
            v-if="manualOrder"
            :disabled="$v.form[2].$invalid"
            variant="primary"
            >Add New Customer</b-button
          >
        </b-form-group>
      </div>
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
  props: {
    manualOrder: {
      default: false
    },
    plan_id: {
      default: null
    }
  },
  data() {
    return {
      showStatesBox: true,
      redirect: null,
      step: 0,

      form: {
        0: {
          role: "customer",
          email: null,
          password: null,
          password_confirmation: null,
          first_name: null,
          last_name: null,
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
    },
    passwordValidator() {
      let password = this.form[0].password;
      if (password && password.length > 0 && password.length < 6) {
        return (
          "Please add at least " + (6 - password.length) + " more characters."
        );
      } else return "";
    },
    passwordConfirmationValidator() {
      let password = this.form[0].password_confirmation;
      if (password && password.length > 0 && password.length < 6) {
        return (
          "Please add at least " + (6 - password.length) + " more characters."
        );
      } else return "";
    },
    emailValidator() {
      let email = this.form[0].email;
      if (email && email.length > 0) {
        if (!email.includes("@")) {
          return "Please include the @ symbol.";
        }
        if (!email.includes(".")) {
          return "Please include the ending of the email such as .com.";
        }
      }
    },
    stateNames() {
      return this.getStateNames();
    }
  },
  validations: {
    form: {
      0: {
        role: validators.required,
        email: validators.email,
        password: validators.password,
        password_confirmation: validators.password,
        first_name: validators.first_name,
        last_name: validators.last_name,
        phone: validators.phone
      },
      1: {
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip,
        country: validators.required,
        delivery: validators.delivery,
        accepted_tos: validators.required
      },
      2: {
        store_name: validators.store_name,
        domain: validators.domain,
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip,
        country: validators.required,
        accepted_tos: validators.required
        //accepted_toa: validators.required
      }
    },
    validationGroup: ["form[0]", "form[1]", "form[3]"]
  },
  created() {
    if (!_.isEmpty(this.$route.query.redirect)) {
      this.redirect = this.$route.query.redirect;
    }
  },
  mounted() {
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
        user_details: this.form[1],
        store: this.form[2],
        plan_id: this.plan_id
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
    }
  }
};
</script>
