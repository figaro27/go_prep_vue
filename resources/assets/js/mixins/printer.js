import { mapGetters, mapActions } from "vuex";
import { PrintJob } from "../store/printer";

export default {
  computed: {
    ...mapGetters("printer", {
      printerIsActive: "isActive",
      printerStatus: "getStatus",
      printerDevices: "getDevices",
      printerDevice: "getDevice"
    })
  },
  methods: {
    ...mapActions("printer", {
      printerInit: "init",
      printerConnect: "connect",
      printerDisconnect: "disconnect",
      printerFindDevices: "findDevices",
      printerSetDevice: "setDevice",
      printerSetStatus: "setStatus",
      printerAddJob: "addJob"
    })
  },
  created() {
    this.printerInit();
  }
};
