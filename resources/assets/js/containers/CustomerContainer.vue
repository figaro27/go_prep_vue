<template>
  <div class="app customer">
    <b-navbar toggleable="lg" class="app-header" fixed>
      <b-link class="navbar-brand" to="#">
        <!-- <img
          class="navbar-brand-full"
          :src="topLogo"
          width="70"
          height="70"
          alt="GoPrep Logo"
        /> -->
        <img
          class="navbar-brand-minimized"
          :src="topLogo"
          width="40"
          height="40"
          alt="GoPrep Logo"
        />
      </b-link>
      <b-navbar-toggle target="nav_collapse" class="mr-auto ml-2" />
      <b-btn
        class="mr-2 d-lg-none"
        variant="light"
        v-if="'id' in viewedStore"
        to="/customer/bag"
        ><i class="fa fa-shopping-bag"></i
      ></b-btn>

      <b-collapse is-nav id="nav_collapse" class="customer-nav">
        <b-navbar-nav class="d-none d-block d-md-none">
          <b-nav-item v-if="'id' in viewedStore && loggedIn" to="/customer/bag"
            >Bag</b-nav-item
          >
        </b-navbar-nav>
        <b-navbar-nav :class="mobileMargin">
          <b-nav-item v-if="'id' in viewedStore" to="/customer/menu"
            >Menu</b-nav-item
          >
          <b-nav-item v-if="'id' in viewedStore && loggedIn" to="/customer/bag"
            >Bag</b-nav-item
          >
          <b-nav-item v-if="loggedIn" to="/customer/orders">Orders</b-nav-item>
          <b-nav-item v-if="loggedIn" to="/customer/meal-plans"
            >Meal Plans</b-nav-item
          >
          <b-nav-item v-if="loggedIn" to="/customer/account/my-account"
            >My Account</b-nav-item
          >
        </b-navbar-nav>
        <b-navbar-nav class="ml-auto">
          <CustomerDropdown v-if="loggedIn" />
          <b-nav-item v-if="!loggedIn" to="/login">Log In</b-nav-item>
          <b-nav-item v-if="!loggedIn" class="px-3 mr-4" to="/register"
            >Register</b-nav-item
          >
        </b-navbar-nav>
        <!--<AsideToggler class="d-lg-none" mobile />-->
      </b-collapse>
    </b-navbar>
    <div class="app-body">
      <main class="main">
        <page-spinner
          v-if="!initialized || isLoading"
          :faded="initialized"
        ></page-spinner>
        <div class="container-fluid" v-if="initialized">
          <router-view></router-view>
        </div>
      </main>
    </div>
    <!-- <TheFooter>
    </TheFooter>-->
    <v-style>
      .navbar, .navbar-brand, .navbar-brand-minimized { background:
      {{ navBgColor }}; } .menu-bag-btn, .brand-color, .filters .active {
      background: {{ bgColor }}; } .dbl-underline:after { border-bottom: 3px
      double {{ bgColor }}; } .nav-item a:hover { background-color: #afafaf
      !important; }
    </v-style>
  </div>
</template>

<style lang="scss" scoped>
main.main {
  position: relative;
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import {
  Header as AppHeader,
  SidebarToggler,
  Sidebar as AppSidebar,
  SidebarFooter,
  SidebarForm,
  SidebarHeader,
  SidebarMinimizer,
  SidebarNav,
  Aside as AppAside,
  AsideToggler,
  Footer as TheFooter,
  Breadcrumb
} from "@coreui/vue";
import DefaultAside from "./DefaultAside";
import DefaultHeaderDropdown from "./DefaultHeaderDropdown";
import DefaultHeaderDropdownNotif from "./DefaultHeaderDropdownNotif";
import DefaultHeaderDropdownAccnt from "./DefaultHeaderDropdownAccnt";
import CustomerDropdown from "./CustomerDropdown";
import DefaultHeaderDropdownMssgs from "./DefaultHeaderDropdownMssgs";
import DefaultHeaderDropdownTasks from "./DefaultHeaderDropdownTasks";

export default {
  name: "DefaultContainer",
  components: {
    AsideToggler,
    AppHeader,
    AppSidebar,
    AppAside,
    TheFooter,
    Breadcrumb,
    DefaultAside,
    DefaultHeaderDropdown,
    DefaultHeaderDropdownMssgs,
    DefaultHeaderDropdownNotif,
    DefaultHeaderDropdownTasks,
    DefaultHeaderDropdownAccnt,
    CustomerDropdown,
    SidebarForm,
    SidebarFooter,
    SidebarToggler,
    SidebarHeader,
    SidebarNav,
    SidebarMinimizer
  },
  data() {
    return {
      navBgColor: "",
      bgColor: "",
      mobileMargin: ""
    };
  },
  computed: {
    ...mapGetters(["initialized", "viewedStore", "loggedIn", "isLoading"]),
    ...mapGetters({
      storeLogo: "viewedStoreLogo"
    }),
    name() {
      return this.$route.name;
    },
    list() {
      return this.$route.matched.filter(
        route => route.name || route.meta.label
      );
    },
    showLogo() {
      return this.viewedStore.settings.showLogo;
    },
    screenWidth() {
      return window.innerWidth;
    },
    topLogo() {
      if (this.screenWidth < 500) return this.storeLogo;
      else return "/images/logo.png";
    }
  },
  updated() {
    if (this.screenWidth < 500) {
      this.navBgColor === "#ffffff !important";
      this.mobileMargin === "ml-5";
    } else {
      this.navBgColor = this.viewedStore.settings.color + "!important";
    }
    this.bgColor = this.viewedStore.settings.color + " !important";

    // let page = this.name;
    // // if (page != 'customer-home' && page != 'login' && page != 'register' && page != 'customer-orders' && page != 'customer-meal-plans')
    // //   this.navBgColor = this.viewedStore.settings.color + ' !important';
    // if (page === "customer-menu" || page === "customer-bag") {
    //   this.navBgColor = this.viewedStore.settings.color + "!important";
    // } else {
    //   this.navBgColor = "#3082cf !important";
    // }

    // if (
    //   this.navBgColor === "#3082cf !important" ||
    //   this.navBgColor === "#3082CF !important"
    // ) {
    //   this.bgColor = "#F25727 !important";
    // } else {
    //   this.bgColor = this.viewedStore.settings.color + " !important";
    // }
  },
  created() {},
  methods: {}
};
</script>
