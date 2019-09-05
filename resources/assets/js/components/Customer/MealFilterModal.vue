<template>
  <div>
    <div class="modal-basic">
      <b-modal
        size="lg"
        v-model="viewFilterModal"
        v-if="viewFilterModal"
        hide-header
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
              :pressed="active[allergy.id]"
              @click="filterByAllergy(allergy.id)"
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
            <b-button :pressed="active[tag]" @click="filterByTag(tag)">
              {{ tag }}
            </b-button>
          </div>
        </div>
        <b-button
          @click="clearFilters"
          class="center mt-4 brand-color white-text"
          >Clear All</b-button
        >
      </b-modal>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  mounted() {
    let tags = this.tags;
    this.active = tags.reduce((acc, tag) => {
      acc[tag] = false;
      return acc;
    }, {});

    let allergies = this.allergies;
    this.active = _.reduce(
      allergies,
      (acc, allergy) => {
        acc[allergy] = false;
        return acc;
      },
      {}
    );
  },
  props: {
    viewFilterModal: false,
    allergies: {}
  },
  data() {
    return {
      active: {}
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    }),
    tags() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.tags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      });
      return grouped;
    }
  },
  methods: {
    filterByAllergy(id) {
      this.$emit(filterByAllergy, id);
    },
    filterByTag(tag) {
      this.$emit(filterByTag, tag);
    }
  }
};
</script>
