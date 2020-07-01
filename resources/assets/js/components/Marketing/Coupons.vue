<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <v-client-table
        :columns="columns"
        :data="tableData"
        :options="{
          orderBy: {
            column: 'id',
            ascending: true
          },
          headings: {
            freeDelivery: 'Free Delivery',
            oneTime: 'One Time'
          },
          filterable: false
        }"
      >
        <div slot="beforeTable" class="mb-2">
          <b-form @submit.prevent="saveCoupon">
            <b-form-group id="coupon">
              <div class="row">
                <div class="col-md-1">
                  <b-form-input
                    id="coupon-code"
                    v-model="coupon.code"
                    required
                    placeholder="Enter Coupon Code"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-radio-group v-model="coupon.type">
                    <div class="row">
                      <div class="col-md-6 pt-2">
                        <b-form-radio name="coupon-type" value="flat"
                          >Flat</b-form-radio
                        >
                      </div>
                      <div class="col-md-6 pt-2">
                        <b-form-radio name="coupon-type" value="percent"
                          >Percent</b-form-radio
                        >
                      </div>
                    </div>
                  </b-form-radio-group>
                </div>
                <div class="col-md-1">
                  <b-form-input
                    id="coupon-code"
                    v-model="coupon.amount"
                    placeholder="Enter Amount"
                    required
                  ></b-form-input>
                </div>
                <div class="col-md-1">
                  <b-form-checkbox
                    v-model="coupon.freeDelivery"
                    value="1"
                    unchecked-value="0"
                    class="pt-2"
                  >
                    Free Delivery
                  </b-form-checkbox>
                </div>
                <div class="col-md-1">
                  <b-form-checkbox
                    v-model="coupon.oneTime"
                    value="1"
                    unchecked-value="0"
                    class="pt-2"
                  >
                    One Time
                  </b-form-checkbox>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    v-model="coupon.minimum"
                    class="pt-2"
                    placeholder="Minimum Amount (optional)"
                  >
                  </b-form-input>
                </div>
                <div class="col-md-1">
                  <b-button type="submit" variant="success">Add</b-button>
                </div>
              </div>
            </b-form-group>
          </b-form>
        </div>

        <div slot="freeDelivery" slot-scope="props">
          <p v-if="props.row.freeDelivery" class="text-success">✓</p>
          <p v-if="!props.row.freeDelivery" class="red">X</p>
        </div>

        <div slot="oneTime" slot-scope="props">
          <p v-if="props.row.oneTime" class="text-success">✓</p>
          <p v-if="!props.row.oneTime" class="red">X</p>
        </div>

        <div slot="actions" slot-scope="props" v-if="props.row.id !== -1">
          <b-btn
            variant="danger"
            size="sm"
            @click="e => deleteCoupon(props.row.id)"
            >Delete</b-btn
          >
        </div>
      </v-client-table>
    </div>
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
      coupon: { type: "flat", freeDelivery: 0, oneTime: 0 },
      columns: [
        "code",
        "type",
        "amount",
        "freeDelivery",
        "oneTime",
        "minimum",
        "actions"
      ]
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized"
    }),
    tableData() {
      if (this.storeCoupons.length > 0) return this.storeCoupons;
      else return [];
    }
  },
  methods: {
    ...mapActions(["refreshStoreCoupons"]),
    formatMoney: format.money,
    saveCoupon() {
      this.spliceCharacters();
      axios
        .post("/api/me/coupons", this.coupon)
        .then(response => {
          this.coupon = {
            type: "flat",
            freeDelivery: 0,
            oneTime: 0
          };
          this.$toastr.s("Coupon Added", "Success");
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error);
        })
        .finally(() => {
          this.refreshStoreCoupons();
        });
    },
    deleteCoupon(id) {
      axios
        .delete("/api/me/coupons/" + id)
        .then(response => {
          this.$toastr.s("Coupon Deleted", "Success");
        })
        .finally(() => {
          this.refreshStoreCoupons();
        });
    },
    spliceCharacters() {
      if (this.coupon.amount != null) {
        let couponAmount = this.coupon.amount;
        if (this.coupon.amount.toString().includes("$")) {
          let intToString = this.coupon.amount.toString();
          let newPrice = intToString.replace("$", "");
          this.coupon.amount = newPrice;
        }
      }

      if (this.coupon.amount != null) {
        let couponAmount = this.coupon.amount;
        if (this.coupon.amount.toString().includes("%")) {
          let intToString = this.coupon.amount.toString();
          let newPrice = intToString.replace("%", "");
          this.coupon.amount = newPrice;
        }
      }
    }
  }
};
</script>
