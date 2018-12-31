let units = {
  mass: {
    selectOptions() {
      return Object
        .keys(units.mass.values)
        .map((key) => {
          return {value: key, text: units.mass.values[key]}
        })
    },
    values: {
      oz: 'oz',
      lb: 'lb',
      g: 'g',
      kg: 'Kg',
    }
  },
  volume: {
    selectOptions() {
      return Object
        .keys(units.volume.values)
        .map((key) => {
          return {value: key, text: units.volume.values[key]}
        })
    },
    values: {
      cups: 'cups',
      ml: 'ml',
      'fl-oz': 'fl. oz.',
    }
  }
}

export default units;