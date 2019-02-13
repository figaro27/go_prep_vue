<template>
  <div class="row">
    <div class="col-md-6 offset-3">
      <div class="card">
        <div class="card-body">
          <b-form @submit.prevent="submit">
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

              
              <b-form-group horizontal label="E-Mail Address" :state="state(0, 'email')">
                <b-input
                  v-model="form[0].email"
                  type="email"
                  @input="$v.form[0].email.$touch()"
                  :state="state(0, 'email')">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Password" :state="state(0, 'password')">
                <b-input
                  v-model="form[0].password"
                  type="password"
                  @input="$v.form[0].password.$touch()"
                  :state="state(0, 'password')">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Confirm Password" :state="state(0, 'password_confirmation')">
                <b-input
                  v-model="form[0].password_confirmation"
                  type="password"
                  @input="$v.form[0].password_confirmation.$touch()"
                  :state="state(0, 'password_confirmation')">
                </b-input>
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
                  @input="$v.form[1].first_name.$touch()"
                  :state="state(1, 'first_name')">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Last Name" :state="!$v.form[1].last_name.$invalid">
                <b-input
                  v-model="form[1].last_name"
                  type="text"
                  @input="$v.form[1].last_name.$touch()"
                  :state="$v.form[1].last_name.$dirty ? !$v.form[1].last_name.$error : null">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Phone Number" :state="!$v.form[1].phone.$invalid">
                <b-input
                  v-model="form[1].phone"
                  type="tel"
                  @input="$v.form[1].phone.$touch()"
                  :state="$v.form[1].phone.$dirty ? !$v.form[1].phone.$error : null">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Address" :state="!$v.form[1].address.$invalid">
                <b-input
                  v-model="form[1].address"
                  type="text"
                  @input="$v.form[1].address.$touch()"
                  :state="$v.form[1].address.$dirty ? !$v.form[1].address.$error : null">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="City" :state="!$v.form[1].city.$invalid">
                <b-input
                  v-model="form[1].city"
                  type="text"
                  @input="$v.form[1].city.$touch()"
                  :state="$v.form[1].city.$dirty ? !$v.form[1].city.$error : null">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="State" :state="!$v.form[1].state.$invalid">
                <b-input
                  v-model="form[1].state"
                  type="text"
                  @input="$v.form[1].state.$touch()"
                  :state="$v.form[1].state.$dirty ? !$v.form[1].state.$error : null">
                </b-input>
              </b-form-group>

              <b-form-group horizontal label="Zip Code" :state="!$v.form[1].state.$invalid">
                <b-input
                  v-model="form[1].zip"
                  type="text"
                  @input="$v.form[1].zip.$touch()"
                  :state="$v.form[1].zip.$dirty ? !$v.form[1].zip.$error : null">
                </b-input>
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
              <b-form-group horizontal label="Store name">
                <b-input v-model="form[2].store_name"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Phone Number">
                <b-input v-model="form[2].phone"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Address">
                <b-input v-model="form[2].address"></b-input>
              </b-form-group>

              <b-form-group horizontal label="City">
                <b-input v-model="form[2].city"></b-input>
              </b-form-group>

              <b-form-group horizontal label="State">
                <b-input v-model="form[2].state"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Zip Code">
                <b-input v-model="form[2].zip"></b-input>
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
import validators from '../validators';

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
          phone: null,
          address: null,
          city: null,
          state: null,
          zip: null
        }
      }
    };
  },
  validations: {
    form: {
      0: {
        role: validators.required,
        email: validators.email,
        password: validators.password,
        password_confirmation: validators.password,
      },
      1: {
        first_name: validators.first_name,
        last_name: validators.last_name,
        phone: validators.phone,
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip,
      },
      2: {
        store_name: validators.required,
        phone: validators.phone,
        address: validators.address,
        city: validators.city,
        state: validators.state,
        zip: validators.zip,
      }
    },
    validationGroup: ['form[0]', 'form[1]', 'form[3]']
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    state(i, key) {
      return this.$v.form[i][key].$dirty ? !this.$v.form[i][key].$error : null
    },
    validate(step) {
      switch (step) {
        case 0:
          return !_.isNull(this.user_type);
          break;
      }

      return false;
    },
    next() {
      if (!this.$v.form[this.step].$invalid) {
        this.step++;
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
        .catch(error => {
          this.$toastr.e("Please try again.", "Registration failed");
        });
    }
  }
};
</script>