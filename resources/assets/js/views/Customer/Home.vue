<template>
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <div class="card">
        <div class="card-body">
          <h5>Welcome to GoPrep</h5>
          <p>We envision GoPrep to be the central hub that allows customers to find local meal prep companies and allow them the convenience of ordering a number of meals taking care of their eating needs for the week. Eventually as we grow, this page will show you a list of all meal prep companies in your area. You can then view each company's menu and make your order.</p>
          <p>Help us spread the word by getting more meal prep companies on-board.</p>

            <p>Contact us below.</p>
          


          <form @submit.prevent="submit">
            <div class="form-group">
              <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
              <div>
                  <b-form-select id="subject" v-model="fields.subject" :options="options" class="contact-subject mb-3" required/>
              </div>
              <div class="form-group">
                <label for="message">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" v-model="fields.message"></textarea>
                <div v-if="errors && errors.message" class="text-danger">{{ errors.message[0] }}</div>
              </div>
            </div>
          <button type="submit" class="btn btn-primary">Send Message</button>

          <div v-if="success" class="alert alert-success mt-3">
                  Message sent! We'll get back to you shortly.
          </div>
          </form>



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
  data() {
    return {
      options: [
                { value: null, text: 'Select a subject' },
                { value: 'General Inquiry', text: 'General Inquiry' },
                { value: 'Issue With My Order', text: 'Issue With My Order' },
                { value: 'Suggestion', text: 'Suggestion' },
                { value: 'Other', text: 'Other' }
            ],
            fields: {
                subject: null
            },
            errors: {},
            success: false,
            loaded: true,
    };
  },
  created() {},
  mounted() {
  },
  methods: {
    ...mapMutations(['setViewedStore']),
    submit() {
              this.loaded = false;
                this.success = false;
                this.errors = {};
                axios.post('/api/contact', this.fields).then(response => {
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
};
</script>