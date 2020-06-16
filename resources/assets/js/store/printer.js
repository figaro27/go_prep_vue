import qz from "qz-tray";

/// Authentication setup ///
qz.security.setCertificatePromise(function(resolve, reject) {
  axios.get("/api/me/print/certificate").then(response => {
    resolve(response.data);
  }, reject);
});

qz.security.setSignatureAlgorithm("SHA512"); // Since 2.1
qz.security.setSignaturePromise(function(toSign) {
  return function(resolve, reject) {
    axios.get("/api/me/print/sign?request=" + toSign).then(response => {
      resolve(response.data);
    }, reject);
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
        await dispatch("findDevices");
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
      autoscale: "true",
      rasterize: "false",
      margins: job.margins,
      units: "in",
      colorType: "grayscale"

      // scaleContent: "true",
      // orientation: "landscape",
      // size: { ...job.size },
      // interpolation: "nearest-neighbor",
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
