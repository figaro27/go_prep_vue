<template>
  <div>
    <div class="modal-basic">
      <b-modal
        size="md"
        v-model="$parent.viewFilterModalParent"
        no-fade
        centered
        hide-header
        hide-footer
        @hide="hide"
      >
        <div class="d-flex" style="justify-content:space-around">
          <div class="mt-3">
            <h3>Tags</h3>
            <p class="font-14">Show Only Items That Are:</p>
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
            <p class="font-14">Hide Items That Contain:</p>
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
