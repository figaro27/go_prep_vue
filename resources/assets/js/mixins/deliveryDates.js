import { mapGetters } from "vuex";

export default {
  computed: {
    ...mapGetters({
      nextDeliveryDates: 'storeNextDeliveryDates'
    })
  },
  methods : {
    checkDateRange(dateRange) {
      var warning = false;
      this.nextDeliveryDates.forEach(delDate => {
        const date = moment
          .utc(delDate.date)
          .unix();
        const cutoff = moment
          .utc(delDate.cutoff)
          .unix();
        const included = date > dateRange
          .start
          .unix() && date < dateRange
          .end
          .unix();
        if (!delDate.cutoff_passed && included) {
          warning = true;
        }
      });
      return warning;
    }
  }
}