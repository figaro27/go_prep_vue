<template>
  <div>
    <v-client-table
      ref="mealPackageMealsTable"
      :columns="columns"
      :data="tableData"
      :options="options"
    >
      <div slot="beforeTable" class="mb-2"></div>

      <span slot="beforeLimit">
        <div class="mr-2">
          Total meal price:
          {{ format.money(mealPriceTotal, storeSettings.currency) }}
        </div>
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
          v-if="props.row.image.url_thumb"
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
    </v-client-table>
  </div>
</template>

<script>
export default {};
</script>

<style></style>
