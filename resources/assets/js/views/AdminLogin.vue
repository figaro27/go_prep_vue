<template>
  <div>
    <b-modal
      size="sm"
      title="Enter Password"
      v-model="showPasswordModal"
      v-if="showPasswordModal"
      no-fade
      no-close-on-backdrop
      hide-header
      hide-footer
    >
      <b-form @submit.prevent="submitAdminPassword" class="pt-3">
        <p class="strong center-text">Enter Password</p>
        <b-form-group>
          <b-input v-model="adminPassword" type="password" required></b-input>
        </b-form-group>
        <b-form-group class="center-text">
          <button type="submit" class="btn btn-primary">Submit</button>
        </b-form-group>
      </b-form>
    </b-modal>

    <div class="row auth-box">
      <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
        <div class="card">
          <div class="card-body p-lg-5">
            <b-form @submit.prevent="submit">
              <v-select
                label="store_name"
                :options="storeOptions"
                v-model="selectedUserId"
                placeholder="Select Store"
                :reduce="store => store.user_id"
                class="mb-2"
                @input="submit"
              >
              </v-select>
            </b-form>
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
  props: {
    redirect: {
      default: null
    }
  },
  data() {
    return {
      showPasswordModal: true,
      adminPassword: null,
      selectedUserId: null,
      storeOptions: []
    };
  },
  created() {},
  mounted() {
    axios.get("/api/auth/getAdminStores").then(resp => {
      this.storeOptions = resp.data;
    });
  },
  methods: {
    ...mapActions(["init"]),
    submitAdminPassword() {
      if (this.adminPassword == "Mellon1!") {
        this.showPasswordModal = false;
      } else {
        this.$toastr.w("Incorrect password. Please try again.");
      }
    },
    submit() {
      if (!this.selectedUserId) {
        return;
      }
      let data = {
        userId: this.selectedUserId
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
          this.$toastr.w("Please try again.", "Log in failed");
        });
    }
  }
};
</script>
