import numeral from "numeral";
import getSymbolFromCurrency from "currency-symbol-map";

export default {
  money(val, currency = "USD") {
    let symbol = getSymbolFromCurrency(currency);
    if (currency == "BHD") {
      return numeral(val).format("0,0.00") + " BD";
    }
    return symbol + numeral(val).format("0,0.00");
  },
  date(val) {
    // todo: implement
    return val;
  },

  nextDay(day) {
    if (!_.isNumber(day)) {
      day = moment(day, "ddd").isoWeekday();
    }
    const today = moment().isoWeekday();

    // if we haven't yet passed the day of the week that I need:
    if (today <= day) {
      // then just give me this week's instance of that day
      return moment().isoWeekday(day);
    } else {
      // otherwise, give me *next week's* instance of that same day
      return moment()
        .add(1, "weeks")
        .isoWeekday(day);
    }
  }
};
