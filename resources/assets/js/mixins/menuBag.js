export default {
  computed: {},
  methods: {
    itemComponents(item) {
      const meal = this.getMeal(item.meal.id);
      let wat = _(item.components)
        .map((options, componentId) => {
          const component = meal.getComponent(componentId);

          const optionTitles = _(options)
            .map(optionId => {
              const option = meal.getComponentOption(component, optionId);
              return option ? option.title : null;
            })
            .filter()
            .toArray()
            .value();

          return optionTitles;
        })
        .flatten()
        .value();

      return wat;
    }
  }
};
