<template>
        <div class="row">
            <div class="col-md-12">
                <Spinner v-if="isLoading"/>
                <div class="card">
                    <div class="card-header">
                        Ingredients
                    </div>
                    <div class="card-body">
                        <v-client-table :columns="columns" :data="tableData" :options="options">
                            
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
    import Spinner from '../../components/Spinner';

    export default {
        components: {
            Spinner
        },
        data(){
            return {
                isLoading: true,
                columns: ['ingredient', 'total', 'unit'],
                tableData: [],
                options: {
                  headings: {
                    'ingredient': 'Ingredient',
                    'total': 'Quantity',
                    'unit': 'Unit'
                  }
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
                axios.get('../ingredients')
                .then(function(response){    
                self.tableData = response.data;
                self.isLoading = false;
              })   
            }

        }
    }
</script>