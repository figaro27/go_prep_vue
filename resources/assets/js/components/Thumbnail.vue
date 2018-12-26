<template>
  <img :src="src" :style="{width, height}" />
</template>

<script>
export default {
  name: 'thumbnail',

  props: {
    src: {

    },
    width: {
      default: 128,
    },
    height: {
      default: 'auto',
    },
  },
  computed: {

  },
}
</script>

<style>

</style>
