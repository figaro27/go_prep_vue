<template>
  <div class="main-customer-container box-shadow">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <h3 class="center-text">Contact Us</h3>
        <form @submit.prevent="submit">
          <div class="form-group">
            <div v-if="errors && errors.name" class="text-danger">
              {{ errors.name[0] }}
            </div>
          </div>

          <div>
            <b-form-select
              id="subject"
              v-model="fields.subject"
              :options="options"
              class="contact-subject mb-3 center"
              required
            />
          </div>

          <div v-if="fields.subject === 'Technical Issue'">
            <p>
              Please be as specific as possible telling us the error message you
              see or exactly what's happening so we can better help you.
            </p>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea
              class="form-control"
              id="message"
              name="message"
              rows="5"
              v-model="fields.message"
            ></textarea>
            <div v-if="errors && errors.message" class="text-danger">
              {{ errors.message[0] }}
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Send Message</button>

          <div v-if="success" class="alert alert-success mt-3">
            Message sent! We'll get back to you shortly.
          </div>
        </form>
        <div class="space-divider-40"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  data() {
    return {
      options: [
        { value: null, text: "Select a subject" },
        { value: "Question About My Order", text: "Question About My Order" },
        { value: "Technical Issue", text: "Technical Issue" },
        { value: "Suggestion", text: "Suggestion" },
        { value: "Other", text: "Other" }
      ],
      fields: {
        subject: null
      },
      errors: {},
      success: false,
      loaded: true
    };
  },
  mounted() {},
  methods: {
    submit() {
      if (this.loaded) {
        this.loaded = false;
        this.success = false;
        this.errors = {};
        axios
          .post("/api/contact", this.fields)
          .then(response => {
            this.fields = {};
            this.loaded = true;
            this.success = true;
          })
          .catch(error => {
            this.loaded = true;
            if (error.response.status === 422) {
              this.errors = error.response.data.errors || {};
            }
          });
      }
    }
  }
};
</script>
