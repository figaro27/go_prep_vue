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

              <b-form-group horizontal label="E-Mail Address">
                <b-input v-model="form[0].email" type="email"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Password">
                <b-input v-model="form[0].password" type="password"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Confirm Password">
                <b-input v-model="form[0].password_confirmation" type="password"></b-input>
              </b-form-group>

              <b-form-group horizontal>
                <b-button @click="next()" :disabled="$v.form[0].$invalid" variant="primary">Next</b-button>
              </b-form-group>
            </div>

            <div v-if="step === 1">
              <b-form-group horizontal label="First Name">
                <b-input v-model="form[1].first_name"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Last Name">
                <b-input v-model="form[1].last_name"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Phone Number">
                <b-input v-model="form[1].phone"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Address">
                <b-input v-model="form[1].address"></b-input>
              </b-form-group>

              <b-form-group horizontal label="City">
                <b-input v-model="form[1].city"></b-input>
              </b-form-group>

              <b-form-group horizontal label="State">
                <b-input v-model="form[1].state"></b-input>
              </b-form-group>

              <b-form-group horizontal label="Zip Code">
                <b-input v-model="form[1].zip"></b-input>
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
import { required, minLength, email } from "vuelidate/lib/validators";

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
          zip: null,
        },
        2: {
          store_name: null,
          phone: null,
          address: null,
          city: null,
          state: null,
          zip: null,
        },
      }
    };
  },
  validations: {
    form: {
      0: {
        role: {
          required
        },
        email: {
          required,
          email
        },
        password: {
          required
        },
        password_confirmation: {
          required
        }
      },
      1: {
        first_name: {
          required
        },
        last_name: {
          required
        },
        phone: {
          required
        },
        address: {
          required
        },
        city: {
          required
        },
        state: {
          required
        },
        zip: {
          required
        },
      },
      2: {
        store_name: {
          required
        },
        phone: {
          required
        },
        address: {
          required
        },
        city: {
          required
        },
        state: {
          required
        },
        zip: {
          required
        },
      }
    }
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
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
        store: this.form[2],
      };

      axios
        .post("/api/auth/register", data)
        .then(response => {
          let jwt = response.data;

          if (jwt.access_token) {
            window.axios.defaults.headers.common["Authorization"] = `Bearer ${
              jwt.access_token
            }`;
            localStorage.setItem("jwt", JSON.stringify(jwt));
            this.init();
            this.$router.go(jwt.redirect);
          }
        })
        .catch(error => {
          this.$toastr.e("Please try again.", "Registration failed");
        });
    }
  }
};
</script>