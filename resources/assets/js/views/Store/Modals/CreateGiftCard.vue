<template>
  <div class="modal-full modal-tabs">
    <b-modal
      size="xl"
      title="Add Gift Card"
      ref="createGiftCardModal"
      @ok.prevent="e => addGiftCard(e)"
      @cancel.prevent="toggleModal()"
      @hidden="toggleModal"
      no-fade
    >
      <b-row>
        <b-col>
          <h4>Gift Card Title</h4>
          <b-form-group label-for="giftCard-title" :state="true">
            <b-form-input
              id="giftCard-title"
              type="text"
              v-model="giftCard.title"
              placeholder="Gift Card Name"
              required
            ></b-form-input>
          </b-form-group>
          <b-form-group>
            <h4>Price</h4>
            <money
              required
              v-model="giftCard.price"
              :min="0.1"
              class="form-control"
              v-bind="{ prefix: storeCurrencySymbol }"
            ></money>
          </b-form-group>

          <h4 class="mt-4">Categories</h4>
          <b-form-checkbox-group
            buttons
            v-model="giftCard.category_ids"
            :options="categoryOptions"
            class="storeFilters"
            required
          ></b-form-checkbox-group>
        </b-col>

        <b-col md="3" lg="2">
          <picture-input
            ref="featuredImageInput"
            :alertOnError="false"
            :autoToggleAspectRatio="true"
            margin="0"
            size="10"
            button-class="btn"
            @change="val => changeImage(val)"
          ></picture-input>
        </b-col>
      </b-row>
    </b-modal>
  </div>
</template>

<script>
import PictureInput from "vue-picture-input";
import { mapGetters, mapActions, mapMutations } from "vuex";
import Spinner from "../../../components/Spinner";
import fs from "../../../lib/fs.js";

export default {
  components: {
    Spinner,
    PictureInput
  },
  data() {
    return {
      giftCard: {}
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCategories: "storeCategories",
      getCategoryTitle: "storeCategoryTitle",
      isLoading: "isLoading",
      storeCurrencySymbol: "storeCurrencySymbol"
    }),

    categoryOptions() {
      return Object.values(this.storeCategories).map(cat => {
        return {
          text: cat.category,
          value: cat.id
        };
      });
    }
  },
  mounted() {
    this.$refs.createGiftCardModal.show();
    setTimeout(() => {
      this.$refs.featuredImageInput.onResize();
    }, 100);
  },
  methods: {
    ...mapActions({
      refreshGiftCards: "refreshGiftCards"
    }),
    forceResize() {
      window.dispatchEvent(new window.Event("resize"));
    },
    async changeImage(val) {
      let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
      this.giftCard.featured_image = b64;
    },
    toggleModal() {
      this.$parent.createGiftCardModal = false;
    },
    addGiftCard() {
      axios.post("/api/me/giftCards", this.giftCard).then(resp => {
        this.$emit("created");
        this.$toastr.s("Gift card created.");
        this.refreshGiftCards();
        this.toggleModal();
      });
    }
  }
};
</script>
