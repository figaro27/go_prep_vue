<template>
  <div class="row auth-box">
    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
      <div class="card">
        <div class="card-body p-lg-5">
          <b-form v-if="!done" @submit.prevent="submit">
            <b-form-group horizontal label="E-Mail Address">
              <b-input v-model="email"></b-input>
            </b-form-group>

            <b-form-group horizontal>
              <button type="submit" class="btn btn-primary">
                Reset Password
              </button>
            </b-form-group>
          </b-form>
          <div v-else class="text-center">
            <p>
              An email has been sent with instructions on resetting your account
              password.
            </p>

            <router-link to="/login">Go back</router-link>
          </div>
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
      done: false
    };
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions(["init"]),
    submit() {
      let data = {
        email: this.email
      };

      axios
        .post("/api/auth/forgot", data)
        .then(async response => {
          this.done = true;
        })
        .catch(error => {
          this.$toastr.e("Please try again.", "Failed to reset password");
        });
    }
  }
};
</script>
