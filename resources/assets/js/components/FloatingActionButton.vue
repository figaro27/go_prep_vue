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

  border-radius: 50%;
  min-width: 0;
  height: 56px;
  width: 56px;
  padding: 0;
  margin: 0;
  right: 16px;
  bottom: 16px;

  align-items: center;
  display: inline-flex;
  flex: 0 0 auto;
  font-size: 14px;
  font-weight: 500;
  justify-content: center;
  outline: 0;
  text-transform: uppercase;
  text-decoration: none;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1), color 1ms;
  vertical-align: middle;
  user-select: none;

  z-index: 1000000000000000;

  border-radius: 50%;
  min-width: 0;
  height: 56px;
  width: 56px;
  padding: 0;

  border-color: rgb(32, 168, 216);

  box-shadow: 0px 3px 5px -1px rgba(0, 0, 0, 0.2),
    0px 6px 10px 0px rgba(0, 0, 0, 0.14), 0px 1px 18px 0px rgba(0, 0, 0, 0.12);
}

.fab .inner {
  align-items: center;
  border-radius: inherit;
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
