import VueRouter from 'vue-router';


import Survey from './components/Customer/Survey.vue';
import CustomerBag from './components/Customer/Bag.vue';
import CustomerMenu from './components/Customer/Menu.vue';
import CustomerBilling from './components/Customer/Account/Billing.vue';
import CustomerContact from './components/Customer/Account/Contact.vue';
import CustomerOrders from './components/Customer/Account/Orders.vue';
import CustomerSettings from './components/Customer/Account/Settings.vue';
import CustomerSubscriptions from './components/Customer/Account/Subscriptions.vue';


import StoreCustomers from './components/Store/Customers.vue';
import StoreDashboard from './components/Store/Dashboard.vue';
import StoreIngredients from './components/Store/Ingredients.vue';
import StoreLabels from './components/Store/Labels.vue';
import StoreMeals from './components/Store/Meals.vue';
import StoreOrders from './components/Store/Orders.vue';
import StorePayments from './components/Store/Payments.vue';
import StoreBilling from './components/Store/Account/Billing.vue';
import StoreContact from './components/Store/Account/Contact.vue';
import StoreSettings from './components/Store/Account/Settings.vue';

import AdminCustomers from './components/Admin/Customers.vue';
import AdminDashboard from './components/Admin/Dashboard.vue';
import AdminMeals from './components/Admin/Meals.vue';
import AdminOrders from './components/Admin/Orders.vue';
import AdminPayments from './components/Admin/Payments.vue';
import AdminStores from './components/Admin/Stores.vue';


let routes = [
    {
        path: '/customer/survey',
        component: Survey
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
        path: '/store/labels',
        component: StoreLabels
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
        path: '/store/payments',
        component: StorePayments
    },
    {
        path: '/store/account/billing',
        component: StoreBilling
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
    }

];


export default new VueRouter({
    routes
});