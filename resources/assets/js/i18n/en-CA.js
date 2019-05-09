import en from "./en";

const messages = {
  ...en.messages // extend default en lang
};

const numberFormats = {
  currency: {
    style: "currency",
    currency: "CAD"
  }
};

const dateTimeFormats = {
  short: {
    year: "numeric",
    month: "short",
    day: "numeric"
  },
  long: {
    year: "numeric",
    month: "short",
    day: "numeric",
    weekday: "short",
    hour: "numeric",
    minute: "numeric"
  }
};

export default { messages, numberFormats, dateTimeFormats };
