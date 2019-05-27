<template>
  <div>
    <div v-if="ingredientAddonId !== null">
      <ingredient-picker
        ref="ingredientPicker"
        v-model="meal.addons[ingredientAddonId].ingredients"
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
          <b-btn
            variant="danger"
            class="pull-right"
            @click="deleteAddon(addon.id)"
            >Delete</b-btn
          >
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
            <b-btn
              variant="primary"
              @click="changeAddonIngredients(i)"
              style="margin-top: 28px;"
              >Adjust</b-btn
            >
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
  components: {
    IngredientPicker
  },
  props: {
    meal: {
      required: true
    }
  },
  data() {
    return {
      ingredientAddonId: null
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
        price: null,
        ingredients: []
      });
    },
    deleteAddon(id) {
      this.meal.addons = _.filter(this.meal.addons, addon => {
        return addon.id !== id;
      });
      this.onChangeAddons();
    },
    onChangeAddons() {
      if (!_.isArray(this.meal.addons)) {
        throw new Error("Invalid addons");
      }

      // Validate all rows
      for (let addon of this.meal.addons) {
        if (!addon.title || !addon.price) {
          return;
        }
      }

      this.$emit("change", this.meal.addons);
    },
    changeAddonIngredients(addonId) {
      this.ingredientAddonId = addonId;
    },
    onChangeIngredients(ingredients) {
      try {
        this.meal.addons[this.ingredientAddonId].ingredients = ingredients;
      } catch (e) {}
      this.ingredientAddonId = null;
    }
  }
};
</script>
