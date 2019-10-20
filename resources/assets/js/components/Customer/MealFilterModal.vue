<template>
  <div>
    <div class="modal-basic">
      <b-modal size="lg" v-model="view" @hide="hide" hide-header no-fade>
        <div>
          <h4 class="center-text mt-4">Allergies</h4>
          <p class="center-text">Hide Items That Contain:</p>
        </div>
        <div
          class="mb-4"
          style="display: flex; flex-wrap: wrap; justify-content: center;"
        >
          <div
            v-for="allergy in allergies"
            :key="`allergy-${allergy.id}`"
            class="filters"
            style="margin-top: 5px; margin-bottom: 5px;"
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
        <div style="display: flex; flex-wrap: wrap; justify-content: center;">
          <div
            v-for="tag in tags"
            :key="`tag-${tag}`"
            class="filters"
            style="margin-top: 5px; margin-bottom: 5px;"
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
  },
  computed: {
    view: {
      get: function() {
        return this.viewFilterModal;
      },
      set: function(newValue) {}
    }
  },
  methods: {
    hide() {
      this.$parent.viewFilterModalParent = false;
    }
  }
};
</script>
