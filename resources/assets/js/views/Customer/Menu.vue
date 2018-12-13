<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Menu</div>
        <div class="card-body">

          <div v-if="store.meals">
            <b-row>
              <b-col v-for="meal in store.meals" :key="meal.id" cols="3">
                <MenuItem :item="meal">{{meal.title}}</MenuItem>
              </b-col>
            </b-row>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import MenuItem from "../../components/Menu/Item";

export default {
  components: {
    MenuItem,
  },
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
    }),
  },
  mounted() {},
  methods: {}
};
</script>