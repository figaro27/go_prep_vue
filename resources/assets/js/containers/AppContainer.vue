<template>
  <div>
    <StoreContainer v-if="user.user_role_id === 2"></StoreContainer>
    <CustomerContainer v-else></CustomerContainer>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import CustomerContainer from './CustomerContainer';
import StoreContainer from './StoreContainer';

export default {
  name: "AppContainer",
  components: {
    CustomerContainer,
    StoreContainer,
  },
  data() {
    return {
      
    };
  },
  computed: {
    ...mapGetters(["loggedIn", "user"]),
    name() {
      return this.$route.name;
    },
  },
  async mounted() {
    // Get initial state
    await this.initState();
  },
  methods: {
    ...mapActions({
      initState: "init"
    })
  }
};
</script>