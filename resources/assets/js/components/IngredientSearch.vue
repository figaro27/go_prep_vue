<template>
  <v-select
    ref="select"
    class="ingredient-search"
    label="name"
    :filterable="false"
    :options="options"
    @search="onSearch"
    :value="ingredient"
    :onChange="onChange"
    placeholder="Search Food Database"
  >
    <template slot="no-options">type to search ingredients...</template>
    <template slot="option" slot-scope="option">
      <div class="d-flex option">
        <div class="thumb-wrap">
          <img v-if="option.photo" :src="option.photo.thumb" class="thumb" />
        </div>
        {{ option.food_name }}
      </div>
    </template>
    <template slot="selected-option" slot-scope="option">
      <div class="selected">
        <img v-if="option.photo" :src="option.photo.thumb" class="thumb">
        {{ option.food_name }}
      </div>
    </template>
  </v-select>
</template>
<style lang="scss">
.ingredient-search.v-select {
  display: block;

  .option {
    font-size: 18px;
    height: 45px;
    padding-left: 21px;
    padding-top: 11px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.15);
  }
  .thumb-wrap {
    width: 35px;
    height: 35px;
    margin-top: -6px;
    margin-right: 6px;
  }
  .thumb {
    max-width: 35px;
    max-height: 35px;
    width: 35px;
    height: 35px;
  }
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import units from "../data/units";
import format from "../lib/format";

export default {
  //components: [],
  props: {},
  data() {
    return {
      ingredient: null,
      options: []
    };
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {},
  methods: {
    onSearch(search, loading) {
      loading(true);
      this.search(loading, search, this);
    },
    search: _.debounce((loading, search, vm) => {
      axios
        .post("../searchInstant", {
          search: search
        })
        .then(response => {
          vm.options = response.data.common;
          loading(false);
        });
    }, 500),
    onChange(val) {
      this.ingredient = null;
      this.$refs.select.mutableValue = null;
      this.$emit("change", val);
    }
  }
};
</script>