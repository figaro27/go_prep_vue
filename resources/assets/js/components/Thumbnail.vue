<template>
  <div class="thumbnail">
    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="filter hidden">
      <defs>
        <filter id="blur">
          <feGaussianBlur in="SourceGraphic" :stdDeviation="deviation" />
        </filter>
      </defs>
    </svg>
     <v-lazy-image
      :style="{
        width: '100%',
        display: 'inline-block'
      }"
      :src="src"
      :src-placeholder="srcPlaceholder"
      @load="animate"
    ></v-lazy-image>
  </div>
</template>

<script>
export default {
  props: {
    src: String,
    srcPlaceholder: {
      type: String,
      default: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mN88vTZfwAJFAOwsK0F9gAAAABJRU5ErkJggg==',
    },
    blurLevel: {
      type: Number,
      default: 20
    },
    duration: {
      type: Number,
      default: 300
    },
    width: {
      type: Number,
      default: 128,
    },
    height: {
      default: 'auto',
    },
  },
  data: () => ({ rate: 1 }),
  computed: {
    deviation() {
      return this.blurLevel * this.rate;
    }
  },
  methods: {
    animate() {
      const start = Date.now() + this.duration;

      const step = () => {
        const remaining = start - Date.now();

        if (remaining < 0) {
          this.rate = 0;
        } else {
          this.rate = remaining / this.duration;
          requestAnimationFrame(step);
        }
      };

      requestAnimationFrame(step);
    }
  }
};
</script>

<style scoped>
.thumbnail {
  overflow: hidden;
}

.filter {
  display: none;
}

.v-lazy-image {
  width: 100%;
  height: auto;
  /*filter: url("#blur");*/
}
</style>
