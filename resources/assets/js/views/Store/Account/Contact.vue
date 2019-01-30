<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" id="name" v-model="fields.name" />
                                <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
                            </div>
                        
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" v-model="fields.message"></textarea>
                                <div v-if="errors && errors.message" class="text-danger">{{ errors.message[0] }}</div>
                            </div>

                            <button type="submit" class="btn btn-primary">Send Message</button>

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
                axios.post('/submitStore', this.fields).then(response => {
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