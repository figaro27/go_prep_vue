<template>
  <div class="app customer">
    <auth-modal :showAuthModal="showAuthModal"></auth-modal>
    <b-navbar toggleable="lg" class="app-header" fixed>
      <b-navbar-brand @click="visitStoreWebsite" class="">
        <img
          class="d-md-none d-flex"
          v-if="storeLogo"
          :src="storeLogo.url_thumb"
          height="70"
        />
      </b-navbar-brand>

      <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

      <!-- <i
        class="fas fa-filter customer-nav-icon"
        :style="brandColor"
        style="position:relative;left:75px"
        @click.prevent="showFilterArea()"
        v-if="showBagAndFilters && mobile"
      ></i> -->
      <i
        class="fas fa-shopping-cart customer-nav-icon"
        v-if="showBagAndFilters && mobile"
        :style="brandColor"
        style="position:relative;right:25px"
        @click.prevent="goToBagPage()"
        ><span :class="bagCounter" style="font-size:17px;padding-left:5px">{{
          total
        }}</span></i
      >
      <i
        class="fas fa-arrow-circle-left customer-nav-icon"
        :style="brandColor"
        style="position:relative;right:25px;font-size:45px"
        v-if="onBagPage && mobile"
        @click.prevent="goToMenuPage()"
      ></i>
      <!-- <div class="navbar-brand"></div> -->
      <b-collapse
        is-nav
        id="nav_collapse"
        class="customer-nav"
        target="nav_collapse"
      >
        <a @click="visitStoreWebsite" class="adjust-nav">
          <img
            v-if="storeLogo"
            :class="logoStyle"
            :src="storeLogo.url_thumb"
            alt="Company Logo"
          />
        </a>

        <b-navbar-nav class="adjust-nav">
          <b-nav-item
            v-if="
              'id' in viewedStore &&
                name != 'customer-subscription-changes' &&
                (!userHasSubscriptions ||
                  store.settings.allowMultipleSubscriptions)
            "
            @click.prevent="backToMenu()"
            >Menu</b-nav-item
          >
          <b-nav-item
            v-if="
              'id' in viewedStore &&
                name != 'customer-subscription-changes' &&
                (!userHasSubscriptions ||
                  store.settings.allowMultipleSubscriptions)
            "
            :to="bagURL"
            >Checkout</b-nav-item
          >
          <b-nav-item v-if="loggedIn" to="/customer/orders">Orders</b-nav-item>
          <b-nav-item v-if="loggedInCheck" to="/customer/subscriptions"
            >Subscriptions</b-nav-item
          >
          <b-nav-item v-if="loggedInCheck" to="/customer/account/my-account"
            >My Account</b-nav-item
          >
          <b-nav-item
            v-if="loggedInCheck && store.referral_settings.enabled"
            to="/customer/account/affiliate-dashboard"
            >Affiliate Dashboard</b-nav-item
          >
          <b-nav-item
            v-if="loggedInCheck"
            to="/customer/account/contact"
            class="white-text d-sm-block d-md-none"
            >Contact</b-nav-item
          >
          <b-nav-item
            v-if="loggedInCheck"
            @click="logout()"
            class="white-text d-sm-block d-md-none"
            >Log Out</b-nav-item
          >
          <b-nav-item
            v-if="!loggedInCheck"
            to="/login"
            class="white-text d-sm-block d-md-none"
            >Log In</b-nav-item
          >
          <b-nav-item
            v-if="!loggedInCheck"
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
          <CustomerDropdown v-if="loggedInCheck" class="d-none d-md-block" />
          <b-nav-item
            v-if="!loggedInCheck && !loginPageCheck"
            @click.prevent="showAuthScreen()"
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
      border: none; } .brand-color:hover { background: {{ bgColor }}; border:
      none; } .dbl-underline:after { border-bottom: 3px double {{ bgColor }}; }
      .customer-nav-icon:hover, .nav-item a:hover{color:
      {{ bgColor }} !important} }
    </v-style>
  </div>
</template>

<style lang="scss" scoped>
main.main {
  position: relative;

  > .container-fluid {
    height: 100%;
  }
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import auth from "../lib/auth";
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
import AuthModal from "../components/Customer/AuthModal";

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
    SidebarMinimizer,
    AuthModal
  },
  data() {
    return {
      navBgColor: "",
      bgColor: "",
      pickup: null,
      showAuthModal: false
    };
  },
  computed: {
    ...mapGetters(["initialized", "viewedStore", "loggedIn", "isLoading"]),
    ...mapGetters({
      storeLogo: "viewedStoreLogo",
      store: "viewedStore",
      total: "bagQuantity",
      subscriptions: "subscriptions",
      user: "user"
    }),
    loggedInCheck() {
      return this.loggedIn && this.user.user_role_id !== 4;
    },
    loginPageCheck() {
      return this.$route.path == "/login" || this.$route.path == "/register";
    },
    loggedInAsGuest() {
      return this.user.user_role_id === 4;
    },
    onBagPage() {
      if (this.$route.path === "/customer/bag") {
        return true;
      }
    },
    brandColor() {
      let style = "color:";
      style += this.store.settings.color + " !important";
      return style;
    },
    userHasSubscriptions() {
      if (
        this.loggedIn &&
        this.subscriptions &&
        this.subscriptions.length > 0
      ) {
        return true;
      } else {
        return false;
      }
    },
    menuURL() {
      let referralUrl = this.$route.query.r ? "?r=" + this.$route.query.r : "";
      return "/customer/menu" + referralUrl;
    },
    bagURL() {
      let referralUrl = this.$route.query.r ? "?r=" + this.$route.query.r : "";
      return "/customer/bag" + referralUrl;
    },
    storeSettings() {
      return this.store.settings;
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
  mounted() {},
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
  methods: {
    ...mapActions(["logout"]),
    async showAuthScreen() {
      if (!this.loggedIn) {
        this.showAuthModal = true;
      } else {
        await axios.post("/api/auth/logout");
        auth.deleteToken();
        window.location.href = window.location.origin + "/login";
      }
    },
    visitStoreWebsite() {
      if (!this.storeSettings.website) {
        this.$router.push({ name: "customer-menu" });
      } else {
        let website = this.storeSettings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        window.location.href = website;
      }
    },
    showBagArea() {
      this.$eventBus.$emit("showRightBagArea");
    },
    showFilterArea() {
      this.$eventBus.$emit("showFilterArea");
    },
    backToMenu() {
      this.$router.replace({
        name: "customer-menu",
        params: { topMenuClicked: true }
      });
      // this.$eventBus.$emit("backToMenu");
    },
    goToBagPage() {
      this.$router.replace("/customer/bag");
    },
    goToMenuPage() {
      this.$router.replace("/customer/menu");
    }
  }
};
</script>
