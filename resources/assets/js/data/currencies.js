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
    USD: "US Dollar ($)",
    CAD: "Canadian Dollar ($)",
    GBP: "Great British Pound (£)"
  }
};

export default countries;
