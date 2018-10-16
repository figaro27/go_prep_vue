<template>
        <div class="row">
            <div class="col-md-12">
                
                <div class="card">
                    <div class="card-header">
                        Dashboard
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
                columns: ['id', 'email', 'password'],
                tableData: [],
                options: {
                    headings: {
                        id: 'Welcome',
                        email: 'Back'
                    },
                    sortable: ['id', 'email'],
                    filterable: ['id', 'email']
                }           
                }
            },
        created(){
        let self = this
        axios.get('user')
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