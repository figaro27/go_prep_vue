<template>
  <div class="mt-4">
    <b-form v-if="!done" @submit.prevent="submit">
      <b-form-group label="E-Mail Address">
        <b-input v-model="email" placeholder="Email"></b-input>
      </b-form-group>

      <b-form-group>
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
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import auth from "../../lib/auth";

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
          this.$toastr.w("Please try again.", "Failed to reset password");
        });
    }
  }
};
</script>
