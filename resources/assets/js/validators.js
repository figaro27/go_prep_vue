import { required, minLength, email } from "vuelidate/lib/validators";

const validators = {
  required: {
    required,
  },
  password: {
    required,
    minLength: 7
  },
  email: {
    required,
    email
  },
  first_name: {
    required,
  },
  last_name: {
    required,
  },
  phone: {
    required,
  },
  address: {
    required,
  },
  city: {
    required,
  },
  state: {
    required,
  },
  zip: {
    required,
  },
}

export default validators;