<template>
  <div>
    <div v-if="ingredientAddonId !== null">
      <ingredient-picker
        ref="ingredientPicker"
        v-model="meal.addons[ingredientAddonId].ingredients"
        :options="{ saveButton: true }"
        :meal="ingredient_picker_size"
        :mealSizeId="ingredient_picker_size.id"
        @save="val => onChangeIngredients(val)"
        :componentAddonPage="true"
        :addonTitle="addonTitle"
        :createMealModal="createMealModal"
      ></ingredient-picker>
    </div>

    <div v-else>
      <div class="mb-4">
        <b-button variant="primary" @click="addAddon()"
          >Add Meal Addon</b-button
        >
        <img
          v-b-popover.hover="
            'Example: Extra meat. Please indicate the price increase that will be added to the overall meal. If you use ingredients, the Adjust Ingredients button lets you adjust how the particular addon affects the overall ingredients for the meal.'
          "
          title="Meal Addon"
          src="/images/store/popover.png"
          class="popover-size"
        />
      </div>

      <div v-for="(addon, i) in meal.addons" :key="addon.id">
        <!-- <div class="addon-header mb-2">
          <h5 class="d-inline-block">#{{ i + 1 }}. {{ addon.title }}</h5>
        </div> -->
        <b-row class="mb-3">
          <b-col cols="6">
            <b-input
              v-model="addon.title"
              placeholder="i.e. Extra Protein"
            ></b-input>
          </b-col>
          <b-col>
            <money
              :disabled="addon.id === -1"
              required
              v-model="addon.price"
              :min="0.1"
              :max="999.99"
              class="form-control"
              v-bind="{ prefix: storeCurrencySymbol }"
            ></money>
          </b-col>
          <b-col>
            <b-select
              v-model="addon.meal_size_id"
              :options="sizeOptions"
            ></b-select>
          </b-col>
          <b-col>
            <b-btn
              variant="primary"
              @click="changeAddonIngredients(i, addon.meal_size_id)"
              >Adjust Ingredients</b-btn
            >
          </b-col>
          <b-col>
            <b-btn
              variant="danger"
              @click="deleteAddon(addon.id)"
              class="pull-right"
              >Delete</b-btn
            >
          </b-col>
        </b-row>
      </div>

      <div v-if="meal.addons.length" class="mt-4">
        <b-button variant="success" @click="addAddon()"
          >Add Meal Addon</b-button
        >
        <b-btn
          variant="warning"
          v-if="meal.sizes.length > 0"
          @click="duplicateAddons(addon)"
          :disabled="duplicated"
          >Duplicate Addons for All Sizes</b-btn
        >
        <b-button variant="primary" @click="save()" class="pull-right"
          >Save</b-button
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
import { mapGetters } from "vuex";

export default {
  components: {
    IngredientPicker
  },
  props: {
    createMealModal: null,
    meal: {
      required: true
    }
  },
  data() {
    return {
      ingredientAddonId: null,
      ingredient_picker_id: null,
      ingredient_picker_size: null,
      duplicated: false
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
    addonTitle() {
      return this.meal.addons[this.ingredientAddonId].title;
    },
    sizeOptions() {
      return _.concat(
        {
          text: this.meal.default_size_title || "Default",
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
        id: 1000000 + this.meal.addons.length, // push to the end of table
        title: "",
        price: null,
        ingredients: [],
        meal_size_id: null
      });
    },
    deleteAddon(id) {
      this.meal.addons = _.filter(this.meal.addons, addon => {
        return addon.id !== id;
      });
      this.save();
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
    changeAddonIngredients(addonId, sizeId) {
      this.ingredientAddonId = addonId;
      if (sizeId != null) {
        this.ingredient_picker_id = sizeId;
        this.ingredient_picker_size = _.find(this.meal.sizes, { id: sizeId });
        if (!this.ingredient_picker_size.ingredients.length) {
          this.ingredient_picker_size.ingredients = [...this.meal.ingredients];
        }
      } else {
        this.ingredient_picker_id = null;
        this.ingredient_picker_size = this.meal;
      }
    },
    onChangeIngredients(ingredients) {
      try {
        this.meal.addons[this.ingredientAddonId].ingredients = ingredients;
      } catch (e) {}
      this.ingredientAddonId = null;
    },
    save() {
      this.$emit("save", this.meal.addons);
      this.$toastr.s("Meal variation saved.");
    },
    duplicateAddons(addon) {
      let addons = [...this.meal.addons];
      this.meal.sizes.forEach(size => {
        addons.forEach(addon => {
          this.meal.addons.push({
            id: 1000000 + this.meal.addons.length, // push to the end of table
            title: addon.title,
            price: addon.price,
            ingredients: addon.ingredients,
            meal_size_id: size.id
          });
        });
      });
      this.duplicated = true;
    }
  }
};
</script>
