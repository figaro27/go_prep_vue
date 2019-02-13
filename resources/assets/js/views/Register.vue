<template>
  <div class="row">
    <div class="col-md-6 offset-3">
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="submit" autocomplete="off">
            <div v-if="step === 0">
              <b-form-group horizontal label="Account Type">
                <b-form-radio-group
                  horizontal
                  label="Account Type"
                  v-model="form[0].role"
                  name="user-type"
                >
                  <b-form-radio value="customer">Customer</b-form-radio>
                  <b-form-radio value="store">Store</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <b-form-group
                horizontal
                label="E-Mail Address"
                :state="state(0, 'email')"
                :invalid-feedback="invalidFeedback(0, 'email')"
                :valid-feedback="validFeedback(0, 'email')"
              >
                <b-input
                  v-model="form[0].email"
                  type="email"
                  @input="$v.form[0].email.$touch(); clearFeedback(0, 'email')"
                  :state="state(0, 'email')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Password" :state="state(0, 'password')">
                <b-input
                  v-model="form[0].password"
                  type="password"
                  @input="$v.form[0].password.$touch(); clearFeedback(0, 'password')"
                  :state="state(0, 'password')"
                ></b-input>
              </b-form-group>

              <b-form-group
                horizontal
                label="Confirm Password"
                :state="state(0, 'password') && state(0, 'password_confirmation')"
                :invalid-feedback="invalidFeedback(0, 'password')"
                :valid-feedback="validFeedback(0, 'password')"
              >
                <b-input
                  v-model="form[0].password_confirmation"
                  type="password"
                  @input="$v.form[0].password_confirmation.$touch(); clearFeedback(0, 'password')"
                  :state="state(0, 'password') && state(0, 'password_confirmation')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal>
                <b-button @click="next()" :disabled="$v.form[0].$invalid" variant="primary">Next</b-button>
              </b-form-group>
            </div>

            <div v-if="step === 1">
              <b-form-group horizontal label="First Name" :state="state(1, 'first_name')">
                <b-input
                  v-model="form[1].first_name"
                  type="text"
                  @input="$v.form[1].first_name.$touch(); clearFeedback(1, 'first_name')"
                  :state="state(1, 'first_name')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Last Name" :state="state(1, 'last_name')">
                <b-input
                  v-model="form[1].last_name"
                  type="text"
                  @input="$v.form[1].last_name.$touch(); clearFeedback(1, 'last_name')"
                  :state="state(1, 'last_name')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Phone Number" :state="state(1, 'phone')">
                <b-input
                  v-model="form[1].phone"
                  type="tel"
                  @input="$v.form[1].phone.$touch(); clearFeedback(1, 'phone')"
                  :state="state(1, 'phone')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Address" :state="state(1, 'address')">
                <b-input
                  v-model="form[1].address"
                  type="text"
                  @input="$v.form[1].address.$touch(); clearFeedback(1, 'address')"
                  :state="state(1, 'address')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="City" :state="state(1, 'city')">
                <b-input
                  v-model="form[1].city"
                  type="text"
                  @input="$v.form[1].city.$touch(); clearFeedback(1, 'city')"
                  :state="state(1, 'city')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="State" :state="state(1, 'state')">
                <b-input
                  v-model="form[1].state"
                  type="text"
                  @input="$v.form[1].state.$touch(); clearFeedback(1, 'state')"
                  :state="state(1, 'state')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Zip Code" :state="state(1, 'zip')">
                <b-input
                  v-model="form[1].zip"
                  type="text"
                  @input="$v.form[1].zip.$touch(); clearFeedback(1, 'zip')"
                  :state="state(1, 'zip')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal v-if="form[0].role === 'store'">
                <b-button @click="next()" :disabled="$v.form[1].$invalid" variant="primary">Next</b-button>
              </b-form-group>

              <b-form-group horizontal v-else>
                <b-button type="submit" :disabled="$v.form[1].$invalid" variant="primary">Submit</b-button>
              </b-form-group>
            </div>

            <div v-if="step === 2">
              <h4>Store Details</h4>

              <b-form-group horizontal label="Store Name" :state="state(2, 'store_name')">
                <b-input
                  v-model="form[2].store_name"
                  type="text"
                  @input="$v.form[2].store_name.$touch(); clearFeedback(2, 'store_name')"
                  :state="state(2, 'store_name')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="First Name" :state="state(2, 'first_name')">
                <b-input
                  v-model="form[2].first_name"
                  type="text"
                  @input="$v.form[2].first_name.$touch(); clearFeedback(2, 'first_name')"
                  :state="state(2, 'first_name')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Last Name" :state="state(2, 'last_name')">
                <b-input
                  v-model="form[2].last_name"
                  type="text"
                  @input="$v.form[2].last_name.$touch(); clearFeedback(2, 'last_name')"
                  :state="state(2, 'last_name')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Phone Number" :state="state(2, 'phone')">
                <b-input
                  v-model="form[2].phone"
                  type="tel"
                  @input="$v.form[2].phone.$touch(); clearFeedback(2, 'phone')"
                  :state="state(2, 'phone')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Address" :state="state(2, 'address')">
                <b-input
                  v-model="form[2].address"
                  type="text"
                  @input="$v.form[2].address.$touch(); clearFeedback(2, 'address')"
                  :state="state(2, 'address')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="City" :state="state(2, 'city')">
                <b-input
                  v-model="form[2].city"
                  type="text"
                  @input="$v.form[2].city.$touch(); clearFeedback(2, 'city')"
                  :state="state(2, 'city')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="State" :state="state(2, 'state')">
                <b-input
                  v-model="form[2].state"
                  type="text"
                  @input="$v.form[2].state.$touch(); clearFeedback(2, 'state')"
                  :state="state(2, 'state')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal label="Zip Code" :state="state(2, 'zip')">
                <b-input
                  v-model="form[2].zip"
                  type="text"
                  @input="$v.form[2].zip.$touch(); clearFeedback(1, 'zip')"
                  :state="state(2, 'zip')"
                ></b-input>
              </b-form-group>

              <b-form-group horizontal>
                <b-button type="submit" :disabled="$v.form[2].$invalid" variant="primary">Submit</b-button>
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
import validators from "../validators";

export default {
  components: {},
  data() {
    return {
      step: 0,

      form: {
        0: {
          role: null,
          email: null,
          password: null,
          password_confirmation: null
        },
        1: {
          first_name: null,
          last_name: null,
          phone: null,
          address: null,
          city: null,
          state: null,
          zip: null
        },
        2: {
          store_name: null,
          first_name: null,
          last_name: null,
          phone: null,
          address: null,
          city: null,
          state: null,
          zip: null
        }
      },
      feedback: {
        invalid: {},
        valid: {}
      }
    };
  },
  computed: {},
  validations: {
    form: {
      0: {
        role: validators.required,
        email: validators.email,
        password: validators.password,
        password_confirmation: validators.password
      },
      1: {
        first_name: validators.first_name,
        last_name: validators.last_name,
        phone: validators.phone,
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip
      },
      2: {
        store_name: validators.required,
        first_name: validators.first_name,
        last_name: validators.last_name,
        phone: validators.phone,
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip
      }
    },
    validationGroup: ["form[0]", "form[1]", "form[3]"]
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    state(step, key) {
      if (!_.isEmpty(this.form[step][key]) && this.$v.form[step][key].$dirty) {
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
          this.step++;
        }
      }
    },
    submit() {
      let data = {
        user: this.form[0],
        user_details: this.form[1],
        store: this.form[2]
      };

      axios
        .post("/api/auth/register", data)
        .then(async response => {
          let jwt = response.data;

          if (jwt.access_token) {
            window.axios.defaults.headers.common["Authorization"] = `Bearer ${
              jwt.access_token
            }`;
            Cookies.set("jwt", jwt);
            localStorage.setItem("jwt", JSON.stringify(jwt));

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
          this.$toastr.e("Please try again.", "Registration failed");
        });
    }
  }
};
</script>