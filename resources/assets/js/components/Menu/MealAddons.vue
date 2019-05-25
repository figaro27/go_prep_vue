<template>
  <div>
    <div v-if="ingredientAddonId !== null && ingredientOptionId !== null">
      <ingredient-picker
        ref="ingredientPicker"
        v-model="
          meal.addons[ingredientAddonId].options[ingredientOptionId].ingredients
        "
        :options="{ saveButton: true }"
        :meal="meal"
        @save="val => onChangeIngredients(val)"
      ></ingredient-picker>
    </div>

    <div v-else>
      <div class="mb-4">
        <b-button variant="primary" @click="addAddon()"
          >Add Meal Addon</b-button
        >
      </div>

      <div v-for="(addon, i) in meal.addons" :key="addon.id" role="tablist">
        <div class="addon-header mb-2">
          <h4 class="d-inline-block">#{{ i + 1 }}. {{ addon.title }}</h4>
          <b-btn variant="danger" class="pull-right">Delete</b-btn>
        </div>
        <b-row>
          <b-col cols="6">
            <b-form-group label="Title">
              <b-input
                v-model="addon.title"
                placeholder="i.e. Extra Meat"
              ></b-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Price">
              <money
                :disabled="addon.id === -1"
                required
                v-model="addon.price"
                :min="0.1"
                :max="999.99"
                class="form-control"
              ></money>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Maximum">
              <b-input v-model="addon.maximum"></b-input>
            </b-form-group>
          </b-col>
        </b-row>
        <hr v-if="i < meal.addons.length - 1" class="my-4" />
      </div>

      <div v-if="meal.addons.length" class="mt-4">
        <b-button variant="primary" @click="addAddon()"
          >Add Meal Addon</b-button
        >
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ingredient-picker {
  //position: absolute;
}
.addon-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>

<script>
import IngredientPicker from "../IngredientPicker";

export default {
  addons: {
    IngredientPicker
  },
  props: {
    meal: {
      required: true
    }
  },
  data() {
    return {
      ingredientAddonId: null,
      ingredientOptionId: null
    };
  },
  computed: {
    sizeOptions() {
      return _.concat(
        {
          text: "All sizes",
          value: null
        },
        this.meal.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    }
  },
  watch: {
    "meal.addons": function() {
      this.onChangeAddons();
    }
  },
  created() {
    this.onChangeAddons = _.debounce(this.onChangeAddons, 2000);
  },
  mounted() {},
  methods: {
    addAddon() {
      this.meal.addons.push({
        id: 100 + this.meal.addons.length, // push to the end of table
        title: "",
        minimum: 1,
        maximum: 1,
        options: [
          {
            id: 0,
            title: "",
            price: null,
            ingredients: [],
            meal_size_id: null
          }
        ]
      });
    },
    deleteAddon(id) {
      this.meal.addons = _.filter(this.meal.addons, addon => {
        return addon.id !== id;
      });
      this.onChangeAddons();
    },
    deleteAddonOption(addonId, id) {
      let options = this.meal.addons[addonId].options;

      options = _.filter(options, option => {
        return option.id !== id;
      });

      this.meal.addons[addonId].options = options;
      this.onChangeAddons();
    },
    onChangeAddons() {
      if (!_.isArray(this.meal.addons)) {
        throw new Error("Invalid addons");
      }

      // Validate all rows
      for (let addon of this.meal.addons) {
        if (!addon.title || !addon.minimum || !addon.maximum) {
          return;
        }
      }

      this.$emit("change", this.meal.addons);
    },
    changeOptionIngredients(addonId, optionId) {
      this.ingredientAddonId = addonId;
      this.ingredientOptionId = optionId;
    },
    onChangeIngredients(ingredients) {
      try {
        this.meal.addons[this.ingredientAddonId].options[
          this.ingredientOptionId
        ].ingredients = ingredients;
      } catch (e) {}
      this.ingredientAddonId = null;
      this.ingredientOptionId = null;
    }
  }
};
</script>
