<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Meals
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success btn-sm" @click="showCreateMealModal">Add New</button>
                        <Spinner v-if="isLoading"/>
                        <v-client-table :columns="columns" :data="tableData" :options="options" >
                        <div slot="featured_image" slot-scope="props">
                            <img :src="tableData[props.index].featured_image">
                        </div>                       
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="viewMeal(props.row.id)">View</button>
                                <button class="btn btn-warning btn-sm" @click="editMeal(props.row.id)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="showDeleteMealModal(props.row.id)">Delete</button>
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

             <div class="form-group">
                <label>Choose Store</label>
                <select class="form-control" v-model="selectedStore">
                  <option v-for="store in stores" :value="store.id">{{ store.name }}</option>
                </select>
                </div>

            <button class="btn btn-primary mt-3 float-right" @click="storeMeal">Add Meal</button>
        </b-modal>

        <b-modal size="lg" title="Meal" v-model="viewMealModal" v-if="viewMealModal">
            <b-list-group>
                <b-list-group-item>Logo: {{ meal.featured_image }}</b-list-group-item>
                <b-list-group-item>Title: {{ meal.title }}</b-list-group-item>
                <b-list-group-item>Category: {{ meal.category }}</b-list-group-item>
                <b-list-group-item>Description: {{ meal.description }}</b-list-group-item>
                <b-list-group-item>Price: {{ meal.price }}</b-list-group-item>
                <b-list-group-item>Created: {{ meal.created_at }}</b-list-group-item>
            </b-list-group>
            
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
        <button class="btn btn-primary mt-3 float-right" @click="updateMeal(mealID)">Save</button>
        </b-modal>

        <b-modal title="Meal" v-model="deleteMealModal" v-if="deleteMealModal">
            <center>
            <h5>Are you sure you want to delete this meal?</h5>
            <button class="btn btn-danger mt-3" @click="deleteMeal(mealID)">Delete</button>
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
                stores: [],
                selectedStore: null,
                isLoading: true,
                createMealModal: false,
                viewMealModal: false,
                editMealModal: false,
                deleteMealModal: false,
                meal: {},
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
                axios.get('../meals')
                .then(function(response){    
                self.tableData = response.data;
                self.isLoading = false;
              })   
            },
            showCreateMealModal(){
                let self = this
                axios.get('../stores')
                .then(function(response){
                    self.stores = response.data;
                })
                this.createMealModal = true
            },
            storeMeal(){
                axios.post('../storeMealAdmin', {
                    store_id: this.selectedStore,
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
                this.viewMealModal = true
                }
                );
            },
            editMeal($id){
                axios.get('/meals/' + $id).then(
                response => {
                this.meal = response.data;
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
            showDeleteMealModal: function($id){
                this.mealID = $id;
                this.deleteMealModal = true
            },
            deleteMeal: function($id){
                axios.delete('/meals/' + $id);
                this.getTableData();
                this.deleteMealModal = false;
            }
    }
}
</script>