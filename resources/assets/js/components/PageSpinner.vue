<template>
  <div :class="classes">
    <spinner></spinner>
  </div>
</template>

<script>
export default {
  props: {
    faded: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    classes() {
      let classes = ['page-spinner'];
      if(this.faded) {
        classes.push('faded');
      }
      return classes;
    }
  }
}
</script>

<style lang="scss" scoped>
.page-spinner {
  position: fixed;
  top: 55px;
  left: 0;
  right: 0;
  bottom: 0;

  z-index: 10005;

  display: flex;
  align-items: center;
  justify-content: center;

  background-color: #e4e5e6;

  &.faded {
    background-color: transparent;
    pointer-events: none;
  }
}
</style>
