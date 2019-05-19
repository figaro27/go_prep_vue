<template>
  <div class="modal-full">
    <b-modal title="Edit Package" ref="modal" @ok.prevent="e => ok(e)">
      <div v-if="meal">
        <b-row>
          <b-col>
            <div
              v-for="(component, i) in meal.components"
              :key="component.id"
              class=""
            >
              {{ component.title }}

              Minimum: {{ component.minimum }}<br />
              Maximum: {{ component.maximum }}

              <b-checkbox-group
                v-model="choices[component.id]"
                :options="getOptions(component)"
                :min="component.minimum"
                :max="component.maximum"
              >
              </b-checkbox-group>

              <div v-if="!$v.choices[component.id].required" class="error">
                This field is required
              </div>
              <div v-if="!$v.choices[component.id].minimum" class="error">
                Minimum {{ component.minimum }}
              </div>
              <div v-if="!$v.choices[component.id].minimum" class="error">
                Maximum {{ component.maximum }}
              </div>
            </div>
          </b-col>
        </b-row>
      </div>
    </b-modal>
  </div>
</template>

<script>
import modal from "../../mixins/modal";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";

export default {
  mixins: [modal],
  props: {},
  data() {
    return {
      meal: null,
      choices: {}
    };
  },
  validations() {
    let componentValidations = _.mapValues(
      _.keyBy(this.meal.components, "id"),
      component => {
        return {
          required,
          minimum: minLength(3)
        };
      }
    );

    return {
      choices: { ...componentValidations }
    };
  },
  methods: {
    show(meal) {
      this.meal = meal;
      this.$refs.modal.show();

      return new Promise((resolve, reject) => {
        this.$on("done", () => {
          this.$v.$touch();

          if (!this.$v.$invalid) {
            resolve(this.choices);
          }
        });
      });
    },
    ok() {
      this.$emit("done");
    },
    getOptions(component) {
      return _.map(component.options, option => {
        return {
          value: option.id,
          text: `${option.title} - ${format.money(option.price)}`
        };
      });
    }
  }
};
</script>

<style></style>
