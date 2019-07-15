<template>
  <span>
    <img
      v-b-popover.hover="hint"
      :title="title"
      src="/images/store/popover.png"
      class="popover-size"
    />
  </span>
</template>

<script>
export default {
  props: {
    title: {
      default: "",
      required: false,
      type: String
    }
  },
  computed: {
    hint() {
      return this.$slots.default ? this.$slots.default[0].text : "";
    }
  },
  created() {}
};
</script>

<style></style>
