<template>
  <b-modal
    title="Choose Options"
    ref="modal"
    size="sm"
    @ok.prevent="e => ok(e)"
  >
    <div v-if="meal">
      <b-row v-if="components.length" class="my-3">
        <b-col>
          <div
            v-for="(component, i) in components"
            :key="meal.id + component.id"
            class
          >
            <h6>{{ getComponentLabel(component) }}</h6>
            <b-form-group :label="null">
              <b-checkbox-group
                v-model="choices[component.id]"
                :options="getOptions(component)"
                :min="component.minimum"
                :max="component.maximum"
                stacked
              ></b-checkbox-group>

              <div v-if="$v.choices[component.id].$dirty">
                <div
                  v-if="false === $v.choices[component.id].required"
                  class="invalid-feedback d-block"
                >
                  This field is required
                </div>
                <div
                  v-if="false === $v.choices[component.id].minimum"
                  class="invalid-feedback d-block"
                >
                  Minimum {{ component.minimum }}
                </div>
                <div
                  v-if="false === $v.choices[component.id].maximum"
                  class="invalid-feedback d-block"
                >
                  Maximum {{ component.maximum }}
                </div>
              </div>
            </b-form-group>
          </div>
        </b-col>
      </b-row>

      <b-row v-if="mealAddons.length" class="my-3">
        <b-col>
          <h6>Add-ons</h6>
          <b-form-group label>
            <b-checkbox-group
              v-model="addons"
              :options="getAddonOptions(mealAddons)"
              stacked
            ></b-checkbox-group>
          </b-form-group>
        </b-col>
      </b-row>
    </div>
  </b-modal>
</template>

<script>
import modal from "../../mixins/modal";
import format from "../../lib/format";
import { required, minLength } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

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
  computed: {
    ...mapGetters(["storeSettings"]),
    sizeId() {
      return _.isObject(this.size) ? this.size.id : null;
    },
    sizeCriteria() {
      return !this.mealPackage
        ? { meal_size_id: this.sizeId }
        : { meal_package_size_id: this.sizeId };
    },
    components() {
      return _.filter(this.meal.components, component => {
        return _.find(component.options, this.sizeCriteria);
      });
    },
    mealAddons() {
      return _.filter(this.meal.addons, addon => {
        return addon.meal_size_id === this.sizeId;
      });
    }
  },
  validations() {
    if (!this.meal) return {};

    let componentValidations = _.mapValues(
      _.keyBy(this.components, "id"),
      component => {
        return {
          minimum: value => {
            return (
              component.minimum === 0 ||
              (_.isArray(value) && value.length >= component.minimum)
            );
          },
          maximum: value => {
            return (
              !value || (_.isArray(value) && value.length <= component.maximum)
            );
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
      this.choices = {};
      this.addons = [];

      this.$refs.modal.show();

      this.$forceUpdate();

      this.$nextTick(() => {
        this.$v.$reset();
      });

      return new Promise((resolve, reject) => {
        this.$refs.modal.$on("cancel", () => {
          resolve(null);
          this.meal = null;
          this.mealPackage = false;
          this.size = null;
          this.choices = {};
          this.$v.$reset();
        });
        this.$on("done", () => {
          this.$v.$touch();

          if (this.$v.$invalid) {
            this.$forceUpdate();
          } else {
            if (!_.isEmpty(this.choices) || !_.isEmpty(this.addons)) {
              resolve({
                components: { ...this.choices },
                addons: [...this.addons]
              });
            } else {
              resolve({
                components: {},
                addons: []
              });
            }

            this.hide();
            this.meal = null;
            this.mealPackage = false;
            this.size = null;
            this.choices = {};
            this.$v.$reset();
          }
        });
      });
    },
    ok() {
      this.$emit("done");
    },
    getOptions(component) {
      let options = _.filter(component.options, this.sizeCriteria);
      return _.map(options, option => {
        let title = option.title;
        if (option.price && option.price > 0) {
          title +=
            " - " + format.money(option.price, this.storeSettings.currency);
        }

        return {
          value: option.id,
          text: title
        };
      });
    },
    getAddonOptions(addons) {
      addons = _.filter(addons, addon => {
        return addon.meal_size_id == this.sizeId;
      });
      return _.map(addons, addon => {
        let title = addon.title;
        if (addon.price && addon.price > 0) {
          title +=
            " - " + format.money(addon.price, this.storeSettings.currency);
        }

        return {
          value: addon.id,
          text: title
        };
      });
    },
    getComponentLabel(component) {
      let qty = "";
      if (component.minimum === component.maximum) {
        qty = `Choose ${component.minimum}`;
      } else {
        qty = `Choose up to ${component.maximum}`;
      }

      return `${component.title} - ${qty}`;
    }
  }
};
</script>

<style></style>
