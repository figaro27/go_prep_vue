let timezones = {
  selectOptions() {
    return Object
      .keys(timezones.values)
      .map((key) => {
        return {text: timezones.values[key], value: key}
      })
  },
  values: {
    HAST: 'Hawaii-Aleutian Standard Time',
    AKST: 'Alaska Standard Time',
    PST: 'Pacific Standard Time',
    MST: 'Mountain Standard Time',
    CST: 'Central Standard Time',
    EST: 'Eastern Standard Time',
    AST: 'Atlantic Standard Time',
    //'America/Sao_Paulo': 'Sao Paulo',
  }
};

export default timezones;