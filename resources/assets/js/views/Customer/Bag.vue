<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Bag
                    </div>
                    <div class="card-body">
                        <li v-for="item in $store.getters.bag">
                        {{ item.title }}
                        </li>
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
            return {}
        },
        mounted()
        {
        },
        methods: {}
    }
</script>