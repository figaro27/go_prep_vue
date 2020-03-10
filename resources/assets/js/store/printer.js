import qz from "qz-tray";

/// Authentication setup ///
qz.security.setCertificatePromise(function(resolve, reject) {
  //Preferred method - from server
  //        $.ajax({ url: "assets/signing/digital-certificate.txt", cache: false, dataType: "text" }).then(resolve, reject);

  //Alternate method 1 - anonymous
  //        resolve();

  //Alternate method 2 - direct
  resolve(
    "-----BEGIN CERTIFICATE-----\n" +
      "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\n" +
      "-----END CERTIFICATE-----\n" +
      "--START INTERMEDIATE CERT--\n" +
      "-----BEGIN CERTIFICATE-----\n" +
      "MIIFEjCCA/qgAwIBAgICEAAwDQYJKoZIhvcNAQELBQAwgawxCzAJBgNVBAYTAlVT\n" +
      "MQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYDVQQKDBJRWiBJ\n" +
      "bmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMsIExMQzEZMBcG\n" +
      "A1UEAwwQcXppbmR1c3RyaWVzLmNvbTEnMCUGCSqGSIb3DQEJARYYc3VwcG9ydEBx\n" +
      "emluZHVzdHJpZXMuY29tMB4XDTE1MDMwMjAwNTAxOFoXDTM1MDMwMjAwNTAxOFow\n" +
      "gZgxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOWTEbMBkGA1UECgwSUVogSW5kdXN0\n" +
      "cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMxGTAXBgNVBAMM\n" +
      "EHF6aW5kdXN0cmllcy5jb20xJzAlBgkqhkiG9w0BCQEWGHN1cHBvcnRAcXppbmR1\n" +
      "c3RyaWVzLmNvbTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBANTDgNLU\n" +
      "iohl/rQoZ2bTMHVEk1mA020LYhgfWjO0+GsLlbg5SvWVFWkv4ZgffuVRXLHrwz1H\n" +
      "YpMyo+Zh8ksJF9ssJWCwQGO5ciM6dmoryyB0VZHGY1blewdMuxieXP7Kr6XD3GRM\n" +
      "GAhEwTxjUzI3ksuRunX4IcnRXKYkg5pjs4nLEhXtIZWDLiXPUsyUAEq1U1qdL1AH\n" +
      "EtdK/L3zLATnhPB6ZiM+HzNG4aAPynSA38fpeeZ4R0tINMpFThwNgGUsxYKsP9kh\n" +
      "0gxGl8YHL6ZzC7BC8FXIB/0Wteng0+XLAVto56Pyxt7BdxtNVuVNNXgkCi9tMqVX\n" +
      "xOk3oIvODDt0UoQUZ/umUuoMuOLekYUpZVk4utCqXXlB4mVfS5/zWB6nVxFX8Io1\n" +
      "9FOiDLTwZVtBmzmeikzb6o1QLp9F2TAvlf8+DIGDOo0DpPQUtOUyLPCh5hBaDGFE\n" +
      "ZhE56qPCBiQIc4T2klWX/80C5NZnd/tJNxjyUyk7bjdDzhzT10CGRAsqxAnsjvMD\n" +
      "2KcMf3oXN4PNgyfpbfq2ipxJ1u777Gpbzyf0xoKwH9FYigmqfRH2N2pEdiYawKrX\n" +
      "6pyXzGM4cvQ5X1Yxf2x/+xdTLdVaLnZgwrdqwFYmDejGAldXlYDl3jbBHVM1v+uY\n" +
      "5ItGTjk+3vLrxmvGy5XFVG+8fF/xaVfo5TW5AgMBAAGjUDBOMB0GA1UdDgQWBBSQ\n" +
      "plC3hNS56l/yBYQTeEXoqXVUXDAfBgNVHSMEGDAWgBQDRcZNwPqOqQvagw9BpW0S\n" +
      "BkOpXjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAJIO8SiNr9jpLQ\n" +
      "eUsFUmbueoxyI5L+P5eV92ceVOJ2tAlBA13vzF1NWlpSlrMmQcVUE/K4D01qtr0k\n" +
      "gDs6LUHvj2XXLpyEogitbBgipkQpwCTJVfC9bWYBwEotC7Y8mVjjEV7uXAT71GKT\n" +
      "x8XlB9maf+BTZGgyoulA5pTYJ++7s/xX9gzSWCa+eXGcjguBtYYXaAjjAqFGRAvu\n" +
      "pz1yrDWcA6H94HeErJKUXBakS0Jm/V33JDuVXY+aZ8EQi2kV82aZbNdXll/R6iGw\n" +
      "2ur4rDErnHsiphBgZB71C5FD4cdfSONTsYxmPmyUb5T+KLUouxZ9B0Wh28ucc1Lp\n" +
      "rbO7BnjW\n" +
      "-----END CERTIFICATE-----\n"
  );
});

qz.security.setSignatureAlgorithm("SHA512"); // Since 2.1
qz.security.setSignaturePromise(function(toSign) {
  return function(resolve, reject) {
    //Preferred method - from server
    //            $.ajax("/secure/url/for/sign-message?request=" + toSign).then(resolve, reject);

    //Alternate method - unsigned
    resolve();
  };
});

export class PrintJob {
  /**
   *
   * @param {string} data
   * @param {PrintSize} size
   * @param {Object} margins
   */
  constructor(data, size, margins) {
    this.data = data || null;
    this.size = size;
    this.margins = margins;
  }
}

export class PrintSize {
  /**
   *
   * @param {int} width
   * @param {int} height
   */
  constructor(width, height) {
    this.width = width;
    this.height = height;
  }
}

const Status = Object.freeze({
  DISCONNECTED: "DISCONNECTED",
  CONNECTED: "CONNECTED",
  ERROR: "ERROR"
});

const Errors = Object.freeze({
  ALREADY_ACTIVE: 1
});

const state = {
  status: Status.DISCONNECTED,
  devices: [],
  device: null
};

const actions = {
  init({ commit, state, dispatch, getters }) {
    qz.websocket.setClosedCallbacks(() => {
      commit("setStatus", "DISCONNECTED");
    });
  },

  async connect({ commit, state, dispatch, getters }, config = {}) {
    config = {
      ...config,
      usingSecure: false
    };

    if (!qz.websocket.isActive()) {
      commit("setStatus", Status.WAITING);

      try {
        await qz.websocket.connect(config);
      } catch (e) {
        return dispatch("handleConnectionError", e);
      }

      commit("setStatus", Status.CONNECTED);
    } else {
      console.error("already connected");
    }
  },

  async disconnect({ commit, state, dispatch, getters }) {
    if (getters.isActive) {
      await qz.websocket.disconnect();
      commit("setStatus", Status.DISCONNECTED);
    } else {
    }
  },

  handleConnectionError({ commit, state, dispatch }, err) {
    commit("setStatus", Status.DISCONNECTED);

    if (err.target != undefined) {
      if (err.target.readyState >= 2) {
        //if CLOSING or CLOSED
        this.displayError("Connection to QZ Tray was closed");
      } else {
        commit("setStatus", Status.ERROR);
        this.displayError("A connection error occurred, check log for details");
      }
    } else {
      this.displayError(err);
    }
  },

  async findDevices({ commit, state, dispatch }) {
    const printers = await qz.printers.find();
    commit("setDevices", printers);
  },

  setDevice({ commit, state, dispatch }, device) {
    commit("setDevice", device);
  },

  displayError(e) {
    console.error(e);
  },

  /**
   *
   * @param {PrintJob} job
   */
  async addJob({ commit, state, dispatch, getters }, job) {
    console.log(job);
    let printData = [
      {
        type: "pdf",
        format: "base64",
        data: job.data
      }
    ];

    const printerName = getters.getDevice;
    let config = qz.configs.create(printerName, {
      size: { width: 4, height: 2.33 },
      orientation: "portrait",
      units: "in",
      colorType: "grayscale",
      interpolation: "nearest-neighbor"
    });

    try {
      await qz.print(config, printData);
    } catch (e) {
      console.error(e);
    }
  }
};

const getters = {
  isActive(state) {
    return qz.websocket.isActive();
  },

  getStatus(state) {
    return state.status;
  },

  getDevices(state) {
    return state.devices;
  },

  getDevice(state) {
    return state.device;
  }
};

const mutations = {
  setStatus(state, status) {
    state.status = status;
  },

  setDevices(state, devices) {
    state.devices = devices;
  },

  setDevice(state, device) {
    state.device = device;
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations,
  getters
};
