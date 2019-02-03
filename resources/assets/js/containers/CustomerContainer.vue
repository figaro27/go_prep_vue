<template>
  <div class="app">
    <AppHeader fixed>
      <b-link class="navbar-brand" to="#">
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
      <b-navbar-nav class="d-none d-block d-md-none">
        <b-nav-item v-if="'id' in viewedStore && loggedIn" class="px-3" to="/customer/bag">Bag</b-nav-item>
      </b-navbar-nav>
      <b-navbar-nav class="d-md-down-none">
        <b-nav-item v-if="'id' in viewedStore" class="px-3" to="/customer/menu">Menu</b-nav-item>
        <b-nav-item v-if="'id' in viewedStore && loggedIn" class="px-3" to="/customer/bag">Bag</b-nav-item>
        <b-nav-item v-if="loggedIn" class="px-3" to="/customer/orders">Orders</b-nav-item>
        <b-nav-item v-if="loggedIn" class="px-3" to="/customer/subscriptions">Meal Plans</b-nav-item>
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <CustomerDropdown v-if="loggedIn" />
      </b-navbar-nav>
      <!--<AsideToggler class="d-lg-none" mobile />-->
    </AppHeader>
    <div class="app-body">
      <main class="main">
        <div class="container-fluid">
          <router-view></router-view>
        </div>
      </main>
    </div>
    <!-- <TheFooter>
    </TheFooter>-->
  </div>
</template>

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
    ...mapGetters(["viewedStore", "loggedIn"]),
    name() {
      return this.$route.name;
    },
    list() {
      return this.$route.matched.filter(
        route => route.name || route.meta.label
      );
    },
  },
  created() {

  },
  methods: {
  }
};
</script>
