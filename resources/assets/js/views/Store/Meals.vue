<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Test Area</div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm form-group">
                <h6>Returns Ingredient & Nutrition Data</h6>
                <input
                  class="input-lg form-control mb-2"
                  v-model="ingredientQuery"
                  placeholder="Type Ingredients Here"
                >
                <button
                  class="btn btn-primary btn-sm form-control"
                  @click="getNutrition"
                >Get Nutrition</button>
              </div>
              <div class="col-sm">
                <h6>Suggests Ingredients</h6>
                <input
                  list="ingredientResults"
                  class="input-lg form-control"
                  v-model="ingredientSearch"
                  @input="searchInstant"
                  placeholder="Search Ingredients"
                >
                <table class="table table-bordered">
                  <tr>
                    <th>Food Name</th>
                  </tr>
                  <tr v-for="ingredientResult in ingredientResults">
                    <td>{{ ingredientResult.food_name }}</td>
                  </tr>
                </table>
              </div>
              <div class="col-sm">
                <h6>Tags Used for Meal Tags</h6>
                <input type="text" value="test1,test2" data-role="tagsinput">
              </div>
            </div>

            <div class="row">
              <div class="col-sm">
                <table class="table table-bordered">
                  <tr>
                    <th>Food Name</th>
                    <th>Serving Quantity</th>
                    <th>Serving Unit</th>
                  </tr>
                  <tr v-for="ingredient in ingredients">
                    <td>{{ ingredient.food_name }}</td>
                    <td>{{ ingredient.serving_qty }}</td>
                    <td>{{ ingredient.serving_unit }}</td>
                  </tr>
                </table>
              </div>
              <div class="col-sm">
                <button
                  class="btn btn-primary btn-sm form-control"
                  @click="getNutritionFacts"
                >Get Nutrition Facts</button>
                <div id="nutritionFacts"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Meals</div>
          <div class="card-body">
            <Spinner v-if="isLoading"/>

            <v-client-table :columns="columns" :data="tableData" :options="options">
              <div slot="beforeTable">
                <button class="btn btn-success btn-sm mb-2" @click="createMeal">Add Meal</button>
              </div>

              <div slot="active" slot-scope="props">
                <b-form-checkbox
                  type="checkbox"
                  v-model="active[props.index-1]"
                  value="true"
                  unchecked-value="false"
                  @change="updateActive(props.row.id, props.index-1)"
                ></b-form-checkbox>
              </div>

              <div slot="featured_image" slot-scope="props">
                <img
                  :class="{ 'faded': !JSON.parse(active[props.index-1].toLowerCase()) }"
                  :src="tableData[props.index].featured_image"
                >
              </div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button class="btn btn-primary btn-sm" @click="viewMeal(props.row.id)">View</button>
                <button class="btn btn-warning btn-sm" @click="editMeal(props.row.id)">Edit</button>
                <button class="btn btn-danger btn-sm" @click="deleteMeal(props.row.id)">Delete</button>
              </div>

              <div slot="child_row" slot-scope="props">
                <div class="row">
                  <div class="col-sm">
                    <h3>Tags</h3>
                    <b-list-group>
                      <b-list-group-item
                        v-for="meal_tag in props.row.meal_tags"
                        :key="meal_tag.id"
                      >{{ meal_tag.tag }}</b-list-group-item>
                    </b-list-group>
                  </div>
                  <div class="col-sm">
                    <h3>Ingredients</h3>
                    <b-list-group>
                      <b-list-group-item
                        v-for="ingredient in props.row.ingredients"
                        :key="ingredient.id"
                      >{{ ingredient.food_name }}</b-list-group-item>
                    </b-list-group>
                  </div>
                  <div class="col-sm">
                    <h3>Nutrition Facts</h3>
                  </div>
                </div>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <b-modal size="lg" title="Meal" v-model="createMealModal" v-if="createMealModal">
      <b-list-group>
        <b-list-group-item>
          <b-form-input v-model="newMeal.featured_image" placeholder="Featured Image"></b-form-input>
        </b-list-group-item>
        <b-list-group-item>
          <b-form-input v-model="newMeal.title" placeholder="Title"></b-form-input>
        </b-list-group-item>
        <b-list-group-item>
          <b-form-input v-model="newMeal.description" placeholder="Description"></b-form-input>
        </b-list-group-item>
        <b-list-group-item>
          <b-form-input v-model="newMeal.price" placeholder="Price"></b-form-input>
        </b-list-group-item>
      </b-list-group>
      <button class="btn btn-primary mt-3 float-right" @click="storeMeal">Save</button>
    </b-modal>

    <b-modal size="lg" title="Meal" v-model="viewMealModal" v-if="viewMealModal">
      <b-list-group>
        <b-list-group-item>Logo: {{ meal.featured_image }}</b-list-group-item>
        <b-list-group-item>Title: {{ meal.title }}</b-list-group-item>
        <b-list-group-item>Description: {{ meal.description }}</b-list-group-item>
        <b-list-group-item>Price: {{ meal.price }}</b-list-group-item>
        <b-list-group-item>Created: {{ meal.created_at.date }}</b-list-group-item>
      </b-list-group>

      <h3 class="mt-3">Tags</h3>
      <b-list-group>
        <b-list-group-item v-for="tag in tags" :key="tag.id">{{ tag.tag }}</b-list-group-item>
      </b-list-group>
      <div>
        <h3>Ingredients</h3>
        <table>
          <tr v-for="ingredient in ingredients">
            <td>{{ ingredient.food_name }}</td>
            <td>{{ ingredient.serving_qty }}</td>
            <td>{{ ingredient.serving_unit }}</td>
            <td>Calories: {{ ingredient.calories}}</td>
            <td>Protein: {{ ingredient.proteins}}</td>
          </tr>
        </table>
      </div>
      <button class="btn btn-primary btn-sm" @click="getNutritionFacts">Get Nutrition Facts</button>
      <div id="nutritionFacts"></div>
    </b-modal>

    <b-modal title="Meal" v-model="editMealModal" v-if="editMealModal" class="modal-full" no-fade>
      <b-form>

        <b-row>
          <b-col cols="9">
            <b-form-group label="Title">
              <b-form-input
                  v-model="meal.title"
                  required
                  placeholder="Enter email">
              </b-form-input>
            </b-form-group>

            <b-form-row>
              <b-col>
                <b-form-group label="Description">
                  <b-form-input
                      v-model="meal.description"
                      required
                      placeholder="Enter description">
                  </b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group label="Price">
                  <b-form-input
                      v-model="meal.price"
                      type="number"
                      required
                      placeholder="Enter price">
                  </b-form-input>
                </b-form-group>
              </b-col>
            </b-form-row>

            <h3 class="mt-3">Ingredients</h3>
            <table>
              <thead>
                <th>Name</th>
                <th>Weight</th>
                <th></th>
              </thead>
              <tbody>
                <tr v-for="ingredient in ingredients" :key="ingredient.id">
                  <td>
                    <b-form-group>
                      <b-form-input placeholder="Name" v-model="ingredient.food_name"></b-form-input>
                    </b-form-group>
                  </td>
                  <td>
                    <b-form-group>
                      <b-form-input placeholder="Weight" v-model="ingredient.serving_qty"></b-form-input>
                    </b-form-group>
                  </td>
                  <td>
                    <b-form-group>
                      <b-select v-model="ingredient.serving_unit" :options="weightUnitOptions">
                        <option slot="top" disabled>-- Select unit --</option>
                      </b-select>
                    </b-form-group>
                  </td>
                </tr>
                <tr>
                  <td colspan="3" class="text-right">
                    <a href="#" @click="onClickAddIngredient"><i class="fas fa-plus-circle"></i></a>
                  </td>
                </tr>
              </tbody>
            </table>

          </b-col>
          <b-col>
            <h3>Tags</h3>
            <div>
              <input-tag ref="editMealTagsInput" v-model="meal.tags" />
            </div>

            <h3 class="mt-3">Image</h3>
            <picture-input 
              ref="editMealImageInput"
              width="600" 
              height="600" 
              margin="0" 
              accept="image/jpeg,image/png" 
              size="10"
              button-class="btn"
              :custom-strings="{
                //upload: '<h1>Bummer!</h1>',
                //drag: 'Drag a 😺 GIF or GTFO'
              }"
              @change="onChangeImage">
            </picture-input>
          </b-col>
        </b-row>
        
        <button class="btn btn-primary mt-3 float-right" @click="updateMeal(mealID)">Save</button>
      
      </b-form>
    </b-modal>

    <b-modal title="Meal" v-model="deleteMealModal" v-if="deleteMealModal">
      <center>
        <h5>Are you sure you want to delete this meal?</h5>
        <button class="btn btn-danger mt-3" @click="destroyMeal(mealID)">Delete</button>
      </center>
    </b-modal>
  </div>
</template>

<style lang="scss">
@import "~bootstrap-tagsinput/dist/bootstrap-tagsinput.css";
@import "~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css";

.label-info {
  background-color: #28a745;
  border-radius: 5px;
  padding: 3px;
}

.faded {
  opacity: 0.5;
}

.VuePagination {
  text-align: center;
}

.vue-title {
  text-align: center;
  margin-bottom: 10px;
}

.vue-pagination-ad {
  text-align: center;
}

.glyphicon.glyphicon-eye-open {
  width: 16px;
  display: block;
  margin: 0 auto;
}

th:nth-child(3) {
  text-align: center;
}

.VueTables__child-row-toggler {
  width: 16px;
  height: 16px;
  line-height: 16px;
  display: block;
  margin: auto;
  text-align: center;
}

.VueTables__child-row-toggler--closed::before {
  content: "+";
}

.VueTables__child-row-toggler--open::before {
  content: "-";
}

.picture-input {
  .preview-container {
  }
}
</style>


<script>
import Spinner from "../../components/Spinner";
import moment from "moment";
import tags from "bootstrap-tagsinput";
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from 'vue-picture-input'
import units from '../../data/units';

export default {
  components: {
    Spinner,
    PictureInput,
  },
  data() {
    return {
      isLoading: true,
      createMealModal: false,
      viewMealModal: false,
      editMealModal: false,
      deleteMealModal: false,

      tags: [],
      newTags: [],
      ingredientSearch: "",
      ingredientResults: [],
      ingredientQuery: "",
      ingredientList: "",
      ingredients: [],
      meal: [],
      mealID: null,
      newMeal: ["featured_image", "title", "description", "price"],
      nutrition: {
        calories: null,
        totalFat: null,
        satFat: null,
        transFat: null,
        cholesterol: null,
        sodium: null,
        totalCarb: null,
        fibers: null,
        sugars: null,
        proteins: null,
        vitaminD: null,
        potassium: null,
        calcium: null,
        iron: null,
        addedSugars: null
      },

      active: [],
      tableData: [],
      columns: [
        "active",
        "featured_image",
        "title",
        "description",
        "price",
        "current_orders",
        "created_at.date",
        "actions"
      ],
      options: {
        headings: {
          active: "Active",
          featured_image: "Image",
          title: "Title",
          description: "Description",
          price: "Price",
          current_orders: "Current Orders",
          "created_at.date": "Added",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          if (row.active == "false") return "faded";
        }
      }
    };
  },
  computed: {
    weightUnitOptions() {
      return units.weight.selectOptions();
    }
  },
  mounted() {
    this.getTableData();
  },
  methods: {
    getTableData() {
      let self = this;
      axios.get("/api/me/meals").then(function(response) {
        self.tableData = response.data;
        self.isLoading = false;
        self.active = response.data.map(row => row.active);
      });
    },
    updateActive: function(id, row) {
      this.$nextTick(function() {
        let row = $row;
        axios.patch(`/api/me/meals/${id}`, {
          active: this.active[row]
        });
      });
    },

    createMeal() {
      this.createMealModal = true;
    },
    storeMeal() {
      axios.post("/api/me/meals", {
        featured_image: this.newMeal.featured_image,
        title: this.newMeal.title,
        description: this.newMeal.description,
        price: this.newMeal.price
      });
      this.getTableData();
      this.createMealModal = false;
    },
    viewMeal($id) {
      axios.get(`/api/me/meals/${$id}`).then(response => {
        this.meal = response.data;
        this.ingredients = response.data.ingredient;
        this.tags = response.data.meal_tag;
        this.viewMealModal = true;
        
        this.$nextTick(function () {
          window.dispatchEvent(new Event('resize'));
        });
      });
    },
    editMeal($id) {
      this.addTag = false;
      axios.get(`/api/me/meals/${$id}`).then(response => {
        this.meal = response.data;
        this.ingredients = response.data.ingredient;
        this.tags = response.data.meal_tag;
        this.editMealModal = true;
        this.mealID = response.data.id;

        setTimeout(() => {
          this.$refs.editMealImageInput.onResize();
          //$(this.$refs.editMealTagsInput).tagsinput();

        }, 50);
      });
    },
    updateMeal: function($id) {
      axios.patch(`/api/me/meals/${$id}`, this.meal);
      this.getTableData();
    },
    deleteMeal: function($id) {
      this.mealID = $id;
      this.deleteMealModal = true;
    },
    destroyMeal: function($id) {
      axios.delete(`/api/me/meals/${$id}`);
      this.getTableData();
      this.deleteMealModal = false;
    },

    getNutrition: function() {
      axios
        .post("../nutrients", {
          query: this.ingredientQuery
        })
        .then(response => {
          this.ingredients = response.data.foods;
        });
    },
    searchInstant: function() {
      axios
        .post("../searchInstant", {
          search: this.ingredientSearch
        })
        .then(response => {
          this.ingredientResults = response.data.common;
        });
    },
    getIngredientList: function() {
      let self = this;
      this.ingredients.forEach(function(ingredient) {
        self.ingredientList +=
          ingredient.food_name.charAt(0).toUpperCase() +
          ingredient.food_name.slice(1) +
          ", ";
      });
    },
    getNutritionTotals: function() {
      let self = this;
      this.ingredients.forEach(function(ingredient) {
        self.nutrition.calories += ingredient.nf_calories;
        self.nutrition.totalFat += ingredient.nf_total_fat;
        self.nutrition.satFat += ingredient.nf_saturated_fat;
        self.nutrition.transFat += ingredient.nf_trans_fat;
        self.nutrition.cholesterol += ingredient.nf_cholesterol;
        self.nutrition.sodium += ingredient.nf_sodium;
        self.nutrition.totalCarb += ingredient.nf_total_carbohydrate;
        self.nutrition.fibers += ingredient.nf_dietary_fiber;
        self.nutrition.sugars += ingredient.nf_sugars;
        self.nutrition.proteins += ingredient.nf_protein;
        self.nutrition.vitaminD += ingredient.nf_vitamind;
        self.nutrition.potassium += ingredient.nf_potassium;
        self.nutrition.calcium += ingredient.nf_calcium;
        self.nutrition.iron += ingredient.nf_iron;
        self.nutrition.addedSugars += ingredient.nf_addedsugars;
      });
    },
    getNutritionFacts() {
      this.getNutritionTotals();
      this.getIngredientList();
      $("#nutritionFacts").nutritionLabel({
        showServingUnitQuantity: false,
        itemName: this.meal.title,
        ingredientList: this.ingredientList,

        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,

        allowFDARounding: true,
        decimalPlacesForNutrition: 2,

        showPolyFat: false,
        showMonoFat: false,

        valueCalories: this.nutrition.calories,
        valueFatCalories: this.nutrition.fatCalories,
        valueTotalFat: this.nutrition.totalFat,
        valueSatFat: this.nutrition.satFat,
        valueTransFat: this.nutrition.transFat,
        valueCholesterol: this.nutrition.cholesterol,
        valueSodium: this.nutrition.sodium,
        valueTotalCarb: this.nutrition.totalCarb,
        valueFibers: this.nutrition.fibers,
        valueSugars: this.nutrition.sugars,
        valueProteins: this.nutrition.proteins,
        valueVitaminD: this.nutrition.vitaminD,
        valuePotassium_2018: this.nutrition.potassium,
        valueCalcium: this.nutrition.calcium,
        valueIron: this.nutrition.iron,
        valueAddedSugars: this.nutrition.addedSugars,
        showLegacyVersion: false
      });
    },
    onChangeImage(image) {
      if (image) {
        this.meal.featured_image = image;
      } else {
        console.log('FileReader API not supported: use the <form>, Luke!')
      }
    },
    onClickAddIngredient() {
      this.ingredients.push({});
    },
  }
};
</script>