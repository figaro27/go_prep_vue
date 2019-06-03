/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "./bootstrap";
import Vue from "vue"; // Importing Vue Library
window.Vue = Vue;
import VueRouter from "vue-router"; // importing Vue router library
import router from "./routes";
import App from "./containers/AppContainer";
import CustomerApp from "./containers/CustomerContainer";
import StoreApp from "./containers/StoreContainer";
import AdminApp from "./containers/AdminContainer";
import BootstrapVue from "bootstrap-vue";
import InputTag from "@johmun/vue-tags-input";
import { ServerTable, ClientTable, Event } from "vue-tables-2";
import "bootstrap-vue/dist/bootstrap-vue.css";
import vSelect from "vue-select";
import VueTimepicker from "vuejs-timepicker";
import draggable from "vuedraggable";
import { Card } from "vue-stripe-elements-plus";
import store from "./store";
import lang from "./i18n";
import format from "./lib/format";
import modal from "./lib/modal";
import Axios from "axios";
import moment from "moment";
import momentTimezone from "moment-timezone";
import Toastr from "vue-toastr";
import PictureInput from "vue-picture-input";
import money from "v-money";
import VueNumberInput from "@chenfengyuan/vue-number-input";
import VueRangedatePicker from "vue-rangedate-picker";
import Vuelidate from "vuelidate";
import vUUID from "vue-uuid";
import uuid from "uuid";
import { VLazyImagePlugin } from "v-lazy-image";
import auth from "./lib/auth";
import VuejsDialog from "vuejs-dialog";
import "vuejs-dialog/dist/vuejs-dialog.min.css";
import DisableAutocomplete from "vue-disable-autocomplete";
import wysiwyg from "vue-wysiwyg";
import "vue-wysiwyg/dist/vueWysiwyg.css";
import slugify from "slugify";
import VueLazyLoad from "vue-lazyload";
import Slick from "vue-slick";
import "slick-carousel/slick/slick.css";
import VueObserveVisibility from "vue-observe-visibility";
import i18n from "./i18n";

Vue.use(VueObserveVisibility);
Vue.component("slick", Slick);
Vue.use(VueLazyLoad);
Vue.use(i18n);
Vue.use(wysiwyg, {});
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(ClientTable, {}, false, "bootstrap4", "default");
Vue.use(money, {
  precision: 2,
  prefix: "$"
});
Vue.use(vUUID);
Vue.use(VueNumberInput);
Vue.component("input-tag", InputTag);
Vue.component("v-select", vSelect);
Vue.component("timepicker", VueTimepicker);
Vue.component("draggable", draggable);
Vue.component("card", Card);
Vue.component("picture-input", PictureInput);

Vue.component("v-style", {
  render: function(createElement) {
    return createElement("style", this.$slots.default);
  }
});

Vue.use(Toastr, { defaultProgressBar: false });
Vue.component("date-range-picker", VueRangedatePicker);
Vue.use(Vuelidate);
Vue.use(VLazyImagePlugin);
Vue.use(VuejsDialog, {
  html: true,
  loader: true,
  okText: "Proceed",
  cancelText: "Cancel",
  animation: "bounce"
});
Vue.use(DisableAutocomplete);

import Thumbnail from "./components/Thumbnail";
Vue.component("thumbnail", Thumbnail);

import IngredientSearch from "./components/IngredientSearch";
Vue.component("ingredient-search", IngredientSearch);

import DeliveryDatePicker from "./components/DeliveryDatePicker";
Vue.component("delivery-date-picker", DeliveryDatePicker);

import Spinner from "./components/Spinner";
Vue.component("spinner", Spinner);

import PageSpinner from "./components/PageSpinner";
Vue.component("page-spinner", PageSpinner);

import FloatingActionButton from "./components/FloatingActionButton";
Vue.component("floating-action-button", FloatingActionButton);

/*
moment.defaultFormat = 'ddd, MMMM Do';
moment.defaultFormatUtc = 'ddd, MMMM Do';
moment.fn.toString = function() { this.format(moment.defaultFormat); }
moment.tz.setDefault(moment.tz.guess());
*/
// For use in templates
Vue.prototype.format = format;
Vue.prototype.moment = moment;
Vue.prototype.momentTimezone = momentTimezone;
Vue.prototype.slugify = slugify;
Vue.prototype.icons = require("./lib/icons");

const files = require.context("./components", true, /\.vue$/i);
files.keys().map(key => {
  Vue.component(
    key
      .split("/")
      .pop()
      .split(".")[0],
    files(key)
  );
});

const app = new Vue({
  el: "#app",
  router,
  store,
  template: "<App/>",
  components: {
    App
  }
});

modal.init();

setInterval(() => {
  $(window).trigger("resize");
}, 1000);

setInterval(() => {
  if (!_.isEmpty(store.getters.user)) {
    axios.get("/api/ping").catch(e => {
      window.location = window.app.url + "/login";
    });
  }
}, 60 * 1000);

$(document).on("dblclick", ".VueTables__table tbody > tr", function() {
  $(this)
    .find(".btn.view")
    .click();
  document.getSelection().removeAllRanges();
});

setInterval(function() {
  $(".vs__selected-options > input").prop("readonly", false);
}, 500);

setInterval(function() {
  $(".VueTables__table:not(.responsive)").each(function() {
    $(this).addClass("responsive");
    var table = $(this);
    var tableRow = table.find("tr");
    table.find("td").each(function() {
      var tdIndex = $(this).index();
      if (
        $(tableRow)
          .find("th")
          .eq(tdIndex)
          .attr("data-label")
      ) {
        var thText = $(tableRow)
          .find("th")
          .eq(tdIndex)
          .data("label");
      } else {
        var thText = $(tableRow)
          .find("th")
          .eq(tdIndex)
          .find(".VueTables__heading")
          .text();
      }
      $(this).attr("data-label", thText + ":");
    });
  });
}, 2000);

// Request interceptors for spinner Always create jobs for these routes
// regardless of http method
const jobRoutes = [
  /^\/api\/me\/print/ // all print routes
];

window.axios.interceptors.request.use(config => {
  let job = _.includes(["post", "patch", "delete", "put"], config.method);

  jobRoutes.forEach(route => {
    if (job || route.test(config.url)) {
      job = true;
    }
  });

  if (job) {
    const id = uuid.v1();
    store.dispatch("addJob", { id });
    config.transactionId = id;
  }
  return config;
});

const responseInterceptor = (response, error = false) => {
  if (!_.isEmpty(response.config.transactionId)) {
    const id = response.config.transactionId;
    store.dispatch("removeJob", id);
  }
  if (error) {
    return Promise.reject(response);
  }
  return response;
};

window.axios.interceptors.response.use(responseInterceptor, resp =>
  responseInterceptor(resp, true)
);
// -
