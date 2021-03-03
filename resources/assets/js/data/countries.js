let countries = {
  countryNames() {
    return countries.values;
  },
  selectOptions() {
    return Object.keys(countries.values).map(key => {
      return {
        value: key,
        text: countries.values[key],
        name: countries.values[key]
      };
    });
  },
  values: {
    US: "United States of America",
    CA: "Canada",
    GB: "Great Britain"
    // BB: "Barbados"
    // BH: "Bahrain"
  }
};

export default countries;
