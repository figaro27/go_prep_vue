/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import Vue from 'vue'; // Importing Vue Library
window.Vue = Vue;
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
import VueTimepicker from 'vuejs-timepicker'
import draggable from 'vuedraggable'
import {Card} from 'vue-stripe-elements-plus'
import store from './store';
import lang from './lang';
import format from './lib/format';
import modal from './lib/modal';
import Axios from 'axios';

Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(ClientTable, {}, false, 'bootstrap4', 'default')
Vue.component('input-tag', InputTag)
Vue.component('v-select', vSelect)
Vue.component('timepicker', VueTimepicker)
Vue.component('draggable', draggable)
Vue.component('card', Card)

// For use in templates
Vue.prototype.format = format;

const files = require.context('./components', true, /\.vue$/i)
files
  .keys()
  .map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

if ($('#customerapp').length) {
  const app = new Vue({el: '#customerapp', router, store, template: '<CustomerApp/>', components: {
      CustomerApp
    }});
} else if ($('#storeapp').length) {
  const app = new Vue({el: '#storeapp', router, store, template: '<StoreApp/>', components: {
      StoreApp
    }});
} else if ($('#adminapp').length) {
  const app = new Vue({el: '#adminapp', router, store, template: '<AdminApp/>', components: {
      AdminApp
    }});
}

modal.init();

setInterval(() => {
  $(window).trigger('resize');
}, 1000);

setInterval(() => {
  if (!_.isEmpty(store.getters.user)) {
    axios
      .get('/api/ping')
      .catch(e => {
        window.location = '/login';
      });
  }
}, 30 * 1000);

$(document).on('dblclick', '.VueTables__table tbody > tr', function() {
  $(this).find('.btn.view').click();
  document.getSelection().removeAllRanges();
});