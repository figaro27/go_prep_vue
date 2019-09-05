<template>
  <div>
    <a :href="storeWebsite" v-if="storeWebsite != null">
      <img
        v-if="storeLogo"
        class="store-logo"
        :src="storeLogo.url_thumb"
        alt="Company Logo"
      />
    </a>
    <img
      v-if="storeLogo && storeWebsite === null"
      class="store-logo"
      :src="storeLogo.url_thumb"
      alt="Company Logo"
    />
    <div class="col-sm-12">
      <b-btn
        v-if="store.details.description"
        @click="$parent.showDescriptionModal = true"
        class="brand-color white-text center mt-3"
        >About</b-btn
      >
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  props: {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeLogo: "viewedStoreLogo"
    }),
    storeWebsite() {
      if (!this.store.settings.website) {
        return null;
      } else {
        let website = this.store.settings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        return website;
      }
    }
  }
};
</script>
