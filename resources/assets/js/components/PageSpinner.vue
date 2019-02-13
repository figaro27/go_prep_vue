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
}
</style>
