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

            <v-client-table
              ref="mealsTable"
              :columns="columns"
              :data="tableData"
              :options="options"
            >
              <div slot="beforeTable" class="mb-2">
                <button class="btn btn-success btn-sm" @click="createMeal">Add Meal</button>

                <b-form-radio-group
                  buttons
                  button-variant="primary"
                  size="sm"
                  v-model="filter.status"
                  @change="onChangeStatusFilter"
                  :options="statusFilterOptions"
                />
              </div>

              <div slot="active" slot-scope="props">
                <b-form-checkbox
                  type="checkbox"
                  v-model="props.row.active"
                  :value="1"
                  :unchecked-value="0"
                  @change="(val) => updateActive(props.row.id, val)"
                ></b-form-checkbox>
              </div>

              <div slot="featured_image" slot-scope="props">
                <div v-if="!props.row.editing">
                  <img :src="props.row.featured_image" v-if="props.row.featured_image">
                </div>
                <div v-else>
                  <picture-input
                    :ref="`featuredImageInput${props.row.id}`"
                    :prefill="props.row.featured_image ? props.row.featured_image : false"
                    @prefill="$refs[`featuredImageInput${props.row.id}`].onResize()"
                    :alertOnError="false"
                    :autoToggleAspectRatio="true"
                    width="100"
                    height="100"
                    margin="0"
                    size="10"
                    button-class="btn"
                    @change="(val) => updateImage(props.row.id, val)"
                  ></picture-input>
                </div>
              </div>

              <div slot="title" slot-scope="props">
                <div v-if="!props.row.editing">{{ props.row.title }}</div>
                <div v-else>
                  <b-form-input
                    :key="`title${props.row.id}`"
                    name="title"
                    v-model.lazy="props.row.title"
                    @change="(e) => { updateMeal(props.row.id, {title: editing[props.row.id].title}) }"
                    :formatter="(val) => val.substring(0, 20)"
                  ></b-form-input>
                </div>
              </div>

              <div slot="description" slot-scope="props">
                <div v-if="!isEditing(props.row.id)">{{ props.row.description }}</div>
                <div v-else>
                  <textarea
                    v-model.lazy="editing[props.row.id].description"
                    :rows="4"
                    @change="(e) => { updateMeal(props.row.id, {description: editing[props.row.id].description}) }"
                    :maxlength="100"
                  ></textarea>
                </div>
              </div>

              <div slot="tags" slot-scope="props">
                <div v-if="!props.row.editing">{{ props.row.tag_titles.join(', ') }}</div>
                <div v-else>
                  <input-tag v-model="editing[props.row.id].tag_titles"/>
                </div>
              </div>

              <div slot="price" slot-scope="props">
                <div v-if="!props.row.editing">{{ formatMoney(props.row.price) }}</div>
                <div v-else>
                  <b-form-input
                    v-model="editing[props.row.id].price"
                    @change="(e) => { updateMeal(props.row.id, {price: editing[props.row.id].price}) }"
                  ></b-form-input>
                </div>
              </div>

              <div slot="current_orders" slot-scope="props">{{ props.row.orders.length }}</div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <!--<button class="btn btn-warning btn-sm" @click="editMeal(props.row.id)">Edit</button>-->
                <button class="btn btn-warning btn-sm" @click="toggleEditing(props.row.id)">Edit</button>
                <button class="btn btn-danger btn-sm" @click="deleteMeal(props.row.id)">Delete</button>

                <div v-if="isEditing(props.row.id)">
                  <button
                    class="btn btn-primary btn-sm mt-1 d-block w-100"
                    @click="updateMeal(props.row.id)"
                  >Save</button>
                </div>
              </div>

              <div slot="child_row" slot-scope="props">
                <b-row>
                  <b-col cols="9">
                    <b-form-group label="Title">
                      <b-form-input
                        v-model="editing[props.row.id].title"
                        required
                        placeholder="Enter title"
                      ></b-form-input>
                    </b-form-group>

                    <b-form-row>
                      <b-col>
                        <b-form-group label="Description">
                          <b-form-input
                            v-model="editing[props.row.id].description"
                            required
                            placeholder="Enter description"
                          ></b-form-input>
                        </b-form-group>
                      </b-col>
                      <b-col>
                        <b-form-group label="Price">
                          <b-form-input
                            v-model="editing[props.row.id].price"
                            type="number"
                            required
                            placeholder="Enter price"
                          ></b-form-input>
                        </b-form-group>
                      </b-col>
                    </b-form-row>

                    <h3 class="mt-3">Ingredients</h3>
                    <ingredient-picker v-model="editing[props.row.id].ingredients"/>
                  </b-col>
                  <b-col>
                    <h3>Tags</h3>
                    <div>
                      <input-tag
                        ref="editMealTagsInput"
                        v-model="editing[props.row.id].tag_titles"
                      />
                    </div>

                    <h3 class="mt-3">Image</h3>
                    <picture-input
                      :key="'editMealImageInput' + props.row.id"
                      ref="editMealImageInput"
                      :prefill="editing[props.row.id].featured_image ? editing[props.row.id].featured_image : false"
                      @prefill="$refs.editMealImageInput.onResize()"
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
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <create-meal-modal v-if="createMealModal" v-on:created="refreshTable()" />

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
              <b-form-input v-model="meal.title" required placeholder="Enter email"></b-form-input>
            </b-form-group>

            <b-form-row>
              <b-col>
                <b-form-group label="Description">
                  <b-form-input v-model="meal.description" required placeholder="Enter description"></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group label="Price">
                  <b-form-input
                    v-model="meal.price"
                    type="number"
                    required
                    placeholder="Enter price"
                  ></b-form-input>
                </b-form-group>
              </b-col>
            </b-form-row>

            <h3 class="mt-3">Ingredients</h3>
            <IngredientPicker/>
          </b-col>
          <b-col>
            <h3>Tags</h3>
            <div>
              <input-tag ref="editMealTagsInput" v-model="meal.tags"/>
            </div>

            <h3 class="mt-3">Image</h3>
            <picture-input
              :key="'editMealImageInput' + meal.id"
              ref="editMealImageInput"
              v-if="editMealModal"
              :prefill="meal.featured_image ? meal.featured_image : false"
              @prefill="$refs.editMealImageInput.onResize()"
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

textarea {
  width: 100%;
}
</style>


<script>
import Spinner from "../../components/Spinner";
import IngredientPicker from "../../components/IngredientPicker";
import CreateMealModal from "./Modals/CreateMeal";
import moment from "moment";
import tags from "bootstrap-tagsinput";
import { Event } from "vue-tables-2";
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from "vue-picture-input";
import units from "../../data/units";
import format from "../../lib/format";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
    CreateMealModal,
  },
  updated() {
    //$(window).trigger("resize");
  },
  data() {
    return {
      filter: {
        status: "all"
      },
      isLoading: true,
      createMealModal: false,
      viewMealModal: false,
      editMealModal: false,
      deleteMealModal: false,

      editing: [], // inline editing

      tags: [],
      newTags: [],
      ingredientSearch: "",
      ingredientResults: [],
      ingredientQuery: "",
      ingredientList: "",
      ingredients: [],
      meal: [],
      mealID: null,
      newMeal: {
        featured_image: "",
        title: "",
        description: "",
        price: "",
        ingredients: []
      },
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
        "tags",
        "price",
        "num_orders",
        "created_at",
        "actions"
      ],
      options: {
        headings: {
          active: "Active",
          featured_image: "Image",
          title: "Title",
          description: "Description",
          tags: "Tags",
          price: "Price",
          num_orders: "# Orders",
          created_at: "Added",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `meal meal-${row.id}`;
          classes += row.active ? "" : " faded";
          return classes;
        },
        customFilters: [
          {
            name: "status",
            callback: function(row, val) {
              if (val === "all") return true;
              else if (val === "active") return row.active;
              else if (val === "inactive") return !row.active;
              return false;
            }
          }
        ]
      }
    };
  },
  computed: {
    weightUnitOptions() {
      return units.weight.selectOptions();
    },
    statusFilterOptions() {
      return [
        { text: "All", value: "all" },
        { text: "Active", value: "active" },
        { text: "Inactive", value: "inactive" }
      ];
    }
  },
  mounted() {
    this.getTableData();
  },
  methods: {
    formatMoney: format.money,
    refreshTable() {
      //this.$refs.mealsTable.render();
      this.getTableData();
    },
    getTableData() {
      let self = this;
      axios.get("/api/me/meals").then(response => {
        this.tableData = response.data.map(meal => {
          meal.editing = false;
          meal.num_orders = meal.orders.length;
          return meal;
        });
        self.isLoading = false;
        self.active = response.data.map(row => row.active);

        this.$nextTick(() => {
          this.syncEditables();
        });
      });
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, o => {
        return o.id === id;
      });
    },
    syncEditables() {
      this.editing = _.keyBy({ ...this.tableData }, "id");
    },
    toggleEditing(id) {
      const i = this.getTableDataIndexById(id);

      if (i !== -1 && !this.isEditing(id)) {
        //this.editing[id] = { ...this.tableData[i] };
        this.tableData[i].editing = true;
        this.$set(this.tableData, i, this.tableData[i]);
      } else {
        this.tableData[i].editing = false;
        this.$set(this.tableData, i, this.tableData[i]);
        //_.unset(this.editing, id);
      }

      this.$nextTick(() => {
        this.syncEditables();
        this.$refs.mealsTable.toggleChildRow(i + 1);
      });
    },
    isEditing(id) {
      const i = this.getTableDataIndexById(id);
      if (i !== -1) {
        return this.tableData[i].editing;
      }

      return false;
    },
    updateMeal(id, changes) {
      const i = this.getTableDataIndexById(id);

      // None found
      if (i === -1) {
        return this.getTableData();
      }

      if (_.isEmpty(changes)) {
        changes = this.editing[id];
      }

      axios.patch(`/api/me/meals/${id}`, changes).then(resp => {
        this.$set(this.tableData, i, resp.data);
        this.$refs.mealsTable.toggleChildRow(i + 1);
        this.syncEditables();
        this.refreshTable();
      });
    },
    updateActive(id, active, props) {
      const i = _.findIndex(this.tableData, o => {
        return o.id === id;
      });

      // None found
      if (i === -1) {
        return this.getTableData();
      }

      this.tableData[i].active = active;

      axios
        .patch(`/api/me/meals/${id}`, {
          active: active
        })
        .then(resp => {
          this.tableData[i] = { ...resp.data };
          this.syncEditables();
          this.refreshTable();
        });
    },

    createMeal() {
      this.createMealModal = true;
    },
    
    viewMeal(id) {
      axios.get(`/api/me/meals/${id}`).then(response => {
        this.meal = response.data;
        this.ingredients = response.data.ingredient;
        this.tags = response.data.meal_tag;
        this.viewMealModal = true;

        this.$nextTick(function() {
          window.dispatchEvent(new Event("resize"));
        });
      });
    },
    editMeal(id) {
      this.addTag = false;
      axios.get(`/api/me/meals/${id}`).then(response => {
        this.meal = response.data;
        this.ingredients = response.data.ingredient;
        this.tags = response.data.meal_tag;
        this.editMealModal = true;
        this.mealID = response.data.id;

        setTimeout(() => {
          this.$refs.editMealImageInput.onResize();
        }, 50);
      });
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
        console.log("FileReader API not supported: use the <form>, Luke!");
      }
    },
    onClickAddIngredient() {
      this.ingredients.push({});
    },
    onChangeStatusFilter(val) {
      Event.$emit("vue-tables.filter::status", val);
    }
  }
};
</script>