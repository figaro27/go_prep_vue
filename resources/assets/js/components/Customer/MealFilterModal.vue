<template>
  <div>
    <div class="modal-basic">
      <b-modal
        size="lg"
        v-model="viewFilterModal"
        v-if="viewFilterModal"
        @hide="$parent.viewFilterModal = false"
        hide-header
        no-fade
      >
        <div>
          <h4 class="center-text mt-4">Allergies</h4>
          <p class="center-text">Hide Items That Contain:</p>
        </div>
        <div class="row mb-4">
          <div
            v-for="allergy in allergies"
            :key="`allergy-${allergy.id}`"
            class="filters"
          >
            <b-button
              :pressed="$parent.active[allergy.id]"
              @click="$parent.filterByAllergy(allergy.id)"
              >{{ allergy.title }}</b-button
            >
          </div>
        </div>
        <hr />
        <div>
          <h4 class="center-text mt-5">Nutrition</h4>
          <p class="center-text">Show Items That Are:</p>
        </div>
        <div class="row">
          <div v-for="tag in tags" :key="`tag-${tag}`" class="filters">
            <b-button
              :pressed="$parent.active[tag]"
              @click="$emit('filterByTag', tag)"
            >
              {{ tag }}
            </b-button>
          </div>
        </div>
        <b-button
          @click="$emit('clearFilters')"
          class="center mt-4 brand-color white-text"
          >Clear All</b-button
        >
      </b-modal>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    viewFilterModal: false,
    allergies: {},
    tags: {}
  }
};
</script>
