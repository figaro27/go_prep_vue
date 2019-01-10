import VueRouter from 'vue-router';

import CustomerHome from './views/Customer/Home.vue';
import CustomerBag from './views/Customer/Bag.vue';
import CustomerMenu from './views/Customer/Menu.vue';
import CustomerBilling from './views/Customer/Account/Billing.vue';
import CustomerContact from './views/Customer/Account/Contact.vue';
import CustomerOrders from './views/Customer/Account/Orders.vue';
import CustomerSettings from './views/Customer/Account/Settings.vue';
import CustomerSubscriptions from './views/Customer/Account/Subscriptions.vue';

import StoreCustomers from './views/Store/Customers.vue';
import StoreDashboard from './views/Store/Dashboard.vue';
import StoreIngredients from './views/Store/Ingredients.vue';
import StorePrint from './views/Store/Print.vue';
import StoreMeals from './views/Store/Meals.vue';
import StoreOrders from './views/Store/Orders.vue';
import StorePastOrders from './views/Store/PastOrders.vue';
import StorePayments from './views/Store/Payments.vue';
import StoreContact from './views/Store/Account/Contact.vue';
import StoreSettings from './views/Store/Account/Settings.vue';
//Will move to customers
import StoreMenu from './views/Store/Menu.vue';

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
        component: CustomerHome
    },
    {
        path: '/customer/bag',
        component: CustomerBag
    },
    {
        path: '/customer/menu',
        component: CustomerMenu
    },
    {
        path: '/customer/account/billing',
        component: CustomerBilling
    },
    {
        path: '/customer/account/contact',
        component: CustomerContact
    },
    {
        path: '/customer/account/orders',
        component: CustomerOrders
    },
    {
        path: '/customer/account/settings',
        component: CustomerSettings
    },
    {
        path: '/customer/account/subscriptions',
        component: CustomerSubscriptions
    },
    {
        path: '/store/dashboard',
        component: StoreDashboard
    },
    {
        path: '/store/customers',
        component: StoreCustomers
    },
    {
        path: '/store/ingredients',
        component: StoreIngredients
    },
    {
        path: '/store/print',
        component: StorePrint
    },
    {
        path: '/store/meals',
        component: StoreMeals
    },
    {
        path: '/store/orders',
        component: StoreOrders
    },
    {
        path: '/store/past-orders',
        component: StorePastOrders
    },
    {
        path: '/store/payments',
        component: StorePayments
    },
    {
        path: '/store/account/contact',
        component: StoreContact
    },
    {
        path: '/store/account/settings',
        component: StoreSettings
    },
    {
        path: '/store/menu',
        component: StoreMenu
    },
    {
        path: '/admin/dashboard',
        component: AdminDashboard
    },
    {
        path: '/admin/customers',
        component: AdminCustomers
    },
    {
        path: '/admin/meals',
        component: AdminMeals
    },
    {
        path: '/admin/orders',
        component: AdminOrders
    },
    {
        path: '/admin/payments',
        component: AdminPayments
    },
    {
        path: '/admin/stores',
        component: AdminStores
    },
    {
        path: '/spinner',
        component: Spinner
    }
];

export default new VueRouter({
    mode: 'history',
    routes
});