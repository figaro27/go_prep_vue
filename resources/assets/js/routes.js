import VueRouter from 'vue-router';

import CustomerHome from './views/Customer/Home.vue';
import CustomerBag from './views/Customer/Bag.vue';
import CustomerCheckout from './views/Customer/Checkout.vue';
import CustomerMenu from './views/Customer/Menu.vue';
import CustomerBilling from './views/Customer/Account/Billing.vue';
import CustomerContact from './views/Customer/Account/Contact.vue';
import CustomerMyAccount from './views/Customer/Account/MyAccount.vue';
import CustomerSubscriptions from './views/Customer/Subscriptions.vue';
import CustomerOrders from './views/Customer/Orders.vue';

import StoreCustomers from './views/Store/Customers.vue';
import StoreIngredients from './views/Store/Ingredients.vue';
import StoreReports from './views/Store/Reports.vue';
import StoreMenu from './views/Store/Menu.vue';
import StoreProduction from './views/Store/Production.vue';
import StoreOrders from './views/Store/Orders.vue';
import StorePastOrders from './views/Store/PastOrders.vue';
import StoreMealPlans from './views/Store/MealPlans.vue';
import StorePayments from './views/Store/Payments.vue';
import StoreMyAccount from './views/Store/Account/MyAccount.vue';
import StoreContact from './views/Store/Account/Contact.vue';
import StoreSettings from './views/Store/Account/Settings.vue';
import StoreMenuPreview from './views/Store/MenuPreview.vue';

import AdminCustomers from './views/Admin/Customers.vue';
import AdminDashboard from './views/Admin/Dashboard.vue';
import AdminMeals from './views/Admin/Meals.vue';
import AdminOrders from './views/Admin/Orders.vue';
import AdminPayments from './views/Admin/Payments.vue';
import AdminStores from './views/Admin/Stores.vue';

import Spinner from './components/Spinner.vue';

let routes = [
  {
    path: '/customer/home',
    component: CustomerHome,
    name: 'customer-home'
  }, {
    path: '/customer/bag',
    component: CustomerBag,
    name: 'customer-bag'
  }, {
    path: '/customer/checkout',
    component: CustomerCheckout,
    name: 'customer-checkout'
  }, {
    path: '/customer/menu',
    component: CustomerMenu,
    name: 'customer-menu'
  }, {
    path: '/customer/account/billing',
    component: CustomerBilling,
    name: 'customer-billing'
  }, {
    path: '/customer/account/contact',
    component: CustomerContact,
    name: 'customer-contact'
  }, {
    path: '/customer/account/my-account',
    component: CustomerMyAccount,
    name: 'customer-myaccount'
  }, {
    path: '/customer/subscriptions',
    component: CustomerSubscriptions,
    name: 'customer-subscriptions'
  }, {
    path: '/customer/orders',
    component: CustomerOrders,
    name: 'customer-orders'
  }, {
    path: '/store/customers',
    component: StoreCustomers,
    name: 'store-customers'
  }, {
    path: '/store/ingredients',
    component: StoreIngredients,
    name: 'store-ingredients'
  }, {
    path: '/store/reports',
    component: StoreReports,
    name: 'store-reports'
  }, {
    path: '/store/menu',
    component: StoreMenu,
    name: 'store-menu'
  }, {
    path: '/store/production',
    component: StoreProduction,
    name: 'store-production'
  }, {
    path: '/store/orders',
    component: StoreOrders,
    name: 'store-orders'
  }, {
    path: '/store/past-orders',
    component: StorePastOrders,
    name: 'store-past-orders'
  }, {
    path: '/store/meal-plans',
    component: StoreMealPlans,
    name: 'store-meal-plans'
  }, {
    path: '/store/payments',
    component: StorePayments,
    name: 'store-payments'
  }, {
    path: '/store/account/my-account',
    component: StoreMyAccount,
    name: 'store-my-account'
  }, {
    path: '/store/account/contact',
    component: StoreContact,
    name: 'store-contact'
  }, {
    path: '/store/account/settings',
    component: StoreSettings,
    name: 'store-settings'
  }, {
    path: '/store/menu/preview',
    component: StoreMenuPreview,
    name: 'store-menu-preview'
  }, {
    path: '/admin/dashboard',
    component: AdminDashboard
  }, {
    path: '/admin/customers',
    component: AdminCustomers
  }, {
    path: '/admin/meals',
    component: AdminMeals
  }, {
    path: '/admin/orders',
    component: AdminOrders
  }, {
    path: '/admin/payments',
    component: AdminPayments
  }, {
    path: '/admin/stores',
    component: AdminStores
  }, {
    path: '/spinner',
    component: Spinner
  }
];

export default new VueRouter({mode: 'history', routes});