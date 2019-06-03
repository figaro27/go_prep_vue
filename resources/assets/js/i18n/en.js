const messages = {};

const numberFormats = {
  currency: {
    style: "currency",
    currency: "USD"
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
