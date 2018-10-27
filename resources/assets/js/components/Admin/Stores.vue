<template>

    <div class="admin-customer-container">
                
        <div class="row">
            <div class="col-md-12">
                <Spinner v-if="isLoading"/>
                <div class="card" style="height: 100;">
                    <div class="card-header" >
                        Stores
                    </div>
                    <div class="card-body" >
                        <v-client-table :columns="columns" :data="tableData" :options="options" v-show="!isLoading">
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="viewStore(props.row.id)">View</button>
                                <button class="btn btn-warning btn-sm" @click="editStore(props.row.id)">Edit</button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
        <b-modal title="Customer" v-model="viewStoreModal" v-if="viewStoreModal">
            <ul>
                <li class="my-4">Logo: {{ store.store_detail.logo }}</li>
                <li class="my-4">Name: {{ store.store_detail.name }}</li>
                <li class="my-4">Phone: {{ store.store_detail.phone }}</li>
                <li class="my-4">Address: {{ store.store_detail.address }}</li>
                <li class="my-4">City: {{ store.store_detail.city }}</li>
                <li class="my-4">State: {{ store.store_detail.state }}</li>
                <li class="my-4" v-for="order in orders">
                    <ul>
                        <li>Order ID: {{ order.id }}</li>
                        <li>Total: {{ order.amount }}</li>
                        <li>Date: {{ order.created_at }}</li>
                    </ul>
                </li>
            </ul>
        </b-modal>

        <b-modal title="Customer" v-model="editStoreModal" v-if="editStoreModal">
            <ul>
              <li class="my-4">Logo: <input v-model="store.store_detail.logo"></input></li>
              <li class="my-4">Name: <input v-model="store.store_detail.name"></input></li>
              <li class="my-4">Phone: <input v-model="store.store_detail.phone"></input></li>
              <li class="my-4">Address: <input v-model="store.store_detail.address"></input></li>
              <li class="my-4">City: <input v-model="store.store_detail.city"></input></li>
              <li class="my-4">State: <input v-model="store.store_detail.state"></input></li>
            </ul>
        <button @click="updateStore">Save</button>
        </b-modal>
    </div>
</template>

<style>

</style>
<script>
    import Spinner from './Spinner';

    export default {
        components: {
            Spinner
        },
        data(){
            return {
                isLoading: true,
                viewStoreModal: false,
                editStoreModal: false,
                userId: '',
                store: {},
                orders: [],
                columns: ['id', 'logo', 'name', 'phone', 'address', 'city', 'state', 'TotalOrders', 'TotalCustomers', 'TotalPaid', 'Joined', 'actions'],
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
                    'Joined': 'Store Since',
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

        },
        mounted()
        {
            this.getTableData();
        },
        methods: {
                getTableData(){
                    let self = this
                    axios.get('store')
                    .then(function(response){    
                    self.tableData = response.data;
                    self.isLoading = false;
                })
            },
          
                viewStore($id){
                    axios.get('/store/' + $id).then(
                    response => {
                    this.store = response.data;
                    this.orders = response.data.order;
                    this.viewStoreModal = true
                    }
                    );
                },
                editStore($id){
                    axios.get('/store/' + $id).then(
                    response => {
                    this.store = response.data;
                    this.orders = response.data.order;
                    this.editStoreModal = true;
                    this.userId = $id;
                    }
                    );
                },
                updateStore: function(){
                axios.put('/store/' + this.userId, {
                      store: this.store
                    }
                  );
                this.getTableData();
              }
            }
    }

</script>