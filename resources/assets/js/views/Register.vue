<template>
  <div class="row auth-box">
    <b-modal
      id="tos"
      size="md"
      ref="tos"
      no-fade
      style="position:relative;top:10px"
    >
      <termsOfService></termsOfService>
    </b-modal>
    <b-modal id="toa" size="md" ref="toa" no-fade>
      <termsOfAgreement></termsOfAgreement>
    </b-modal>
    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
      <div class="card">
        <div class="card-body p-lg-5">
          <b-alert variant="success" v-if="freeTrial && step === 0" show>
            <p class="center-text">
              Thank you for trying GoPrep! Enter your info below to get started
              with your free two week trial. You'll be able to do everything a
              paid user can do and you can cancel at any point.
            </p>
          </b-alert>
          <b-form @submit.prevent="submit" autocomplete="off" ref="form">
            <div v-if="step === 0">
              <b-form-group
                horizontal
                label="Account Type"
                v-if="
                  !$route.params.customerRegister &&
                    !$route.query.store &&
                    !freeTrial
                "
              >
                <b-form-radio-group
                  horizontal
                  label="Account Type"
                  v-model="form[0].role"
                  name="user-type"
                >
                  <b-form-radio value="customer">Customer</b-form-radio>
                  <b-form-radio value="store" v-if="!manualOrder"
                    >Company</b-form-radio
                  >
                </b-form-radio-group>
              </b-form-group>
              <div v-if="$route.query.store" class="mb-3 center-text">
                <h5>Not ready to sign up yet?</h5>
                <a href="https://www.goprep.com/get-started/" target="_blank">
                  <h6 v-if="$route.query.store">
                    Get in touch with us here first for any questions.
                  </h6>
                </a>
              </div>
              <b-form-group horizontal :state="state(0, 'firstname')">
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

              <b-form-group horizontal :state="state(0, 'lastname')">
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

              <b-form-group
                horizontal
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal :state="state(0, 'password')">
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
                :state="
                  state(0, 'password') && state(0, 'password_confirmation')
                "
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
                  :state="
                    state(0, 'password') && state(0, 'password_confirmation')
                  "
                  autocomplete="new-password"
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal :state="state(0, 'phone')">
                <!-- @input="
                    $v.form[0].phone.$touch();
                    clearFeedback(0, 'phone');
                  " -->
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

              <b-form-group horizontal>
                <b-button
                  v-if="!store.modules.pickupOnly"
                  @click="next()"
                  :disabled="$v.form[0].$invalid"
                  variant="primary"
                  >Next</b-button
                >
              </b-form-group>

              <b-form-group horizontal v-if="store.modules.pickupOnly">
                <b-form-checkbox
                  id="accepted-tos"
                  name="accepted-tos"
                  v-model="form[1].accepted_tos"
                  :value="1"
                  :unchecked-value="0"
                  :state="state(1, 'accepted_tos')"
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

              <b-form-group horizontal v-if="store.modules.pickupOnly">
                <b-button
                  type="submit"
                  :disabled="$v.form[0].$invalid"
                  variant="primary"
                  >Submit</b-button
                >
              </b-form-group>
            </div>

            <div v-if="step === 1">
              <h4>Account Details</h4>

              <b-form-group horizontal :state="state(1, 'country')">
                <b-select
                  v-if="!form[1].country || form[1].country === ''"
                  placeholder="Country"
                  :options="countryNames"
                  v-model="form[1].country"
                  class="w-100"
                  style="font-size:16px"
                ></b-select>
              </b-form-group>

              <b-form-group horizontal :state="state(1, 'address')">
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

              <b-form-group horizontal>
                <b-input
                  v-model="form[1].unit"
                  type="text"
                  placeholder="House / Unit # Optional"
                  autocomplete="new-password"
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
                :state="state(1, 'city')"
                v-if="showCityInput"
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
                horizontal
                :state="state(1, 'state')"
                v-if="showStatesInput"
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

              <b-form-group horizontal :state="state(1, 'zip')">
                <p class="strong" v-if="selectPostal">
                  Select {{ postalLabel }}
                </p>
                <b-select
                  :options="getPostalNames(form[1].country)"
                  v-model="form[1].zip"
                  class="w-180"
                  style="font-size:16px"
                  v-if="selectPostal"
                >
                </b-select>
                <b-input
                  v-else
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

              <b-form-group horizontal :state="state(1, 'delivery')">
                <b-input
                  placeholder="Delivery Instructions (Optional)"
                  v-if="!noDeliveryInstructions"
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

              <b-form-group horizontal>
                <b-form-checkbox
                  id="accepted-tos"
                  name="accepted-tos"
                  v-model="form[1].accepted_tos"
                  :value="1"
                  :unchecked-value="0"
                  :state="state(1, 'accepted_tos')"
                  style="font-size:16px"
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

              <b-form-group horizontal v-if="form[0].role === 'store'">
                <b-button
                  @click="next()"
                  :disabled="$v.form[1].$invalid"
                  variant="primary"
                  >Next</b-button
                >
              </b-form-group>

              <b-form-group horizontal v-else>
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
                You can change these details later before launching.
              </p>
              <b-form-group
                horizontal
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
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
                    style="font-size:16px"
                  ></b-input>
                  <div class="input-group-append">
                    <span class="input-group-text">.goprep.com</span>
                  </div>
                </div>
              </b-form-group>

              <!--b-form-group
                horizontal
                label="Currency"
                :state="state(2, 'currency')"
              >
                <b-select
                  :options="currencyOptions"
                  v-model="form[2].currency"
                  class="w-100"
                ></b-select>
              </b-form-group-->

              <b-form-group horizontal :state="state(2, 'country')">
                <b-select
                  placeholder="Country"
                  :options="countryNames"
                  v-model="form[2].country"
                  class="w-100"
                  style="font-size:16px"
                ></b-select>
              </b-form-group>

              <b-form-group horizontal :state="state(2, 'state')">
                <b-select
                  :placeholder="stateLabel"
                  :options="getStateNames(form[2].country)"
                  v-model="form[2].state"
                  :on-change="val => changeState(val, 2)"
                  class="w-100"
                  style="font-size:16px"
                ></b-select>
              </b-form-group>

              <b-form-group
                horizontal
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
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
                  style="font-size:16px"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal :state="state(2, 'accepted_tos')">
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

              <!--<b-form-group horizontal :state="state(2, 'accepted_toa')">
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

              <b-form-group horizontal v-if="!manualOrder">
                <b-button
                  v-if="!planless"
                  @click="next()"
                  :disabled="$v.form[2].$invalid"
                  variant="primary"
                  >Next</b-button
                >
                <b-button
                  type="submit"
                  v-else
                  :disabled="$v.form[2].$invalid"
                  variant="primary"
                  >Submit</b-button
                >
              </b-form-group>
            </div>

            <div v-if="step === 3">
              <h4 v-if="!freeTrial">Payment Details</h4>

              <!-- <b-form-group label="Billing method" horizontal v-if="!freeTrial">
                <b-form-radio-group
                  v-model="form[3].plan_method"
                  :options="[
                    { text: 'Credit Card', value: 'credit_card' },
                    { text: 'Bank Transfer', value: 'connect' }
                  ]"
                ></b-form-radio-group>
              </b-form-group> -->

              <b-form-group label="Billing period" horizontal v-if="!freeTrial">
                <b-form-radio-group
                  v-model="form[3].plan_period"
                  :options="[
                    { text: 'Monthly', value: 'monthly' },
                    { text: 'Annually', value: 'annually' }
                  ]"
                ></b-form-radio-group>
              </b-form-group>

              <b-form-group label="Select Plan" horizontal v-if="!freeTrial">
                <b-form-radio-group
                  v-model="form[3].plan"
                  :options="planOptions"
                  stacked
                ></b-form-radio-group>
              </b-form-group>
              <b-alert variant="success" v-if="freeTrial" show>
                <p class="strong">
                  Please enter your card details below to get started with your
                  free two week trial. You will not be charged now. After two
                  weeks, you will be charged $119 for our Basic Plan which
                  allows up to 50 orders per month. <br /><br />You can cancel
                  before the two weeks are up on the Billing page.
                </p>
              </b-alert>

              <div v-if="planRequiresCard" class="mb-2">
                <card
                  class="stripe-card"
                  :stripe="stripeKey"
                  :options="stripeOptions"
                  :class="{ cardStatus }"
                  @change="onChangeCard"
                />
              </div>
              <div v-else></div>
              <b-form-group horizontal>
                <b-button
                  type="submit"
                  v-if="!manualOrder"
                  :disabled="$v.form[3].$invalid"
                  variant="primary"
                  >Submit</b-button
                >
              </b-form-group>
            </div>
          </b-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { required, minLength, email, sameAs } from "vuelidate/lib/validators";
import { createToken } from "vue-stripe-elements-plus";
import validators from "../validators";
import auth from "../lib/auth";
import format from "../lib/format";
import TermsOfService from "./TermsOfService";
import TermsOfAgreement from "./TermsOfAgreement";
import countries from "../data/countries.js";
import currencies from "../data/currencies.js";
import states from "../data/states.js";
import postals from "../data/postals.js";
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
      error: null,
      freeTrial: false,
      noDeliveryInstructions: false,
      redirect: null,
      step: 0,
      plans: {},
      stripeKey: window.app.stripe_key,
      stripeOptions: {},
      cardStatus: null,
      showStatesInput: true,
      showCityInput: true,

      form: {
        0: {
          guest: false,
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
          unit: null,
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
        },
        3: {
          plan: null,
          plan_method: "credit_card",
          plan_period: "monthly",
          stripe_token: null,
          allowed_orders: null
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
    selectPostal() {
      // Certain countries have string based postal areas like Barbados having 'Parishes' and this is needed for delivery_day_zip_code input matching
      if (
        this.store &&
        this.store.details &&
        this.store.details.country === "BB"
      ) {
        return true;
      } else {
        return false;
      }
    },
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
        case "BB":
          return "Parish";
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
    planOptions() {
      return _.map(this.plans, (plan, planId) => {
        if (plan.title !== "Monthly SMS phone number") {
          const period = this.form[3].plan_period;
          const planDetails = plan[period];
          return {
            text: sprintf(
              "%s - %s %s %s",
              plan.title,
              format.money(planDetails.price / 100),
              period === "monthly" ? "Per Month" : "Per Year",
              planDetails.price_upfront
                ? ` - ${format.money(planDetails.price_upfront / 100)} up front`
                : ""
            ),
            value: planId
          };
        }
      });
    },
    planSelected() {
      if (!this.form[3].plan) {
        return null;
      }

      const period = this.form[3].plan_period;
      const planId = this.form[3].plan;

      return this.plans[planId][period] || null;
    },
    planRequiresCard() {
      let requires = this.form[3].plan_method === "credit_card";

      if (this.planSelected && 0 < parseInt(this.planSelected.price_upfront)) {
        requires = true;
      }

      return requires;
    },
    planless() {
      const url = new URL(window.location.href);
      return null !== url.searchParams.get("planless") || false;
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
        firstname: validators.firstname,
        lastname: validators.lastname,
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
      },
      3: {
        plan: validators.required,
        plan_method: validators.required,
        plan_period: validators.required,
        stripe_token: validators.required(val => {
          return this.planRequiresCard;
        })
      }
    },
    validationGroup: ["form[0]", "form[1]", "form[2]", "form[3]"]
  },
  created() {
    if (!_.isEmpty(this.$route.query.redirect)) {
      this.redirect = this.$route.query.redirect;
    }

    axios.get("/api/plans").then(resp => {
      if (!this.freeTrial) {
        delete resp.data.plans.free_trial;
      }
      this.plans = resp.data.plans;
    });
  },
  mounted() {
    // this.form[1].state = this.store.details.state;
    // this.form[1].country = this.store.details.country;
    // return;
    if (this.store.modules.guestCheckout) {
      this.form[0].guest = true;
    }
    if (this.store.details) {
      this.form[1].state = this.store.details.state;
      this.form[1].country = this.store.details.country;
      let stateAbr = this.store.details.state;
      let state = this.stateNames.filter(stateName => {
        return stateName.value.toLowerCase() === stateAbr.toLowerCase();
      });

      this.form[1].state = state[0].value;
    }

    // Hides certain inputes like state & city depending on the selected country

    if (this.$route.query === "store") {
      this.role = "store";
    }

    if (this.$route.query.free_trial) {
      this.freeTrial = true;
      this.planSelected = this.planOptions[0];
      this.form[0].role = "store";
    }

    if (this.$route.query.orders) {
      this.form[3].allowed_orders = this.$route.query.orders;
    }

    if (this.$route.query.plan) {
      this.form[3].plan = this.$route.query.plan;
    }

    if (this.$route.query.period) {
      this.form[3].plan_period = this.$route.query.period;
    }

    this.hideInputs();
  },
  methods: {
    ...mapActions(["init", "setToken"]),
    getStateNames(country = "US") {
      return states.selectOptions(country);
    },
    getPostalNames(country = "US") {
      return postals.getPostals(country);
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
          this.error = resp.errors[Object.keys(resp.errors)[0]];
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
          } else if (this.step === 0) {
            this.step += 2;
          } else {
            this.step++;
          }
        } else if (this.form[1].accepted_tos === 0) {
          this.$toastr.w(this.error);
        } else this.$toastr.w("Please try again.", "Registration failed");

        this.$v.form.$touch();

        this.$nextTick(() => {
          window.scrollTo(0, 1);
          window.scrollTo(0, 0);
        });
      }
    },
    async submit() {
      if (this.freeTrial) {
        this.form[3].plan = "free_trial";
      }
      if (
        this.form[0].role === "store" &&
        this.form[3].plan === null &&
        !this.planless
      ) {
        this.$toastr.w("Please pick a plan.");
        return;
      }

      if (
        this.form[0].role === "store" &&
        this.planRequiresCard &&
        !this.cardStatus &&
        !this.planless
      ) {
        this.$toastr.w("Please add a credit card.");
        return;
      }

      if (!(await this.validate(this.step))) {
        return;
      }

      if (
        this.form[1].unit !== null &&
        this.form[1].unit !== "" &&
        !this.form[1].unit.includes("#")
      ) {
        this.form[1].unit = "#" + this.form[1].unit;
      }

      if (this.store.modules.pickupOnly) {
        this.form[1].address = "N/A";
        this.form[1].city = "N/A";
        this.form[1].zip = "N/A";
      }

      this.asYouType();

      let data = {
        user: this.form[0],
        user_details: this.form[1],
        store: this.form[2],
        plan: this.form[3],
        planless: this.planless,
        freeTrial: this.freeTrial,
        last_viewed_store_id: this.$route.query.store_id
          ? this.$route.query.store_id
          : null
      };

      if (data.user.role === "store") {
        data.user_details = { ...data.store };
      }
      if (
        this.store.details &&
        this.store.details.country === "GB" &&
        !this.form[1].zip.includes(" ")
      ) {
        this.$toastr.w("Please include a space in your postal code.");
        return;
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
    async onChangeCard($event) {
      this.cardStatus = $event.complete;
      if ($event.complete) {
        const result = await createToken();

        if (result.token && result.token.id) {
          this.form[3].stripe_token = result.token.id;
        } else if (result.error) {
        }
      }
    },
    asYouType() {
      let country =
        this.store && this.store.details ? this.store.details.country : "US";
      this.form[0].phone = this.form[0].phone.replace(/[^\d.-]/g, "");
      this.form[0].phone = new AsYouType(country).input(this.form[0].phone);
    },
    hideInputs() {
      // Eventually move to external js file
      if (this.form[1].country === "BH") {
        this.showStatesInput = false;
        this.form[1].state = "N/A";
      }

      if (this.form[1].country === "BB") {
        this.showStatesInput = false;
        this.showCityInput = false;
        this.form[1].state = "N/A";
        this.form[1].city = "N/A";
      }
    }
  }
};
</script>
