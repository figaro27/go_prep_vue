<template>
  <div>
    <b-form class="mb-2" @submit.prevent="searchRecipe">
      <div class="d-flex mb-2">
        <b-input v-model="recipe" class="flex-grow-1 mr-1" placeholder="Type Ingredients Here"></b-input>
        <b-button @click="searchRecipe" variant="primary">Search</b-button>
      </div>
      <div class="d-flex mb-2">
        <v-select
          class="flex-grow-1 mr-1"
          placeholder="Or search from your saved ingredients"
          :options="existingIngredientOptions"
          v-model="selectedExistingIngredients"
          multiple
        ></v-select>
        <b-button @click="onClickAddExistingIngredient" variant="primary">Add</b-button>
      </div>
    </b-form>
    <table class="table w-100">
      <thead>
        <th>Name</th>
        <th>Weight</th>
        <th></th>
      </thead>
      <tbody>
        <tr v-for="(ingredient, i) in ingredients" :key="ingredient.id">
          <td>
            <b-form-group>
              <v-select
                class="ingredient-dropdown"
                label="name"
                :filterable="false"
                :options="ingredientOptions"
                @search="onSearch"
                :value="ingredient"
                :onChange="(val) => { ingredients[i] = val }"
              >
                <template slot="no-options">type to search ingredients...</template>
                <template slot="option" slot-scope="option">
                  <div class="d-center">
                    <img v-if="option.photo" :src="option.photo.thumb" class="thumb">
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
import { mapGetters, mapActions } from "vuex";
import units from "../data/units";
import format from "../lib/format";

export default {
  //components: [],
  props: ["value"],
  data() {
    return {
      recipe: "",
      ingredients: [],
      ingredientOptions: [],

      selectedExistingIngredients: [],
    };
  },
  computed: {
    ...mapGetters({
      existingIngredients: "ingredients",
      defaultWeightUnit: "defaultWeightUnit"
    }),
    existingIngredientOptions() {
      return Object.values(this.existingIngredients).map(ingredient => {
        return {
          value: ingredient,
          label: ingredient.food_name,
        };
      });
    },
    weightUnitOptions() {
      return units.weight.selectOptions();
    }
  },
  watch: {
    ingredients() {
      this.update();
    }
  },
  created() {
    this.ingredients = _.isArray(this.value) ? this.value : [];
    this.update();
  },
  mounted() {
    this.refreshIngredients();
  },
  methods: {
    ...mapActions(["refreshIngredients"]),
    onClickAddIngredient() {
      this.ingredients.push({
        food_name: "",
        serving_qty: 1,
        serving_unit: "oz"
      });
    },
    onClickAddExistingIngredient() {
      this.selectedExistingIngredients.forEach((ingredient) => {
        this.ingredients.push(ingredient.value);
      });

      this.selectedExistingIngredients = [];
    },
    update() {
      this.$emit("input", this.ingredients);
    },
    searchInstant: function() {},
    searchRecipe: function() {
      axios
        .post("/nutrients", {
          query: this.recipe
        })
        .then(response => {
          this.ingredients = _.concat(this.ingredients, response.data.foods);
          this.recipe = "";
        });
    },
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