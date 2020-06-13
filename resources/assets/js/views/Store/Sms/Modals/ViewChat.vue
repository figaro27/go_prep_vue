<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div v-if="!conflict">
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
      <div v-else>
        <b-alert variant="warning" show>
          <p class="center-text">There is a conflict with this chat.</p>
          <p class="center-text">
            Please message the contact directly:
            <span class="strong">{{ phone }}</span>
          </p>
        </b-alert>
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
      message: ""
    };
  },
  props: {
    chat: null,
    phone: null,
    row: null,
    conflict: null
  },
  created() {},
  mounted() {
    // Scroll modal to bottom
    setTimeout(() => {
      const modal = document.querySelector(".modal-content");
      modal.scrollTo({
        top: modal.scrollHeight,
        left: 0,
        behavior: "smooth"
      });
    }, 100);

    // Fetch / refresh chat every 10 seconds
    this.$nextTick(function() {
      window.setInterval(() => {
        this.$emit("disableSpinner");
        this.$emit("showChat", this.row);
      }, 10000);
    });
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
        .post("/api/me/SMSChats", {
          message: this.message,
          chatId: this.row.id
        })
        .then(resp => {
          this.$emit("showChat", this.row);
          this.$toastr.s("Message sent.", "Success");
          this.message = "";
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
