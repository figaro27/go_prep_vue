<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <v-client-table :columns="columns" :data="tableData" :options="options">
        <div slot="beforeTable" class="mb-2">
          <button
            class="btn btn-success btn-md mb-2 mb-sm-0"
            @click="showAddPromotionModal = true"
          >
            Add Promotion
          </button>
        </div>
        <div slot="active" slot-scope="props">
          <b-form-checkbox
            class="largeCheckboxPromotions"
            type="checkbox"
            v-model="props.row.active"
            :value="1"
            :unchecked-value="0"
            @change="val => updatePromotion(props.row.id, 'active', val)"
          ></b-form-checkbox>
        </div>
        <div slot="promotionType" slot-scope="props">
          <b-form-select
            v-model="props.row.promotionType"
            :options="promotionTypeOptions"
            @change="val => updatePromotion(props.row.id, 'promotionType', val)"
          ></b-form-select>
        </div>
        <div slot="promotionAmount" slot-scope="props">
          <b-form-input
            v-model="props.row.promotionAmount"
            @change="
              val => updatePromotion(props.row.id, 'promotionAmount', val)
            "
          ></b-form-input>
        </div>
        <div
          slot="freeDelivery"
          slot-scope="props"
          v-if="props.row.promotionType !== 'points'"
        >
          <b-form-checkbox
            class="largeCheckboxPromotions ml-3"
            type="checkbox"
            v-model="props.row.freeDelivery"
            :value="1"
            :unchecked-value="0"
            @change="val => updatePromotion(props.row.id, 'freeDelivery', val)"
          ></b-form-checkbox>
        </div>
        <div
          slot="conditionType"
          slot-scope="props"
          v-if="props.row.promotionType !== 'points'"
        >
          <b-form-select
            v-model="props.row.conditionType"
            :options="conditionTypeOptions"
            @change="val => updatePromotion(props.row.id, 'conditionType', val)"
          ></b-form-select>
        </div>
        <div
          slot="conditionAmount"
          slot-scope="props"
          v-if="props.row.promotionType !== 'points'"
        >
          <b-form-input
            v-model="props.row.conditionAmount"
            @change="
              val => updatePromotion(props.row.id, 'conditionAmount', val)
            "
          ></b-form-input>
        </div>
        <div slot="actions" slot-scope="props">
          <b-btn
            variant="danger"
            @click="
              (showDeletePromotionModal = true), (promotionId = props.row.id)
            "
            >Delete</b-btn
          >
        </div>
      </v-client-table>
    </div>
    <b-modal
      size="md"
      title="Delete Promotion"
      v-model="showDeletePromotionModal"
      v-if="showDeletePromotionModal"
      hide-header
      hide-footer
      no-fade
    >
      <h5 class="center-text p-2 mt-2">
        Are you sure you want to delete this promotion?
      </h5>
      <div class="d-flex pt-2" style="justify-content:center">
        <b-btn
          class="d-inline mr-2"
          variant="secondary"
          @click="(showDeletePromotionModal = false), (promotionId = null)"
          >Cancel</b-btn
        >
        <b-btn class="d-inline" variant="danger" @click="destroyPromotion"
          >Delete</b-btn
        >
      </div>
    </b-modal>
    <b-modal
      size="lg"
      title="Add Promotion"
      v-model="showAddPromotionModal"
      v-if="showAddPromotionModal"
      no-fade
      hide-footer
    >
      <div class="container-md mt-3 pl-5 pr-5">
        <b-form @submit.prevent="addPromotion">
          <h6 class="strong mt-2 mb-2">
            Promotion Type
            <img
              v-b-popover.hover="
                'Choose if you want the promotion amount given to the customer to be a flat amount or a percentage.'
              "
              title="Promotion Type"
              src="/images/store/popover.png"
              class="popover-size ml-1"
            />
          </h6>
          <b-form-select
            v-model="newPromotion.promotionType"
            :options="promotionTypeOptions"
            required
          ></b-form-select>
          <div v-if="newPromotion.promotionType === 'points'">
            <h6 class="strong mt-2 mb-2">
              Cash Back Percentage
              <img
                v-b-popover.hover="
                  'A points rewards system is essentially a cash back system for your customers. You are letting them earn points based on their purchases and then they can spend those points on future orders. Enter the percentage amount in the box which determines the amount of points they receive on each purchase. Points will be multiplied by 100. A 2% points system on a $100 order would earn the customer 200 points. 200 points would be equivalent to $2 dollars that they can spend on future orders.'
                "
                title="Cash Back Percentage"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-input
              v-model="newPromotion.promotionAmount"
              placeholder="2%"
              required
              type="number"
            ></b-form-input>

            <h6 class="strong mt-2 mb-2">
              Points Naming
              <img
                v-b-popover.hover="
                  'Add a custom title for your points system to be communicated to customers in various places. E.G. \'Earn 200 Fresh Points on this order.\' The naming of company\'s points systems are usually similar to or found in the company name. Or you can just simply let it be called \'Points\'.'
                "
                title="Cash Back Percentage"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-input
              v-model="newPromotion.pointsName"
              placeholder="Fresh Points"
              required
            ></b-form-input>
          </div>
          <div v-if="newPromotion.promotionType !== 'points'">
            <h6 class="strong mt-2 mb-2">
              Promotion Amount

              <img
                v-b-popover.hover="
                  'Enter the amount you want to give to the customer.'
                "
                title="Promotion Amount"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-input
              type="number"
              v-model="newPromotion.promotionAmount"
              required
            ></b-form-input>

            <h6 class="strong mt-2 mb-2">
              Add Free Delivery
              <img
                v-b-popover.hover="'Do you want to add on free delivery?'"
                title="Add Free Delivery"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-checkbox
              type="checkbox"
              v-model="newPromotion.freeDelivery"
              :value="1"
              :unchecked-value="0"
            ></b-form-checkbox>

            <h6 class="strong mt-2 mb-2">
              Condition Type
              <img
                v-b-popover.hover="
                  'Choose the condition you want to be met to allow the promotion. A certain subtotal, number of items being checked out, number of orders placed by the customer, or no condition.'
                "
                title="Condition Type"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-select
              v-model="newPromotion.conditionType"
              :options="conditionTypeOptions"
              required
            ></b-form-select>

            <h6 class="strong mt-2 mb-2">
              Condition Amount
              <img
                v-b-popover.hover="
                  'Enter the amount required for the condition to be met in order to provide the promotion.'
                "
                title="Condition Amount"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h6>
            <b-form-input
              type="number"
              v-model="newPromotion.conditionAmount"
              required
            ></b-form-input>
          </div>
          <div class="d-flex">
            <b-button
              variant="secondary"
              @click="showAddPromotionModal = false"
              class="mt-3 d-inline mr-2 mb-3"
              >Cancel</b-button
            >
            <b-button type="submit" variant="primary" class="mt-3 d-inline mb-3"
              >Add</b-button
            >
          </div>
        </b-form>
      </div>
    </b-modal>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      newPromotion: { freeDelivery: 0, pointsName: "Points" },
      promotionId: null,
      showAddPromotionModal: false,
      showDeletePromotionModal: false,
      columns: [
        "active",
        "promotionType",
        "promotionAmount",
        "freeDelivery",
        "conditionType",
        "conditionAmount",
        "actions"
      ],
      options: {
        filterable: false,
        headings: {
          active: "Active",
          promotionType: "Promotion Type",
          promotionAmount: "Promotion Amount",
          freeDelivery: "Free Delivery",
          conditionType: "Condition Type",
          conditionAmount: "Condition Amount",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `promotion promotion-${row.id}`;
          classes += row.active ? "" : " faded";
          return classes;
        }
      }
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized",
      promotions: "storePromotions"
    }),
    tableData() {
      return Object.values(this.promotions);
    },
    promotionTypeOptions() {
      return [
        { value: "flat", text: "Flat Reward" },
        { value: "percent", text: "Percent Reward" },
        { value: "points", text: "Points System" }
      ];
    },
    conditionTypeOptions() {
      return [
        { value: "subtotal", text: "Subtotal" },
        { value: "meals", text: "Meals" },
        { value: "orders", text: "Orders" },
        { value: "none", text: "None" }
      ];
    }
  },
  methods: {
    ...mapActions(["refreshStorePromotions"]),
    formatMoney: format.money,
    async updatePromotion(id, field, val) {
      axios
        .patch(`/api/me/promotions/${id}`, { [field]: val })
        .then(response => {
          this.$toastr.s("Promotion updated.");
        });
    },
    destroyPromotion() {
      axios
        .delete(`/api/me/promotions/${this.promotionId}`)
        .then(async response => {
          await this.refreshStorePromotions();
          this.showDeletePromotionModal = false;
          this.$toastr.s("Promotion deleted.");
        });
    },
    addPromotion() {
      axios
        .post(`/api/me/promotions`, { promotion: this.newPromotion })
        .then(async response => {
          await this.refreshStorePromotions();
          this.newPromotion = {};
          this.showAddPromotionModal = false;
          this.$toastr.s("Promotion added.");
        })
        .catch(response => {
          console.log(_.first(Object.values(response.response.data)));

          let error = _.first(Object.values(response.response.data));
          this.$toastr.w(error);
        });
    }
  }
};
</script>
