<template>
  <div class="modal-full modal-tabs">
    <b-modal title="Create Meal" ref="createMealModal" @ok.prevent="e => storeMeal()">
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
                  :maxlength="100"
                ></textarea>
              </b-form-group>
              <b-form-group>
                <h4>Price</h4>
                <money required v-model="meal.price" :min="0.1" class="form-control"></money>
              </b-form-group>
                <br>
                <h4>Categories</h4>
                <b-form-checkbox-group
                  buttons
                  v-model="meal.category_ids"
                  :options="categoryOptions"
                  class="filters"
                ></b-form-checkbox-group>

                <h4 class="mt-4">Tags</h4>
                <b-form-checkbox-group
                  buttons
                  v-model="meal.tag_ids"
                  :options="tagOptions"
                  class="filters"
                ></b-form-checkbox-group>

                <h4 class="mt-4">Contains</h4>
                <b-form-checkbox-group
                  buttons
                  v-model="meal.allergy_ids"
                  :options="allergyOptions"
                  class="filters"
                ></b-form-checkbox-group>
            </b-tab>

            <b-tab title="Ingredients">
              <ingredient-picker
                v-model="meal.ingredients"
                :options="{saveButton:true}"
                :meal="meal"
              ></ingredient-picker>
            </b-tab>
          </b-tabs>
        </b-col>

        <b-col cols="2">
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
import fs from '../../../lib/fs.js';

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker
  },
  data() {
    return {
      meal: {}
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
    },
  },
  mounted() {
    this.$refs.createMealModal.show();
    setTimeout(() => {
      this.$refs.featuredImageInput.onResize();
    }, 100)
  },
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals",
      _updateMeal: "updateMeal"
    }),
    storeMeal() {
      axios
        .post("/api/me/meals", this.meal)
        .then(resp => {
          this.$toastr.s('Meal created!');
          this.$refs.createMealModal.hide();
        })
        .catch(e => {
          this.$toastr.e('Failed to create meal.');
        })
        .finally(() => {
          //his.getTableData();
          this.$emit("created");
        });
    },
    async changeImage(val) {
      let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
      this.meal.featured_image = b64;
    }
  }
};
</script>