import VueRouter from "vue-router";
import slugify from "slugify";

import store from "./store";
import auth from "./lib/auth";

import Login from "./views/Login.vue";
import AdminLogin from "./views/AdminLogin.vue";
import Register from "./views/Register.vue";
import Forgot from "./views/ForgotPassword.vue";
import Reset from "./views/ResetPassword.vue";

import CustomerHome from "./views/Customer/Home.vue";
import CustomerBag from "./views/Customer/Bag.vue";
import CustomerCheckout from "./views/Customer/Checkout.vue";
import CustomerMenu from "./views/Customer/Menu.vue";
import CustomerContact from "./views/Customer/Account/Contact.vue";
import CustomerMyAccount from "./views/Customer/Account/MyAccount.vue";
import CustomerSubscriptions from "./views/Customer/Subscriptions.vue";
import CustomerAdjustSubscription from "./views/Customer/AdjustSubscription.vue";
import CustomerOrders from "./views/Customer/Orders.vue";

import StoreCustomers from "./views/Store/Customers.vue";
import StoreIngredients from "./views/Store/Ingredients.vue";
import StoreReports from "./views/Store/Reports.vue";
import StoreMenu from "./views/Store/Menu.vue";
import StoreProduction from "./views/Store/Production.vue";
import StoreOrders from "./views/Store/Orders.vue";
import StoreSubscriptions from "./views/Store/Subscriptions.vue";
import StoreMyAccount from "./views/Store/Account/MyAccount.vue";
import StoreBilling from "./views/Store/Account/Billing.vue";
import StoreStripeConnect from "./views/Store/Account/Stripe.vue";
import StoreContact from "./views/Store/Account/Contact.vue";
import StoreSettings from "./views/Store/Account/Settings.vue";
import StoreMenuPreview from "./views/Store/MenuPreview.vue";
import StoreManualOrder from "./views/Store/ManualOrder.vue";
import StoreAdjustOrder from "./views/Store/AdjustOrder.vue";
import StoreAdjustSubscription from "./views/Store/AdjustSubscription.vue";
import StoreBag from "./views/Store/Bag.vue";
import StoreUpdates from "./views/Store/Updates.vue";
import StoreMarketing from "./views/Store/Marketing.vue";
import StoreSMS from "./views/Store/SMS.vue";
import StoreFinancials from "./views/Store/Financials.vue";

import Spinner from "./components/Spinner.vue";

const middleware = {
  role: {
    customer: (to, from, next) => {
      if (!store.getters.loggedIn) {
        next("/login");
      } else {
        next();
      }
    }
  }
};

let routes = [
  {
    path: "/login",
    component: Login,
    name: "login",
    meta: {
      bodyClass: "login"
    },
    props(route) {
      return route.query;
    }
  },
  {
    path: "/admin",
    component: AdminLogin,
    name: "adminLogin",
    meta: {
      bodyClass: "adminLogin"
    },
    props(route) {
      return route.query;
    }
  },
  {
    path: "/register",
    component: Register,
    name: "register",
    meta: {
      bodyClass: "register"
    },
    props(route) {
      return route.query;
    }
  },
  {
    path: "/forgot",
    component: Forgot,
    name: "forgot",
    meta: {
      bodyClass: "forgot"
    }
  },
  {
    path: "/forgot/reset/:token",
    component: Reset,
    name: "reset",
    meta: {
      bodyClass: "reset"
    }
  },
  {
    path: "/customer/home",
    component: CustomerHome,
    name: "customer-home",
    beforeEnter: (to, from, next) => {
      window.location = window.app.front_url;
    }
  },
  {
    path: "/customer/bag",
    component: CustomerBag,
    name: "customer-bag"
  },
  {
    path: "/customer/checkout",
    component: CustomerCheckout,
    name: "customer-checkout"
  },
  {
    path: "/customer/menu",
    component: CustomerMenu,
    name: "customer-menu",
    meta: {
      bodyClass: "menu"
    }
  },
  {
    path: "/customer/account/contact",
    component: CustomerContact,
    name: "customer-contact"
  },
  {
    path: "/customer/account/my-account",
    component: CustomerMyAccount,
    name: "customer-myaccount"
  },
  {
    path: "/customer/subscriptions",
    component: CustomerSubscriptions,
    name: "customer-subscriptions"
  },
  {
    path: "/customer/adjust-subscription/:id",
    component: CustomerAdjustSubscription,
    name: "customer-adjust-subscription"
  },
  {
    path: "/customer/orders",
    component: CustomerOrders,
    name: "customer-orders"
  },
  {
    path: "/store/customers",
    component: StoreCustomers,
    name: "store-customers"
  },
  {
    path: "/store/ingredients",
    component: StoreIngredients,
    name: "store-ingredients"
  },
  {
    path: "/store/reports",
    component: StoreReports,
    name: "store-reports"
  },
  {
    path: "/store/menu",
    component: StoreMenu,
    name: "store-menu"
  },
  {
    path: "/store/production",
    component: StoreProduction,
    name: "store-production"
  },
  {
    path: "/store/orders",
    component: StoreOrders,
    name: "store-orders"
  },
  {
    path: "/store/subscriptions",
    component: StoreSubscriptions,
    name: "store-subscriptions"
  },
  {
    path: "/store/account/my-account",
    component: StoreMyAccount,
    name: "store-my-account"
  },
  {
    path: "/store/account/billing",
    component: StoreBilling,
    name: "store-billing"
  },
  {
    path: "/store/stripe/redirect",
    component: StoreStripeConnect,
    name: "store-stripe-connect"
  },
  {
    path: "/store/account/contact",
    component: StoreContact,
    name: "store-contact"
  },
  {
    path: "/store/account/settings",
    component: StoreSettings,
    name: "store-settings"
  },
  {
    path: "/store/menu/preview",
    component: StoreMenuPreview,
    name: "store-menu-preview"
  },
  {
    path: "/store/manual-order",
    component: StoreManualOrder,
    name: "store-manual-order"
  },
  {
    path: "/store/adjust-order",
    component: StoreAdjustOrder,
    name: "store-adjust-order"
  },
  {
    path: "/store/adjust-subscription/:id",
    component: StoreAdjustSubscription,
    name: "store-adjust-subscription"
  },
  {
    path: "/store/bag",
    component: StoreBag,
    name: "store-bag"
  },
  {
    path: "/store/updates",
    component: StoreUpdates,
    name: "store-updates"
  },
  {
    path: "/store/marketing",
    component: StoreMarketing,
    name: "store-marketing"
  },
  {
    path: "/store/SMS",
    component: StoreSMS,
    name: "store-sms"
  },
  {
    path: "/store/Financials",
    component: StoreFinancials,
    name: "store-financials"
  },
  {
    path: "/spinner",
    component: Spinner
  }
];

const router = new VueRouter({ mode: "history", routes });

router.beforeEach(async (to, from, next) => {
  await store.dispatch("ensureInitialized");

  if (to.fullPath === "/") {
    if (store.state.context === "store") {
      return next("/store/orders");
    } else if (
      store.state.context === "customer" ||
      store.state.context === "guest"
    ) {
      if (
        _.isNull(store.getters.viewedStore) ||
        !store.getters.viewedStore.id
      ) {
        return next("/customer/home");
      } else {
        return next("/customer/menu");
      }
    }
  }

  // Routes to add class to body. Exclude leading /
  const classRoutes = [
    "login",
    "register",
    "forgot",
    "forgot/reset/:token",
    "customer/menu",
    "customer/adjust-subscription/:id"
  ];

  // Handle body classes
  classRoutes.forEach(route => {
    let className = slugify(route, {
      remove: /[*+~.()'"!:@\/]/
    });

    if (
      to.path === "/" + route ||
      (to.matched[0] && to.matched[0].path === "/" + route)
    ) {
      $("body").addClass(className);
    } else $("body").removeClass(className);
  });

  if (!auth.hasToken()) {
    const redirectRoutes = [
      /^\/store.*/,
      /^\/customer\/((?!home|menu|bag).*)\/?$/
    ];

    let matched = false;

    redirectRoutes.forEach(route => {
      if (matched || route.test(to.fullPath)) {
        matched = true;
      }
    });

    if (matched) {
      next({
        path: "/login",
        query: {
          redirect: to.path
        }
      });
    }
  }

  next();
});

export default router;
