<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Meals
                    </div>
                    <div class="card-body">

                        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Archivo+Black" />
                        <div style="width:50%;margin-top:20px;margin-bottom:20px">
                        <input class="form-control input-lg" v-model="query" style="margin-bottom:10px" placeholder="Type ingredients here">
                        <button class="btn btn-primary btn-sm" @click="getNutrition" >Get Nutrition</button>
                        <table>
                            <tr v-for="ingredient in ingredients">
                            <td>{{ ingredient.food_name }}</td>
                            <td>{{ ingredient.serving_qty }}</td>
                            <td>{{ ingredient.serving_unit }}</td>
                            </tr>
                        </table>


                        <input class="form-control input-lg" v-model="search" style="margin-bottom:10px" placeholder="Search Ingredients">
                        <p>{{ search }}</p>
                        <button class="btn btn-primary btn-sm" @click="searchInstant">Search Ingredients</button>
                        <p>{{ searchResults }}</p>

                        </div>
                        
                        <Spinner v-if="isLoading"/>
                        <v-client-table :columns="columns" :data="tableData" :options="options" >
                            <div slot="beforeTable">
                                <button class="btn btn-success btn-sm" @click="createMeal">Add New</button>
                            </div>


                            <template slot="child_row" slot-scope="props">
                              <div><b>First name:</b></div>
                              <div><b>Last name:</b></div>
                            </template>

                        <div slot="featured_image" slot-scope="props">
                            <img :src="tableData[props.index].featured_image">
                        </div>                       
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="viewMeal(props.row.id)">View</button>
                                <button class="btn btn-warning btn-sm" @click="editMeal(props.row.id)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="deleteMeal(props.row.id)">Delete</button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>

        <b-modal size="lg" title="Meal" v-model="createMealModal" v-if="createMealModal">
            <b-list-group>
              <b-list-group-item><b-form-input v-model="newMeal.featured_image" placeholder="Featured Image"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="newMeal.title" placeholder="Title"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="newMeal.category" placeholder="Category"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="newMeal.description" placeholder="Description"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="newMeal.price" placeholder="Price"></b-form-input></b-list-group-item>
            </b-list-group>
            <button class="btn btn-primary mt-3 float-right" @click="storeMeal">Add Meal</button>
        </b-modal>

        <b-modal size="lg" title="Meal" v-model="viewMealModal" v-if="viewMealModal">
            <b-list-group>
                <b-list-group-item>Logo: {{ meal.featured_image }}</b-list-group-item>
                <b-list-group-item>Title: {{ meal.title }}</b-list-group-item>
                <b-list-group-item>Category: {{ meal.category }}</b-list-group-item>
                <b-list-group-item>Description: {{ meal.description }}</b-list-group-item>
                <b-list-group-item>Price: {{ meal.price }}</b-list-group-item>
                <b-list-group-item>Created: {{ meal.created_at.date }}</b-list-group-item>
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
                        <div id="test2"></div>
        </b-modal>

        <b-modal title="Meal" v-model="editMealModal" v-if="editMealModal">
            <b-list-group>
              <b-list-group-item><b-form-input v-model="meal.featured_image"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="meal.title"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="meal.category"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="meal.description"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="meal.price"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="meal.created_at"></b-form-input></b-list-group-item>
            </b-list-group>

            <div>
                <h3>Ingredients</h3>
                <table>
                    <tr v-for="ingredient in ingredients" :key="ingredient.id">
                      <td><b-form-input v-model="ingredient.food_name"></b-form-input></td>
                      <td><b-form-input v-model="ingredient.serving_qty"></b-form-input></td>
                      <td><b-form-input v-model="ingredient.serving_unit"></b-form-input></td>
                  </tr>
              </table>

            </div>

        <button class="btn btn-primary mt-3 float-right" @click="updateMeal(mealID)">Save</button>
        </b-modal>

        <b-modal title="Meal" v-model="deleteMealModal" v-if="deleteMealModal">
            <center>
            <h5>Are you sure you want to delete this meal?</h5>
            <button class="btn btn-danger mt-3" @click="destroyMeal(mealID)">Delete</button>
            </center>
        </b-modal>

    </div>
</template>

<style>
@import '~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css';
</style>

<script>
    import Spinner from '../../components/Spinner';
    import moment from 'moment';
    import nutritionFacts from 'nutrition-label-jquery-plugin';

    export default {
        components: {
            Spinner
        },
        data(){
            return {
                search: '',
                searchResults: {},
                query: '',
                nutrients: [],
                ingredients: [],
                isLoading: true,
                createMealModal: false,
                viewMealModal: false,
                editMealModal: false,
                deleteMealModal: false,
                meal: {},
                ingredients: [],
                ingredientList: '',
                nutrition: {'calories': null, 'fatCalories': null, 'totalFat': null, 'satFat': null, 'transFat': null, 'cholesterol': null, 'sodium': null, 'totalCarb': null, 'fibers': null, 'sugars': null, 'proteins': null, 'vitaminD': null, 'potassium': null, 'calcium': null, 'iron': null, 'addedSugars': null},
                newMeal: ['featured_image', 'title', 'category', 'description', 'price'],
                mealID: null,
                columns: ['featured_image', 'title', 'category', 'description', 'price', 'current_orders', 'past_orders', 'created_at.date', 'actions'],
                tableData: [],
                options: {
                  headings: {
                    'featured_image': 'Image',
                    'title': 'Title',
                    'category': 'Category',
                    'description': 'Description',
                    'price': 'Price',
                    'current_orders': 'Current Orders',
                    'past_orders': 'Past Orders',
                    'created_at.date': 'Created',
                    'actions': 'Actions'
                  },
            }
        }
    },
        mounted()
        {
            this.getTableData();
        },
        methods: {
            getTableData(){
                let self = this;
                axios.get('../storeMeals')
                .then(function(response){    
                self.tableData = response.data;
                self.isLoading = false;
              })   
            },
            createMeal(){
                this.createMealModal = true
            },
            storeMeal(){
                axios.post('../meals', {
                    featured_image: this.newMeal.featured_image,
                    title: this.newMeal.title,
                    category: this.newMeal.category,
                    description: this.newMeal.description,
                    price: this.newMeal.price,
                });
                this.getTableData();
                this.createMealModal = false  
            },
            viewMeal($id){
                axios.get('/meals/' + $id).then(
                response => {
                this.meal = response.data;
                this.ingredients = response.data.ingredient;
                this.viewMealModal = true
                }
                );
                
            },
            editMeal($id){
                axios.get('/meals/' + $id).then(
                response => {
                this.meal = response.data;
                this.ingredients = response.data.ingredient;
                this.editMealModal = true;
                this.mealID = response.data.id;
                }
                );
            },
            updateMeal: function($id){
                axios.put('/meals/' + $id, {
                      meal: this.meal
                    }
                  );
                this.getTableData();
            },
            deleteMeal: function($id){
                this.mealID = $id;
                this.deleteMealModal = true
            },
            destroyMeal: function($id){
                axios.delete('/meals/' + $id);
                this.getTableData();
                this.deleteMealModal = false;
            },






            getNutrition: function(){
                axios.post('../nutrients', {
                    query: this.query 
                })
                .then((response) => {
                    this.ingredients = response.data.foods
                });
        },
            searchInstant: function(){
                axios.post('../searchInstant', {
                    search: this.search 
                })
                .then((response) =>{
                    this.searchResults = response.data.common
                });
            },
            getIngredientList: function(){
                let self = this;
                this.ingredients.forEach(function(ingredient){
                    self.ingredientList += ingredient.food_name + ', '
                })
            },
            getNutritionTotals: function(){
                let self = this;
                this.ingredients.forEach(function(ingredient) {
                  self.nutrition.calories += ingredient.calories                  
                  self.nutrition.fatCalories += ingredient.fatcalories
                  self.nutrition.totalFat += ingredient.totalfat
                  self.nutrition.satFat += ingredient.satfat
                  self.nutrition.transFat += ingredient.transfat
                  self.nutrition.cholesterol += ingredient.cholesterol
                  self.nutrition.sodium += ingredient.sodium
                  self.nutrition.totalCarb += ingredient.totalcarb
                  self.nutrition.fibers += ingredient.fibers
                  self.nutrition.sugars += ingredient.sugars
                  self.nutrition.proteins += ingredient.proteins
                  self.nutrition.vitaminD += ingredient.vitamind
                  self.nutrition.potassium += ingredient.potassium
                  self.nutrition.calcium += ingredient.calcium
                  self.nutrition.iron += ingredient.iron
                  self.nutrition.addedSugars += ingredient.addedsugars
                });
            },
            getNutritionFacts(){
                this.getNutritionTotals();
                this.getIngredientList();
                $('#test2').nutritionLabel({
                showServingUnitQuantity : false,
                itemName : this.meal.title,
                ingredientList : this.ingredientList,

                decimalPlacesForQuantityTextbox : 2,
                valueServingUnitQuantity : 1,

                allowFDARounding : true,
                decimalPlacesForNutrition : 2,

                showPolyFat : false,
                showMonoFat : false,

                valueCalories : this.nutrition.calories,
                valueFatCalories : this.nutrition.fatCalories,
                valueTotalFat : this.nutrition.totalFat,
                valueSatFat : this.nutrition.satFat,
                valueTransFat : this.nutrition.transFat,
                valueCholesterol : this.nutrition.cholesterol,
                valueSodium : this.nutrition.sodium,
                valueTotalCarb : this.nutrition.totalCarb,
                valueFibers : this.nutrition.fibers,
                valueSugars : this.nutrition.sugars,
                valueProteins : this.nutrition.proteins,
                valueVitaminD : this.nutrition.vitaminD,
                valuePotassium_2018 : this.nutrition.potassium,
                valueCalcium : this.nutrition.calcium,
                valueIron : this.nutrition.iron,
                valueAddedSugars : this.nutrition.addedSugars,
                showLegacyVersion : false
             });
            }
    }
}
</script>