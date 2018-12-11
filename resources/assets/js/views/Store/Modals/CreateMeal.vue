<template>
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
          <input-tag ref="newMealTagsInput" v-model="newMeal.tag_titles"/>
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
</template>

<script>
import Spinner from "../../../components/Spinner";
import IngredientPicker from "../../../components/IngredientPicker";
import PictureInput from "vue-picture-input";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
  },
  data() {
    return {
      newMeal: {},
    }
  },
  mounted() {
    this.$refs.createMealModal.show();

    setTimeout(() => {
      this.$refs.newMealImageInput.onResize();
    }, 50);
  },
  methods: {
    onChangeImage(image) {
      if (image) {
        this.newMeal.featured_image = image;
      } else {
        console.log("FileReader API not supported: use the <form>, Luke!");
      }
    },
    storeMeal() {
      axios
        .post("/api/me/meals", {
          featured_image: this.newMeal.featured_image,
          title: this.newMeal.title,
          description: this.newMeal.description,
          price: this.newMeal.price,
          tag_titles: this.newMeal.tag_titles,
        })
        .then(resp => {
          this.$refs.createMealModal.hide();
        })
        .finally(() => {
          //his.getTableData();
          this.$emit('created');
        });
    }
  }
};
</script>