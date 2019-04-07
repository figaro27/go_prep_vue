<template>
  <div
    :class="'thumbnail' + (loaded ? ' loaded' : '') + (aspect ? ' aspect' : '')"
    @click="onClick"
    :style="`width: ${width}`"
  >
    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="filter hidden">
      <defs>
        <filter id="blur">
          <feGaussianBlur
            in="SourceGraphic"
            :stdDeviation="deviation"
          ></feGaussianBlur>
        </filter>
      </defs>
    </svg>
    <v-lazy-image
      v-if="lazy"
      ref="lazy"
      :style="{
        width: '100%',
        height: 'auto',
        display: 'inline-block'
      }"
      :src="src"
      :src-placeholder="srcPlaceholder"
      @load="onLoaded"
    ></v-lazy-image>
    <img
      v-else
      :src="src"
      :style="{
        width: '100%',
        height: 'auto',
        display: 'inline-block'
      }"
    />

    <div class="spinner" v-if="spinner">
      <div class="lds-ring">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    src: String,
    spinner: {
      type: Boolean,
      default: true
    },
    aspect: {
      type: Boolean,
      default: true
    },
    lazy: {
      type: Boolean,
      default: true
    },
    srcPlaceholder: {
      type: String,
      default:
        "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mN88vTZfwAJFAOwsK0F9gAAAABJRU5ErkJggg=="
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
      type: String,
      default: "128px"
    },
    height: {
      default: "auto"
    }
  },
  data: () => ({ rate: 1, loaded: false }),
  computed: {
    deviation() {
      return this.blurLevel * this.rate;
    }
  },
  mounted() {},
  methods: {
    onLoaded(e) {
      this.loaded = true;
    },
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
    },
    onClick(e) {
      this.$emit("click", e);
    }
  }
};
</script>

<style lang="scss" scoped>
.thumbnail {
  overflow: hidden;
  position: relative;
  background-color: #f7f2f2;

  &.aspect {
    &:before {
      content: "";
      display: block;
      padding-bottom: 100%;
      width: 100%;
    }

    img {
      position: absolute;
      bottom: 0;
      right: 0;
      left: 0;
      top: 0;
    }
  }

  &:after {
  }

  &.loaded {
    .spinner {
      opacity: 0;
    }
  }
}

.spinner {
  position: absolute;
  bottom: 0;
  right: 0;
  left: 0;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  transition: opacity 0.2s;
  pointer-events: none;
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
