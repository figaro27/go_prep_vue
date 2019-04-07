import VueRouter from "vue-router";

import store from "./store";
import auth from "./lib/auth";

import Login from "./views/Login.vue";
import Register from "./views/Register.vue";

import CustomerHome from "./views/Customer/Home.vue";
import CustomerBag from "./views/Customer/Bag.vue";
import CustomerCheckout from "./views/Customer/Checkout.vue";
import CustomerMenu from "./views/Customer/Menu.vue";
import CustomerContact from "./views/Customer/Account/Contact.vue";
import CustomerMyAccount from "./views/Customer/Account/MyAccount.vue";
import CustomerMealPlans from "./views/Customer/MealPlans.vue";
import CustomerMealPlanChange from "./views/Customer/MealPlan.vue";
import CustomerOrders from "./views/Customer/Orders.vue";

import StoreCustomers from "./views/Store/Customers.vue";
import StoreIngredients from "./views/Store/Ingredients.vue";
import StoreReports from "./views/Store/Reports.vue";
import StoreMenu from "./views/Store/Menu.vue";
import StoreProduction from "./views/Store/Production.vue";
import StoreOrders from "./views/Store/Orders.vue";
import StoreMealPlans from "./views/Store/MealPlans.vue";
import StoreMyAccount from "./views/Store/Account/MyAccount.vue";
import StoreStripeConnect from "./views/Store/Account/Stripe.vue";
import StoreContact from "./views/Store/Account/Contact.vue";
import StoreSettings from "./views/Store/Account/Settings.vue";
import StoreMenuPreview from "./views/Store/MenuPreview.vue";
import StoreManualOrder from "./views/Store/ManualOrder.vue";

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
    path: "/register",
    component: Register,
    name: "register",
    meta: {
      bodyClass: "register"
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
    name: "customer-menu"
  },
  {
    path: "/customer/account/billing",
    component: CustomerBilling,
    name: "customer-billing"
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
    path: "/customer/meal-plans",
    component: CustomerMealPlans,
    name: "customer-meal-plans"
  },
  {
    path: "/customer/meal-plans/:id",
    component: CustomerMealPlanChange,
    name: "customer-meal-plan-changes"
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
    path: "/store/past-orders",
    component: StorePastOrders,
    name: "store-past-orders"
  },
  {
    path: "/store/meal-plans",
    component: StoreMealPlans,
    name: "store-meal-plans"
  },
  {
    path: "/store/account/my-account",
    component: StoreMyAccount,
    name: "store-my-account"
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
    path: "/store/menu/manual-order",
    component: StoreManualOrder,
    name: "store-menu-manual-order"
  },
  {
    path: "/admin/dashboard",
    component: AdminDashboard
  },
  {
    path: "/admin/customers",
    component: AdminCustomers
  },
  {
    path: "/admin/meals",
    component: AdminMeals
  },
  {
    path: "/admin/orders",
    component: AdminOrders
  },
  {
    path: "/admin/payments",
    component: AdminPayments
  },
  {
    path: "/admin/stores",
    component: AdminStores
  },
  {
    path: "/spinner",
    component: Spinner
  }
];

const router = new VueRouter({ mode: "history", routes });

router.beforeEach((to, from, next) => {
  // Routes to add class to body. Exclude leading /
  const classRoutes = ["login", "register"];

  // Handle body classes
  classRoutes.forEach(route => {
    if (to.path === "/" + route) {
      $("body").addClass(route);
    } else $("body").removeClass(route);
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
