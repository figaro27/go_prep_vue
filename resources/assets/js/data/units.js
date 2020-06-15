import convert from "convert-units";

let map = {
  tablespoons: "Tbs",
  tablespoon: "Tbs",
  teaspoons: "tsp",
  teaspoon: "tsp",
  tbsp: "Tbs",
  tbs: "Tbs",
  milligramme: "mg",
  milligrammes: "mg",
  liter: "l",
  liters: "l",
  "fl oz": "fl-oz"
};
convert()
  .list()
  .forEach(unit => {
    map[unit.singular.toLowerCase()] = unit.abbr;
    map[unit.plural.toLowerCase()] = unit.abbr;
    map[unit.abbr] = unit.abbr;
  });

let units = {
  mass: {
    selectOptions() {
      return Object.keys(units.mass.values).map(key => {
        return { value: key, text: units.mass.values[key] };
      });
    },
    values: {
      mg: "mg",
      g: "g",
      oz: "oz",
      lb: "lb",
      kg: "kg"
    }
  },
  volume: {
    selectOptions() {
      return Object.keys(units.volume.values).map(key => {
        return { value: key, text: units.volume.values[key] };
      });
    },
    values: {
      // Imperial
      tsp: "tsp",
      Tbs: "tbsp",
      "fl-oz": "fl. oz.",
      cup: "cup",
      pnt: "pint",
      qt: "quart",
      gal: "gallon",

      // Metric
      ml: "ml",
      l: "liter"
    }
  },

  /**
   *
   * @param {String|Number} val Value
   * @param {String} from From unit
   * @param {String} to To unit
   */
  convert(val, from, to, round = true) {
    from = units.normalize(from);
    to = units.normalize(to);

    if (from === "unit" || to === "unit") {
      return val;
    }

    if (from === "grams") from = "g";
    if (to === "grams") to = "g";

    if (from === "cups") from = "cup";
    if (to === "cups") to = "cup";

    val = convert(val)
      .from(from)
      .to(to);

    if (round) {
      val = Math.round(val * 100) / 100;
    }
    return val;
  },
  normalize(unitName) {
    unitName = unitName.toLowerCase();

    if (unitName === "unit") {
      return "unit";
    }

    if (unitName in map) {
      let unit = map[unitName];
      return unit;
    }
    return unitName;
  },
  describe(unit) {
    return convert().describe(unit);
  },
  toBest(val, from, extended = false) {
    from = this.normalize(from);

    let exclude = _.concat(excluded, ["kg", "ml", "l"]);

    let options = {
      exclude: exclude,
      cutOffNumber: 1
    };

    let best;

    convert()
      .from(from)
      .possibilities()
      .forEach(possibility => {
        var unit = convert().describe(possibility);
        var isIncluded = options.exclude.indexOf(possibility) === -1;

        if (isIncluded) {
          var result = convert(val)
            .from(from)
            .to(possibility);
          if (!best || (result >= options.cutOffNumber && result < best.val)) {
            best = {
              val: result,
              unit: possibility,
              singular: unit.singular,
              plural: unit.plural
            };
          }
        }
      });

    if (!best) {
      return unit;
    }

    return extended ? best : best.unit;
  },
  type(unit) {
    try {
      return convert().describe(unit).measure;
    } catch (e) {
      return "unit";
    }
  },
  base(unitType) {
    switch (unitType) {
      case "mass":
        return "g";
      case "volume":
        return "ml";
      case "unit":
        return "unit";
      default:
        throw new Error("Unrecognized unit type");
    }
  }
};

const allUnits = _.concat(
  Object.keys(units.mass.values),
  Object.keys(units.volume.values)
);

let excluded = _.filter(convert().possibilities(), unit => {
  let has = allUnits.includes(unit);
  return !has;
});

export default units;
