<template>
  <div>
    <div class="modal-basic">
      <b-modal
        :title="store.details.name"
        size="lg"
        v-model="showDescriptionModal"
        v-if="showDescriptionModal"
        @hide="$parent.showDescriptionModal = false"
      >
        <p v-html="store.details.description"></p>
      </b-modal>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  props: {
    showDescriptionModal: false
  },
  computed: {
    ...mapGetters({
      store: "viewedStore"
    })
  }
};
</script>
