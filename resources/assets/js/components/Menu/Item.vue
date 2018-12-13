<template>
  <b-card :title="title" :img-src="imageUrl" :img-alt="title" img-top>

    <div slot="footer">
      <b-btn>Add to Cart</b-btn>
    </div>
  </b-card>
</template>
<style lang="scss">
</style>

<script>
export default {
  //components: [],
  props: {
    item: {
      required: true
    }
  },
  data() {
    return {};
  },
  computed: {
    title() {
      return this.item.title || null;
    },
    imageUrl() {
      return this.item.featured_image || null;
    },
  },
  watch: {},
  created() {},
  mounted() {},
  methods: {}
};
</script>