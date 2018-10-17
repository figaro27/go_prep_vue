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
                columns: ['user_detail.firstname', 'user_detail.lastname', 'user_detail.phone', 'user_detail.city', 'user_detail.state', 'created_at'],
                tableData: [],
                options: {
                  headings: {
                    'user_detail.firstname': 'First',
                    'user_detail.lastname': 'Last',
                    'user_detail.phone': 'Phone',
                    'user_detail.city': 'City',
                    'user_detail.state': 'State',
                    'created_at': 'Customer Since'
                  },
                  columnsDropdown: true 
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