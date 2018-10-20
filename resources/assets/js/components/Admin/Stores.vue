<template>
    <div class="admin-customer-container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Stores
                    </div>
                    <div class="card-body">
                        <v-client-table :columns="columns" :data="tableData" :options="options">
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

    export default {
        components: {
        },
        data(){
            return {
                columns: ['id', 'logo', 'name', 'phone', 'address', 'city', 'state', 'TotalOrders', 'TotalCustomers', 'TotalPaid', 'Joined.date'],
                tableData: [],
                options: {
                  headings: {
                    'id': '#',
                    'logo': 'Logo',
                    'name': 'Name',
                    'phone': 'Phone',
                    'address': 'Address',
                    'city': 'City',
                    'state': 'State',
                    'TotalCustomers': 'Total Customers',
                    'TotalOrders': 'Total Orders',
                    'TotalPaid': 'Total Paid',
                    'Joined.date': 'Store Since'
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
        axios.get('store')
              .then(function(response){    
             self.tableData = response.data;
              })
        },
        mounted()
        {

        },
        methods: {

            }
        }

</script>