<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <div class="m-3">customer@goprep.com</div>
    </template>\
    <template slot="dropdown">


        <router-link to="/customer/account/my-account" class="link-remove dropdown-item">
          <i class="fa fa-user"/>
          My Account
        </router-link>
      
        <router-link to="/customer/account/contact" class="link-remove dropdown-item">
          <i class="fa fa-envelope"/>
          Contact Us
        </router-link>
      
      <b-dropdown-item @click="logout()">
        <i class="fa fa-lock"/> Logout
      </b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
export default {
  name: "CustomerDropdown",
  components: {
    AppHeaderDropdown
  },
  data: () => {
    return { itemsCount: 42 };
  },
  methods: {
    logout() {
      axios.post("/api/auth/logout").finally(resp => {
        window.location.href = "/login";
      });
    }
  }
};
</script>
