<template>
  <div>
    <meal-picker v-if="meal_picker_id"></meal-picker>

    <b-button
      variant="primary"
      @click="
        mealPackage.sizes.push({
          id: 1000000 + mealPackage.sizes.length, // push to the end of table
          title: '',
          price: mealPackage.price
        })
      "
      >Add Meal Package Size</b-button
    >
    <img
      v-b-popover.hover="
        'Example: Medium, Large, Family Sized, etc. Please indicate the price for each size. For ingredient multiplier, please indicate the ratio of how many more ingredients are used for the new size. For example if the meal is twice as large, put 2. If you don\'t use ingredients, just put 1 in each field.'
      "
      title="Meal Packages"
      src="/images/store/popover.png"
      class="popover-size"
    />
    <v-client-table
      v-if="mealPackage.sizes.length > 0"
      :columns="columns"
      :data="tableData"
      :options="{
        headings: {
          actions: ''
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
              ? 'Default meal package size title'
              : 'Meal package size title'
          "
          @change="
            val => {
              if (props.row.id !== -1) {
                mealPackage.sizes[props.index - 2].title = val;
                onChangeSizes();
              } else {
                mealPackage.default_size_title = val;
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
              mealPackage.sizes[props.index - 2].price = props.row.price;
              onChangeSizes();
            }
          "
        ></money>
      </div>
      <div slot="meals" slot-scope="props">
        <b-btn @click.prevent="selectMeals(props.row.id)">Select</b-btn>
      </div>
    </v-client-table>

    <b-button variant="primary" @click="save()" class="pull-right"
      >Save</b-button
    >
  </div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  props: {
    mealPackage: {
      required: true
    }
  },
  data() {
    return {
      sizes: [],
      meal_picker_id: null
    };
  },
  computed: {
    ...mapGetters({
      storeCurrencySymbol: "storeCurrencySymbol"
    }),
    columns() {
      let cols = ["title", "price", "meals"];

      if (this.tableData.length) {
        cols = ["actions", ...cols];
      }

      return cols;
    },
    tableData() {
      return _.concat(
        {
          id: -1,
          title: this.mealPackage.default_size_title,
          price: this.mealPackage.price,
          meals: []
        },
        this.mealPackage.sizes
      );
    }
  },
  watch: {},
  created() {
    this.sizes = _.isArray(this.mealPackage.sizes)
      ? this.mealPackage.sizes
      : [];
    this.onChangeSizes = _.debounce(this.onChangeSizes, 2000);
  },
  mounted() {},
  methods: {
    deleteSize(id) {
      this.mealPackage.sizes = _.filter(this.mealPackage.sizes, size => {
        return size.id !== id;
      });
      this.onChangeSizes();
    },
    onChangeSizes() {
      if (!_.isArray(this.mealPackage.sizes)) {
        throw new Error("Invalid sizes");
      }

      // Validate all rows
      for (let size of this.mealPackage.sizes) {
        if (!size.title || !size.price || !size.meals) {
          return;
        }
      }

      this.$emit("change", this.mealPackage.sizes);
    },
    selectMeals(mealPackageId) {},
    save() {
      this.$emit("save", this.mealPackage.sizes);
      this.$toastr.s("Meal package variation saved.");
    }
  }
};
</script>
