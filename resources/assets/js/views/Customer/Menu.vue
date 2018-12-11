<template>
        <div class="row">
            <div class="col-sm-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-9">

                            </div>
                            <div class="col-sm-3">

                            </div>
                        </div>
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
                meals: []
            }
        },
        mounted()
        {
            this.getMeals();
        },
        methods: {
            getMeals(){
                let self = this;
                axios.get("meals").then(response =>{
                    this.meals = response.data
                })
            }
        }
    }
</script>
