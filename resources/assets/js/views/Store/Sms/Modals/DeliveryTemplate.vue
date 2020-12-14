<template>
  <div class="row mt-3">
    <div class="col-md-12 pb-3">
      <Spinner v-if="isLoading" />
      <p class="pb-2">
        <span class="strong">Preview:</span>
        {{ smsSettings.deliveryTemplatePreview }}
      </p>
      <div class="d-flex">
        <div style="flex-basis:85%">
          <b-form-textarea
            v-model="smsSettings.autoSendDeliveryTemplate"
            rows="3"
          ></b-form-textarea>
          <b-btn
            @click="update"
            variant="primary"
            class="mt-2 pull-right d-inline"
            >Update</b-btn
          >
        </div>
        <div class="d-inline pl-2">
          <i class="fas fa-tag d-inline pr-1 pt-1" style="color:#737373"></i>
          <p class="d-inline" @click="showTagDropdown = !showTagDropdown">
            <u>Insert tag</u>
          </p>

          <div v-if="showTagDropdown" class="tagDropdown">
            <li v-for="tag in tags" :key="tag" @click="addTag(tag)">
              <p>{{ tag }}</p>
              <hr />
            </li>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../../../mixins/deliveryDates";
import format from "../../../../lib/format";
import store from "../../../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      showTagDropdown: false
    };
  },
  props: {},
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      smsSettings: "storeSMSSettings"
    }),
    tags() {
      return ["store name", "URL", "pickup/delivery"];
    }
  },
  methods: {
    ...mapActions({
      refreshSMSSettings: "refreshStoreSMSSettings"
    }),
    update() {
      if (this.smsSettings.autoSendDeliveryTemplate === "") {
        this.$toastr.w(
          "Template cannot be empty. Please add some text or toggle it off on the main page to not send anything."
        );
        return;
      }
      this.$emit("closeModal");
      this.$emit("update");
    },
    formatMoney: format.money,
    addTag(tag) {
      this.smsSettings.autoSendDeliveryTemplate += "{" + tag + "}";
      this.showTagDropdown = false;
    }
  }
};
</script>
