import Cookies from 'js-cookie';

let refreshTimeout = null;
let interceptor = null;

const auth = {

  init() {
    if(auth.hasToken()) {
      auth.setToken(auth.getToken())
    }
  },

  getToken() {
    return Cookies.getJSON("jwt") || null;
  },

  hasToken() {
    return !_.isNull(auth.getToken());
  },

  setToken(jwt) {
    Cookies.set("jwt", jwt, {domain: window.app.domain});

    interceptor = window.axios.interceptors.request.use(config => {
      const jwt = auth.getToken()
      if (jwt) {
        config.headers.common['Authorization'] = `Bearer ${jwt.access_token}`;
      }
      return config
    });

    // Schedule a token refresh
    clearTimeout(refreshTimeout)
    refreshTimeout = setTimeout(() => {
      auth.refreshToken();
    }, jwt.expires_in - 30);
  },

  deleteToken() {
    //window.axios.defaults.headers.common["Authorization"] = null;
    Cookies.remove("jwt", {domain: window.app.domain});
    window.axios.interceptors.request.eject(interceptor);
  },

  async refreshToken() {
    let resp = {};
    
    try {
      resp = await axios.post('/api/auth/refresh', {jwt: auth.getToken()});
    }
    catch(e) {
      auth.deleteToken();
      return;
    }

    if(resp.data.access_token) {
      auth.setToken(resp.data);
    }
  },
}

export default auth;