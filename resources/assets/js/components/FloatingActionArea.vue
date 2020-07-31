<template>
  <div class="fab" @click="onClick">
    <div class="inner">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    to: {
      type: String,
      default: null
    }
  },
  methods: {
    onClick(e) {
      if (this.to) {
        this.$router.push(this.to);
      }
    }
  }
};
</script>

<style scoped>
.fab {
  position: fixed;

  min-width: 0;
  height: 56px;
  width: 56px;
  padding: 0;
  margin: 0;
  right: 110px;
  bottom: 10px;

  align-items: center;
  display: inline-flex;
  flex: 0 0 auto;
  font-size: 10px;
  font-weight: 999;
  justify-content: center;
  outline: 0;
  text-transform: uppercase;
  text-decoration: none;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1), color 1ms;
  vertical-align: middle;
  user-select: none;

  z-index: 1000000000000000;
}

.fab .inner {
  align-items: center;
  color: inherit;
  display: flex;
  flex: 1 0 auto;
  justify-content: center;
  margin: 0 auto;
  position: relative;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
  white-space: nowrap;
  width: inherit;
}
</style>
