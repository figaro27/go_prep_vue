<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce feugiat suscipit leo, nec bibendum nunc auctor pellentesque. Morbi sit amet tellus vitae ligula eleifend fringilla vitae vel sapien. Fusce dictum imperdiet sem at vestibulum. Cras lacinia augue vel arcu condimentum tempus. Morbi egestas bibendum imperdiet. Nunc hendrerit tellus ut hendrerit ultricies. Quisque sagittis a urna facilisis tempor. Nunc a erat ex. Integer volutpat ipsum placerat nisi sodales vehicula. In interdum fringilla turpis, a vulputate eros porta ut. Mauris sollicitudin fermentum magna, et finibus tortor iaculis id. Vivamus tincidunt magna quis est elementum, non sollicitudin massa suscipit.</p>

          <p>Fusce at fermentum est. Aenean ut erat magna. Morbi id eros ornare diam tincidunt fermentum ut ac nibh. Vestibulum augue diam, interdum sed nisl sit amet, placerat tempus massa. Donec porttitor purus et ante condimentum, vitae ultricies justo hendrerit. Nullam auctor egestas commodo. Phasellus eleifend vulputate neque nec viverra. Sed ultricies fringilla tellus id ultricies.</p>

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
</style>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
export default {
  components: {
  },
  data() {
    return {
      fields: {},
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
};
</script>