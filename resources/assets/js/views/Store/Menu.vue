<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <menu-categories-modal
              v-if="showCategoriesModal"
              @hidden="
                showCategoriesModal = false;
                refreshTable();
              "
            ></menu-categories-modal>

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

                <!-- <button
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="createGiftCardModal = true"
                >
                  Add Gift Card
                </button> -->
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
                  v-if="!storeURLcheck"
                  :to="'/store/menu/preview'"
                  class="btn btn-warning btn-md"
                  tag="button"
                  >Preview Menu</router-link
                >
                <a
                  :href="storeURL"
                  v-if="storeURLcheck"
                  class="btn btn-warning btn-md"
                  tag="button"
                  >Preview Menu</a
                >
                <b-btn variant="danger" @click="showCategoriesModal = true"
                  >Edit Categories</b-btn
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
                      updateActive(
                        props.row.id,
                        val,
                        props.row.meal_package,
                        props.row.gift_card
                      )
                  "
                ></b-form-checkbox>
              </div>

              <div slot="featured_image" slot-scope="props">
                <thumbnail
                  v-if="props.row.image != null && props.row.image.url_thumb"
                  :src="props.row.image.url_thumb"
                ></thumbnail>
              </div>

              <div
                slot="tags"
                slot-scope="props"
                v-if="!props.row.meal_package && !props.row.gift_card"
              >
                {{ props.row.tag_titles.join(", ") }}
              </div>
              <div slot="categories" slot-scope="props">
                <div v-if="!props.row.meal_package && !props.row.gift_card">
                  {{
                    props.row.category_ids
                      .map(categoryId => getCategoryTitle(categoryId))
                      .join(", ")
                  }}
                </div>
                <div v-else-if="props.row.meal_package">Packages</div>
                <div v-else>Gift Cards</div>
              </div>

              <div
                slot="contains"
                slot-scope="props"
                v-if="!props.row.meal_package && !props.row.gift_card"
              >
                {{
                  props.row.allergy_ids
                    .map(allergyId => getAllergyTitle(allergyId))
                    .join(", ")
                }}
              </div>

              <div slot="price" slot-scope="props">
                {{ formatMoney(props.row.price, storeSettings.currency) }}
              </div>

              <div slot="current_orders" slot-scope="props">
                {{ props.row.orders.length }}
              </div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-warning btn-sm"
                  @click="
                    props.row.gift_card
                      ? viewGiftCard(props.row.id)
                      : props.row.meal_package
                      ? viewMealPackage(props.row.id)
                      : viewMeal(props.row.id)
                  "
                >
                  View
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="
                    props.row.meal_package
                      ? deleteMealPackage(props.row.id)
                      : deleteMeal(props.row.id)
                  "
                >
                  Delete
                </button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <view-gift-card-modal
      v-if="viewGiftCardModal"
      :giftCard="giftCard"
      @updated="refreshTable()"
    ></view-gift-card-modal>
    <create-gift-card-modal
      v-if="createGiftCardModal"
      @created="refreshTable()"
    ></create-gift-card-modal>
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
        size="xl"
        title="View Meal"
        v-model="viewMealModal"
        v-if="viewMealModal"
        :key="`view-meal-modal${meal.id}`"
        @ok.prevent="onViewMealModalOk"
        no-fade
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
                    placeholder="Item Name"
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
                    v-bind="{ prefix: storeCurrencySymbol }"
                    @blur.native="
                      e => updateMeal(meal.id, { price: meal.price })
                    "
                  ></money>
                  <br />
                  <h4 v-if="store.modules.stockManagement">Stock</h4>
                  <b-form-group
                    label-for="meal-stock"
                    :state="true"
                    v-if="store.modules.stockManagement"
                    class="mb-3"
                  >
                    <b-form-input
                      v-model="meal.stock"
                      placeholder="Leave blank for no stock management."
                      @change="val => updateMeal(meal.id, { stock: val }, true)"
                    ></b-form-input>
                  </b-form-group>
                  <h4 v-if="storeSettings.showMacros">
                    Macros
                    <img
                      v-b-popover.hover="
                        'Here you can enter the main macro-nutrients for your meals which will then show underneath the meal titles on your menu page. If you have Nutrition Facts enabled, be sure to keep these numbers consistent as your customers will see the differences.'
                      "
                      title="Macros"
                      src="/images/store/popover.png"
                      class="popover-size"
                    />
                  </h4>
                  <b-form-group
                    label-for="meal-macros"
                    :state="true"
                    v-if="storeSettings.showMacros"
                  >
                    <div class="row">
                      <div class="col-md-3">
                        Calories
                      </div>
                      <div class="col-md-3">
                        Carbs
                      </div>
                      <div class="col-md-3">
                        Protein
                      </div>
                      <div class="col-md-3">
                        Fat
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3">
                        <b-form-input
                          id="macros-calories"
                          type="number"
                          v-model="meal.macros.calories"
                          required
                        ></b-form-input>
                      </div>
                      <div class="col-md-3">
                        <b-form-input
                          id="macros-carbs"
                          type="number"
                          v-model="meal.macros.carbs"
                          required
                        ></b-form-input>
                      </div>
                      <div class="col-md-3">
                        <b-form-input
                          id="macros-protein"
                          type="number"
                          v-model="meal.macros.protein"
                          required
                        ></b-form-input>
                      </div>
                      <div class="col-md-3">
                        <b-form-input
                          id="macros-fat"
                          type="number"
                          v-model="meal.macros.fat"
                          required
                        ></b-form-input>
                      </div>
                    </div>
                  </b-form-group>
                  <div
                    v-if="
                      storeModules.productionGroups &&
                        storeProductionGroups.length > 0
                    "
                  >
                    <h4>Production Group</h4>
                    <b-form-radio-group
                      v-if="storeModules.productionGroups"
                      buttons
                      v-model="meal.production_group_id"
                      class="storeFilters"
                      @change="
                        val => updateMeal(meal.id, { production_group_id: val })
                      "
                      :options="productionGroupOptions"
                    ></b-form-radio-group>
                  </div>
                  <h4 class="mt-2">
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

                  <div v-if="store.modules.customSalesTax">
                    <h4 class="mt-4">
                      Custom Sales Tax
                      <img
                        v-b-popover.hover="
                          'If this meal should be charged different sales tax or even 0 sales tax, you can type the amount in this field.'
                        "
                        title="Custom Sales Tax"
                        src="/images/store/popover.png"
                        class="popover-size"
                      />
                    </h4>
                    <b-form-input
                      v-model.number="meal.salesTax"
                      placeholder="Leave blank for default sales tax or type 0 for no sales tax."
                    ></b-form-input>
                  </div>
                </b-form-group>

                <h4 v-if="storeSettings.mealInstructions" class="mt-4">
                  Instructions
                  <img
                    v-b-popover.hover="
                      'Here you can include special heating instructions, preparation instructions, or expiration info to your customers for this particular meal. If this meal is ordered, these specific instructions will be shown on the customer\'s packing slips & email receipts.'
                    "
                    title="Special Meal Instructions"
                    src="/images/store/popover.png"
                    class="popover-size"
                  />
                </h4>
                <textarea
                  v-if="storeSettings.mealInstructions"
                  v-model.lazy="meal.instructions"
                  id="meal-instructions"
                  class="form-control"
                  :rows="2"
                  :maxlength="150"
                  @change="e => updateMealInstructions(meal.id, e.target.value)"
                ></textarea>
              </b-tab>

              <b-tab title="Ingredients">
                <ingredient-picker
                  ref="ingredientPicker"
                  v-model="meal.ingredients"
                  :options="{ saveButton: true }"
                  :meal="meal"
                  @save="onViewMealModalOk"
                  :viewMealModal="true"
                ></ingredient-picker>
              </b-tab>

              <b-tab title="Variations">
                <b-tabs pills>
                  <b-tab title="Sizes">
                    <meal-sizes
                      :meal="meal"
                      @change="val => (meal.sizes = val)"
                      @changeDefault="val => (meal.default_size_title = val)"
                      @save="
                        val =>
                          updateMeal(meal.id, {
                            sizes: val,
                            default_size_title: meal.default_size_title
                          })
                      "
                    ></meal-sizes>
                  </b-tab>

                  <b-tab title="Components">
                    <meal-components
                      :meal="meal"
                      @change="val => (meal.components = val)"
                      @save="val => updateMeal(meal.id, { components: val })"
                    ></meal-components>
                  </b-tab>

                  <b-tab title="Addons">
                    <meal-addons
                      :meal="meal"
                      @change="val => (meal.addons = val)"
                      @save="val => updateMeal(meal.id, { addons: val })"
                    ></meal-addons>
                  </b-tab>
                </b-tabs>
              </b-tab>

              <b-tab title="Gallery">
                <div class="gallery row">
                  <div
                    v-for="(image, i) in meal.gallery"
                    :key="i"
                    class="col-sm-4 col-md-3 mb-3"
                  >
                    <div class="position-relative">
                      <b-btn
                        @click="deleteGalleryImage(i)"
                        variant="danger"
                        size="sm"
                        class="position-absolute"
                        style="top: 5px; right: 5px; z-index: 1"
                      >
                        <i class="fa fa-trash"></i>
                      </b-btn>
                      <thumbnail
                        v-if="image.url_thumb != null"
                        :src="image.url_thumb"
                        width="100%"
                      ></thumbnail>
                    </div>
                  </div>

                  <div class="col-sm-4 col-md-3">
                    <picture-input
                      :ref="`galleryImageInput${meal.id}`"
                      :alertOnError="false"
                      :autoToggleAspectRatio="true"
                      margin="0"
                      size="10"
                      button-class="btn"
                      @change="val => changeGalleryImage(val, meal.id)"
                      v-observe-visibility="forceResize"
                    ></picture-input>
                  </div>
                </div>
              </b-tab>
            </b-tabs>
          </b-col>

          <b-col md="3" lg="2">
            <picture-input
              :ref="`featuredImageInput${meal.id}`"
              :prefill="getMealImage(meal)"
              @prefill="$refs[`featuredImageInput${meal.id}`].onResize()"
              :alertOnError="false"
              :autoToggleAspectRatio="true"
              margin="0"
              size="10"
              button-class="btn"
              @change="val => changeImage(val, meal.id)"
              v-observe-visibility="forceResize"
            ></picture-input>
            <!-- <p class="center-text">
              Image size too big?
              <br />You can compress images
              <a href="https://imagecompressor.com/" target="_blank">here.</a>
            </p>-->
          </b-col>
        </b-row>
      </b-modal>
    </div>
    <b-modal
      title="Delete Meal Package"
      v-model="deleteMealPackageModal"
      v-if="deleteMealPackageModal"
      :hide-footer="true"
      no-fade
    >
      <p class="center-text mb-3 mt-3">
        Are you sure you want to delete this meal package?
      </p>
      <p class="center-text mb-3 mt-3">
        Note: This does not delete the meals inside the meal package. You may
        have active subscriptions with those meals that will be unaffected.
      </p>
      <b-btn
        variant="danger"
        class="center"
        @click="destroyMealPackage(mealPackageId)"
        >Delete</b-btn
      >
    </b-modal>
    <b-modal
      title="Delete Meal"
      v-model="deleteMealModalNonSubstitute"
      v-if="deleteMealModalNonSubstitute"
      :hide-footer="true"
      no-fade
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
      no-fade
    >
      <center>
        <h5 class="mt-5 mb-5">
          This meal is tied to one or more subscriptions or is found in one or
          more meal packages. Please select a meal to replace this meal in all
          existing subscriptions & packages.
          <img
            v-b-popover.hover="
              'You currently have one or more subscriptions with your customers that contain this meal OR a meal package that contains this meal. Please select a substitute replacement meal. The recommended meals below are the closest meals in your menu to the meal being deleted in terms of allergies, meal tags, and categories. We also limit the recommended meals to be within 20% of the price of the meal being deleted.'
            "
            title="Replacement Meal"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </h5>
        <v-select
          label="title"
          :options="meals"
          :reduce="meal => meal.id"
          v-model="substitute_id"
        ></v-select>
        <button
          v-if="substitute_id"
          class="btn btn-danger btn-lg mt-3"
          @click="destroyMeal(deletingMeal.id, substitute_id)"
        >
          Delete & Replace
        </button>
        <div
          class="col-xs-12"
          style="height:300px;"
          v-if="mealSubstituteOptions(deletingMeal).length === 0"
        ></div>
        <div v-if="mealSubstituteOptions(deletingMeal).length > 0">
          <h5 class="mt-3">Recommended Replacements</h5>
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
              class="mb-1"
            >
              <div class="d-flex align-items-center text-left">
                <img
                  class="mr-2"
                  style="width:65px"
                  :src="meal.image.thumb_url"
                  v-if="meal.image != null && meal.image.thumb_url"
                />
                <div class="flex-grow-1 mr-2">
                  <p>{{ meal.title }}</p>
                  <p class="strong">
                    {{ format.money(meal.price, storeSettings.currency) }}
                  </p>
                </div>
                <b-btn variant="warning">Select</b-btn>
              </div>
            </b-list-group-item>
          </b-list-group>

          <!-- <div v-if="mealSubstituteOptions(deletingMeal).length <= 0">
            There are currently no substitute options for this meal. Please add a
            similar meal that 1) doesn't contain the same allergies, and 2) is
            within the same meal category.
          </div> -->

          <!--<b-select v-model="deleteMeal.subtitute_id" :options="mealSubstituteOptions(deleteMeal)"></b-select>-->
          <button
            v-if="substitute_id"
            class="btn btn-danger btn-lg mt-3"
            @click="destroyMeal(deletingMeal.id, substitute_id)"
          >
            Delete & Replace
          </button>
        </div>
      </center>
    </b-modal>
    <b-modal
      title="Deactivate Meal"
      v-model="deactivateMealModal"
      v-if="deactivateMealModal"
      :hide-footer="true"
      no-fade
    >
      <center>
        <h5 class="mt-3">
          This meal is tied to one or more subscriptions or is found in one or
          more meal packages. Please select a meal to replace this meal in all
          existing subscriptions & packages.
          <img
            v-b-popover.hover="
              'You currently have one or more subscriptions with your customers that contain this meal OR a meal package that contains this meal. Please select a substitute replacement meal. The recommended meals below are the closest meals in your menu to the meal being deleted in terms of allergies, meal tags, and categories. We also limit the recommended meals to be within 20% of the price of the meal being deleted.'
            "
            title="Replacement Meal"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </h5>
        <h5 class="mb-5 mt-5">Please select a replacement meal.</h5>

        <v-select
          label="title"
          :options="meals"
          :reduce="meal => meal.id"
          v-model="substitute_id"
        ></v-select>
        <button
          class="btn btn-warning btn-lg mt-3"
          @click="deactivateMealModal = false"
        >
          Deactivate & Keep
        </button>
        <button
          v-if="substitute_id"
          class="btn btn-danger btn-lg mt-3"
          @click="deactivateAndReplace(deactivatingMeal.id, substitute_id)"
        >
          Deactivate & Replace
        </button>
        <div v-if="mealSubstituteOptions(deactivatingMeal).length > 0">
          <h5 class="mt-3">Recommended Replacements</h5>
          <b-list-group>
            <b-list-group-item
              v-for="meal in mealSubstituteOptions(deactivatingMeal)"
              :active="substitute_id === meal.id"
              @click="
                () => {
                  substitute_id = meal.id;
                }
              "
              :key="meal.id"
              class="mb-1"
            >
              <div class="d-flex align-items-center text-left">
                <img
                  class="mr-2"
                  style="width:65px"
                  :src="meal.image.thumb_url"
                  v-if="meal.image != null && meal.image.thumb_url"
                />
                <div class="flex-grow-1 mr-2">
                  <p>{{ meal.title }}</p>
                  <p class="strong">
                    {{ format.money(meal.price, storeSettings.currency) }}
                  </p>
                </div>
                <b-btn variant="warning">Select</b-btn>
              </div>
            </b-list-group-item>
          </b-list-group>

          <div v-if="mealSubstituteOptions(deactivatingMeal).length <= 0">
            There are currently no substitute options for this meal. Please add
            a similar meal that 1) doesn't contain the same allergies, and 2) is
            within the same meal category.
          </div>

          <button
            class="btn btn-warning btn-lg mt-3"
            @click="deactivateMealModal = false"
          >
            Deactivate & Keep
          </button>
          <button
            v-if="substitute_id"
            class="btn btn-danger btn-lg mt-3"
            @click="deactivateAndReplace(deactivatingMeal.id, substitute_id)"
          >
            Deactivate & Replace
          </button>
        </div>
      </center>
    </b-modal>
  </div>
</template>

<style lang="scss" scoped>
.categories {
  .btn {
    position: relative;

    i {
      position: absolute;
      top: 0;
      right: 0;
      opacity: 0;
    }

    &:hover {
      i {
        opacity: 1;
      }
    }
  }
}
</style>

<script>
import Spinner from "../../components/Spinner";
import IngredientPicker from "../../components/IngredientPicker";
import MealSizes from "../../components/Menu/MealSizes";
import MealComponents from "../../components/Menu/MealComponents";
import MealAddons from "../../components/Menu/MealAddons";
import CreateGiftCardModal from "./Modals/CreateGiftCard";
import CreateMealModal from "./Modals/CreateMeal";
import CreatePackageModal from "./Modals/CreateMealPackage";
import ViewPackageModal from "./Modals/ViewMealPackage";
import ViewGiftCardModal from "./Modals/ViewGiftCard";
import MenuCategoriesModal from "./Modals/MenuCategories";
import moment from "moment";
import tags from "bootstrap-tagsinput";
import { Event } from "vue-tables-2";
import nutritionFacts from "nutrition-label-jquery-plugin";
import PictureInput from "vue-picture-input";
import units from "../../data/units";
import format from "../../lib/format";
import fs from "../../lib/fs.js";
import { mapGetters, mapActions, mapMutations } from "vuex";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import store from "../../store";

export default {
  components: {
    Spinner,
    PictureInput,
    IngredientPicker,
    CreateGiftCardModal,
    CreateMealModal,
    CreatePackageModal,
    ViewPackageModal,
    ViewGiftCardModal,
    MenuCategoriesModal,
    MealSizes,
    MealComponents,
    MealAddons,
    vSelect
  },
  updated() {
    //$(window).trigger("resize");
  },
  data() {
    return {
      _,
      filter: {
        status: "active"
      },
      meal: {
        title: "",
        featured_image: "",
        description: "",
        instructions: "",
        new_category: "",
        tags: "",
        price: "",
        num_orders: "",
        created_at: "",
        categories: [],
        image: {},
        macros: {},
        salesTax: null
      },
      giftCard: {},
      viewGiftCardModal: false,
      editingCategory: false,
      editingCategoryId: null,
      newCategoryName: "",
      showCategoriesModal: false,
      createGiftCardModal: false,
      createMealModal: false,
      createPackageModal: false,
      viewMealModal: false,
      deleteMealModal: false,
      deleteMealModalNonSubstitute: false,
      deactivateMealModal: false,
      viewPackageModal: false,
      deleteMealPackageModal: false,
      mealPackageId: null,
      deletingMeal: {},
      deactivatingMeal: {},
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
        instructions: "",
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
          subscription_count: "Subscriptions",
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
      storeDetail: "storeDetail",
      meals: "storeMeals",
      mealPackages: "mealPackages",
      getMeal: "storeMeal",
      tags: "tags",
      storeCategories: "storeCategories",
      getCategoryTitle: "storeCategoryTitle",
      getAllergyTitle: "storeAllergyTitle",
      allergies: "allergies",
      isLoading: "isLoading",
      storeCurrencySymbol: "storeCurrencySymbol",
      storeModules: "storeModules",
      storeProductionGroups: "storeProductionGroups",
      giftCards: "storeGiftCards"
    }),
    storeURLcheck() {
      let URL = window.location.href;
      let subdomainCheck = URL.substr(0, URL.indexOf("."));
      if (subdomainCheck.includes("goprep")) return true;
      else return false;
    },
    categories() {
      return _.chain(this.storeCategories)
        .orderBy("order")
        .toArray()
        .value();
    },
    storeURL() {
      return (
        "http://" + this.storeDetail.domain + ".goprep.com/store/menu/preview"
      );
    },
    tableData() {
      const packages = Object.values(this.mealPackages).map(mealPackage => {
        return mealPackage;
      });
      const meals = Object.values(this.meals).filter(
        meal => meal.deleted_at === null
      );
      const giftCards = Object.values(this.giftCards).map(mealPackage => {
        return mealPackage;
      });

      return _.concat(packages, meals, giftCards);
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
    },
    productionGroupOptions() {
      let prodGroups = this.storeProductionGroups;
      let prodGroupOptions = [];

      prodGroups.forEach(prodGroup => {
        prodGroupOptions.push({ text: prodGroup.title, value: prodGroup.id });
      });
      return prodGroupOptions;
    }
  },
  created() {
    this.setBagMealPlan(false);

    this.updateMealDescription = _.debounce((id, description) => {
      this.updateMeal(id, { description }, true);
    }, 300);

    this.updateMealInstructions = _.debounce((id, instructions) => {
      this.updateMeal(id, { instructions }, true);
    }, 300);
  },
  mounted() {
    this.onChangeStatusFilter(this.filter.status);
  },
  methods: {
    ...mapActions({
      refreshMeals: "refreshMeals",
      refreshMealPackages: "refreshMealPackages",
      _updateMeal: "updateMeal",
      _updateMealPackage: "updateMealPackage",
      _updateGiftCard: "updateGiftCard",
      refreshCategories: "refreshCategories",
      addJob: "addJob",
      removeJob: "removeJob",
      refreshSubscriptions: "refreshStoreSubscriptions",
      refreshGiftCards: "refreshStoreGiftCards"
    }),
    ...mapMutations({
      setBagMealPlan: "setBagMealPlan"
    }),
    updateCategories() {},
    formatMoney: format.money,
    refreshTable() {
      this.refreshMeals();
      this.refreshMealPackages();
      this.refreshGiftCards();
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, o => {
        return o.id === id;
      });
    },
    forceResize() {
      window.dispatchEvent(new window.Event("resize"));
    },
    async onViewMealModalOk(e) {
      const data = {
        validate_all: true,
        title: this.meal.title,
        description: this.meal.description,
        instructions: this.meal.instructions,
        price: this.meal.price,
        category_ids: this.meal.category_ids,
        ingredients: this.meal.ingredients,
        sizes: this.meal.sizes,
        default_size_title: this.meal.default_size_title,
        components: this.meal.components,
        addons: this.meal.addons,
        macros: this.meal.macros,
        salesTax: this.meal.salesTax
      };
      const updated = await this.updateMeal(this.meal.id, data, true);

      if (updated) {
        this.viewMealModal = false;
      } else {
        e.preventDefault();
      }
    },
    async updateMeal(id, changes, toast = false, updateLocal = true) {
      const i = this.getTableDataIndexById(id);
      if (i === -1) {
        return this.getTableData();
      }
      if (_.isEmpty(changes)) {
        changes = this.editing[id];
      }

      try {
        const meal = await this._updateMeal({ id, data: changes, updateLocal });

        if (toast) {
          this.$toastr.s("Meal updated.");
        }

        if (id === this.meal.id) {
          this.meal = meal;
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
    async updateActive(id, active, isMealPackage = false, isGiftCard = false) {
      const i = _.findIndex(this.tableData, o => {
        return o.id === id && !!o.meal_package === isMealPackage;
      });

      if (i === -1) {
        return this.getTableData();
      }

      if (!isMealPackage && !isGiftCard) {
        await this._updateMeal({ id, data: { active } });
      } else if (isGiftCard) {
        await this._updateGiftCard({ id, data: { active } });
      } else {
        await this._updateMealPackage({ id, data: { active } });
      }

      this.deactivatingMeal = this.getMeal(id);

      if (!this.deactivatingMeal) {
        return;
      }

      if (
        (!this.deactivatingMeal.active && this.deactivatingMeal.substitute) ||
        this.deactivatingMeal.in_package
      ) {
        this.deactivateMealModal = true;
        return;
      }

      if (active) {
        this.$toastr.s("Meal activated!");
      } else {
        this.$toastr.s("Meal deactivated!");
      }
      //this.refreshTable();
    },
    deactivateAndReplace(mealId, substituteId) {
      axios
        .post("/api/me/deactivateAndReplace", {
          mealId: mealId,
          substituteId: substituteId
        })
        .then(resp => {
          this.deactivateMealModal = false;
          this.refreshSubscriptions();
          this.$toastr.s("Meal deactivated and replaced.");
        });
    },
    createMeal() {
      this.createMealModal = true;
    },
    createMealPackage() {
      if (this.meals.length > 0) {
        this.createPackageModal = true;
      } else {
        this.$toastr.e(
          "Please add at least one meal before creating a meal package.",
          "Error"
        );
      }
    },

    async viewMeal(id) {
      this.viewMealModal = false;

      const jobId = await this.addJob();
      axios
        .get(`/api/me/meals/${id}`)
        .then(response => {
          this.meal = response.data;
          if (!response.data.macros) {
            this.meal.macros = {};
          }
          this.ingredients = response.data.ingredient;
          //this.tags = response.data.meal_tag;
          this.mealID = response.data.id;
          this.viewMealModal = true;

          setTimeout(() => {
            window.dispatchEvent(new window.Event("resize"));
          }, 100);
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },
    async viewMealPackage(id) {
      this.viewPackageModal = false;

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

      if (this.deletingMeal.substitute || this.deletingMeal.in_package) {
        this.deleteMealModal = true;
      } else {
        this.deleteMealModalNonSubstitute = true;
      }
    },
    destroyMeal: function(id, subId) {
      axios.delete(`/api/me/meals/${id}?substitute_id=${subId}`).then(resp => {
        this.refreshTable();
        this.refreshSubscriptions();
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
    deleteMealPackage(id) {
      this.mealPackageId = id;
      this.deleteMealPackageModal = true;
    },
    destroyMealPackage() {
      let id = this.mealPackageId;
      axios.delete(`/api/me/packages/${id}`).then(resp => {
        this.refreshTable();
        this.deleteMealPackageModal = false;
        this.$toastr.s("Meal package deleted!");
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
    async changeGalleryImage(val, mealId = null) {
      let gallery = [...this.meal.gallery];

      if (!mealId) {
        let b64 = await fs.getBase64(this.$refs.galleryImageInput.file);
        gallery.push({
          url: b64,
          url_thumb: b64
        });
        this.$refs.galleryImageInput.removeImage();
      } else {
        let b64 = await fs.getBase64(
          this.$refs[`galleryImageInput${mealId}`].file
        );
        gallery.push({
          url: b64,
          url_thumb: b64
        });
        this.$refs[`galleryImageInput${mealId}`].removeImage();
        this.updateMeal(mealId, { gallery }, false, false);
      }
    },
    async deleteGalleryImage(index) {
      let gallery = [];

      if (this.meal.gallery) {
        gallery = [...this.meal.gallery];
        gallery.splice(index, 1);
      }
      this.updateMeal(this.meal.id, { gallery });
    },
    onChangeIngredients(mealId, ingredients) {
      if (!_.isNumber(mealId) || !_.isArray(ingredients)) {
        throw new Error("Invalid ingredients");
      }

      this.updateMeal(mealId, { ingredients }, true);
    },
    onChangeSizes(mealId, sizes) {
      if (!_.isNumber(mealId) || !_.isArray(sizes)) {
        throw new Error("Invalid sizes");
      }

      // Validate all rows
      for (let size of sizes) {
        if (!size.title || !size.price || !size.multiplier) {
          return;
        }
      }

      this.updateMeal(mealId, { sizes }, false);
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
    },

    getMealImage(meal) {
      if (meal.image === null) return null;
      else return meal.image.url_thumb ? meal.image.url_thumb : false;
    },
    async viewGiftCard(id) {
      this.viewGiftCardModal = false;

      const jobId = await this.addJob();
      axios
        .get(`/api/me/giftCards/${id}`)
        .then(response => {
          this.giftCard = response.data;
          this.viewGiftCardModal = true;

          setTimeout(() => {
            window.dispatchEvent(new window.Event("resize"));
          }, 100);
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    }
  }
};
</script>
