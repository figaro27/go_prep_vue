<template>
  <div class="auth-background">
  <div class="row auth-box">
    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
      <div class="card">
        <div class="card-body p-lg-5">
          <b-form @submit.prevent="submit">
            <b-form-group horizontal label="E-Mail Address">
              <b-input v-model="email"></b-input>
            </b-form-group>

            <b-form-group horizontal label="Password">
              <b-input v-model="password" type="password"></b-input>
            </b-form-group>

            <div class="form-group row">
              <div class="col-md-6 offset-md-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="remember" id="remember">
                  <label class="form-check-label" for="remember">Remember Me</label>
                </div>
              </div>
            </div>

            <b-form-group horizontal>
              <button type="submit" class="btn btn-primary">Login</button>

              <!--
              <a
                class="btn btn-link"
                href="#"
              >Forgot Your Password?</a>
              -->
              <router-link class="btn btn-link" to="/register">No Account?</router-link>
            </b-form-group>
          </b-form>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import auth from '../lib/auth';

export default {
  components: {},
  props: {
    redirect: {
      default: null
    }
  },
  data() {
    return {
      email: "",
      password: ""
    };
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    submit() {
      let data = {
        email: this.email,
        password: this.password
      };

      axios
        .post("/api/auth/login", data)
        .then(async response => {
          let jwt = response.data;

          if (jwt.access_token) {
            auth.setToken(jwt);

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
          this.$toastr.e("Please try again.", "Log in failed");
        });
    }
  }
};
</script>