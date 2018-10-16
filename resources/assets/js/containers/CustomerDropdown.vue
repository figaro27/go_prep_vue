<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <img
        src="img/avatars/6.jpg"
        class="img-avatar"
        alt="admin@bootstrapmaster.com" />
    </template>\
    <template slot="dropdown">
      <b-dropdown-item><i class="fa fa-bell-o" /><router-link to="/customer/account/settings"> Settings</router-link>
      </b-dropdown-item>
      <b-dropdown-item><i class="fa fa-bell-o" /><router-link to="/customer/account/orders"> Orders</router-link>
      </b-dropdown-item>
      <b-dropdown-item><i class="fa fa-bell-o" /><router-link to="/customer/account/billing"> Billing</router-link>
      </b-dropdown-item>
      <b-dropdown-item><i class="fa fa-bell-o" /><router-link to="/customer/account/subscriptions"> Subscriptions</router-link>
      </b-dropdown-item>
      <b-dropdown-item><i class="fa fa-bell-o" /><router-link to="/customer/account/contact"> Contact Us</router-link>
      </b-dropdown-item>
      <b-dropdown-item @click="logout()"><i class="fa fa-lock" /> Logout</b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from '@coreui/vue'
export default {
  name: 'CustomerDropdown',
  components: {
    AppHeaderDropdown
  },
  data: () => {
    return { itemsCount: 42 }
  },
  methods: {
            logout(){
                axios.post('/logout');
                window.location.href = '/login';
            }
          }
}
</script>
