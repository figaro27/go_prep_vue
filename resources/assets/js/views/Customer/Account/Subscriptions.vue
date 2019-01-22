<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Meal Plans</div>
        <div class="card-body">
          <div v-for="subscription in subscriptions" :key="subscription.id">
            <div v-b-toggle="'collapse' + subscription.id">
              <b-list-group-item>
                <div class="row">
                  <div class="col-md-4">
                    <h4>Meal Plan ID</h4>
                    <p>{{ subscription.id }}</p>
                  </div>
                  <div class="col-md-4">
                    <h4>Placed On</h4>
                    <p>{{ subscription.created_at }}</p>
                  </div>
                  <div class="col-md-4">
                    <h2>{{ format.money(subscription.amount) }} per {{subscription.interval}}</h2>
                  </div>
                </div>

                <b-collapse :id="'collapse' + subscription.id" class="mt-2">
                  <b-table striped :items="getOrderTableData(subscription)">
                    <template slot="image" slot-scope="row">
                      <img :src="row.value" class="modalMeal">
                    </template>
                  </b-table>
                </b-collapse>
              </b-list-group-item>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
export default {
  components: {},
  data() {
    return {};
  },
  computed: {
    ...mapGetters(["subscriptions"])
  },
  mounted() {
    this.refreshSubscriptions();
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    getOrderTableData(subscription) {
      if(!subscription || !_.isArray(subscription.orders)) {
        return [];
      }

      return subscription.orders.map(order => {
        return {
          date: order.created_at,
          delivery_date: order.delivery_date,
          delivered: order.fulfilled ? 'Yes' : 'No',
          meals: order.meals.map(meal => {
            return meal.title + ' x ' + meal.pivot.quantity
          }).join(', ')
        };
      });
    }
  }
};
</script>