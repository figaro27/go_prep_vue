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
import App from './containers/AppContainer';
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
import moment from 'moment';
import Toastr from 'vue-toastr';
import PictureInput from "vue-picture-input";
import money from "v-money";
import VueNumberInput from '@chenfengyuan/vue-number-input';
import VueRangedatePicker from 'vue-rangedate-picker'
import Vuelidate from 'vuelidate'

Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(ClientTable, {}, false, 'bootstrap4', 'default')
Vue.use(money, {
  precision: 2,
  prefix: '$'
})
Vue.use(VueNumberInput)
Vue.component('input-tag', InputTag)
Vue.component('v-select', vSelect)
Vue.component('timepicker', VueTimepicker)
Vue.component('draggable', draggable)
Vue.component('card', Card)
Vue.component('picture-input', PictureInput)
Vue.use(Toastr, { 
  "defaultProgressBar": false,
  
});
Vue.component('date-range-picker', VueRangedatePicker);
Vue.use(Vuelidate)

import Thumbnail from './components/Thumbnail';
Vue.component('thumbnail', Thumbnail)

import IngredientSearch from './components/IngredientSearch';
Vue.component('ingredient-search', IngredientSearch);

import DeliveryDatePicker from './components/DeliveryDatePicker';
Vue.component('delivery-date-picker', DeliveryDatePicker);

import Spinner from './components/Spinner';
Vue.component('spinner', Spinner);

/*
moment.defaultFormat = 'ddd, MMMM Do';
moment.defaultFormatUtc = 'ddd, MMMM Do';
moment.fn.toString = function() { this.format(moment.defaultFormat); }
moment.tz.setDefault(moment.tz.guess());
*/
// For use in templates
Vue.prototype.format = format;
Vue.prototype.moment = moment;
Vue.prototype.icons = require('./lib/icons');

const files = require.context('./components', true, /\.vue$/i)
files
  .keys()
  .map(key => {
    console.log(key, key.split('/').pop())
    Vue.component(key.split('/').pop().split('.')[0], files(key))
  })

const app = new Vue({el: '#app', router, store, template: '<App/>', components: {
    App
  }});

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

$(document).on('dblclick', '.VueTables__table tbody > tr', function () {
  $(this)
    .find('.btn.view')
    .click();
  document
    .getSelection()
    .removeAllRanges();
});