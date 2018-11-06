<template>

    <div class="admin-customer-container">
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Archivo+Black" />
        <div style="width:50%;margin-top:20px;margin-bottom:20px">
        <input class="form-control input-lg" v-model="query" style="margin-bottom:10px" placeholder="Type ingredients here">
        <button class="btn btn-primary btn-sm" @click="getNutrition" >Get Nutrition</button>
        <p>{{ nutrients.food_name }}</p>
        <p>{{ nutrients.nf_calories }}</p>
        <p>{{ nutrients.nf_total_fat }}</p>
        <p>{{ nutrients.nf_protein }}</p>
        <hr/><hr/><hr/>


        <input class="form-control input-lg" v-model="search" style="margin-bottom:10px" placeholder="Search Ingredients">
        <p>{{ search }}</p>
        <button class="btn btn-primary btn-sm" @click="searchInstant">Search Ingredients</button>
        <p>{{ searchResults }}</p>

        <hr/><hr/><hr/>
        <button class="btn btn-primary btn-sm" @click="getNutritionFacts">Get Nutrition Facts</button>
        <div id="test2"></div>

    </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                <ViewCustomer :userId="viewUserId"></ViewCustomer>
                <EditCustomer :userId="editUserId"></EditCustomer>
                    <div class="card-header">
                        Customers
                    </div>
                    <div class="card-body">
                        <Spinner v-if="isLoading"/>
                        <v-client-table :columns="columns" :data="tableData" :options="options" >                           
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="viewUserId = props.row.id">View</button>
                                <button class="btn btn-warning btn-sm" @click="editUserId = props.row.id">Edit</button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
</template>

<style>
@import '~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css';
</style>

<script>

    import Spinner from '../../components/Spinner';
    import ViewCustomer from './Modals/ViewCustomer';
    import EditCustomer from './Modals/EditCustomer';
    import moment from 'moment';
    import nutritionFacts from 'nutrition-label-jquery-plugin';



    

    export default {
        components: {
            Spinner,
            ViewCustomer,
            EditCustomer
        },
        data(){
            return {
                search: '',
                searchResults: {},
                query: '',
                nutrients: {},
                isLoading: true,
                store: {},
                orders: [],
                viewUserId: '',
                editUserId: '',
                columns: ['Name', 'phone', 'address', 'city', 'state', 'Joined', 'TotalPayments', 'TotalPaid', 'LastOrder', 'actions' ],
                tableData: [],
                options: {
                  headings: {
                    'LastOrder': 'Last Order',
                    'TotalPayments': 'Total Orders',
                    'TotalPaid': 'Total Paid',
                    'Name': 'Name',
                    'phone': 'Phone',
                    'address': 'Address',
                    'city': 'City',
                    'state': 'State',
                    'Joined': 'Customer Since',
                    'actions': 'Actions'
                  },
                  columnsDropdown: true,
                  customSorting: {
                    TotalPaid: function(ascending) {
                            return function(a, b) {
                                var n1 = a.TotalPaid.toString();
                                var n1a = n1.replace("$", "");
                                var n1a1 = n1a.replace(",", "");

                                var n2 = b.TotalPaid.toString();
                                var n2a = n1.replace("$", "");
                                var n2a2 = n1a.replace(",", "");


                                var numA = parseInt(n1a1);
                                var numB = parseInt(n2a2);
                                if (ascending)
                                    return numA >= numB ? 1 : -1;
                                return numA <= numB ? 1 : -1;
                        }
                    },
                    Joined: function(ascending) {
                        return function(a, b){
                            var n1 = a.Joined.toString();
                            var n2 = b.Joined.toString();

                            var numA = parseInt(n1.substr(-4,4) + n1.substr(0,2) + n1.substr(3,2));
                            var numB = parseInt(n2.substr(-4,4) + n1.substr(0,2) + n1.substr(3,2));

                            if (ascending)
                                return numA >= numB ? 1 : -1;
                            return numA <= numB ? 1 : -1;

                        }
                    }
                  } 
                }
            }
        },
        created(){

        },
        mounted()
        {
            this.getTableData();


        },
        methods: {
            getTableData(){
                let self = this;
                axios.get('../user')
                .then(function(response){    
                self.tableData = response.data;
                self.isLoading = false;
              })
                
            },
            resetUserId(){
                this.viewUserId = 0;
                this.editUserId = 0;
            },
            getNutrition: function(){
                axios.post('../nutrients', {
                    query: this.query 
                })
                .then((response) => {
                    this.nutrients = response.data.foods[0]
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
            getNutritionFacts: function(){
                $('#test2').nutritionLabel({
                showServingUnitQuantity : false,
                itemName : this.nutrients.food_name,
                ingredientList : 'Bleu Cheese Dressing',

                decimalPlacesForQuantityTextbox : 2,
                valueServingUnitQuantity : 1,

                allowFDARounding : true,
                decimalPlacesForNutrition : 2,

                showPolyFat : false,
                showMonoFat : false,

                valueCalories : this.nutrients.nf_calories,
                valueFatCalories : 430,
                valueTotalFat : 48,
                valueSatFat : 6,
                valueTransFat : 0,
                valueCholesterol : 30,
                valueSodium : 780,
                valueTotalCarb : 3,
                valueFibers : 0,
                valueSugars : 3,
                valueProteins : this.nutrients.nf_protein,
                valueVitaminD : 12.22,
                valuePotassium_2018 : 4.22,
                valueCalcium : 7.22,
                valueIron : 11.22,
                valueAddedSugars : 17,
                showLegacyVersion : false
             });
            }
    }
}

</script>