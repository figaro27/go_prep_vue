  <template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <div class="m-3">admin@goprep.com</div>
    </template>\
    <template slot="dropdown">
      <b-dropdown-item @click="logout()">
        <i class="fa fa-lock"/> Logout
      </b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
import { mapActions } from 'vuex';
export default {
  name: "AdminDropdown",
  components: {
    AppHeaderDropdown
  },
  data: () => {
    return { itemsCount: 42 };
  },
  methods: {
    ...mapActions(['logout'])
  }
};
</script>
