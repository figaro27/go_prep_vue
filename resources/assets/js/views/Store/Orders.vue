<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Orders
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
                tableData: [{Order: "test"}],
                columns: ['Order']
            }
        },
        mounted()
        {
        },
        methods: {}
    }
</script>