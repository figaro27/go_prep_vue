<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <div class="m-3 pr-4">
        <h6>{{ email }}</h6>
      </div>
    </template>\
    <template slot="dropdown">
      <router-link to="/store/account/my-account" class="link-remove dropdown-item">
        <i class="fa fa-user"/>
        My Account
      </router-link>

      <router-link to="/store/account/contact" class="link-remove dropdown-item">
        <i class="fa fa-comment-dots"/>
        Contact Us
      </router-link>

      <b-dropdown-item href="#" @click="logout()">
        <i class="fa fa-lock"/> Logout
      </b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  name: "StoreDropdown",
  components: {
    AppHeaderDropdown
  },
  data() {
    return {
      itemsCount: 42,
    };
  },
  computed: {
    ...mapGetters({
      user: "user"
    }),
    email() {
      return this.user.email || '';
    }
  },
  created() {},
  methods: {
    logout() {
      axios.post("/api/auth/logout").finally(resp => {
        window.location.href = "/login";
      });
    }
  }
};
</script>
