
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import Vue from 'vue'; // Importing Vue Library
import VueRouter from 'vue-router'; // importing Vue router library
import router from './routes';
import CustomerApp from './containers/CustomerContainer';
import StoreApp from './containers/StoreContainer';
import AdminApp from './containers/AdminContainer';
import BootstrapVue from 'bootstrap-vue'
import {ServerTable, ClientTable, Event} from 'vue-tables-2';


window.Vue = Vue;
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(ClientTable, {}, false, 'bootstrap4', 'default')


const customerapp = new Vue({
    el: '#customerapp',
    router,
    template: '<CustomerApp/>',
    components: {
        CustomerApp
    }
});

const storeapp = new Vue({
    el: '#storeapp',
    router,
    template: '<StoreApp/>',
    components: {
        StoreApp
    }
});

const adminapp = new Vue({
    el: '#adminapp',
    router,
    template: '<AdminApp/>',
    components: {
        AdminApp
    }
});


