<template>
        <div class="row">
            <div class="col-sm-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-10">
                                <b-row>
                                  <b-col v-for="meal in store.meals" :key="meal.id" cols="3">
                                        <img :src="meal.featured_image" class="menu-item-img">
                                        <img src="/storage/minus.jpg" @click="minusOne(meal)">
                                        <input type="text" name="" id="" class="quantity" v-model="meal.quantity" readonly="">
                                        <img src="/storage/plus.jpg" @click="addOne(meal)">
                                        <p> {{ meal.title }} </p>
                                        <p> {{ meal.price }} </p>
                                  </b-col>
                                </b-row>
                              </div>
                              <div class="col-sm-2">

                                <b-col v-for="meal in store.meals" :key="meal.id" cols="12">
                                  <div v-if="meal.quantity > 0">
                                    <p @click="clearAll">Clear All</p>
                                    <img :src="meal.featured_image" class="cart-item-img">
                                    <p> {{ meal.title }} </p>
                                    <p> {{ meal.quantity }} </p>
                                    <img src="/storage/minus.jpg" @click="minusOne(meal)">
                                    <img src="/storage/plus.jpg" @click="addOne(meal)">
                                    <img src="/storage/x.png" @click="clearMeal(meal)">
                                  </div>
                                </b-col>
                                <p v-if="total < minimum">Please choose {{ remainingMeals }} meals to continue.</p>
                                <hr>
                                <router-link to="/customer/bag">
                                  <img v-if="total > minimum" src="/storage/next.jpg">
                                </router-link>
                                
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

.cart-item-img{
  height:100px;
}

</style>

<script>
import { mapGetters } from "vuex";

    export default {
        components: {
        },
        data(){
            return {
                meals: [],
                total: 0,
                minimum: 5
            }
        },
        computed: {
          ...mapGetters({
            store: "viewedStore"
          }),
          remainingMeals(){
            return this.minimum - this.total;
          } 
        },
        mounted()
        {

        },
        methods: {
            minusOne(meal){
                meal.quantity -= 1;
                if (meal.quantity < 0){
                    meal.quantity += 1;
                }
                this.total -= 1;
                this.preventNegative();
            },
            addOne(meal){
                meal.quantity += 1;
                this.total +=1;
                this.preventNegative();
            },
            clearMeal(meal){
              this.total -= meal.quantity;
              this.preventNegative();
              meal.quantity = 0;
            },
            clearAll(){
              this.store.meals.forEach(function(meal){
                meal.quantity = 0;
              });
              this.total = 0;
              this.preventNegative();
          },
            preventNegative(){
              if (this.total < 0){
                this.total += 1;
              }
            }
        }
    }
</script>
