<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" v-model="fields.message"></textarea>
                                <div v-if="errors && errors.message" class="text-danger">{{ errors.message[0] }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Send message</button>

                            <div v-if="success" class="alert alert-success mt-3">
                                Message sent!
                            </div>
                        </form>
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
            fields: {},
            errors: {},
            success: false,
            loaded: true,
            }
        },
        mounted()
        {
        },
        methods: {
            submit() {
              if (this.loaded) {
                this.loaded = false;
                this.success = false;
                this.errors = {};
                axios.post('/submitCustomer', this.fields).then(response => {
                  this.fields = {};
                  this.loaded = true;
                  this.success = true;
                }).catch(error => {
                  this.loaded = true;
                  if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                  }
                });
                }
            }
        }
    }

</script>