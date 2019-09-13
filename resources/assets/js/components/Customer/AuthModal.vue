<template>
  <div>
    <b-modal
      size="lg"
      hide-header
      v-model="showAuthModal"
      v-if="showAuthModal"
      @hide="resetScreens()"
      hide-footer
    >
      <div class="row auth-box" v-if="login">
        <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
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
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember"
                  />
                  <label class="form-check-label" for="remember"
                    >Remember Me</label
                  >
                </div>
              </div>
            </div>

            <b-form-group horizontal>
              <button type="submit" class="btn btn-primary d-inline">
                Login
              </button>

              <!--
		              <a
		                class="btn btn-link"
		                href="#"
		              >Forgot Your Password?</a>
		              -->
              <p class="d-inline pl-2" @click="switchScreens('register')">
                <a href="#">No Account?</a>
              </p>
            </b-form-group>

            <b-form-group horizontal>
              <p @click="switchScreens('forgotPassword')">
                <a href="#">Forgot password?</a>
              </p>
            </b-form-group>
          </b-form>
        </div>
      </div>
      <register v-if="register"></register>
      <forgot-password v-if="forgotPassword"></forgot-password>
    </b-modal>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import auth from "../../lib/auth";
import Register from "./Register";
import ForgotPassword from "./ForgotPassword";

export default {
  components: {
    Register,
    ForgotPassword
  },
  props: {
    redirect: null,
    showAuthModal: false
  },
  data() {
    return {
      email: "",
      password: "",
      login: true,
      register: false,
      forgotPassword: false
    };
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    switchScreens(screen) {
      this.login = false;
      if (screen === "register") this.register = true;
      else if (screen === "forgotPassword") this.forgotPassword = true;
    },
    resetScreens() {
      this.$parent.showAuthModal = false;
      this.login = true;
      this.register = false;
      this.forgotPassword = false;
    },
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

            this.$nextTick(async () => {
              if (!_.isEmpty(this.redirect)) {
                this.init();
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
            });
          }
        })
        .catch(error => {
          this.$toastr.e("Please try again.", "Log in failed");
        });
    }
  }
};
</script>
