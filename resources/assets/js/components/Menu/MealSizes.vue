<template>
  <div>
    <v-client-table
      :columns="['title', 'price', 'multiplier']"
      :data="tableData"
      :options="{
        orderBy: {
          column: 'id',
          ascending: true
        }
      }"
    >
      <div slot="beforeTable" class="mb-2">
        <b-button
          @click="
            meal.sizes.push({
              id: 100 + meal.sizes.length, // push to the end of table
              title: '',
              price: meal.price,
              multiplier: 1
            })
          "
          >Add Meal Size Variation</b-button
        >
      </div>
      <div slot="title" slot-scope="props">
        <b-input
          v-model="props.row.title"
          :placeholder="
            props.row.id === -1 ? 'Default meal size title' : 'Meal size title'
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
          @blur.native="
            e => {
              meal.sizes[props.index - 2].price = props.row.price;
              onChangeSizes();
            }
          "
        ></money>
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
  </div>
</template>

<script>
export default {
  props: {
    meal: {
      required: true
    }
  },
  data() {
    return {
      sizes: []
    };
  },
  computed: {
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
    }
  }
};
</script>
