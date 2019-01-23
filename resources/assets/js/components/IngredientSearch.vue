<template>
  <div class="ingredient-search d-flex">
    <b-form-group label="Quantity" label-for="ingredient-quantity" class="ing-quantity">
      <b-input id="ingredient-quantity" type="number" min="0" v-model="quantity"></b-input>
    </b-form-group>
    <b-form-group label="Unit" class="unit">
      <b-select v-model="unit">
        <optgroup label="Mass">
          <option
            v-for="option in massOptions"
            :key="option.value"
            :value="option.value"
          >{{option.text}}</option>
        </optgroup>
        <optgroup label="Volume">
          <option
            v-for="option in volumeOptions"
            :key="option.value"
            :value="option.value"
          >{{option.text}}</option>
        </optgroup>
      </b-select>
    </b-form-group>
    <b-form-group label="Name" class="name">
      <v-select
        ref="select"
        label="name"
        :filterable="false"
        :options="options"
        @search="onSearch"
        :value="ingredient"
        :onChange="onChange"
        placeholder="Search Food Database"
        :disabled="!quantity || !unit"
      >
        <template slot="no-options">type to search ingredients...</template>
        <template slot="option" slot-scope="option">
          <div class="d-flex option">
            <div class="thumb-wrap">
              <img v-if="option.photo" :src="option.photo.thumb" class="thumb">
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
    </b-form-group>
  </div>
</template>
<style lang="scss">
.ingredient-search {
  margin: 0 -4px;

  .ing-quantity {
    flex-grow: 0;
    flex-shrink: 1;
    flex-basis: 80px;
    margin: 0 4px;

    input {
      height: 45px;
    }
  }
  .unit {
    flex-grow: 0;
    flex-shrink: 1;
    flex-basis: 80px;
    margin: 0 4px;

    input, select {
      height: 45px;
    }
  }
  .name {
    flex-grow: 1;
    margin: 0 4px;

    .v-select {
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
      options: [],
      quantity: null,
      unit: null,
    };
  },
  computed: {
    massOptions() {
      return units.mass.selectOptions();
    },
    volumeOptions() {
      return units.volume.selectOptions();
    }
  },
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
      this.$emit("change", {
        serving_qty: this.quantity,
        serving_unit: this.unit,
        food_name: val.food_name,
      });
    }
  }
};
</script>