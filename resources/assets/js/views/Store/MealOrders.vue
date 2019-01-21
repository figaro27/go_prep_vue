<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <v-client-table
                          ref="mealsTable"
                          :columns="columns"
                          :data="tableData"
                          :options="options"
                        >

                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

    export default {
        components: {

        },
        data(){
            return {

            }
        },
        computed: {
            ...mapGetters({
              meals: "storeMeals",
              orders: "storeOrders",
              isLoading: "isLoading"
            }),
            storeMeals(){
                return this.meals;
            },
            storeOrders(){
                // return this.orders;
                // return this.orders.reduce(function(all, item){
                //     all[item.order_number] = item.amount;
                //     return all;

                // }, {}) 
                let meal_ids = this.orders.map(function(item){
                    return item.meal_ids;
                })
                let counts = {};
                meal_ids.forEach(arr => arr.forEach(d => counts[d] = (counts[d] || 0) + 1))

                return counts;

                // return meal_ids.reduce(function(all, item, index){
                //     all[item[index]] += all[item]
                //     return all;
                // }, {})
            },
        },
        mounted()
        {
        },
        methods: {

        }
    }

</script>