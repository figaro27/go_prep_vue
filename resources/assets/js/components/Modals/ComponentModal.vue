<template>
  <div class="modal-full">
    <b-modal title="Edit Package" ref="modal" @ok.prevent="e => ok(e)">
      <b-row>
        <b-col>
          <div
            v-for="component in meal.components"
            :key="component.id"
            class=""
          >
            {{ component.title }}

            <b-checkbox-group
              :options="getOptions(component)"
              :min="component.minimum"
              :max="component.maximum"
            >
            </b-checkbox-group>
          </div>
        </b-col>
      </b-row>
    </b-modal>
  </div>
</template>

<script>
export default {
  props: {
    meal: {
      required: true
    }
  },
  methods: {
    ok() {}
  }
};
</script>

<style></style>
