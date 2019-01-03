<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <b-form @submit.prevent="updateCustomer">
                                <b-form-input type="text" v-model="userDetail.firstname" placeholder="First Name"></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.lastname" placeholder="Last Name" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.phone" placeholder="Phone" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.address" placeholder="Address" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.city" placeholder="City" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.state" placeholder="State" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.zip" placeholder="Zip Code" required></b-form-input> <hr>
                                <b-form-input type="text" v-model="userDetail.delivery" placeholder="Delivery" required></b-form-input>               
                          <b-button type="submit" variant="primary">Submit</b-button>
                        </b-form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        Payment Methods
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
                userDetail: {
                    firstname: '',
                    lastname: '',
                    phone: '',
                    address: '',
                    city: '',
                    state: '',
                    zip: null,
                    delivery: ''
                },
            }
        },
        mounted()
        {
            this.getCustomer();
        },
        methods: {
            getCustomer(){
                axios.get('/getCustomer').then(response =>{
                    this.userDetail = response.data;
                });
            },
            updateCustomer(){
                this.spliceZip();
                axios.post("/updateCustomer", this.userDetail).then(response => {
                    console.log(response);
              });
            },
            spliceZip(){
                if (this.userDetail.zip.toString().length > 5){
                    let reducedZip = this.userDetail.zip.toString();
                    this.userDetail.zip = parseInt(reducedZip.substring(0,5));
                }
            }
        }
    }
</script>