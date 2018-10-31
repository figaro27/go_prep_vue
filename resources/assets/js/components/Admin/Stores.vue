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
        <b-modal size="lg" title="Customer" v-model="viewStoreModal" v-if="viewStoreModal">
            <b-list-group>
                <b-list-group-item>Logo: {{ store.store_detail.logo }}</b-list-group-item>
                <b-list-group-item>Name: {{ store.store_detail.name }}</b-list-group-item>
                <b-list-group-item>Phone: {{ store.store_detail.phone }}</b-list-group-item>
                <b-list-group-item>Address: {{ store.store_detail.address }}</b-list-group-item>
                <b-list-group-item>City: {{ store.store_detail.city }}</b-list-group-item>
                <b-list-group-item>State: {{ store.store_detail.state }}</b-list-group-item>
            </b-list-group>
            <hr/>
            <b-list-group v-for="order in orders" :key="order.id">
                <b-list-group-item>Order ID: {{ order.id }}</b-list-group-item>
                <b-list-group-item>Total: {{ order.amount }}</b-list-group-item>
                <b-list-group-item>Date: {{ order.created_at }}</b-list-group-item>
            </b-list-group>
        </b-modal>

        <b-modal title="Customer" v-model="editStoreModal" v-if="editStoreModal">
            <b-list-group>
              <b-list-group-item><b-form-input v-model="store.store_detail.logo"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="store.store_detail.name"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="store.store_detail.phone"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="store.store_detail.address"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="store.store_detail.city"></b-form-input></b-list-group-item>
              <b-list-group-item><b-form-input v-model="store.store_detail.state"></b-form-input></b-list-group-item>
            </b-list-group>
        <button class="btn btn-primary mt-3 float-right" @click="updateStore">Save</button>
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
                    axios.get('../stores')
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