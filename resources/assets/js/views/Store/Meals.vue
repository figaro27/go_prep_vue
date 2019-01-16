<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
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
                <img class="thumb" :src="props.row.featured_image" v-if="props.row.featured_image">
              </div>

              <div slot="tags" slot-scope="props">{{ props.row.tag_titles.join(', ') }}</div>
              <div slot="categories" slot-scope="props">{{ props.row.categories.map(category => category.category).join(', ') }}</div>

              <div slot="price" slot-scope="props">{{ formatMoney(props.row.price) }}</div>

              <div slot="current_orders" slot-scope="props">{{ props.row.orders.length }}</div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button class="btn view btn-warning btn-sm" @click="viewMeal(props.row.id)">View</button>
                <button class="btn btn-danger btn-sm" @click="deleteMeal(props.row.id)">Delete</button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <create-meal-modal v-if="createMealModal" v-on:created="refreshTable()"/>

    <div class="modal-full modal-tabs">
      <b-modal title="Meal" v-model="viewMealModal" v-if="viewMealModal">
        <b-row>
          <b-col>
            <b-tabs>
              <b-tab title="General" active>
                <b-form-group label="Meal title" label-for="meal-title" :state="true">
                  <b-form-input
                    id="meal-title"
                    type="text"
                    v-model="meal.title"
                    placeholder="Meal Name"
                    required
                    @change="val => updateMeal(meal.id, {title: val})"
                  ></b-form-input>
                </b-form-group>

                <b-form-group label="Meal description" label-for="meal-description" :state="true">
                  <textarea
                    v-model.lazy="meal.description"
                    id="meal-description"
                    class="form-control"
                    :rows="4"
                    :maxlength="100"
                    @input="e => updateMealDescription(meal.id, e.target.value)"
                  ></textarea>
                </b-form-group>
              </b-tab>
              <b-tab title="Allergies">
                <h3>This meal contains</h3>
                <b-form-checkbox-group
                  buttons
                  v-model="meal.allergy_ids"
                  :options="allergyOptions"
                  @change="val => updateMeal(meal.id, {allergies: val})"
                ></b-form-checkbox-group>
              </b-tab>
              <b-tab title="Ingredients">
                <h3>Ingredients</h3>
                <ingredient-picker v-model="meal.ingredients" :options="{saveButton:true}" @save="val => onChangeIngredients(meal.id, val)"></ingredient-picker>
              </b-tab>
              <b-tab title="Categories">
                <h3>Categories</h3>
                <b-form-checkbox-group
                  buttons
                  v-model="meal.category_ids"
                  :options="categoryOptions"
                  @change="val => updateMeal(meal.id, {categories: val})"
                ></b-form-checkbox-group>
              </b-tab>
            </b-tabs>
          </b-col>

          <b-col cols="2">
            <picture-input
              :ref="`featuredImageInput${meal.id}`"
              :prefill="meal.featured_image ? meal.featured_image : false"
              @prefill="$refs[`featuredImageInput${meal.id}`].onResize()"
              :alertOnError="false"
              :autoToggleAspectRatio="true"
              margin="0"
              size="10"
              button-class="btn"
              @change="(val) => updateImage(meal.id, val)"
            ></picture-input>

            <b-form-group label="Tags" label-for="meal-tags" :state="true">
              <input-tag
                ref="editMealTagsInput"
                id="meal-tags"
                v-model="meal.tag_titles_flat"
                :tags="meal.tag_titles_input"
                @tags-changed="tags => onChangeTags(meal.id, tags)"
              />
            </b-form-group>

            <!--<b-form-group label="Categories" label-for="meal-categories" :state="true">
              <ul>
                <draggable v-model="meal.categories" @change="onChangeCategories">
                  <li v-for="category in meal.categories">{{ category.category }}</li>
                </draggable>
              </ul>

              <b-form @submit.prevent="onAddCategory">
                <b-input v-model="meal.new_category" :type="text" placeholder="New category..."></b-input>
              </b-form>
            </b-form-group>
            -->
          </b-col>
        </b-row>

        <!--

        <hr>
        <img :src="meal.featured_image">
        <hr>
        <hr>

        <div v-for="tag in meal.tags" :key="tag">
          <b-button @click="activate(tag.tag)">{{ tag.tag }}</b-button>
          <hr>
        </div>

        
        <hr>
        <b-form-input type="text" v-model="meal.price" placeholder="Meal Name" required></b-form-input>
        <hr>
        <b-form-input type="text" v-model="meal.active_orders" placeholder="Meal Name" required></b-form-input>
        <hr>
        <p>{{ meal.num_orders }}</p>
        <hr>
        <b-form-input type="text" v-model="meal.created_at" placeholder="Meal Name" required></b-form-input>
        <hr>
        -->
      </b-modal>
    </div>

    <b-modal title="Meal" v-model="deleteMealModal" v-if="deleteMealModal">
      <center>
        <h5>Are you sure you want to delete this meal?</h5>
        <button class="btn btn-danger mt-3" @click="destroyMeal(mealID)">Delete</button>
      </center>
    </b-modal>
  </div>
</template>

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
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
    CreateMealModal
  },
  updated() {
    //$(window).trigger("resize");
  },
  data() {
    return {
      _,
      filter: {
        status: "all"
      },
      meal: {
        title: "",
        featured_image: "",
        description: "",
        new_category: "",
        tags: "",
        price: "",
        active_orders: "",
        num_orders: "",
        created_at: "",
        categories: [],
      },
      createMealModal: false,
      viewMealModal: false,
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

      columns: [
        "active",
        "featured_image",
        "title",
        "categories",
        "tags",
        "price",
        "active_orders",
        "lifetime_orders",
        "created_at",
        "actions"
      ],
      options: {
        headings: {
          active: "Active",
          featured_image: "Image",
          title: "Title",
          categories: "Categories",
          tags: "Tags",
          price: "Price",
          active_orders: "Active Orders",
          lifetime_orders: "Lifetime Orders",
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
    ...mapGetters({
      store: "viewedStore",
      meals: "storeMeals",
      storeCategories: "storeCategories",
      allergies: "allergies",
      isLoading: "isLoading"
    }),
    tableData() {
      return Object.values(this.meals);
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
    weightUnitOptions() {
      return units.mass.selectOptions();
    },
    statusFilterOptions() {
      return [
        { text: "All", value: "all" },
        { text: "Active", value: "active" },
        { text: "Inactive", value: "inactive" }
      ];
    },
    tagsForInput() {
      return _.map(["Breakfast", "Dinner"], tag => {
        return { text: tag };
      });
    }
  },
  created() {
    this.updateMealDescription = _.debounce((id, description) => {
      this.updateMeal(id, { description });
    }, 300);
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals"
    }),
    formatMoney: format.money,
    refreshTable() {
      this.refreshMeals();
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, o => {
        return o.id === id;
      });
    },
    updateMeal(id, changes) {
      const i = this.getTableDataIndexById(id);
      if (i === -1) {
        return this.getTableData();
      }
      if (_.isEmpty(changes)) {
        changes = this.editing[id];
      }
      axios.patch(`/api/me/meals/${id}`, changes).then(resp => {
        this.$set(this.tableData, i, resp.data);
        //this.$refs.mealsTable.toggleChildRow(i + 1);
        //this.syncEditables();
        this.refreshTable();
      });
    },
    updateActive(id, active, props) {
      const i = _.findIndex(this.tableData, o => {
        return o.id === id;
      });

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
          //this.syncEditables();
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
        this.mealID = response.data.id;
        this.viewMealModal = true;

        this.$nextTick(function() {
          window.dispatchEvent(new window.Event("resize"));
        });
      });
    },
    deleteMeal: function($id) {
      this.mealID = $id;
      this.deleteMealModal = true;
    },
    destroyMeal: function($id) {
      axios.delete(`/api/me/meals/${$id}`);
      this.refreshTable();
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
    getIngredientList: function(ingredients) {
      let ingredientList = "";
      ingredients.forEach(function(ingredient) {
        ingredientList +=
          ingredient.food_name.charAt(0).toUpperCase() +
          ingredient.food_name.slice(1) +
          ", ";
      });
      return ingredientList;
    },
    getNutritionTotals: function(ingredients) {
      let nutrition = {
        calories: 0,
        totalFat: 0,
        satFat: 0,
        transFat: 0,
        cholesterol: 0,
        sodium: 0,
        totalCarb: 0,
        fibers: 0,
        sugars: 0,
        proteins: 0,
        vitaminD: 0,
        potassium: 0,
        calcium: 0,
        iron: 0,
        addedSugars: 0
      };

      ingredients.forEach(ingredient => {
        nutrition.calories += ingredient.nf_calories || ingredient.calories;
        nutrition.totalFat += ingredient.nf_total_fat || ingredient.totalFat;
        nutrition.satFat += ingredient.nf_saturated_fat || ingredient.satFat;
        nutrition.transFat += ingredient.nf_trans_fat || ingredient.transFat;
        nutrition.cholesterol +=
          ingredient.nf_cholesterol || ingredient.cholesterol;
        nutrition.sodium += ingredient.nf_sodium || ingredient.sodium;
        nutrition.totalCarb +=
          ingredient.nf_total_carbohydrate || ingredient.totalCarb;
        nutrition.fibers += ingredient.nf_dietary_fiber || ingredient.fibers;
        nutrition.sugars += ingredient.nf_sugars || ingredient.sugars;
        nutrition.proteins += ingredient.nf_protein || ingredient.proteins;
        nutrition.vitaminD += ingredient.nf_vitamind || ingredient.vitaminD;
        nutrition.potassium += ingredient.nf_potassium || ingredient.potassium;
        nutrition.calcium += ingredient.nf_calcium || ingredient.calcium;
        nutrition.iron += ingredient.nf_iron || ingredient.iron;
        nutrition.sugars += ingredient.nf_addedsugars || ingredient.sugars;
      });

      return nutrition;
    },
    getNutritionFacts(ingredients, meal) {
      const nutrition = this.getNutritionTotals(ingredients);
      const ingredientList = this.getIngredientList(ingredients);

      $(`#nutritionFacts${meal.id}`).nutritionLabel({
        showServingUnitQuantity: false,
        itemName: meal.title,
        ingredientList: ingredientList,

        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,

        allowFDARounding: true,
        decimalPlacesForNutrition: 2,

        showPolyFat: false,
        showMonoFat: false,

        valueCalories: nutrition.calories,
        valueFatCalories: nutrition.fatCalories,
        valueTotalFat: nutrition.totalFat,
        valueSatFat: nutrition.satFat,
        valueTransFat: nutrition.transFat,
        valueCholesterol: nutrition.cholesterol,
        valueSodium: nutrition.sodium,
        valueTotalCarb: nutrition.totalCarb,
        valueFibers: nutrition.fibers,
        valueSugars: nutrition.sugars,
        valueProteins: nutrition.proteins,
        valueVitaminD: nutrition.vitaminD,
        valuePotassium_2018: nutrition.potassium,
        valueCalcium: nutrition.calcium,
        valueIron: nutrition.iron,
        valueAddedSugars: nutrition.addedSugars,
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
    onChangeIngredients(mealId, ingredients) {
      if (!_.isNumber(mealId) || !_.isArray(ingredients)) {
        throw new Exception("Invalid ingredients");
      }

      this.updateMeal(mealId, { ingredients });
    },
    onClickAddIngredient() {
      this.ingredients.push({});
    },
    onChangeStatusFilter(val) {
      Event.$emit("vue-tables.filter::status", val);
    },
    onChangeTags(id, newTags) {
      this.editing[id].tag_titles_input = newTags;
      this.editing[id].tag_titles = _.map(newTags, "text");
      this.updateMeal(id, { tag_titles: this.editing[id].tag_titles });
    },
    activate(tag) {
      alert(tag);
    },
    onAddCategory() {
      this.meal.categories.push({
        category: this.meal.new_category
      });
      this.meal.new_category = "";

      this.updateMeal(this.meal.id, { categories: this.meal.categories });
    },
    onChangeCategories(e) {
      if(_.isObject(e.moved)) {
        this.updateMeal(this.meal.id, { categories: this.meal.categories });
      }
    }
  }
};
</script>