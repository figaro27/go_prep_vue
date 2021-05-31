<template>
  <div>
    <div v-if="smsSettings.phone">
      <p><b>Monthly SMS Number Charge:</b> $8.00</p>
      <p v-if="store.settings.account_type === 'express'">
        <b>Upcoming Number Charge:</b>
        {{
          moment(smsSettings.last_payment)
            .add(1, "M")
            .format("M/DD/YYYY")
        }}
      </p>
      <p>
        <b>Current Message Balance:</b> {{ format.money(smsSettings.balance) }}
      </p>
      <p v-if="store.settings.account_type === 'express'">
        <b>Upcoming Message Balance Charge:</b> This will occur every time you
        cross a $10.00 threshold.
      </p>
      <div v-if="store.settings.account_type === 'standard'">
        <p>
          <b>Upcoming Charge:</b>
          {{
            moment
              .unix(smsSettings.subscription.current_period_end)
              .format("M/DD/YYYY")
          }}
        </p>
      </div>
    </div>
    <div v-else>
      You have not yet purchased a phone number. You won't be charged.
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from "../../../lib/format";

export default {
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      smsSettings: "storeSMSSettings"
    })
  },
  methods: {}
};
</script>
