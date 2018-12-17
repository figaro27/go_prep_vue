<template>
        <div class="row">
            <div class="col-sm-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-sm-3 menu-item" v-for="meal in meals">
                                        <img :src="meal.featured_image" class="menu-item-img">
                                        <img src="/images/minus.jpg" @click="minusOne(meal)">
                                        <input type="text" name="" id="" class="quantity" v-model="meal.quantity" readonly="">
                                        <img src="/images/plus.jpg" @click="addOne(meal)">
                                        <p> {{ meal.title }} </p>
                                        <p> {{ meal.description }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3" v-for="meal in meals">
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
</template>

<style>
.menu-item{
    margin-bottom:10px;
}

.menu-item-img{
    width:100%;
}

.cart-item{
    width:100%;
}

</style>

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
