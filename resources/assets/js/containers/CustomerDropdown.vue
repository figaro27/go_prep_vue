<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <div><i class="fas fa-user customer-nav-icon"></i></div> </template
    >\
    <template slot="dropdown" class="account-dropdown" style="margin-top:30px">
      <!-- <router-link
        to="/customer/account/my-account"
        class="link-remove dropdown-item"
      >
        <i class="fa fa-user" />
        My Account
      </router-link> -->

      <router-link
        to="/customer/account/contact"
        class="link-remove dropdown-item"
      >
        <i class="fa fa-envelope" />
        Contact Us
      </router-link>

      <b-dropdown-item @click="logout()">
        <i class="fa fa-lock" /> Logout
      </b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  name: "CustomerDropdown",
  components: {
    AppHeaderDropdown
  },
  data: () => {
    return { itemsCount: 42 };
  },
  computed: {
    ...mapGetters({
      user: "user"
    }),
    email() {
      return this.user.email || "";
    }
  },
  methods: {
    ...mapActions(["logout"])
  }
};
</script>
