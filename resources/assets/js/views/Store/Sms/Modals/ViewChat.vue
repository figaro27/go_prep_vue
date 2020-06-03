<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="chatArea">
        <div class="chat">
          <div v-for="text in chat" :class="text.css">
            <div :class="text.innerCSS">
              {{ text.text }}
            </div>
          </div>
        </div>
      </div>
      <b-form-textarea
        v-model="message"
        placeholder="New message.."
      ></b-form-textarea>
      <b-btn variant="primary" @click="sendMessage" class="mt-2 pull-right"
        >Send</b-btn
      >
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
      message: ""
    };
  },
  props: {
    chat: null,
    phone: null,
    row: null
  },
  created() {},
  mounted() {
    // Scroll to bottom
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    })
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    sendMessage() {
      axios
        .post("/api/me/SMSChats", { message: this.message, phone: this.phone })
        .then(resp => {
          this.$emit("refreshChatMessage", this.row);
          this.$toastr.s("Message sent.", "Success");
        });
    }
  }
};
</script>

<style>
.chatArea {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.chat {
  width: 100%;
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.messages {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
}

.message {
  border-radius: 20px;
  padding: 8px 15px;
  margin-top: 5px;
  margin-bottom: 5px;
  display: inline-block;
}

.yours {
  align-items: flex-start;
}

.yours .message {
  margin-right: 25%;
  background-color: #eee;
  position: relative;
}

.yours .message.last:before {
  content: "";
  position: absolute;
  z-index: 0;
  bottom: 0;
  left: -7px;
  height: 20px;
  width: 20px;
  background: #eee;
  border-bottom-right-radius: 15px;
}
.yours .message.last:after {
  content: "";
  position: absolute;
  z-index: 1;
  bottom: 0;
  left: -10px;
  width: 10px;
  height: 20px;
  background: white;
  border-bottom-right-radius: 10px;
}

.mine {
  align-items: flex-end;
}

.mine .message {
  color: white;
  margin-left: 25%;
  background: rgb(0, 120, 254);
  position: relative;
}

.mine .message.last:before {
  content: "";
  position: absolute;
  z-index: 0;
  bottom: 0;
  right: -8px;
  height: 20px;
  width: 20px;
  background: rgb(0, 120, 254);
  border-bottom-left-radius: 15px;
}

.mine .message.last:after {
  content: "";
  position: absolute;
  z-index: 1;
  bottom: 0;
  right: -10px;
  width: 10px;
  height: 20px;
  background: white;
  border-bottom-left-radius: 10px;
}
</style>
