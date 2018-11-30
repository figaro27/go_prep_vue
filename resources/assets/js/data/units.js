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
      kg: 'Kg',
      oz: 'oz',
    }
  }
}

export default units;