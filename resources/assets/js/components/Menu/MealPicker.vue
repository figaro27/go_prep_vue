<template>
  <div>
    <v-client-table
      ref="mealPackageMealsTable"
      :columns="columns"
      :data="tableData"
      :options="options"
    >
      <div slot="beforeTable" class="d-inline">
        <!-- <b-form-checkbox
          @change="addAll"
          v-model="all"
          class="largeCheckbox ml-3"
          type="checkbox"
        >
          <span class="paragraph pb-1">Select All</span>
        </b-form-checkbox> -->
      </div>
      <div slot="afterFilter" class="d-inline">
        <b-form-group class="ml-3">
          <b-form-radio-group v-model="meals_selectable">
            <b-form-radio
              :value="false"
              name="radio-options"
              v-if="selectable_toggle"
            >
              Preset
              <hint title="Preset" class="ml-1"
                >Choose this option to pre-select the meals that will be
                included in this variation.</hint
              >
            </b-form-radio>

            <b-form-radio
              :value="true"
              name="radio-options"
              v-if="selectable_toggle"
            >
              Selectable
              <hint title="Selectable" class="ml-1"
                >Choose this option to let your customer choose which meals they
                want to make up this variation.</hint
              >
            </b-form-radio>
            <b-form-select
              v-model="categoryFilter"
              :options="categories"
              class="width-130"
              @change="selectAll = false"
            ></b-form-select>
          </b-form-radio-group>
        </b-form-group>
      </div>

      <span slot="beforeLimit" class="d-flex align-items-start">
        <!-- Helpers for Danny -->
        <div v-if="store.id === 108 || store.id === 109 || store.id === 110">
          <b-form-checkbox
            @change="addAll"
            v-model="all"
            class="mr-2"
            type="checkbox"
          >
            <span class="paragraph pb-1">Select All</span>
          </b-form-checkbox>
          <b-form-checkbox v-model="secondSize" type="checkbox" class="mr-2"
            >2nd Size</b-form-checkbox
          >

          <b-form-input
            v-model="allQuantity"
            placeholder="Quantity"
            class="width-100 mr-2"
          ></b-form-input>

          <b-form-input
            v-model="addedPrice"
            placeholder="Price"
            class="width-100 mr-2"
          ></b-form-input>
          <b-btn @click="addSizeQuantityPrice()" variant="primary" class="mr-3">
            Set
          </b-btn>
        </div>
        <!-- Helpers for Danny -->

        <div class="mr-2 pt-1">
          Total Price:
          {{ format.money(mealPriceTotal, storeSettings.currency) }}
        </div>
        <div class="mr-2">
          <b-form-radio-group
            v-model="filter_deselected"
            :options="[
              { text: 'Show all', value: false },
              { text: 'Show included', value: true }
            ]"
            buttons
            button-variant="outline-primary"
          >
          </b-form-radio-group>
        </div>
        <b-button
          variant="primary"
          :disabled="!canSave"
          @click.prevent="save"
          class="pull-right mr-2"
          >Save Meals</b-button
        >
      </span>

      <div slot="included" slot-scope="props">
        <b-form-checkbox
          class="largeCheckbox"
          type="checkbox"
          v-model="props.row.included"
          :value="true"
          :unchecked-value="false"
          @change="val => toggleMeal(props.row.id)"
        ></b-form-checkbox>
      </div>

      <div slot="featured_image" slot-scope="props">
        <thumbnail
          v-if="props.row.image != null && props.row.image.url_thumb"
          :src="props.row.image.url_thumb"
          width="64px"
        ></thumbnail>
      </div>

      <div slot="quantity" slot-scope="props">
        <b-input
          type="number"
          v-model="props.row.quantity"
          @change="val => setMealQuantity(props.row.id, val)"
        ></b-input>
      </div>

      <div slot="added_price" slot-scope="props">
        <money
          v-model="props.row.price"
          class="form-control"
          v-bind="{ prefix: storeCurrencySymbol }"
          @blur.native="e => setMealAddedPrice(props.row.id, e.target.value)"
        ></money>
      </div>

      <div slot="meal_size_id" slot-scope="props">
        <b-select
          v-model="props.row.meal_size_id"
          :options="sizeOptions(props.row)"
          @change="val => setMealSizeId(props.row.id, val)"
        ></b-select>
      </div>

      <div slot="afterTable">
        <b-button
          class="pull-right"
          variant="primary"
          :disabled="!canSave"
          @click.prevent="save"
          >Save Meals</b-button
        >
      </div>
    </v-client-table>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";

export default {
  components: {
    cSwitch
  },
  props: {
    meal_sizes: {
      type: Boolean,
      default: false
    },
    value: {
      type: Array,
      default: []
    },
    selectable_toggle: {
      type: Boolean,
      default: false
    },
    selectable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      all: false,
      // Temp for Danny
      secondSize: false,
      allQuantity: null,
      addedPrice: null,
      //
      categoryFilter: -1,
      selected: [],
      meals_selectable: false,
      filter_deselected: false,
      options: {
        perPage: 100,
        headings: {
          included: "Included",
          featured_image: "Image",
          title: "Title",
          meal_size_id: "Meal Size",
          quantity: "Quantity",
          added_price: "Added Price"
        },
        rowClassCallback: function(row) {
          let classes = `meal meal-${row.id}`;
          classes += row.included ? "" : " faded";
          return classes;
        },
        orderBy: {
          column: "title",
          ascending: true
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      meals: "storeMeals",
      findMeal: "storeMeal",
      isLoading: "isLoading",
      storeCurrencySymbol: "storeCurrencySymbol",
      storeSettings: "storeSettings",
      storeCategories: "storeCategories"
    }),
    columns() {
      let cols = ["included", "featured_image", "title"];
      if (this.meal_sizes) {
        cols.push("meal_size_id");
      }
      cols.push("quantity");
      if (this.selectable_toggle && this.meals_selectable) {
        cols.push("added_price");
      }
      return cols;
    },
    canSave() {
      return this.selected.length > 0;
    },
    tableData() {
      let meals = _.cloneDeep(this.meals).map(meal => {
        meal.included = this.hasMeal(meal.id);
        meal.quantity = this.getMealQuantity(meal.id);
        meal.meal_size_id = this.getMealSizeId(meal.id);
        meal.price = this.getMealAddedPrice(meal.id);
        return meal;
      });

      if (this.filter_deselected) {
        meals = _.filter(meals, meal => meal.included);
      }

      if (this.categoryFilter != -1) {
        meals = _.filter(meals, meal => {
          let check = false;
          meal.category_ids.forEach(category => {
            if (this.categoryFilter === category) check = true;
          });
          return check;
        });
      }

      // Need to adjust this because if you switch categories this gets lost.
      // if (this.selectAll){
      //  meals.forEach(meal => {
      //    meal.included = true;
      //    meal.quantity = 1;
      //    this.selected.push(meal);
      //  })
      // }
      // else {
      //  meals.forEach(meal => {
      //    meal.included = false;
      //    meal.quantity = 0;
      //    this.selected.pop(meal);
      //  })
      // }

      return meals;
    },
    categories() {
      let categories = _.map(this.storeCategories, category => {
        return {
          value: category.id,
          text: category.category
        };
      });

      categories.unshift({ value: -1, text: "All Categories" });
      return categories;
    },
    mealPriceTotal() {
      let total = 0;
      this.selected.forEach(_meal => {
        const meal = this.findMeal(_meal.id);

        if (this.selectable_toggle && this.selectable) {
          total += _meal.price ? parseFloat(_meal.price) : 0;
        } else {
          if (_meal) {
            total += meal.price * _meal.quantity;
          }
        }
      });
      return total;
    }
  },
  created() {
    this.selected = _.isArray(this.value) ? this.value : [];
    this.meals_selectable = this.selectable == true;
  },
  methods: {
    save() {
      this.$emit("save", {
        meals: this.selected,
        selectable: this.meals_selectable
      });
    },
    findMealIndex(id) {
      return _.findIndex(this.selected, { id });
    },
    sizeOptions(meal) {
      return _.concat(
        {
          text: meal.default_size_title || "Default",
          value: null
        },
        meal.sizes.map(size => {
          return {
            text: size.title,
            value: size.id
          };
        })
      );
    },
    hasMeal(id) {
      return this.findMealIndex(id) !== -1;
    },
    toggleMeal(id) {
      if (this.hasMeal(id)) {
        this.removeMeal(id);
      } else {
        this.addMeal(id, 1);
      }
    },
    removeMeal(id) {
      this.selected = _.filter(this.selected, meal => {
        return meal.id !== id;
      });
    },
    addMeal(id, quantity = 1, meal_size_id = null) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        this.selected.push({
          id,
          quantity,
          meal_size_id
        });
      } else {
        let meal = this.selected[index];
        meal.quantity += 1;
        this.$set(this.selected, index, meal);
      }
    },
    setMealQuantity(id, quantity) {
      const index = this.findMealIndex(id);
      quantity = parseInt(quantity);
      if (quantity <= 0) {
        return this.removeMeal(id);
      }
      if (index === -1) {
        this.selected.push({
          id,
          quantity
        });
      } else {
        let meal = { ...this.selected[index] };
        meal.quantity = quantity;
        this.$set(this.selected, index, meal);
      }
    },
    getMealQuantity(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.selected[index].quantity;
      }
    },
    setMealSizeId(id, mealSizeId) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.selected[index] };
        meal.meal_size_id = mealSizeId;
        this.$set(this.selected, index, meal);
      }
    },
    getMealSizeId(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return null;
      } else {
        return this.selected[index].meal_size_id;
      }
    },
    setMealAddedPrice(id, price) {
      const index = this.findMealIndex(id);
      if (index !== -1) {
        let meal = { ...this.selected[index] };
        meal.price = parseFloat(price.replace(/[^\d.-]/g, ""));
        this.$set(this.selected, index, meal);
      }
    },
    getMealAddedPrice(id) {
      const index = this.findMealIndex(id);
      if (index === -1) {
        return 0;
      } else {
        return this.selected[index].price || 0;
      }
    },
    addAll() {
      this.$nextTick(() => {
        if (this.all) {
          this.meals.forEach(meal => {
            meal.category_ids.forEach(category => {
              if (
                this.categoryFilter === category ||
                this.categoryFilter === -1
              ) {
                meal.included = true;
                meal.quantity = 1;
                meal.price = 0;
                this.selected.push(meal);
              }
            });
          });
        } else {
          this.meals.forEach(meal => {
            meal.category_ids.forEach(category => {
              if (
                this.categoryFilter === category ||
                this.categoryFilter === -1
              ) {
                meal.included = true;
                meal.quantity = 1;
                meal.price = 0;
                this.selected.pop(meal);
              }
            });
          });
        }
      });
    },

    // Temp for Danny
    addSizeQuantityPrice() {
      this.$nextTick(() => {
        this.choose2ndSize = !this.choose2ndSize;
        this.meals.forEach(meal => {
          if (this.secondSize) {
            meal.meal_size_id = meal.sizes.length > 0 ? meal.sizes[0].id : null;
          }
          if (this.allQuantity != null) {
            meal.quantity = this.allQuantity;
          }
          if (this.addedPrice != null) {
            meal.price = this.addedPrice;
          }
          if (meal.included) {
            this.selected.push(meal);
          }
        });
      });
    }
    //
  }
};
</script>
