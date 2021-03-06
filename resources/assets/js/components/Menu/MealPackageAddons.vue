<template>
  <div>
    <div v-if="!_.isNull(meal_picker_addon_id)">
      <b-btn @click.prevent="hideMealPicker()" class="mb-3">Back</b-btn>
      <meal-picker
        ref="mealPicker"
        :meal_sizes="true"
        :selectable_toggle="true"
        :selectable="meal_picker_selectable"
        :mealPackageAddonPage="true"
        v-model="meal_picker_meals"
        @save="val => onChangeMeals(val.meals, val.selectable)"
      ></meal-picker>
    </div>

    <div v-else>
      <div class="mb-4">
        <b-button variant="primary" @click="addAddon()"
          >Add Meal Package Addon</b-button
        >
        <!--         <img
          v-b-popover.hover="
            'Example: Extra meat. Extra veggies. Please indicate the price increase that will be added to the overall meal. If you use ingredients, the Adjust button lets you adjust how the particular addon affects the overall ingredients for the meal.'
          "
          title="Meal Addon"
          src="/images/store/popover.png"
          class="popover-size"
        /> -->
      </div>

      <div
        v-for="(addon, i) in meal_package.addons"
        :key="addon.id"
        role="tablist"
      >
        <!-- <div class="addon-header mb-2">
          <h5 class="d-inline-block">#{{ i + 1 }}. {{ addon.title }}</h5>
        </div> -->
        <b-row>
          <b-col cols="4">
            <b-form-group label="Title" class="font-weight-bold">
              <b-input
                v-model="addon.title"
                placeholder="i.e. Extra Entree"
              ></b-input>
            </b-form-group>
          </b-col>
          <b-col cols="2">
            <b-form-group label="Price" class="font-weight-bold">
              <money
                :disabled="addon.id === -1"
                required
                v-model="addon.price"
                :min="0.1"
                :max="999.99"
                class="form-control"
                v-bind="{ prefix: storeCurrencySymbol }"
              ></money>
            </b-form-group>
          </b-col>
          <b-col cols="2">
            <b-form-group label="Meal Package Size" class="font-weight-bold">
              <b-select
                v-model="addon.meal_package_size_id"
                :options="sizeOptions"
              ></b-select>
            </b-form-group>
          </b-col>
          <b-col v-if="store.modules.multipleDeliveryDays" cols="2">
            <b-form-group label="Delivery Day" class="font-weight-bold">
              <b-form-select
                v-model="addon.delivery_day_id"
                :options="deliveryDayOptions"
              ></b-form-select>
            </b-form-group>
          </b-col>
          <b-col cols="1">
            <b-btn
              variant="primary"
              @click="changeAddonMeals(i)"
              style="margin-top: 28px;"
              >Adjust</b-btn
            >
          </b-col>
          <b-col cols="1">
            <b-btn
              variant="danger"
              @click="deleteAddon(addon.id)"
              style="margin-top: 28px;"
              >Delete</b-btn
            >
          </b-col>
        </b-row>
        <hr v-if="i < meal_package.addons.length - 1" class="my-2" />
      </div>

      <div v-if="meal_package.addons.length" class="mt-4">
        <b-button variant="primary" @click="addAddon()"
          >Add Meal Package Addon</b-button
        >
        <b-btn
          variant="warning"
          v-if="meal_package.sizes.length > 0"
          @click="duplicateAddons(addon)"
          :disabled="duplicated"
          >Duplicate Addons for All Sizes</b-btn
        >
        <!-- <b-button variant="primary" @click="save()" class="pull-right"
          >Save</b-button
        > -->
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
    meal_package: {
      required: true
    }
  },
  data() {
    return {
      meal_picker_addon_id: null,
      meal_picker_meals: [],
      meal_picker_selectable: false,
      duplicated: false
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol",
      store: "viewedStore"
    }),
    deliveryDayOptions() {
      let options = [];
      this.store.delivery_days.forEach(day => {
        options.push({
          value: day.id,
          text:
            day.day_long +
            " - " +
            day.type.charAt(0).toUpperCase() +
            day.type.slice(1)
        });
      });
      return options;
    },
    sizeOptions() {
      return _.concat(
        {
          text: this.meal_package.default_size_title || "Default",
          value: null
        },
        this.meal_package.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    }
  },
  watch: {
    "meal_package.addons": function() {
      this.onChangeAddons();
    }
  },
  created() {
    this.onChangeAddons = _.debounce(this.onChangeAddons, 2000);
  },
  mounted() {},
  methods: {
    addAddon() {
      this.meal_package.addons.push({
        id: 1000000 + this.meal_package.addons.length, // push to the end of table
        title: "",
        price: null,
        meal_package_size_id: null,
        meals: []
      });
    },
    deleteAddon(id) {
      this.meal_package.addons = _.filter(this.meal_package.addons, addon => {
        return addon.id !== id;
      });
      this.save();
      this.onChangeAddons();
    },
    onChangeAddons() {
      if (!_.isArray(this.meal_package.addons)) {
        throw new Error("Invalid addons");
      }

      // Validate all rows
      for (let addon of this.meal_package.addons) {
        if (!addon.title || !addon.price) {
          return;
        }
      }

      this.$emit("change", this.meal_package.addons);
    },
    changeAddonMeals(addonIndex) {
      let addon = this.meal_package.addons[addonIndex];

      if (!addon) {
        return;
      }

      this.meal_picker_addon_id = addonIndex;
      this.meal_picker_selectable = addon.selectable;

      this.meal_picker_meals = addon
        ? _.map(addon.meals, meal => {
            return {
              id: meal.id,
              meal_size_id: meal.meal_size_id,
              quantity: meal.quantity,
              price: meal.price || 0
            };
          })
        : [];
    },
    onChangeMeals(meals, selectable = false) {
      this.meal_package.addons[this.meal_picker_addon_id].meals = meals;
      this.meal_package.addons[
        this.meal_picker_addon_id
      ].selectable = selectable;

      // this.hideMealPicker();
    },
    save() {
      this.$emit("save", this.meal_package.addons);
      this.$toastr.s("Meal variation saved.");
    },
    hideMealPicker() {
      this.meal_picker_meals = [];
      this.meal_picker_selectable = false;
      this.meal_picker_addon_id = null;
    },
    duplicateAddons(addon) {
      let addons = [...this.meal_package.addons];
      this.meal_package.sizes.forEach(size => {
        addons.forEach(addon => {
          this.meal_package.addons.push({
            id: 1000000 + this.meal_package.addons.length, // push to the end of table
            title: addon.title,
            price: addon.price,
            meals: addon.meals,
            selectable: addon.selectable,
            meal_package_size_id: size.id
          });
        });
      });
      this.duplicated = true;
    }
  }
};
</script>
