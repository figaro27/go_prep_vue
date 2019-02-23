<template>
  <div :class="classes">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile/>
      <b-link class="navbar-brand" to="#">
        <img
          class="navbar-brand-full"
          src="/images/logo.png"
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
      <SidebarToggler class="d-md-down-none" display="lg"/>
      <b-navbar-nav class="ml-auto">
        <!-- <b-nav-item class="d-md-down-none">
          <DefaultHeaderDropdownNotif/>
        </b-nav-item>-->
        <!-- <b-nav-item class="d-md-down-none">
          <DefaultHeaderDropdownTasks/>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <DefaultHeaderDropdownMssgs/>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <DefaultHeaderDropdown/>
        </b-nav-item>-->
        <StoreDropdown/>
      </b-navbar-nav>
      <!-- <AsideToggler class="d-none d-lg-block" /> -->
      <!--<AsideToggler class="d-lg-none" mobile />-->
    </AppHeader>
    <div class="app-body">
      <AppSidebar fixed>
        <SidebarHeader/>
        <SidebarForm/>
        <SidebarNav :navItems="navItems"></SidebarNav>
        <SidebarFooter/>
        <SidebarMinimizer/>
      </AppSidebar>
      <main class="main">
        <page-spinner v-if="!initialized"></page-spinner>
        <!-- <Breadcrumb :list="list"/> -->
        <div class="container-fluid" v-if="initialized">
          <router-view></router-view>
        </div>
      </main>
      <AppAside fixed>
        <!--aside-->
        <DefaultAside/>
      </AppAside>
    </div>
    <!-- <TheFooter>
      footer
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
import nav from "./../_storenav";
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
import StoreDropdown from "./StoreDropdown";
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
    StoreDropdown,
    DefaultHeaderDropdownMssgs,
    DefaultHeaderDropdownNotif,
    DefaultHeaderDropdownTasks,
    DefaultHeaderDropdownAccnt,
    SidebarForm,
    SidebarFooter,
    SidebarToggler,
    SidebarHeader,
    SidebarNav,
    SidebarMinimizer
  },
  data() {
    return {
      payments_url: "#payments"
    };
  },
  computed: {
    ...mapGetters(["initialized"]),
    classes() {
      let classes = ['app'];
      classes.push(this.$route.name);
      return classes;
    },
    navItems() {
      return nav.items.map(item => {
        if (item.url === "#payments") {
          item.url = this.payments_url;
        }

        if(!item.class) {
          item.class = '';
        }

        if(item.url === this.$route.path) {
          item.class += ' active';
        }
        
        return item;
      });
    },
    name() {
      return this.$route.name;
    },
    list() {
      return this.$route.matched.filter(
        route => route.name || route.meta.label
      );
    }
  },
  created() {
    axios.get("/api/me/stripe/login").then(resp => {
      if (resp.data.url) {
        this.payments_url = resp.data.url;
      }
    });
  },
  methods: {
    ...mapActions({
    })
  }
};
</script>
