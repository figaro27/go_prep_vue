<template>
  <div>
    <div v-if="ingredient_picker_id">
      <ingredient-picker
        ref="ingredientPicker"
        v-model="ingredient_picker_size.ingredients"
        :options="{ saveButton: true }"
        :meal="ingredient_picker_size"
        @save="val => onChangeIngredients(val)"
      ></ingredient-picker>
    </div>
    <div v-else>
      <b-button
        variant="primary"
        @click="
          meal.sizes.push({
            id: 1000000 + meal.sizes.length, // push to the end of table
            title: '',
            price: meal.price,
            multiplier: 1,
            ingredients: []
          })
        "
        >Add Meal Size</b-button
      >
      <img
        v-b-popover.hover="
          'Example: Medium, Large, Family Sized, etc. Please indicate the price for each size. For ingredient multiplier, please indicate the ratio of how many more ingredients are used for the new size. For example if the meal is twice as large, put 2. If you don\'t use ingredients, just put 1 in each field.'
        "
        title="Meal Sizes"
        src="/images/store/popover.png"
        class="popover-size"
      />
      <v-client-table
        v-if="meal.sizes.length > 0"
        :columns="columns"
        :data="tableData"
        :options="{
          headings: {
            actions: '',
            multiplier: 'Ingredient Multiplier'
          },
          orderBy: {
            column: 'id',
            ascending: true
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2"></div>
        <div slot="actions" slot-scope="props" v-if="props.row.id !== -1">
          <b-btn variant="danger" size="sm" @click="deleteSize(props.row.id)"
            >Delete</b-btn
          >
        </div>
        <div slot="title" slot-scope="props">
          <b-input
            v-model="props.row.title"
            :placeholder="
              props.row.id === -1
                ? 'Default meal size title'
                : 'Meal size title'
            "
            @change="
              val => {
                if (props.row.id !== -1) {
                  meal.sizes[props.index - 2].title = val;
                  onChangeSizes();
                } else {
                  meal.default_size_title = val;
                  $emit('changeDefault', val);
                }
              }
            "
          ></b-input>
        </div>
        <div slot="price" slot-scope="props">
          <money
            :disabled="props.row.id === -1"
            required
            v-model="props.row.price"
            :min="0.1"
            :max="999.99"
            class="form-control"
            v-bind="{ prefix: storeCurrencySymbol }"
            @blur.native="
              e => {
                meal.sizes[props.index - 2].price = props.row.price;
                onChangeSizes();
              }
            "
          ></money>
        </div>
        <div slot="ingredients" slot-scope="props">
          <b-btn v-if="props.row.id > -1" @click="editIngredients(props.row.id)"
            >Adjust</b-btn
          >
        </div>
        <div slot="multiplier" slot-scope="props">
          <b-input
            :disabled="props.row.id === -1"
            v-model="props.row.multiplier"
            @change="
              meal.sizes[props.index - 2].multiplier = props.row.multiplier;
              onChangeSizes();
            "
          ></b-input>
        </div>
      </v-client-table>

      <b-button variant="primary" @click="save()" class="pull-right"
        >Save</b-button
      >
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  props: {
    meal: {
      required: true
    }
  },
  data() {
    return {
      sizes: [],
      ingredient_picker_id: null,
      ingredient_picker_size: null
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
    columns() {
      let cols = ["title", "price", "multiplier", "ingredients"];

      if (this.tableData.length) {
        cols = ["actions", ...cols];
      }

      return cols;
    },
    tableData() {
      return _.concat(
        {
          id: -1,
          title: this.meal.default_size_title,
          price: this.meal.price,
          multiplier: 1
        },
        this.meal.sizes
      );
    }
  },
  watch: {},
  created() {
    this.sizes = _.isArray(this.meal.sizes) ? this.meal.sizes : [];
    this.onChangeSizes = _.debounce(this.onChangeSizes, 2000);
  },
  mounted() {},
  methods: {
    deleteSize(id) {
      this.meal.sizes = _.filter(this.meal.sizes, size => {
        return size.id !== id;
      });
      this.onChangeSizes();
    },
    onChangeSizes() {
      if (!_.isArray(this.meal.sizes)) {
        throw new Error("Invalid sizes");
      }

      // Validate all rows
      for (let size of this.meal.sizes) {
        if (!size.title || !size.price || !size.multiplier) {
          return;
        }
      }

      this.$emit("change", this.meal.sizes);
    },
    save() {
      this.$emit("save", this.meal.sizes);
      this.$toastr.s("Meal variation saved.");
    },
    editIngredients(sizeId) {
      this.ingredient_picker_id = sizeId;
      this.ingredient_picker_size = _.find(this.meal.sizes, { id: sizeId });
    },
    onChangeIngredients(ingredients) {
      const index = _.findIndex(this.meal.sizes, {
        id: this.ingredient_picker_id
      });
      this.ingredient_picker_size.ingredients = ingredients;
      this.$set(this.meal.sizes, index, this.ingredient_picker_size);

      this.ingredient_picker_id = null;
      this.ingredient_picker_size = null;
    }
  }
};
</script>
