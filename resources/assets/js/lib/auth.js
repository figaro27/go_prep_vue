import Cookies from 'js-cookie';

const auth = {

  getToken() {
    return Cookies.getJSON("jwt") || null;
  },

  setToken(jwt) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${
    jwt.access_token}`;

    Cookies.set("jwt", jwt, {domain: window.app.domain});
    //localStorage.setItem("jwt", JSON.stringify(jwt));
  },

  deleteToken() {
    window.axios.defaults.headers.common["Authorization"] = null;
    Cookies.remove("jwt", {domain: window.app.domain});
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
  }
}

export default auth;