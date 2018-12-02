import numeral from 'numeral';

export default {
  money(val) {
    return numeral(val).format('$0,0.00');
  },
  date(val) {
    // todo: implement
    return val;
  },
}