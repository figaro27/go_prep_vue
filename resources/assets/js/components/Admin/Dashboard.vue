<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Dashboard
                    </div>
                    <div class="card-body">
                        <v-client-table :columns="columns" :data="data" :options="options">
                            <a slot="email" slot-scope="props" :href="'mailto:${props.row.email}'">
                                {{ props.row.email }}
                            </a>
                            <a slot="edit" slot-scope="props" class="fa fa-edit" :href="edit(props.row.id)">
                            </a>
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
                columns: [
                'id',
                'name',
                'email',
                'edit'
                ],
                data: [
                    {'id': 1, 'name': 'mike', 'email': 'michaelcsoldano@yahoo.com'}
                ],
                options: {
                    headings: {
                        id: 'id',
                        name: 'name',
                        email: 'email'
                    },
                    sortable: [
                    'id', 'name'
                    ],
                    texts: {
                        filterPlaceholder: 'filter'
                    }
                }

            }
        },
        mounted()
        {
        },
        methods: {
            getData: function(){
                const arr = []
                return arr;
            },
            edit($id){
                return 'http://google.com';
            }

        }
    }
</script>