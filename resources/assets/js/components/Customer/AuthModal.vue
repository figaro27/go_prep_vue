<template>
  <div>
    <b-modal
      size="lg"
      hide-header
      v-model="showAuthModal"
      v-if="showAuthModal"
      @hide="resetScreens()"
      hide-footer
      no-fade
    >
      <div
        class="d-flex mt-4"
        v-if="login"
        style="justify-content:space-around"
      >
        <div style="width:50%">
          <b-form @submit.prevent="submit">
            <b-form-group>
              <b-input
                v-model="email"
                placeholder="Email"
                style="font-size:16px"
              ></b-input>
            </b-form-group>

            <b-form-group>
              <b-input
                v-model="password"
                type="password"
                placeholder="Password"
                style="font-size:16px"
              ></b-input>
            </b-form-group>

            <div class="form-group">
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

            <b-form-group>
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
              <p
                class="d-inline pl-1"
                @click="switchScreens('registerGuest')"
                v-if="store.modules.guestCheckout"
              >
                <a href="#">Checkout As Guest</a>
              </p>
            </b-form-group>

            <b-form-group>
              <p @click="switchScreens('forgotPassword')">
                <a href="#">Forgot password?</a>
              </p>
            </b-form-group>
          </b-form>
        </div>
      </div>

      <register v-if="register"></register>
      <register-guest v-if="registerGuest"></register-guest>
      <forgot-password v-if="forgotPassword"></forgot-password>
    </b-modal>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import auth from "../../lib/auth";
import Register from "./Register";
import RegisterGuest from "./RegisterGuest";
import ForgotPassword from "./ForgotPassword";

export default {
  components: {
    Register,
    RegisterGuest,
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
      registerGuest: false,
      forgotPassword: false
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    })
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore"
    })
  },
  methods: {
    ...mapActions(["init"]),
    switchScreens(screen) {
      this.login = false;
      switch (screen) {
        case "register":
          this.register = true;
          break;
        case "registerGuest":
          this.registerGuest = true;
          break;
        case "forgotPassword":
          this.forgotPassword = true;
          break;
      }
    },
    resetScreens() {
      this.$parent.showAuthModal = false;
      this.login = true;
      this.register = false;
      this.registerGuest = false;
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
          let lastViewedStoreUrl = response.data.user.last_viewed_store_url;

          if (jwt.access_token) {
            auth.setToken(jwt);

            this.$nextTick(async () => {
              if (this.$route.path === "/customer/bag") {
                this.redirect = "/customer/bag";
              }
              if (!_.isEmpty(this.redirect)) {
                this.init();
                this.$router.replace(this.redirect);
              } else if (jwt.redirect) {
                if (lastViewedStoreUrl) {
                  window.location = lastViewedStoreUrl + jwt.redirect;
                } else {
                  window.location = jwt.redirect;
                }
              } else {
                await this.init();
                switch (jwt.user.user_role_id) {
                  case 1:
                    this.$router.replace(this.$route.path);
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
          this.$toastr.w("Please try again.", "Log in failed");
        });
    }
  }
};
</script>
