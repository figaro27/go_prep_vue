<template>
  <div class="page-spinner">
    <spinner></spinner>
  </div>
</template>

<script>
export default {

}
</script>

<style lang="scss" scoped>
.page-spinner {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  z-index: 50;

  display: flex;
  align-items: center;
  justify-content: center;

  background-color: #e4e5e6;
}
</style>
