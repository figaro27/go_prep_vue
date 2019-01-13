<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Orders</div>
        <div class="card-body">
          {{orders}}
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
    ...mapGetters(["orders"])
  },
  mounted() {
    this.refreshOrders();
  },
  methods: {
    ...mapActions(['refreshOrders']),
  }
};
</script>