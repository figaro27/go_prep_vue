let timezones = {
  selectOptions() {
    return Object
      .keys(timezones.values)
      .map((key) => {
        return {text: timezones.values[key], value: key}
      })
  },
  values: {
    'America/Adak': 'Hawaii-Aleutian Standard Time',
    'America/Anchorage': 'Alaska Standard Time',
    'America/Los_Angeles': 'Pacific Standard Time',
    'America/Denver': 'Mountain Standard Time',
    'America/Chicago': 'Central Standard Time',
    'America/New_York': 'Eastern Standard Time',
    'America/Puerto_Rico': 'Atlantic Standard Time',
    //'America/Sao_Paulo': 'Sao Paulo',
  }
};

export default timezones;