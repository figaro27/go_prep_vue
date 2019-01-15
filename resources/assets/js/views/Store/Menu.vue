<template>

    <div class="row">
        <div class="col-sm-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-sm-3 menu-item" v-for="meal in meals">
                                    <img :src="meal.featured_image" class="menu-item-img">
                                    <div class="row">
                                        <div class="col-3">
                                            <img src="/images/customer/minus.jpg" @click="minusOne(meal)">
                                        </div>
                                        <div class="col-6">
                                            <b-form-input type="text" name="" id="" class="quantity" v-model="meal.quantity" readonly=""></b-form-input>
                                        </div>
                                        <div class="col-3">
                                            <img src="/images/customer/plus.jpg" @click="addOne(meal)" class="pull-right">
                                        </div>
                                    </div>
                                        <p> {{ meal.title }} </p>
                                        <p> {{ meal.description }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div v-for="meal in meals">
                                <div class="cart-item" v-if="meal.quantity > 0">
                                    <img :src="meal.featured_image" class="menu-item-img">
                                    <p> {{ meal.title }} </p>
                                    <p> {{ meal.quantity }} </p>
                                </div>
                            </div>
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
                axios.get("/api/me/meals").then(response =>{
                    this.meals = response.data
                })
            },
            minusOne(meal){
                meal.quantity -= 1;
                if (meal.quantity < 0){
                    meal.quantity += 1;
                }
            },
            addOne(meal){
                meal.quantity += 1;
            }
        }
    }
</script>
