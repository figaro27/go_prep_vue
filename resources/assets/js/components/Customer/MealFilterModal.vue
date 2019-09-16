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
          <h4 class="center-text mb-5 mt-5">Hide Meals That Contain</h4>
        </div>
        <div class="row mb-4">
          <div
            v-for="allergy in allergies"
            :key="`allergy-${allergy.id}`"
            class="filters col-6 col-sm-4 col-md-3 mb-3"
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
          <h4 class="center-text mb-5">Show Meals With</h4>
        </div>
        <div class="row">
          <div
            v-for="tag in tags"
            :key="`tag-${tag}`"
            class="filters col-6 col-sm-4 col-md-3 mb-3"
          >
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
