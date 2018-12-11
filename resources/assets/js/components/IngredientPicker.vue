<template>
  <div>
    <table class="table w-100">
      <thead>
        <th>Name</th>
        <th>Weight</th>
        <th></th>
      </thead>
      <tbody>
        <tr v-for="ingredient in ingredients" :key="ingredient.id">
          <td>
            <b-form-group>
              <v-select
                class="ingredient-dropdown"
                label="name"
                :filterable="false"
                :options="ingredientOptions"
                @search="onSearch"
                v-model="ingredient.food_name"
              >
                <template slot="no-options">type to search ingredients...</template>
                <template slot="option" slot-scope="option">
                  <div class="d-center">
                    <img :src="option.photo.thumb" class="thumb">
                    {{ option.food_name }}
                  </div>
                </template>
                <template slot="selected-option" slot-scope="option">
                  <div class="selected">
                    <img :src="option.photo.thumb" class="thumb">
                    {{ option.food_name }}
                  </div>
                </template>
              </v-select>
            </b-form-group>
          </td>
          <td>
            <b-form-group>
              <b-form-input placeholder="Weight" v-model="ingredient.serving_qty"></b-form-input>
            </b-form-group>
          </td>
          <td>
            <b-form-group>
              <b-select v-model="ingredient.serving_unit" :options="weightUnitOptions">
                <option slot="top" disabled>-- Select unit --</option>
              </b-select>
            </b-form-group>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="text-right">
            <a href="#" @click="onClickAddIngredient">
              <i class="fas fa-plus-circle"></i>
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<style lang="scss">

.ingredient-dropdown {
  
}
</style>

<script>
import units from "../data/units";
import format from "../lib/format";

export default {
  //components: [],
  props: ["value"],
  data() {
    return {
      ingredients: [],
      ingredientOptions: []
    };
  },
  computed: {
    weightUnitOptions() {
      return units.weight.selectOptions();
    }
  },
  watch: {
    ingredients() {
      this.update();
    },
  },
  created() {
    this.ingredients = _.isArray(this.value) ? this.value : [];
    this.update();
  },
  mounted() {},
  methods: {
    onClickAddIngredient() {
      this.ingredients.push({
        serving_qty: 1,
        serving_unit: "oz"
      });
    },
    update() {
      this.$emit("input", this.ingredients);
    },
    searchInstant: function() {},
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
          vm.ingredientOptions = response.data.common;
          loading(false);
        });
    }, 350)
  }
};
</script>