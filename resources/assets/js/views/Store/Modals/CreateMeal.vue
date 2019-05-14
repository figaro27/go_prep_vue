<template>
  <div class="modal-full modal-tabs">
    <b-modal
      title="Add Meal"
      ref="createMealModal"
      @ok.prevent="e => storeMeal(e)"
      @cancel.prevent="toggleModal()"
      @hidden="toggleModal"
    >
      <b-row>
        <b-col>
          <b-tabs>
            <b-tab title="General" active>
              <h4>Meal Title</h4>
              <b-form-group label-for="meal-title" :state="true">
                <b-form-input
                  id="meal-title"
                  type="text"
                  v-model="meal.title"
                  placeholder="Meal Name"
                  required
                ></b-form-input>
              </b-form-group>
              <h4>Meal Description</h4>
              <b-form-group label-for="meal-description" :state="true">
                <textarea
                  v-model.lazy="meal.description"
                  id="meal-description"
                  class="form-control"
                  :rows="4"
                ></textarea>
              </b-form-group>
              <b-form-group>
                <h4>Price</h4>
                <money
                  required
                  v-model="meal.price"
                  :min="0.1"
                  class="form-control"
                ></money>
              </b-form-group>
              <br />
              <h4>Categories</h4>
              <b-form-checkbox-group
                buttons
                v-model="meal.category_ids"
                :options="categoryOptions"
                class="storeFilters"
                required
              ></b-form-checkbox-group>

              <h4 class="mt-4">Tags</h4>
              <b-form-checkbox-group
                buttons
                v-model="meal.tag_ids"
                :options="tagOptions"
                class="storeFilters"
              ></b-form-checkbox-group>

              <h4 class="mt-4">Contains</h4>
              <b-form-checkbox-group
                buttons
                v-model="meal.allergy_ids"
                :options="allergyOptions"
                class="storeFilters"
              ></b-form-checkbox-group>
            </b-tab>

            <b-tab title="Ingredients">
              <ingredient-picker
                v-model="meal.ingredients"
                :options="{ saveButton: true }"
                :meal="meal"
              ></ingredient-picker>
            </b-tab>

            <b-tab title="Size Variations">
              <meal-sizes
                :meal="meal"
                @change="val => onChangeSizes(val)"
                @changeDefault="val => (meal.default_size_title = val)"
              ></meal-sizes>
            </b-tab>

            <b-tab title="Gallery">
              <div class="gallery row">
                <div
                  v-for="(image, i) in meal.gallery"
                  :key="i"
                  class="col-sm-4 col-md-3 mb-3"
                >
                  <div class="position-relative">
                    <b-btn
                      @click="deleteGalleryImage(i)"
                      variant="danger"
                      size="sm"
                      class="position-absolute"
                      style="top: 5px; right: 5px; z-index: 1"
                    >
                      <i class="fa fa-trash"></i>
                    </b-btn>
                    <thumbnail :src="image.url_thumb" width="100%"></thumbnail>
                  </div>
                </div>

                <div class="col-sm-4 col-md-3">
                  <picture-input
                    :ref="`galleryImageInput`"
                    :alertOnError="false"
                    :autoToggleAspectRatio="true"
                    margin="0"
                    size="10"
                    button-class="btn"
                    @change="val => changeGalleryImage(val)"
                    v-observe-visibility="forceResize"
                  ></picture-input>
                </div>
              </div>
            </b-tab>
          </b-tabs>
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
          <!-- <p class="center-text mt-2">Image size too big?<br>
          You can compress images <a href="https://imagecompressor.com/" target="_blank">here.</a></p>-->
        </b-col>
      </b-row>
    </b-modal>
  </div>
  <!--
  <b-modal
    ref="createMealModal"
    size="lg"
    title="Create Meal"
  >
    <b-row>
      <b-col cols="9">
        <b-form-group label="Title">
          <b-form-input v-model="newMeal.title" required placeholder="Enter title"></b-form-input>
        </b-form-group>

        <b-form-row>
          <b-col>
            <b-form-group label="Description">
              <b-form-input v-model="newMeal.description" required placeholder="Enter description"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Price">
              <b-form-input
                v-model="newMeal.price"
                type="number"
                required
                placeholder="Enter price"
              ></b-form-input>
            </b-form-group>
          </b-col>
        </b-form-row>

        <h3 class="mt-3">Ingredients</h3>
        <ingredient-picker v-model="newMeal.ingredients"/>
      </b-col>
      <b-col>
        <h3>Tags</h3>
        <div>
          <input-tag
            ref="editMealTagsInput"
            v-model="tag_titles_flat"
            :tags="tag_titles_input"
            @tags-changed="onChangeTags"
          />
        </div>

        <h3 class="mt-3">Image</h3>
        <picture-input
          ref="newMealImageInput"
          :prefill="newMeal.featured_image ? newMeal.featured_image : ''"
          @prefill="$refs.newMealImageInput.onResize()"
          :alertOnError="false"
          :autoToggleAspectRatio="true"
          width="600"
          height="600"
          margin="0"
          size="10"
          button-class="btn"
          @change="onChangeImage"
        ></picture-input>
      </b-col>
    </b-row>

    <div slot="modal-footer">
      <button class="btn btn-primary" @click="storeMeal">Save</button>
    </div>
  </b-modal>
  -->
</template>

<script>
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from "vue-picture-input";
import units from "../../../data/units";
import format from "../../../lib/format";
import { mapGetters, mapActions, mapMutations } from "vuex";
import Spinner from "../../../components/Spinner";
import IngredientPicker from "../../../components/IngredientPicker";
import MealSizes from "../../../components/Menu/MealSizes";
import fs from "../../../lib/fs.js";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
    MealSizes
  },
  data() {
    return {
      meal: {
        sizes: [],
        gallery: []
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      tags: "tags",
      storeCategories: "storeCategories",
      getCategoryTitle: "storeCategoryTitle",
      allergies: "allergies",
      isLoading: "isLoading"
    }),
    tagOptions() {
      return Object.values(this.tags).map(tag => {
        return {
          text: tag.tag,
          value: tag.id
        };
      });
    },
    categoryOptions() {
      return Object.values(this.storeCategories).map(cat => {
        return {
          text: cat.category,
          value: cat.id
        };
      });
    },
    allergyOptions() {
      return Object.values(this.allergies).map(allergy => {
        return {
          text: allergy.title,
          value: allergy.id
        };
      });
    }
  },
  mounted() {
    this.$refs.createMealModal.show();
    setTimeout(() => {
      this.$refs.featuredImageInput.onResize();
    }, 100);
  },
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals",
      _updateMeal: "updateMeal"
    }),
    forceResize() {
      window.dispatchEvent(new window.Event("resize"));
    },
    async changeGalleryImage(val) {
      let b64 = await fs.getBase64(this.$refs.galleryImageInput.file);
      this.meal.gallery.push({
        url: b64,
        url_thumb: b64
      });
      this.$refs.galleryImageInput.removeImage();
    },
    async deleteGalleryImage(index) {
      this.meal.gallery.splice(index, 1);
    },
    async storeMeal(e) {
      try {
        const { data } = await axios.post("/api/me/meals", this.meal);
      } catch (response) {
        e.preventDefault();
        let error = _.first(Object.values(response.response.data.errors));
        error = error.join(" ");
        this.$toastr.e(error, "Error");
        return;
      }

      this.$toastr.s("Meal created!");
      this.$emit("created");
      this.$refs.createMealModal.hide();
      this.$parent.createMealModal = false;
    },
    async changeImage(val) {
      let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
      this.meal.featured_image = b64;
    },
    toggleModal() {
      this.$parent.createMealModal = false;
    },
    onChangeSizes(sizes) {
      if (!_.isArray(sizes)) {
        throw new Error("Invalid sizes");
      }

      // Validate all rows
      for (let size of sizes) {
        if (!size.title || !size.price || !size.multiplier) {
          return;
        }
      }

      this.meal.sizes = sizes;
    }
  }
};
</script>
