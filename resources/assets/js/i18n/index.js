const locales = {
  en: require("./en"),
  "en-GB": require("./en-GB"),
  "en-CA": require("./en-CA")
};

const messages = {
  en: locales.en.messages,
  "en-GB": locales["en-GB"].messages,
  "en-CA": locales["en-CA"].messages
};

const numberFormats = {
  en: locales.en.numberFormats,
  "en-GB": locales["en-GB"].numberFormats,
  "en-CA": locales["en-CA"].numberFormats
};

const dateTimeFormats = {
  en: locales.en.dateTimeFormats,
  "en-GB": locales["en-GB"].dateTimeFormats,
  "en-CA": locales["en-CA"].dateTimeFormats
};

// Create VueI18n instance with options
import VueI18n from "vue-i18n";
const i18n = new VueI18n({
  locale: "en", // set locale
  messages,
  numberFormats
});

export default i18n;
