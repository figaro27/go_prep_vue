<template>
  <div class="app customer">
    <b-navbar toggleable="lg" class="app-header" fixed>
      <b-link class="navbar-brand" to="/customer/home">
        <img
          class="navbar-brand-full"
          src="/images/logo.jpg"
          width="90"
          height="40"
          alt="GoPrep Logo"
        >
        <img
          class="navbar-brand-minimized"
          src="/images/logo-min.png"
          width="33"
          height="40"
          alt="GoPrep Logo"
        >
      </b-link>
      <b-navbar-toggle target="nav_collapse" class="mr-auto ml-2" />
      <b-btn class="mr-2 d-lg-none" variant="light" v-if="'id' in viewedStore" to="/customer/bag"><i class="fa fa-shopping-bag"></i></b-btn>

      <b-collapse is-nav id="nav_collapse">
        <b-navbar-nav class="d-none d-block d-md-none">
          <b-nav-item v-if="'id' in viewedStore && loggedIn" to="/customer/bag">Bag</b-nav-item>
        </b-navbar-nav>
        <b-navbar-nav class="">
          <b-nav-item v-if="'id' in viewedStore" to="/customer/menu">Menu</b-nav-item>
          <b-nav-item v-if="'id' in viewedStore && loggedIn" to="/customer/bag">Bag</b-nav-item>
          <b-nav-item v-if="loggedIn" to="/customer/orders">Orders</b-nav-item>
          <b-nav-item v-if="loggedIn" to="/customer/subscriptions">Meal Plans</b-nav-item>
        </b-navbar-nav>
        <b-navbar-nav class="ml-auto">
          <CustomerDropdown v-if="loggedIn"/>
          <b-nav-item v-if="!loggedIn" to="/login">Log In</b-nav-item>
          <b-nav-item v-if="!loggedIn" class="px-3 mr-4" to="/register">Register</b-nav-item>
        </b-navbar-nav>
        <!--<AsideToggler class="d-lg-none" mobile />-->
      </b-collapse>
    </b-navbar>
    <div class="app-body">
      <main class="main">
        <page-spinner v-if="!initialized"></page-spinner>
        <div class="container-fluid" v-if="initialized">
          <router-view></router-view>
        </div>
      </main>
    </div>
    <!-- <TheFooter>
    </TheFooter>-->
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
    return {};
  },
  computed: {
    ...mapGetters(["initialized", "viewedStore", "loggedIn"]),
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
    }
  },
  created() {},
  methods: {}
};
</script>
