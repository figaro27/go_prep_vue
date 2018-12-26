import numeral from 'numeral';
import convert from 'convert-units';

export default {
  money(val) {
    return numeral(val).format('$0,0.00');
  },
  date(val) {
    // todo: implement
    return val;
  },
  /**
   *
   * @param {String|Number} val Value
   * @param {String} from From unit
   * @param {String} to To unit
   */
  unit(val, from, to) {
    if (from === 'grams') 
      from = 'g';
    if (to === 'grams') 
      to = 'g';
    
    if (from === 'cups') 
      from = 'cup';
    if (to === 'cups') 
      to = 'cup';
    
    const newVal = convert(val)
      .from(from)
      .to(to);

    return Math.round(newVal * 100) / 100;
  },
  unitType(unit) {
    return convert().describe(unit).measure;
  },
  baseUnit(unitType) {
    switch(unitType) {
      case 'mass':
        return 'g';
      case 'volume':
        return 'ml';
      case 'unit':
        return 'unit';
      default:
        throw new Error('Unrecognized unit type');
    }
  },
  bestUnit(val, unit, extended = false) {
    return unit;

    // todo: finish this
    const best = convert(val).from(unit).toBest({
      exclude: [
        'msk',
      ],
    });
    return extended ? best : best.unit;
  },
}