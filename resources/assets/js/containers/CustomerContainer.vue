<template>
  <div class="app customer">
    <b-navbar toggleable="lg" class="app-header" fixed>
      <b-navbar-brand :href="storeWebsite" class="">
        <img
          class="d-md-none d-flex"
          v-if="storeLogo"
          :src="storeLogo.url_thumb"
          height="70"
        />
      </b-navbar-brand>

      <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>
      <!-- <div class="navbar-brand"></div> -->
      <b-collapse
        is-nav
        id="nav_collapse"
        class="customer-nav"
        target="nav_collapse"
      >
        <a :href="storeWebsite" class="adjust-nav">
          <img
            v-if="storeLogo"
            :class="logoStyle"
            :src="storeLogo.url_thumb"
            alt="Company Logo"
          />
        </a>

        <b-navbar-nav class="adjust-nav">
          <b-nav-item v-if="'id' in viewedStore" to="/customer/menu"
            >Menu</b-nav-item
          >
          <b-nav-item v-if="'id' in viewedStore" to="/customer/bag"
            >Checkout</b-nav-item
          >
          <b-nav-item v-if="loggedIn" to="/customer/orders">Orders</b-nav-item>
          <b-nav-item v-if="loggedIn" to="/customer/subscriptions"
            >Subscriptions</b-nav-item
          >
          <b-nav-item
            v-if="loggedIn"
            to="/customer/account/my-account"
            class="white-text d-sm-block d-md-none"
            >My Account</b-nav-item
          >
          <b-nav-item
            v-if="loggedIn"
            to="/customer/account/contact"
            class="white-text d-sm-block d-md-none"
            >Contact</b-nav-item
          >
          <b-nav-item
            v-if="loggedIn"
            @click="logout()"
            class="white-text d-sm-block d-md-none"
            >Log Out</b-nav-item
          >
          <b-nav-item
            v-if="!loggedIn"
            to="/login"
            class="white-text d-sm-block d-md-none"
            >Log In</b-nav-item
          >
          <b-nav-item
            v-if="!loggedIn"
            class="px-3 mr-4 white-text d-sm-block d-md-none"
            to="/register"
            >Register</b-nav-item
          >
        </b-navbar-nav>
        <b-navbar-nav class="ml-auto adjust-nav">
          <b-nav-item
            class="white-text d-none d-md-block"
            @click.prevent="showFilterArea()"
            v-if="showBagAndFilters"
            ><i class="fas fa-filter customer-nav-icon"></i
          ></b-nav-item>
          <CustomerDropdown v-if="loggedIn" class="d-none d-md-block" />
          <b-nav-item
            v-if="!loggedIn"
            @click.prevent="showAuthModal()"
            class="white-text d-none d-md-block"
            ><i class="fas fa-user customer-nav-icon"></i
          ></b-nav-item>
          <b-nav-item
            class="white-text"
            @click.prevent="showBagArea()"
            v-if="showBagAndFilters"
            ><i class="fas fa-shopping-bag customer-nav-icon d-none d-md-block"
              ><span :class="bagCounter">{{ total }}</span></i
            ></b-nav-item
          >
        </b-navbar-nav>
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
      .menu-bag-btn, .brand-color, .filters .active { background: {{ bgColor }};
      } .dbl-underline:after { border-bottom: 3px double {{ bgColor }}; }
      .customer-nav-icon:hover, .nav-item a:hover{color:
      {{ bgColor }} !important} }
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
      bgColor: ""
    };
  },
  computed: {
    ...mapGetters(["initialized", "viewedStore", "loggedIn", "isLoading"]),
    ...mapGetters({
      storeLogo: "viewedStoreLogo",
      store: "viewedStore",
      total: "bagQuantity"
    }),
    storeSettings() {
      return this.store.settings;
    },
    storeWebsite() {
      if (!this.storeSettings.website) {
        return null;
      } else {
        let website = this.storeSettings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        return website;
      }
    },
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
    showBagAndFilters() {
      if (
        this.$route.name === "customer-menu" ||
        this.$route.name === "customer-subscription-changes"
      )
        return true;
      else return false;
    },
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    topLogo() {
      if (this.mobile) {
        return this.storeLogo ? this.storeLogo.url_thumb : "";
      } else return "/images/logo.png";
    },
    bagCounter() {
      if (this.total >= 10) return "bag-counter bag-counter-adjust";
      else return "bag-counter";
    },
    logoStyle() {
      // if the logo is less than 70px in height then return '' - need package to get height of the image
      return "store-logo d-none d-md-block";
    }
  },
  updated() {
    if (this.mobile) {
      this.navBgColor === "#ffffff !important";
    } else {
      this.navBgColor = this.viewedStore.settings.color + "!important";
    }

    if (this.viewedStore.settings.color != "#3082cf") {
      this.bgColor = this.viewedStore.settings.color;
    } else {
      this.bgColor = "#F25727";
    }

    // let page = this.name;
    // // if (page != 'customer-home' && page != 'login' && page != 'register' && page != 'customer-orders' && page != 'customer-subscriptions')
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
  methods: {
    ...mapActions(["logout"]),
    showBagArea() {
      this.$eventBus.$emit("showRightBagArea");
    },
    showFilterArea() {
      this.$eventBus.$emit("showFilterArea");
    },
    showAuthModal() {
      this.$eventBus.$emit("showAuthModal");
    }
  }
};
</script>
