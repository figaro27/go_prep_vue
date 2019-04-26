export default {
  computed: {},
  methods: {
    toastErrorResponse(resp, title = null) {
      let err = "";
      let errors = [];
      if (_.isObject(resp.errors) && Object.values(resp.errors).length > 0) {
        errors = _.filter(Object.values(resp.errors));
      }

      if (!title && resp.message) {
        title = resp.message;
      }

      if (errors.length > 0) {
        err += _(Object.values(errors))
          .filter()
          .map(fieldErrors => {
            return _(fieldErrors).map(error => {
              if (error.substr(-1) === ".") {
                error = error.substr(0, error.length - 1);
              }
              return error;
            });
          })
          .join(", ");
      }

      this.$toastr.e(err, title);
    },
    toastSuccessResponse(resp, title = "Success!") {
      this.$toastr.e(err, title);
    },
    toastResponse(resp) {
      if (resp.data) {
        resp = resp.data;
      }

      if (resp.errors) {
        this.toastErrorResponse(resp);
      } else {
        this.toastSuccessResponse(resp);
      }
    }
  }
};
