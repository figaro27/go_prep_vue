<template>
    <div class="store-customer-container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    
                    <!-- <ShowCustomer :condition="condition" :idk="id">
                        <template slot="h"></template>
                        <template slot="y">You</template>
                    </ShowCustomer> 
                 <EditCustomer></EditCustomer>-->

                    <div class="card-header">
                        Customers
                    </div>
                    <div class="card-body">
                        <v-client-table :columns="columns" :data="tableData" :options="options">
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="view(props.row.id)">View</button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
        
       
    </div>
</template>

<style>

</style>
<script>
    // import ShowCustomer from './Modals/ShowCustomer';
    // import EditCustomer from './Modals/EditCustomer';

    export default {
        components: {
            // ShowCustomer,
            // EditCustomer
        },
        data(){
            return {
                id: '',
                columns: ['Name', 'phone', 'address', 'city', 'state', 'Joined', 'TotalPayments', 'TotalPaid', 'LastOrder.date', 'actions' ],
                tableData: [],
                options: {
                  headings: {
                    'LastOrder.date': 'Last Order',
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
                                var numA = parseInt(a.TotalPaid);
                                var numB = parseInt(b.TotalPaid);
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
        let self = this
        axios.get('storeCustomers')
              .then(function(response){    
             self.tableData = response.data;
              })
        },
        mounted()
        {

        },
        methods: {
            view(id){
                axios.get('/user/' + id).then(
                    response => {
                        this.id = response.data.id;
                    }
                    );
            }

            }
        }

</script>