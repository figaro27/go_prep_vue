<template>
  <div>
    <div class="modal-basic">
      <b-modal
        size="md"
        v-model="view"
        no-fade
        centered
        hide-header
        hide-footer
      >
        <div class="d-flex" style="justify-content:space-around">
          <div class="mt-3">
            <h3>Tags</h3>
            <p class="font-14">Include Meals That Are:</p>
            <div
              v-for="tag in tags"
              :key="`tag-${tag}`"
              class="filters"
              style="margin-top: 5px; margin-bottom: 5px;"
            >
              <span
                class="badge badge-pill badge-light"
                @click="$emit('filterByTag', tag)"
                v-if="!$parent.active[tag]"
              >
                {{ tag }}
              </span>
              <span
                class="badge badge-pill brand-color white-text"
                @click="$emit('filterByTag', tag)"
                v-if="$parent.active[tag]"
              >
                {{ tag }}
              </span>
            </div>
          </div>
          <div class="mt-3">
            <h3>Allergies</h3>
            <p class="font-14">Hide Meals That Contain:</p>
            <div
              v-for="allergy in allergies"
              :key="`allergy-${allergy.id}`"
              class="filters"
              style="margin-top: 5px; margin-bottom: 5px;"
            >
              <span
                class="badge badge-pill badge-light"
                @click="$parent.filterByAllergy(allergy.id)"
                v-if="!$parent.active[allergy.id]"
              >
                {{ allergy.title }}
              </span>
              <span
                class="badge badge-pill brand-color white-text"
                @click="$parent.filterByAllergy(allergy.id)"
                v-if="$parent.active[allergy.id]"
              >
                {{ allergy.title }}
              </span>
            </div>
          </div>
        </div>
        <div class="d-flex mt-3" style="justify-content:center">
          <div>
            <b-btn
              @click="$parent.viewFilterModalParent = false"
              variant="secondary"
              class="mt-2"
              >Back</b-btn
            >
            <b-btn
              @click="$emit('clearFilters')"
              class="brand-color white-text mt-2 ml-2"
              >Clear All</b-btn
            >
          </div>
        </div>
        <!-- <div>
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
        > -->
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
