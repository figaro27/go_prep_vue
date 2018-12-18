let units = {
  weight: {
    selectOptions() {
      return Object.keys(units.weight.values).map((key) => {
        return {
          value: key,
          text: units.weight.values[key],
        }
      })
    },
    values: {
      oz: 'oz',
      lb: 'lb',
      grams: 'g',
      kg: 'Kg',
      cups: 'cups',
    }
  }
}

export default units;