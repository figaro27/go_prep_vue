<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from "vuex";
export default {
  components: {},
  data() {
    return {};
  },
  mounted() {
    this.refreshPayments();
  },
  methods: {
    ...mapActions({
      refreshPayments: "refreshPayments"
    })
  }
};
</script>