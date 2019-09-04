<template>
  <div>
    <div v-if="store.settings.open === false">
      <div class="row">
        <div class="col-sm-12 mt-3">
          <div class="card">
            <div class="card-body">
              <h5 class="center-text">
                This company will not be taking new orders at this time.
              </h5>
              <p class="center-text mt-3">
                <strong>Reason:</strong>
                {{ store.settings.closedReason }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  computed: {
    ...mapGetters({
      store: "viewedStore"
    })
  }
};
</script>
