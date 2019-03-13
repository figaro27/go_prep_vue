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
      if(!dateRange.start) {
        dateRange.start = moment.utc('1000-01-01')
      }
      if(!dateRange.end) {
        dateRange.end = moment.utc('3000-01-01')
      }
      this.nextDeliveryDates.forEach(delDate => {
        const date = moment
          .utc(delDate.date)
          .hours(12)
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