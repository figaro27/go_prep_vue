<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <Spinner v-if="isLoading" />

            <v-client-table
              ref="mealsTable"
              :columns="columns"
              :data="tableData"
              :options="options"
            >
              <div slot="beforeTable" class="mb-2">
                <button
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="createMeal"
                >
                  Add Meal
                </button>

                <button
                  v-if="storeSettings.meal_packages"
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="createMealPackage"
                >
                  Add Package
                </button>

                <b-form-radio-group
                  buttons
                  button-variant="primary"
                  size="md"
                  v-model="filter.status"
                  @change="onChangeStatusFilter"
                  :options="statusFilterOptions"
                  class="mb-2 mb-sm-0"
                />
                <router-link
                  :to="'/store/menu/preview'"
                  class="btn btn-warning btn-md"
                  tag="button"
                  >Preview Menu</router-link
                >
              </div>

              <span slot="beforeLimit">
                <b-btn
                  variant="success"
                  @click="exportData('meals_ingredients', 'pdf', true)"
                  class="mb-2 mb-sm-0"
                >
                  <i class="fa fa-print"></i>&nbsp; Print Meals Ingredients
                </b-btn>
                <b-btn
                  variant="primary"
                  @click="exportData('meals', 'pdf', true)"
                >
                  <i class="fa fa-print"></i>&nbsp; Print
                </b-btn>
                <b-dropdown class="mx-1" right text="Export as">
                  <b-dropdown-item @click="exportData('meals', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meals', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meals', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </span>

              <div slot="active" slot-scope="props">
                <b-form-checkbox
                  class="largeCheckbox"
                  type="checkbox"
                  v-model="props.row.active"
                  :value="1"
                  :unchecked-value="0"
                  @change="
                    val =>
                      updateActive(props.row.id, val, props.row.meal_package)
                  "
                ></b-form-checkbox>
              </div>

              <div slot="featured_image" slot-scope="props">
                <thumbnail
                  v-if="props.row.image.url_thumb"
                  :src="props.row.image.url_thumb"
                ></thumbnail>
              </div>

              <div
                slot="tags"
                slot-scope="props"
                v-if="!props.row.meal_package"
              >
                {{ props.row.tag_titles.join(", ") }}
              </div>
              <div slot="categories" slot-scope="props">
                <div v-if="!props.row.meal_package">
                  {{
                    props.row.category_ids
                      .map(categoryId => getCategoryTitle(categoryId))
                      .join(", ")
                  }}
                </div>
                <div v-else>Packages</div>
              </div>

              <div
                slot="contains"
                slot-scope="props"
                v-if="!props.row.meal_package"
              >
                {{
                  props.row.allergy_ids
                    .map(allergyId => getAllergyTitle(allergyId))
                    .join(", ")
                }}
              </div>

              <div slot="price" slot-scope="props">
                {{ formatMoney(props.row.price) }}
              </div>

              <div slot="current_orders" slot-scope="props">
                {{ props.row.orders.length }}
              </div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-warning btn-sm"
                  @click="
                    props.row.meal_package
                      ? viewMealPackage(props.row.id)
                      : viewMeal(props.row.id)
                  "
                >
                  View
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="() => deleteMeal(props.row.id)"
                >
                  Delete
                </button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <create-meal-modal v-if="createMealModal" @created="refreshTable()" />
    <create-package-modal v-if="createPackageModal" @created="refreshTable()" />
    <view-package-modal
      v-if="viewPackageModal"
      :meal_package="mealPackage"
      @hide="viewPackageModal = false"
      @updated="refreshTable()"
    />

    <div class="modal-full modal-tabs">
      <b-modal
        title="View Meal"
        v-model="viewMealModal"
        v-if="viewMealModal"
        :key="`view-meal-modal${meal.id}`"
        @ok.prevent="onViewMealModalOk"
      >
        <b-row>
          <b-col>
            <b-tabs>
              <b-tab title="General" active>
                <h4>Meal Title</h4>
                <b-form-group label-for="meal-title" :state="true">
                  <b-form-input
                    id="meal-title"
                    type="text"
                    v-model="meal.title"
                    placeholder="Meal Name"
                    required
                    @change="val => updateMeal(meal.id, { title: val }, true)"
                  ></b-form-input>
                </b-form-group>
                <h4>Meal Description</h4>
                <b-form-group label-for="meal-description" :state="true">
                  <textarea
                    v-model.lazy="meal.description"
                    id="meal-description"
                    class="form-control"
                    :rows="4"
                    :maxlength="450"
                    @change="
                      e => updateMealDescription(meal.id, e.target.value)
                    "
                  ></textarea>
                  <br />
                  <h4>Price</h4>
                  <money
                    required
                    v-model="meal.price"
                    :min="0.1"
                    :max="999.99"
                    class="form-control"
                    @blur.native="
                      e => updateMeal(meal.id, { price: meal.price })
                    "
                  ></money>
                  <br />
                  <h4>
                    Categories
                    <img
                      v-b-popover.hover="
                        'Categories show up as different sections of your menu to your customers. You can have the same meal show up in multiple categories. Add, remove, or rearrange the order of categories in Settings.'
                      "
                      title="Categories"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </h4>
                  <b-form-checkbox-group
                    buttons
                    v-model="meal.category_ids"
                    :options="categoryOptions"
                    @change="val => updateMeal(meal.id, { category_ids: val })"
                    class="storeFilters"
                  ></b-form-checkbox-group>

                  <h4 class="mt-4">
                    Tags
                    <img
                      v-b-popover.hover="
                        'Meal tags describe the nutritional benefits contained in your meal. These allow your meals to be filtered by your customer on your menu page for anyone with specific dietary preferences.'
                      "
                      title="Tags"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </h4>
                  <b-form-checkbox-group
                    buttons
                    v-model="meal.tag_ids"
                    :options="tagOptions"
                    @change="val => updateMeal(meal.id, { tag_ids: val })"
                    class="storeFilters"
                  ></b-form-checkbox-group>

                  <h4 class="mt-4">
                    Contains
                    <img
                      v-b-popover.hover="
                        'Indicate if your meal contains any of the below. These allow your meals to be filtered by your customer on your menu page for anyone looking to avoid meals that contain any of these options.'
                      "
                      title="Contains"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </h4>
                  <b-form-checkbox-group
                    buttons
                    v-model="meal.allergy_ids"
                    :options="allergyOptions"
                    @change="val => updateMeal(meal.id, { allergy_ids: val })"
                    class="storeFilters"
                  ></b-form-checkbox-group>
                </b-form-group>
              </b-tab>

              <b-tab title="Ingredients">
                <ingredient-picker
                  ref="ingredientPicker"
                  v-model="meal.ingredients"
                  :options="{ saveButton: true }"
                  :meal="meal"
                  @save="val => onChangeIngredients(meal.id, val)"
                ></ingredient-picker>
              </b-tab>
            </b-tabs>
          </b-col>

          <b-col md="3" lg="2">
            <picture-input
              :ref="`featuredImageInput${meal.id}`"
              :prefill="meal.image.url_thumb ? meal.image.url_thumb : false"
              @prefill="$refs[`featuredImageInput${meal.id}`].onResize()"
              :alertOnError="false"
              :autoToggleAspectRatio="true"
              margin="0"
              size="10"
              button-class="btn"
              @change="val => changeImage(val, meal.id)"
            ></picture-input>
            <!-- <p class="center-text">
              Image size too big?
              <br />You can compress images
              <a href="https://imagecompressor.com/" target="_blank">here.</a>
            </p> -->
          </b-col>
        </b-row>
      </b-modal>
    </div>
    <b-modal
      title="Delete Meal"
      v-model="deleteMealModalNonSubstitute"
      v-if="deleteMealModalNonSubstitute"
      :hide-footer="true"
    >
      <p class="center-text mb-3 mt-3">
        Are you sure you want to delete this meal?
      </p>
      <b-btn
        variant="danger"
        class="center"
        @click="destroyMealNonSubstitute(deletingMeal.id)"
        >Delete</b-btn
      >
    </b-modal>

    <b-modal
      title="Delete Meal"
      v-model="deleteMealModal"
      v-if="deleteMealModal"
      :hide-footer="true"
    >
      <center>
        <h5 class="mt-3">
          This meal is tied to one or more meal plans.
          <img
            v-b-popover.hover="
              'You currently have one or more meal plans with your customers that contain this meal. Please select a substitute and your customers will be informed via email. The recommended meals below are the closest meals in your menu to the meal being deleted in terms of foods they contain, meal tags, and categories. We also limit the recommended meals to be within 20% of the price of the meal being deleted.'
            "
            title="Replacement Meal"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </h5>
        <h5 class="mb-3">Please select a recommended replacement meal.</h5>

        <b-list-group>
          <b-list-group-item
            v-for="meal in mealSubstituteOptions(deletingMeal)"
            :active="substitute_id === meal.id"
            @click="
              () => {
                substitute_id = meal.id;
              }
            "
            :key="meal.id"
          >
            <div class="d-flex align-items-center text-left">
              <img
                class="mr-2"
                style="width:65px"
                :src="meal.image.thumb_url"
                v-if="meal.image.thumb_url"
              />
              <div class="flex-grow-1 mr-2">
                <p>{{ meal.title }}</p>
                <p class="strong">{{ format.money(meal.price) }}</p>
              </div>
              <b-btn variant="warning">Select</b-btn>
            </div>
          </b-list-group-item>
        </b-list-group>

        <div v-if="mealSubstituteOptions(deletingMeal).length <= 0">
          No substitutes lorem ipsum
        </div>

        <!--<b-select v-model="deleteMeal.subtitute_id" :options="mealSubstituteOptions(deleteMeal)"></b-select>-->
        <button
          v-if="substitute_id"
          class="btn btn-danger btn-lg mt-3"
          @click="destroyMeal(deletingMeal.id, substitute_id)"
        >
          Delete & Replace
        </button>
      </center>
    </b-modal>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import IngredientPicker from "../../components/IngredientPicker";
import CreateMealModal from "./Modals/CreateMeal";
import CreatePackageModal from "./Modals/CreateMealPackage";
import ViewPackageModal from "./Modals/ViewMealPackage";
import moment from "moment";
import tags from "bootstrap-tagsinput";
import { Event } from "vue-tables-2";
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from "vue-picture-input";
import units from "../../data/units";
import format from "../../lib/format";
import fs from "../../lib/fs.js";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
    CreateMealModal,
    CreatePackageModal,
    ViewPackageModal
  },
  updated() {
    //$(window).trigger("resize");
  },
  data() {
    return {
      _,
      filter: {
        status: "all"
      },
      meal: {
        title: "",
        featured_image: "",
        description: "",
        new_category: "",
        tags: "",
        price: "",
        num_orders: "",
        created_at: "",
        categories: [],
        image: {}
      },
      createMealModal: false,
      createPackageModal: false,
      viewMealModal: false,
      deleteMealModal: false,
      deleteMealModalNonSubstitute: false,
      viewPackageModal: false,
      deletingMeal: {},
      substitute_id: null,

      newTags: [],
      ingredientSearch: "",
      ingredientResults: [],
      ingredientQuery: "",
      ingredientList: "",
      ingredients: [],
      meal: [],
      mealPackage: {},
      mealID: null,
      newMeal: {
        featured_image: "",
        title: "",
        description: "",
        price: "",
        ingredients: [],
        image: {}
      },
      nutrition: {
        calories: null,
        totalFat: null,
        satFat: null,
        transFat: null,
        cholesterol: null,
        sodium: null,
        totalCarb: null,
        fibers: null,
        sugars: null,
        proteins: null,
        vitaminD: null,
        potassium: null,
        calcium: null,
        iron: null,
        addedSugars: null
      },

      active: [],

      columns: [
        "active",
        "featured_image",
        "title",
        "categories",
        "tags",
        "contains",
        "price",
        "subscription_count",
        "lifetime_orders",
        "created_at",
        "actions"
      ],
      options: {
        headings: {
          active: "Active",
          featured_image: "Image",
          title: "Title",
          categories: "Categories",
          tags: "Tags",
          contains: "Contains",
          price: "Price",
          subscription_count: "Meal Plans",
          lifetime_orders: "Lifetime Orders",
          created_at: "Added",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `meal meal-${row.id}`;
          classes += row.active ? "" : " faded";
          return classes;
        },
        customFilters: [
          {
            name: "status",
            callback: function(row, val) {
              if (val === "all") return true;
              else if (val === "active") return row.active;
              else if (val === "inactive") return !row.active;
              return false;
            }
          }
        ],
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              var numA = moment(a.created_at);
              var numB = moment(b.created_at);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          }
        },
        orderBy: {
          column: "title",
          ascending: true
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSettings: "storeSettings",
      meals: "storeMeals",
      mealPackages: "mealPackages",
      getMeal: "storeMeal",
      tags: "tags",
      storeCategories: "storeCategories",
      getCategoryTitle: "storeCategoryTitle",
      getAllergyTitle: "storeAllergyTitle",
      allergies: "allergies",
      isLoading: "isLoading"
    }),
    tableData() {
      const packages = Object.values(this.mealPackages).map(mealPackage => {
        return mealPackage;
      });
      const meals = Object.values(this.meals);
      return _.concat(packages, meals);
    },
    tagOptions() {
      return Object.values(this.tags).map(tag => {
        return {
          text: tag.tag,
          value: tag.id
        };
      });
    },
    categoryOptions() {
      return Object.values(this.storeCategories).map(cat => {
        return {
          text: cat.category,
          value: cat.id
        };
      });
    },
    allergyOptions() {
      return Object.values(this.allergies).map(allergy => {
        return {
          text: allergy.title,
          value: allergy.id
        };
      });
    },
    weightUnitOptions() {
      return units.mass.selectOptions();
    },
    statusFilterOptions() {
      return [
        { text: "All", value: "all" },
        { text: "Active", value: "active" },
        { text: "Inactive", value: "inactive" }
      ];
    },
    tagsForInput() {
      return _.map(["Breakfast", "Dinner"], tag => {
        return { text: tag };
      });
    },
    mealSubstituteOptions: vm => meal => {
      return _.filter(
        meal.substitute_ids.map(id => {
          const sub = vm.getMeal(id);
          return sub;
        })
      );
    }
  },
  created() {
    this.updateMealDescription = _.debounce((id, description) => {
      this.updateMeal(id, { description }, true);
    }, 300);
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals",
      refreshMealPackages: "refreshMealPackages",
      _updateMeal: "updateMeal",
      _updateMealPackage: "updateMealPackage",
      addJob: "addJob",
      removeJob: "removeJob"
    }),
    formatMoney: format.money,
    refreshTable() {
      this.refreshMeals();
      this.refreshMealPackages();
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, o => {
        return o.id === id;
      });
    },
    async onViewMealModalOk(e) {
      const data = {
        validate_all: true,
        title: this.meal.title,
        description: this.meal.description,
        price: this.meal.price,
        category_ids: this.meal.category_ids,
        ingredients: this.meal.ingredients
      };
      const updated = await this.updateMeal(this.meal.id, data, true);

      if (updated) {
        this.viewMealModal = false;
      } else {
        e.preventDefault();
      }
    },
    async updateMeal(id, changes, toast = false) {
      const i = this.getTableDataIndexById(id);
      if (i === -1) {
        return this.getTableData();
      }
      if (_.isEmpty(changes)) {
        changes = this.editing[id];
      }

      try {
        await this._updateMeal({ id, data: changes });

        if (toast) {
          this.$toastr.s("Meal updated!");
        }

        return true;
      } catch (e) {
        if (toast) {
          let error = _.first(Object.values(e.response.data.errors));

          if (error) {
            error = error.join(" ");
            this.$toastr.e(error, "Error");
          } else {
            this.$toastr.e("Failed to update meal!", "Error");
          }
        }

        return false;
      }
    },
    async updateMealPackage(id, changes, toast = false) {
      const i = this.getTableDataIndexById(id);
      if (i === -1) {
        return this.getTableData();
      }
      if (_.isEmpty(changes)) {
        changes = this.editing[id];
      }

      try {
        await this._updateMeal({ id, data: changes });

        if (toast) {
          this.$toastr.s("Meal updated!");
        }

        return true;
      } catch (e) {
        if (toast) {
          let error = _.first(Object.values(e.response.data.errors));

          if (error) {
            error = error.join(" ");
            this.$toastr.e(error, "Error");
          } else {
            this.$toastr.e("Failed to update meal!", "Error");
          }
        }

        return false;
      }
    },
    async updateActive(id, active, isMealPackage = false) {
      const i = _.findIndex(this.tableData, o => {
        return o.id === id && !!o.meal_package === isMealPackage;
      });

      if (i === -1) {
        return this.getTableData();
      }

      if (!isMealPackage) {
        await this._updateMeal({ id, data: { active } });
      } else {
        await this._updateMealPackage({ id, data: { active } });
      }

      if (active) {
        this.$toastr.s("Meal activated!");
      } else {
        this.$toastr.s("Meal deactivated!");
      }
      //this.refreshTable();
    },

    createMeal() {
      this.createMealModal = true;
    },
    createMealPackage() {
      this.createPackageModal = true;
    },

    async viewMeal(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/meals/${id}`)
        .then(response => {
          this.meal = response.data;
          this.ingredients = response.data.ingredient;
          //this.tags = response.data.meal_tag;
          this.mealID = response.data.id;
          this.viewMealModal = true;

          this.$nextTick(function() {
            window.dispatchEvent(new window.Event("resize"));
            this.$refs[`featuredImageInput${this.meal.id}`].onResize();
          });
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },
    async viewMealPackage(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/packages/${id}`)
        .then(response => {
          this.mealPackage = response.data;
          this.viewPackageModal = true;

          this.$nextTick(function() {
            window.dispatchEvent(new window.Event("resize"));
          });
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },

    deleteMeal: function(id) {
      this.deletingMeal = this.getMeal(id);

      if (!this.deletingMeal) {
        return;
      }

      if (this.deletingMeal.substitute) {
        this.deleteMealModal = true;
      } else {
        this.deleteMealModalNonSubstitute = true;
      }
    },
    destroyMeal: function(id, subId) {
      axios.delete(`/api/me/meals/${id}?substitute_id=${subId}`).then(resp => {
        this.refreshTable();
        this.deleteMealModal = false;
        this.$toastr.s("Meal deleted!");
        this.substitute_id = null;
      });
    },
    destroyMealNonSubstitute(mealId) {
      axios
        .post(`/api/me/destroyMealNonSubstitute`, { id: mealId })
        .then(resp => {
          this.refreshTable();
          this.deleteMealModalNonSubstitute = false;
          this.$toastr.s("Meal deleted!");
        });
    },
    getNutrition: function() {
      axios
        .post("/api/nutrients", {
          query: this.ingredientQuery
        })
        .then(response => {
          this.ingredients = response.data.foods;
        });
    },
    searchInstant: function() {
      axios
        .post("/api/searchInstant", {
          search: this.ingredientSearch
        })
        .then(response => {
          this.ingredientResults = response.data.common;
        });
    },
    async changeImage(val, mealId = null) {
      if (!mealId) {
        let b64 = await fs.getBase64(this.$refs.featuredImageInput.file);
        this.meal.featured_image = b64;
      } else {
        let b64 = await fs.getBase64(
          this.$refs[`featuredImageInput${mealId}`].file
        );
        this.meal.featured_image = b64;
        this.updateMeal(mealId, { featured_image: b64 });
      }
    },
    onChangeIngredients(mealId, ingredients) {
      if (!_.isNumber(mealId) || !_.isArray(ingredients)) {
        throw new Exception("Invalid ingredients");
      }

      this.updateMeal(mealId, { ingredients }, true);
    },
    onClickAddIngredient() {
      this.ingredients.push({});
    },
    onChangeStatusFilter(val) {
      Event.$emit("vue-tables.filter::status", val);
    },
    onChangeTags(id, newTags) {
      this.editing[id].tag_titles_input = newTags;
      this.editing[id].tag_titles = _.map(newTags, "text");
      this.updateMeal(id, { tag_titles: this.editing[id].tag_titles });
    },
    activate(tag) {
      alert(tag);
    },
    onAddCategory() {
      this.meal.categories.push({
        category: this.meal.new_category
      });
      this.meal.new_category = "";

      this.updateMeal(this.meal.id, { categories: this.meal.categories });
    },
    onChangeCategories(e) {
      if (_.isObject(e.moved)) {
        this.updateMeal(this.meal.id, { categories: this.meal.categories });
      }
    },
    exportData(report, format = "pdf", print = false) {
      axios
        .get(`/api/me/print/${report}/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if (print) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                },
                false
              );
            }
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>
