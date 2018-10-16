<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Survey
                    </div>
                    <div class="card-body">
                        <b-form @submit.prevent="submit">
                        <b-form-group id="survey">
                        <h2>Do you have any food allergies?</h2>
                        <!--Potentially change to search bar with autopopulate-->
                            <b-form-checkbox-group id="allergy" v-model="allergy" name="allergy">
                              <b-form-checkbox value="poultry">Poultry</b-form-checkbox>
                              <b-form-checkbox value="nuts">Nuts</b-form-checkbox>
                              <b-form-checkbox value="dairy">Dairy</b-form-checkbox>
                              <b-form-checkbox value="shellfish">Shellfish</b-form-checkbox>
                              <b-form-checkbox value="soy">Soy</b-form-checkbox>
                            </b-form-checkbox-group>


                        <h2>What is your goal?</h2>
                             <b-form-radio-group id="goal" v-model="goal" name="goal">
                                <b-form-radio value="0">No Goal</b-form-radio>
                                <b-form-radio value="1">Lose Weight</b-form-radio>
                                <b-form-radio value="2">Gain Weight</b-form-radio>
                                <b-form-radio :value="3">Maintain</b-form-radio>
                              </b-form-radio-group>


                        <h2>How many breakfasts / lunch / dinners?</h2>
                        <div class="row">
                            <div class="col-sm">
                                <b-form-select id="breakfast"
                                                  :options="numMeals"
                                                  v-model="breakfast"
                                                  required
                                                  >
                                </b-form-select>
                            </div>
                            <div class="col-sm">
                                <b-form-select id="lunch"
                                                  :options="numMeals"
                                                  v-model="lunch"
                                                  required
                                                  >
                                </b-form-select>
                            </div>
                            <div class="col-sm">
                                <b-form-select id="dinner"
                                                  :options="numMeals"
                                                  v-model="dinner"
                                                  required
                                                  >
                                </b-form-select>
                            </div>
                        </div>
                                
                        </b-form-group>
                        <b-button type="submit" variant="primary" @click.prevent="submit">Submit</b-button>
                    </b-form>
                    </div>
                </div>
            </div>
    </div>
</template>

<script>
    
    export default {
        data(){
            return {
                allergy: [],
                goal: '',
                breakfast: '',
                lunch: '',
                dinner: '',
                numMeals: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
            };
        },
        mounted()
        {
        },
        methods: {
            submit(){
                var i;
                for (i = 0; i < this.allergy.length; i++) { 
                axios.post('./api/userMeta', {
                    user_id: 1,
                    meta_key: 'allergy',
                    meta_value: this.allergy[i]
                });     
            }
                axios.post('./api/userMeta', {
                    user_id: 1,
                    meta_key: 'goal',
                    meta_value: this.goal
                });
                axios.post('./api/userMeta', {
                    user_id: 1,
                    meta_key: 'breakfast',
                    meta_value: this.breakfast
                });
                axios.post('./api/userMeta', {
                    user_id: 1,
                    meta_key: 'lunch',
                    meta_value: this.lunch
                });
                axios.post('./api/userMeta', {
                    user_id: 1,
                    meta_key: 'dinner',
                    meta_value: this.dinner
                });
            }
        }
    }
</script>