
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
import BootstrapVue from 'bootstrap-vue';
import InputTag from '@johmun/vue-tags-input';
import {ServerTable, ClientTable, Event} from 'vue-tables-2';
import 'bootstrap-vue/dist/bootstrap-vue.css'
import vSelect from 'vue-select';
import VueI18n from 'vue-i18n';
import store from './store';
import lang from './lang';
import modal from './lib/modal';

window.Vue = Vue;
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(ClientTable, {}, false, 'bootstrap4', 'default')
Vue.component('input-tag', InputTag)
Vue.component('v-select', vSelect)

const files = require.context('./components', true, /\.vue$/i)
files
  .keys()
  .map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

if($('#customerapp').length) {
  const app = new Vue({
    el: '#customerapp',
    router,
    store,
    template: '<CustomerApp/>',
    components: {
      CustomerApp
    }
  });
}
else if($('#storeapp').length) {
  const app = new Vue({
    el: '#storeapp',
    router,
    store,
    template: '<StoreApp/>',
    components: {
      StoreApp
    }
  });
}
else if ($('#adminapp').length) {
  const app = new Vue({
    el: '#adminapp',
    router,
    store,
    template: '<AdminApp/>',
    components: {
      AdminApp
    }
  });
}


modal.init();

setInterval(() => {
  $(window).trigger('resize');
}, 1000);


