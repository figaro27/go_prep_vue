<template>
  <div class="app customer">
    <b-navbar toggleable="lg" class="app-header" fixed>
      <!-- <div class="navbar-brand"></div> -->
      <b-collapse is-nav id="nav_collapse" class="customer-nav">
        <a :href="storeWebsite">
          <img
            v-if="storeLogo"
            class="store-logo"
            :src="storeLogo.url_thumb"
            alt="Company Logo"
          />
        </a>
        <a :href="storeWebsite" v-if="storeWebsite != null">
          <img
            class="navbar-brand-full"
            :src="topLogo"
            height="75"
            v-if="mobile"
          />
          <img
            class="navbar-brand-minimized"
            :src="topLogo"
            height="75"
            v-if="mobile"
          />
        </a>
        <b-btn
          class="mr-2 d-lg-none"
          variant="light"
          v-if="'id' in viewedStore"
          to="/customer/bag"
          ><i class="fa fa-shopping-bag"></i
        ></b-btn>

        <b-navbar-nav class="d-none d-block d-md-none">
          <b-nav-item v-if="'id' in viewedStore && loggedIn" to="/customer/bag"
            >Bag</b-nav-item
          >
        </b-navbar-nav>
        <b-navbar-nav>
          <b-nav-item v-if="'id' in viewedStore" to="/customer/menu"
            >Menu</b-nav-item
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
          <b-nav-item class="white-text"
            ><i
              class="fas fa-search customer-nav-icon"
              @click.prevent="showSearch()"
            ></i
          ></b-nav-item>
          <b-nav-item class="white-text"
            ><i class="fas fa-filter customer-nav-icon"></i
          ></b-nav-item>
          <CustomerDropdown v-if="loggedIn" />
          <b-nav-item
            v-if="!loggedIn"
            @click.prevent="showAuthModal()"
            class="white-text"
            ><i class="fas fa-user customer-nav-icon"></i
          ></b-nav-item>
          <b-nav-item class="white-text" @click.prevent="showBagArea()"
            ><i class="fas fa-shopping-bag customer-nav-icon"></i
          ></b-nav-item>
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
      {{ bgColor }} !important}
    </v-style>
  </div>
</template>

<style lang="scss" scoped>
main.main {
  position: relative;
}
.main {
  background-color: #ffffff !important;
}

.container-fluid {
  background-color: #ffffff !important;
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
      store: "viewedStore"
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
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    topLogo() {
      if (this.mobile) {
        return this.storeLogo ? this.storeLogo.url_thumb : "";
      } else return "/images/logo.png";
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
  methods: {
    showSearch() {
      this.$eventBus.$emit("showSearchBar");
    },
    showBagArea() {
      this.$eventBus.$emit("showRightBagArea");
    },
    showAuthModal() {
      this.$eventBus.$emit("showAuthModal");
    }
  }
};
</script>
