<template>
        <div class="row">
            <div class="col-md-8 offset-2">
                <p>My Account</p>
                <div class="card">
                    <div class="card-body">
                        <p>Email</p>
                        <hr/>
                        <p>Password</p>
                    </div>
                </div>
                <p>Store</p>
                <div class="card">
                    <div class="card-body">

                            <b-form @submit.prevent="updateStoreDetails">
                                <b-form-input type="text" v-model="storeDetail.name" placeholder="Store Name" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.logo" placeholder="Logo" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.phone" placeholder="Phone" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.address" placeholder="Address" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.city" placeholder="City" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.state" placeholder="State" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.zip" placeholder="Zip Code" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="storeDetail.description" placeholder="About" required></b-form-input>
                                <hr>               
                          <b-button type="submit" variant="primary">Submit</b-button>
                        </b-form>
                        </div>
                    </div>

                <p>Orders</p>
                <div class="card">
                    <div class="card-body">
                        <b-form @submit.prevent="updateStoreSettings">
                            <p>Cut Off Period</p>
                            <hr>
                            <p>Delivery Day(s)</p>
                            <hr>
                            <p>Delivery Distance</p>
                            <hr>
                            <p>Minimum Meals Requirement</p>
                            <b-form-input type="text" v-model="storeSettings.minimum" placeholder="Minimum Number of Meals" required></b-form-input>
                            <hr>
                            <p>Delivery Fee</p>
                            <c-switch color="success" variant="pill" size="lg" v-model="storeSettings.applyDeliveryFee" />
                            <b-form-input v-if="storeSettings.applyDeliveryFee" type="text" v-model="storeSettings.deliveryFee" placeholder="Delivery Fee" required></b-form-input>
                            <hr>
                            <p>Allow Pickup</p>
                            <c-switch color="success" variant="pill" size="lg" v-model="storeSettings.allowPickup" />
                            <b-form-input v-if="storeSettings.allowPickup" type="text" v-model="storeSettings.pickupInstructions" placeholder="Pickup Instructions (Include address, phone number, and time)" required></b-form-input>
                            <hr>
                            <b-button type="submit" variant="primary">Submit</b-button>
                        </b-form>
                    </div>
                </div>
                <p>Menu</p>
                <div class="card">
                    <div class="card-body">
                        <b-form @submit.prevent="updateStoreSettings">
                            <p>Show Nutrition Facts</p>
                            <c-switch color="success" variant="pill" size="lg" v-model="storeSettings.showNutrition" />
                            <hr/>
                            <p>Ingredients</p>
                            <hr/>
                            <p>Categories</p>
                            <hr>
                            <b-button type="submit" variant="primary">Submit</b-button>
                        </b-form>
                    </div>
                </div>
                <p>Notifications</p>
                <div class="card">
                    <div class="card-body">
                        <p>New Orders</p>
                    </div>
                </div>
                <p>Inventory</p>
                <div class="card">
                    <div class="card-body">
                        <p>Automatic Ordering</p>
                    <hr/>
                        <p>Low Threshold</p>
                    </div>
                </div>
                <p>Payments</p>
                <div class="card">
                    <div class="card-body">
                        <p>Payment Info</p>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from '@coreui/vue'
    export default {
        components: {
            cSwitch
        },
        data(){
            return {
              
            }
        },
        computed: {
            ...mapGetters({
              store: "store",
              storeDetail: "storeDetail",
              storeSetting: "storeSetting",
              storeSettings: "storeSettings",
            }),
            // storeDetail(){
            //     return this.store.store_detail;
            // } 
        },
        mounted()
        {
        },
        methods: {
            updateStoreDetails(){
                this.spliceZip();
                axios.post("/updateStoreDetails", this.storeDetail).then(response => {
              });
            },
            updateStoreSettings(){
                axios.post("/updateStoreSettings", this.storeSettings).then(response => {
              });
            },
            spliceZip(){
                if (this.storeDetail.zip.toString().length > 5){
                    let intToString = this.storeDetail.zip.toString();
                    let newZip = parseInt(intToString.substring(0,5));
                    this.storeDetail.zip = newZip;
                }
            }
        }
    }
</script>