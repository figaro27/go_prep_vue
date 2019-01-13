<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Subscriptions</div>
        <div class="card-body">
          {{subscriptions}}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
export default {
  components: {},
  data() {
    return {};
  },
  computed: {
    ...mapGetters(["subscriptions"])
  },
  mounted() {
    this.refreshSubscriptions();
  },
  methods: {
    ...mapActions(['refreshSubscriptions']),
  }
};
</script>