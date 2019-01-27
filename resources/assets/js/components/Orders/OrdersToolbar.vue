<template>
  <div class="d-flex align-items-end">
    <div class="mr-2">
      <b-btn
        @click="filterNotes"
        :selected="filters.notes"
        variant="primary"
        class="filter-btn"
      >Filter Notes</b-btn>
    </div>
    <div class="flex-grow-1">
      <delivery-date-picker v-model="filters.delivery_dates"></delivery-date-picker>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style>
</style>
