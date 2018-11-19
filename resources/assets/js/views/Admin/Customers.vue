<template>
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

<script>

    import Spinner from '../../components/Spinner';
    import ViewCustomer from './Modals/ViewCustomer';
    import EditCustomer from './Modals/EditCustomer';
    import moment from 'moment';
    

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
            }
            
    }
}

</script>