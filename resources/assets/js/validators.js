import { required, minLength, email, helpers } from "vuelidate/lib/validators";

const validators = {
  required,
  password: {
    required,
    minLength: minLength(6)
  },
  email: {
    required,
    email
  },
  firstname: {
    required
  },
  lastname: {
    required
  },
  phone: {
    required
  },
  address: {
    required
  },
  city: {
    required
  },
  state: {
    required
  },
  zip: {
    required
  },
  country: {
    required
  },
  delivery: {
    minLength: minLength(0)
  },
  store_name: {
    required,
    minLength: minLength(6)
  },
  domain: {
    required,
    minLength: minLength(4),
    regex: helpers.regex(
      "domain",
      /^[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?$/
    )
  }
};

export default validators;
