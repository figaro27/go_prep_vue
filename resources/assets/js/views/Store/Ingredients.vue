<template>
        <div class="row">
            <div class="col-md-12">
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
    export default {
        components: {

        },
        data(){
            return {
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