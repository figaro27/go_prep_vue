<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <div class="m-3 pr-4"><h6>{{ email }}</h6></div>
    </template>\
    <template slot="dropdown">
      <b-dropdown-item>
      <i class="fa fa-user"/>
        <router-link to="/store/account/my-account" class="link-remove">My Account</router-link>
      </b-dropdown-item>

      <b-dropdown-item>
        <i class="fa fa-comment-dots"/>
        <router-link to="/store/account/contact" class="link-remove">Contact Us</router-link>
      </b-dropdown-item>
      <b-dropdown-item @click="logout()">
        <i class="fa fa-lock"/> Logout
      </b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
// import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  name: "StoreDropdown",
  components: {
    AppHeaderDropdown
  },
  data() {
    return { 
      itemsCount: 42,
      email: '' 
    }
  },
  // computed: {
  //   ...mapGetters({
  //     user: "user",
  //   }),
  //   email(){
  //     return this.user.email;
  //   }
  // },
  created(){
    this.getEmail();
  },
  methods: {
    logout() {
      axios.post("/logout").then(resp => {
        window.location.href = "/login";
      });
    },
    getEmail() {
      let self = this;
      axios.get("/api/me/user").then(resp => {
        self.email = resp.data;
      });
    }
  }
};
</script>
