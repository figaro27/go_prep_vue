<template>
  <div class="row auth-box">
    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
      <div class="card">
        <div class="card-body p-lg-5">
          <b-form @submit.prevent="submit">
            <b-form-group horizontal label="E-Mail Address">
              <b-input v-model="email"></b-input>
            </b-form-group>

            <b-form-group horizontal label="New Password">
              <b-input v-model="password" type="password"></b-input>
            </b-form-group>

            <b-form-group horizontal label="Confirm New Password">
              <b-input v-model="password_confirm" type="password"></b-input>
            </b-form-group>

            <b-form-group horizontal>
              <button type="submit" class="btn btn-primary">
                Reset Password
              </button>
            </b-form-group>
          </b-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import auth from "../lib/auth";

export default {
  components: {},
  props: {},
  data() {
    return {
      email: "",
      password: "",
      password_confirm: ""
    };
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    submit() {
      let data = {
        email: this.email,
        password: this.password,
        password_confirmation: this.password_confirm,
        token: this.$route.params.token
      };

      axios
        .post("/api/auth/reset", data)
        .then(async response => {
          this.$toastr.s("Password reset.");
          this.$router.replace("/login");
        })
        .catch(error => {
          this.$toastr.e("Please try again.", "Failed to reset password");
        });
    }
  }
};
</script>
