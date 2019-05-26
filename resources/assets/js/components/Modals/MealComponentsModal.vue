<template>
  <div class="modal-full">
    <b-modal title="Choose Options" ref="modal" @ok.prevent="e => ok(e)">
      <div v-if="meal">
        <b-row v-if="meal.components.length">
          <b-col>
            <div
              v-for="(component, i) in meal.components"
              :key="meal.id + component.id"
              class
            >
              <b-form-group :label="component.title">
                <b-checkbox-group
                  v-model="choices[component.id]"
                  :options="getOptions(component)"
                  :min="component.minimum"
                  :max="component.maximum"
                ></b-checkbox-group>
              </b-form-group>

              <div>
                <div
                  v-if="
                    $v.choices[component.id] &&
                      !$v.choices[component.id].required
                  "
                  class="invalid-feedback"
                >
                  This field is required
                </div>
                <div
                  v-if="
                    $v.choices[component.id] &&
                      !$v.choices[component.id].minimum
                  "
                  class="invalid-feedback"
                >
                  Minimum {{ component.minimum }}
                </div>
                <div
                  v-if="
                    $v.choices[component.id] &&
                      !$v.choices[component.id].minimum
                  "
                  class="invalid-feedback"
                >
                  Maximum {{ component.maximum }}
                </div>
              </div>
            </div>
          </b-col>
        </b-row>

        <b-row v-if="meal.addons.length">
          <b-col>
            <b-form-group label="Addons">
              <b-checkbox-group
                v-model="addons"
                :options="getAddonOptions(meal.addons)"
              ></b-checkbox-group>
            </b-form-group>

            <div v-if="$v.$dirty">
              <div
                v-if="!$v.choices[component.id].required"
                class="invalid-feedback"
              >
                This field is required
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
      mealPackage: false,
      size: null,
      choices: {},
      addons: []
    };
  },
  computed: {},
  validations() {
    if (!this.meal) return {};

    let componentValidations = _.mapValues(
      _.keyBy(this.meal.components, "id"),
      component => {
        return {
          required,
          minimum: value => {
            return _.isArray(value) && value.length >= component.minimum;
          },
          maximum: value => {
            return _.isArray(value) && value.length <= component.maximum;
          }
        };
      }
    );

    return {
      choices: { ...componentValidations }
    };
  },
  methods: {
    show(meal, mealPackage = false, size = null) {
      this.meal = meal;
      this.mealPackage = mealPackage;
      this.size = size;

      this.$refs.modal.show();

      this.$forceUpdate();

      return new Promise((resolve, reject) => {
        this.$on("done", () => {
          this.$v.$touch();

          if (this.$v.$invalid) {
            this.$toastr.e("");
          } else {
            if (!_.isEmpty(this.choices) || !_.isEmpty(this.addons)) {
              resolve({
                components: { ...this.choices },
                addons: [...this.addons]
              });
            }
            this.hide();
            this.meal = null;
            this.mealPackage = false;
            this.size = null;
            this.choices = {};
          }
        });
      });
    },
    ok() {
      this.$emit("done");
    },
    getOptions(component) {
      let options = _.filter(component.options, option => {
        return option.meal_size_id == this.size;
      });
      return _.map(options, option => {
        return {
          value: option.id,
          text: `${option.title} - ${format.money(option.price)}`
        };
      });
    },
    getAddonOptions(addons) {
      return _.map(addons, addon => {
        return {
          value: addon.id,
          text: `${addon.title} - ${format.money(addon.price)}`
        };
      });
    }
  }
};
</script>

<style></style>
