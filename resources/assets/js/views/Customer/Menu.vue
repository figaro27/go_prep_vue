<template>
  <div>
    <outside-delivery-area></outside-delivery-area>
    <floating-action-button
      class="d-md-none brand-color"
      to="/customer/bag"
      v-if="!subscriptionId || !adjustOrder"
    >
      <div class="d-flex flex-column h-100">
        <i class="fa fa-shopping-bag text-white"></i>
        <i v-if="total" class="text-white mt-1">{{ total }}</i>
      </div>
    </floating-action-button>

    <meal-components-modal
      ref="componentModal"
      :key="total"
    ></meal-components-modal>

    <meal-package-components-modal
      ref="packageComponentModal"
      :key="total"
    ></meal-package-components-modal>

    <category-slider></category-slider>

    <div class="menu ml-auto mr-auto">
      <div v-if="storeSettings.open === false">
        <div class="row">
          <div class="col-sm-12 mt-3">
            <div class="card">
              <div class="card-body">
                <h5 class="center-text">
                  This company will not be taking new orders at this time.
                </h5>
                <p class="center-text mt-3">
                  <strong>Reason:</strong>
                  {{ storeSettings.closedReason }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-basic">
        <b-modal
          :title="store.details.name"
          size="lg"
          v-model="showDescriptionModal"
          v-if="showDescriptionModal"
        >
          <p v-html="description"></p>
        </b-modal>
      </div>

      <div class="modal-basic">
        <b-modal
          size="lg"
          v-model="viewFilterModal"
          v-if="viewFilterModal"
          hide-header
        >
          <div>
            <h4 class="center-text mb-5 mt-5">Hide Meals That Contain</h4>
          </div>
          <div class="row mb-4">
            <div
              v-for="allergy in allergies"
              :key="`allergy-${allergy.id}`"
              class="filters col-6 col-sm-4 col-md-3 mb-3"
            >
              <b-button
                :pressed="active[allergy.id]"
                @click="filterByAllergy(allergy.id)"
                >{{ allergy.title }}</b-button
              >
            </div>
          </div>
          <hr />
          <div>
            <h4 class="center-text mb-5">Show Meals With</h4>
          </div>
          <div class="row">
            <div
              v-for="tag in tags"
              :key="`tag-${tag}`"
              class="filters col-6 col-sm-4 col-md-3 mb-3"
            >
              <b-button :pressed="active[tag]" @click="filterByTag(tag)">
                {{ tag }}
              </b-button>
            </div>
          </div>
          <b-button
            @click="clearFilters"
            class="center mt-4 brand-color white-text"
            >Clear All</b-button
          >
        </b-modal>
      </div>

      <div class="row">
        <div class="col-sm-12 mt-3">
          <div :class="desktopCard">
            <div :class="desktopCardBody">
              <b-modal
                ref="mealModal"
                size="lg"
                :title="meal.title"
                v-model="mealModal"
                v-if="mealModal"
              >
                <div class="row mt-3">
                  <div class="col-lg-6 modal-meal-image">
                    <thumbnail
                      v-if="meal.image.url"
                      :src="meal.image.url"
                      :aspect="false"
                      width="100%"
                      @click="$refs.lightbox.showImage(0)"
                    ></thumbnail>
                    <img v-else :src="meal.featured_image" />

                    <LightBox
                      ref="lightbox"
                      :images="getMealGallery(meal)"
                      :showLightBox="false"
                    ></LightBox>

                    <slick ref="mealGallery" :options="slickOptions">
                      <div
                        v-for="(image, i) in getMealGallery(meal)"
                        :key="image.id"
                      >
                        <div style="image">
                          <thumbnail
                            v-if="image.url"
                            :src="image.url"
                            :aspect="true"
                            :lazy="false"
                            :spinner="false"
                            :width="'70px'"
                            @click="$refs.lightbox.showImage(i)"
                          ></thumbnail>
                        </div>
                      </div>
                    </slick>

                    <div
                      class="title"
                      v-if="meal.macros && storeSettings.showMacros"
                    >
                      <div class="row">
                        <div class="col-6 col-md-3">
                          <div class="row">
                            <p class="small strong col-6 col-md-12">Calories</p>
                            <p class="small col-6 col-md-12">
                              {{ meal.macros.calories }}
                            </p>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="row">
                            <p class="small strong col-6 col-md-12">Carbs</p>
                            <p class="small col-6 col-md-12">
                              {{ meal.macros.carbs }}
                            </p>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="row">
                            <p class="small strong col-6 col-md-12">Protein</p>
                            <p class="small col-6 col-md-12">
                              {{ meal.macros.protein }}
                            </p>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="row">
                            <p class="small strong col-6 col-md-12">Fat</p>
                            <p class="small col-6 col-md-12">
                              {{ meal.macros.fat }}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <p
                      v-if="storeSettings.showNutrition"
                      v-html="mealDescription"
                    ></p>
                    <div
                      class="row mt-3 mb-5"
                      v-if="storeSettings.showNutrition"
                    >
                      <div class="col-lg-6">
                        <h5>Tags</h5>
                        <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                      </div>
                      <div class="col-lg-6">
                        <h5>Contains</h5>
                        <li v-for="allergy in meal.allergy_titles">
                          {{ allergy }}
                        </li>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6" v-if="storeSettings.showNutrition">
                    <div
                      id="nutritionFacts"
                      ref="nutritionFacts"
                      class="mt-2 mt-lg-0"
                    ></div>

                    <div class="row mt-2" v-if="storeSettings.showNutrition">
                      <div class="col-lg-5 mt-3">
                        <h5>
                          {{ format.money(meal.price, storeSettings.currency) }}
                        </h5>
                      </div>
                      <div class="col-lg-5">
                        <b-btn
                          v-if="meal.sizes.length === 0"
                          @click="addOne(meal)"
                          class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                        <b-dropdown v-else toggle-class="menu-bag-btn">
                          <span slot="button-content">+ ADD</span>
                          <b-dropdown-item @click="addOne(meal)">
                            {{ meal.default_size_title }} -
                            {{
                              format.money(
                                meal.item_price,
                                storeSettings.currency
                              )
                            }}
                          </b-dropdown-item>
                          <b-dropdown-item
                            v-for="size in meal.sizes"
                            :key="size.id"
                            @click="addOne(meal, false, size)"
                          >
                            {{ size.title }} -
                            {{
                              format.money(size.price, storeSettings.currency)
                            }}
                          </b-dropdown-item>
                        </b-dropdown>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6" v-if="!storeSettings.showNutrition">
                    <p v-html="mealDescription"></p>
                    <div class="row">
                      <div class="col-lg-6">
                        <h5>Tags</h5>
                        <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                      </div>
                      <div class="col-lg-6">
                        <h5>Contains</h5>
                        <li v-for="allergy in meal.allergy_titles">
                          {{ allergy }}
                        </li>
                      </div>
                    </div>
                    <div
                      class="row mt-3 mb-3"
                      v-if="storeSettings.showIngredients"
                    >
                      <div class="col-lg-12">
                        <h5>Ingredients</h5>
                        {{ ingredients }}
                      </div>
                    </div>
                    <div class="row mt-5" v-if="storeSettings.showNutrition">
                      <div class="col-lg-8">
                        <h5>
                          {{ format.money(meal.price, storeSettings.currency) }}
                        </h5>
                      </div>
                      <div class="col-lg-4">
                        <b-btn @click="addOne(meal)" class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                      </div>
                    </div>
                    <div class="row mt-5" v-if="!storeSettings.showNutrition">
                      <div class="col-lg-6">
                        <h5>
                          {{ format.money(meal.price, storeSettings.currency) }}
                        </h5>
                      </div>
                      <div class="col-lg-6">
                        <b-btn
                          v-if="meal.sizes.length === 0"
                          @click="addOne(meal)"
                          class="menu-bag-btn"
                          >+ ADD</b-btn
                        >
                        <b-dropdown v-else toggle-class="menu-bag-btn">
                          <span slot="button-content">+ ADD</span>
                          <b-dropdown-item @click="addOne(meal)">
                            {{ meal.default_size_title }} -
                            {{
                              format.money(
                                meal.item_price,
                                storeSettings.currency
                              )
                            }}
                          </b-dropdown-item>
                          <b-dropdown-item
                            v-for="size in meal.sizes"
                            :key="size.id"
                            @click="addOne(meal, false, size)"
                          >
                            {{ size.title }} -
                            {{
                              format.money(size.price, storeSettings.currency)
                            }}
                          </b-dropdown-item>
                        </b-dropdown>
                      </div>
                    </div>
                  </div>
                </div>
              </b-modal>

              <b-modal
                ref="mealPackageModal"
                size="lg"
                :title="mealPackage.title"
                v-model="mealPackageModal"
                v-if="mealPackageModal"
                @shown="$forceUpdate()"
                :hide-footer="true"
              >
                <carousel
                  ref="carousel"
                  :perPage="1"
                  @mounted="
                    () => {
                      loaded = true;
                    }
                  "
                >
                  <slide>
                    <div v-if="loaded" class="row">
                      <div class="col-lg-6 modal-meal-image">
                        <thumbnail
                          v-if="mealPackage.image.url"
                          :src="mealPackage.image.url"
                          :aspect="false"
                          width="100%"
                          :spinner="false"
                          :lazy="false"
                        ></thumbnail>
                        <img v-else :src="mealPackage.featured_image" />
                      </div>
                      <div class="col-lg-6">
                        <div class="modal-meal-package-description mt-2">
                          <p>{{ mealPackage.description }}</p>
                        </div>
                        <div class="modal-meal-package-meals">
                          <h5 class="mt-2">Meals</h5>

                          <li v-for="meal in mealPackage.meals" :key="meal.id">
                            {{ meal.title }} x {{ meal.quantity }}
                          </li>

                          <div class="modal-meal-package-price">
                            <h5 class="mt-3 mb-3">
                              {{
                                format.money(
                                  mealPackage.price,
                                  storeSettings.currency
                                )
                              }}
                            </h5>
                          </div>
                          <b-btn
                            v-if="mealPackage.sizes.length === 0"
                            @click="addOne(mealPackage, true)"
                            class="menu-bag-btn"
                            >+ ADD</b-btn
                          >
                          <b-dropdown v-else toggle-class="menu-bag-btn">
                            <span slot="button-content">+ ADD</span>
                            <b-dropdown-item @click="addOne(mealPackage, true)">
                              {{ mealPackage.default_size_title }} -
                              {{
                                format.money(
                                  mealPackage.price,
                                  storeSettings.currency
                                )
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              v-for="size in mealPackage.sizes"
                              :key="size.id"
                              @click="addOne(mealPackage, true, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>
                        </div>
                      </div>
                    </div>
                  </slide>

                  <slide
                    v-for="meal in mealPackage.meals"
                    v-if="mealPackage.meal_carousel"
                    :key="meal.id"
                    :caption="meal.title"
                  >
                    <div class="row">
                      <div class="col-lg-6 modal-meal-image">
                        <h4 class="center-text">{{ meal.title }}</h4>
                        <thumbnail
                          v-if="meal.image.url"
                          :src="meal.image.url"
                          :aspect="false"
                          width="100%"
                          :lazy="false"
                          :spinner="false"
                        ></thumbnail>
                        <img v-else :src="meal.featured_image" />
                        <p
                          v-if="storeSettings.showNutrition"
                          v-html="mealDescription"
                        ></p>
                        <div
                          class="row mt-3 mb-5"
                          v-if="storeSettings.showNutrition"
                        >
                          <div class="col-lg-6">
                            <h5>Tags</h5>
                            <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                          </div>
                          <div class="col-lg-6">
                            <h5>Contains</h5>
                            <li v-for="allergy in meal.allergy_titles">
                              {{ allergy }}
                            </li>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6" v-if="storeSettings.showNutrition">
                        <div
                          :id="`nutritionFacts${meal.id}`"
                          :ref="`nutritionFacts${meal.id}`"
                          class="mt-2 mt-lg-0"
                        ></div>
                        <b-btn
                          @click="addOne(mealPackage)"
                          class="menu-bag-btn width-80 mt-3"
                          >+ ADD PACKAGE</b-btn
                        >
                      </div>
                      <div class="col-lg-6" v-if="!storeSettings.showNutrition">
                        <p v-html="mealDescription"></p>
                        <div class="row">
                          <div class="col-lg-6">
                            <h5>Tags</h5>
                            <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                          </div>
                          <div class="col-lg-6">
                            <h5>Contains</h5>
                            <li v-for="allergy in meal.allergy_titles">
                              {{ allergy }}
                            </li>
                          </div>
                        </div>
                        <div
                          class="row mt-3 mb-3"
                          v-if="storeSettings.showIngredients"
                        >
                          <div class="col-lg-12">
                            <h5>Ingredients</h5>
                            {{ ingredients }}
                          </div>
                        </div>
                        <b-btn
                          @click="addOne(mealPackage)"
                          class="menu-bag-btn width-80 mt-3"
                          >+ ADD PACKAGE</b-btn
                        >
                      </div>
                    </div>
                  </slide>
                </carousel>
              </b-modal>

              <div class="row">
                <div class="col-sm-12">
                  <div class="row">
                    <div class="col-sm-12 store-logo-area" v-if="!mobile">
                      <a :href="storeWebsite" v-if="storeWebsite != null">
                        <img
                          v-if="storeLogo"
                          class="store-logo"
                          :src="storeLogo.url_thumb"
                          alt="Company Logo"
                        />
                      </a>
                      <img
                        v-if="storeLogo && storeWebsite === null"
                        class="store-logo"
                        :src="storeLogo.url_thumb"
                        alt="Company Logo"
                      />
                    </div>
                    <div class="col-sm-12" v-if="store.details.description">
                      <b-btn
                        @click="showDescription"
                        class="brand-color white-text center mt-3"
                        >About</b-btn
                      >
                    </div>
                    <div class="col-sm-2">
                      <b-form-input
                        type="text"
                        v-model="search"
                        placeholder="Search"
                      />
                    </div>
                    <div class="col-sm-12 category-area">
                      <div class="filter-area">
                        <b-button
                          @click="viewFilters"
                          class="brand-color white-text"
                        >
                          <i class="fa fa-filter"></i>
                          <span class="d-none d-sm-inline">&nbsp;Filters</span>
                        </b-button>
                        <b-button @click="clearFilters" class="gray white-text">
                          <i class="fa fa-eraser"></i>
                          <span class="d-none d-sm-inline"
                            >&nbsp;Clear Filters</span
                          >
                        </b-button>
                      </div>

                      <ul class="d-none d-sm-block">
                        <li
                          v-for="category in categories"
                          :key="category"
                          @click.prevent="goToCategory(slugify(category))"
                          class="d-inline ml-sm-4"
                        >
                          {{ category }}
                        </li>
                      </ul>

                      <div>
                        <b-btn
                          class="gray white-text pull-right"
                          @click="clearAll"
                        >
                          <i class="fa fa-eraser"></i>&nbsp;Clear Bag
                        </b-btn>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div :class="`col-md-9 main-menu-area`">
                  <Spinner
                    v-if="!meals.length && !mealPackages.length"
                    position="absolute"
                  />
                  <div
                    v-for="(group, catIndex) in meals"
                    :key="group.category"
                    :id="slugify(group.category)"
                    v-observe-visibility="
                      (isVisible, entry) =>
                        onCategoryVisible(isVisible, catIndex)
                    "
                    class="categories"
                  >
                    <h2 class="text-center mb-3 dbl-underline">
                      {{ group.category }}
                    </h2>
                    <div class="row">
                      <div
                        class="item col-sm-6 col-lg-4 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3"
                        v-for="(meal, i) in group.meals"
                        :key="meal.id"
                      >
                        <div :class="card">
                          <div :class="cardBody">
                            <div class="item-wrap">
                              <div class="title d-md-none">
                                {{ meal.title }}
                              </div>

                              <div class="image">
                                <thumbnail
                                  v-if="meal.image.url_medium"
                                  :src="meal.image.url_medium"
                                  class="menu-item-img"
                                  width="100%"
                                  @click="showMealModal(meal)"
                                  style="background-color:#ffffff"
                                ></thumbnail>

                                <div class="price">
                                  {{
                                    format.money(
                                      meal.price,
                                      storeSettings.currency
                                    )
                                  }}
                                </div>
                              </div>

                              <div class="meta">
                                <div class="title d-none d-md-block">
                                  {{ meal.title }}
                                </div>
                                <div
                                  class="title"
                                  v-if="meal.macros && storeSettings.showMacros"
                                >
                                  <div class="row">
                                    <div class="col-12 col-md-3">
                                      <div class="row">
                                        <p class="small strong col-6 col-md-12">
                                          Calories
                                        </p>
                                        <p class="small col-6 col-md-12">
                                          {{ meal.macros.calories }}
                                        </p>
                                      </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                      <div class="row">
                                        <p class="small strong col-6 col-md-12">
                                          Carbs
                                        </p>
                                        <p class="small col-6 col-md-12">
                                          {{ meal.macros.carbs }}
                                        </p>
                                      </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                      <div class="row">
                                        <p class="small strong col-6 col-md-12">
                                          Protein
                                        </p>
                                        <p class="small col-6 col-md-12">
                                          {{ meal.macros.protein }}
                                        </p>
                                      </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                      <div class="row">
                                        <p class="small strong col-6 col-md-12">
                                          Fat
                                        </p>
                                        <p class="small col-6 col-md-12">
                                          {{ meal.macros.fat }}
                                        </p>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                <div class="description d-md-none">
                                  {{ meal.description }}
                                </div>

                                <div class="actions">
                                  <div
                                    class="d-flex justify-content-between align-items-center mt-1"
                                  >
                                    <b-btn
                                      @click="minusOne(meal)"
                                      class="plus-minus gray"
                                    >
                                      <i>-</i>
                                    </b-btn>
                                    <b-form-input
                                      type="text"
                                      name
                                      id
                                      class="quantity"
                                      :value="mealQuantity(meal)"
                                      readonly
                                    ></b-form-input>
                                    <b-btn
                                      v-if="meal.sizes.length === 0"
                                      @click="addOne(meal)"
                                      class="menu-bag-btn plus-minus"
                                    >
                                      <i>+</i>
                                    </b-btn>
                                    <b-dropdown
                                      v-else
                                      toggle-class="menu-bag-btn plus-minus"
                                      :right="i > 0 && (i + 1) % 4 === 0"
                                    >
                                      <i slot="button-content">+</i>
                                      <b-dropdown-item @click="addOne(meal)">
                                        {{
                                          meal.default_size_title || "Regular"
                                        }}
                                        -
                                        {{
                                          format.money(
                                            meal.item_price,
                                            storeSettings.currency
                                          )
                                        }}
                                      </b-dropdown-item>
                                      <b-dropdown-item
                                        v-for="size in meal.sizes"
                                        :key="size.id"
                                        @click="addOne(meal, false, size)"
                                      >
                                        {{ size.title }} -
                                        {{
                                          format.money(
                                            size.price,
                                            storeSettings.currency
                                          )
                                        }}
                                      </b-dropdown-item>
                                    </b-dropdown>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    v-if="storeSettings.meal_packages && mealPackages.length"
                    id="Packages"
                  >
                    <h2 class="text-center mb-3 dbl-underline">Packages</h2>

                    <div class="row">
                      <div
                        class="col-sm-6 col-lg-4 col-xl-3"
                        v-for="mealPkg in mealPackages"
                        :key="mealPkg.id"
                      >
                        <thumbnail
                          v-if="mealPkg.image.url_medium"
                          :src="mealPkg.image.url_medium"
                          class="menu-item-img"
                          width="100%"
                          @click="showMealPackageModal(mealPkg)"
                          style="background-color:#ffffff"
                        ></thumbnail>
                        <div
                          class="d-flex justify-content-between align-items-center mb-2 mt-1"
                        >
                          <b-btn
                            @click="minusOne(mealPkg, true)"
                            class="plus-minus gray"
                          >
                            <i>-</i>
                          </b-btn>
                          <b-form-input
                            type="text"
                            name
                            id
                            class="quantity"
                            :value="quantity(mealPkg, true)"
                            readonly
                          ></b-form-input>
                          <b-btn
                            v-if="mealPkg.sizes.length === 0"
                            @click="addOne(mealPkg, true)"
                            class="plus-minus menu-bag-btn"
                          >
                            <i>+</i>
                          </b-btn>
                          <b-dropdown v-else toggle-class="menu-bag-btn">
                            <span slot="button-content">+</span>
                            <b-dropdown-item @click="addOne(mealPkg, true)">
                              {{ mealPkg.default_size_title }} -
                              {{
                                format.money(
                                  mealPkg.price,
                                  storeSettings.currency
                                )
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              v-for="size in mealPkg.sizes"
                              :key="size.id"
                              @click="addOne(mealPkg, true, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>
                        </div>
                        <p class="center-text strong featured">
                          {{ mealPkg.title }}
                        </p>
                        <p class="center-text featured">
                          {{
                            format.money(mealPkg.price, storeSettings.currency)
                          }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- BAG AREA -->
                <div class="col-sm-5 col-md-3 bag-area">
                  <ul class="list-group">
                    <li
                      v-for="(item, mealId) in bag"
                      :key="`bag-${mealId}`"
                      class="bag-item"
                    >
                      <div
                        v-if="item && item.quantity > 0"
                        class="d-flex align-items-center"
                      >
                        <div class="bag-item-quantity mr-2">
                          <div
                            v-if="!item.meal_package"
                            @click="
                              addOne(
                                item.meal,
                                false,
                                item.size,
                                item.components,
                                item.addons
                              )
                            "
                            class="bag-plus-minus brand-color white-text"
                          >
                            <i>+</i>
                          </div>
                          <div
                            v-if="item.meal_package"
                            @click="
                              addOne(
                                item.meal,
                                true,
                                item.size,
                                item.components,
                                item.addons
                              )
                            "
                            class="bag-plus-minus brand-color white-text"
                          >
                            <i>+</i>
                          </div>
                          <p class="bag-quantity">{{ item.quantity }}</p>
                          <div
                            @click="
                              minusOne(
                                item.meal,
                                false,
                                item.size,
                                item.components,
                                item.addons
                              )
                            "
                            class="bag-plus-minus gray white-text"
                          >
                            <i>-</i>
                          </div>
                        </div>
                        <div class="bag-item-image mr-2">
                          <thumbnail
                            :src="item.meal.image.url_thumb"
                            :spinner="false"
                            width="80px"
                          ></thumbnail>
                        </div>
                        <div class="flex-grow-1 mr-2">
                          <span v-if="item.meal_package">{{
                            item.meal.title
                          }}</span>
                          <span v-else-if="item.size">
                            {{ item.size.full_title }}
                          </span>
                          <span v-else>{{ item.meal.item_title }}</span>

                          <ul
                            v-if="item.components || item.addons"
                            class="plain"
                          >
                            <li
                              v-for="component in itemComponents(item)"
                              class="plain"
                            >
                              {{ component }}
                            </li>
                            <li v-for="addon in itemAddons(item)" class="plus">
                              {{ addon }}
                            </li>
                          </ul>
                        </div>
                        <div class="flex-grow-0">
                          <img
                            src="/images/customer/x.png"
                            @click="
                              clearMeal(
                                item.meal,
                                false,
                                item.size,
                                item.components,
                                item.addons
                              )
                            "
                            class="clear-meal"
                          />
                        </div>
                      </div>
                    </li>
                  </ul>

                  <p
                    class="align-right"
                    v-if="
                      minOption === 'meals' &&
                        total < minimumMeals &&
                        !manualOrder
                    "
                  >
                    Please add {{ remainingMeals }} {{ singOrPlural }} to
                    continue.
                  </p>

                  <div
                    v-if="
                      minOption === 'meals' &&
                        total >= minimumMeals &&
                        !manualOrder &&
                        !adjustOrder &&
                        !adjustMealPlan
                    "
                    class="menu-btns-container"
                  >
                    <router-link
                      to="/customer/bag"
                      v-if="!subscriptionId && !manualOrder"
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                    <router-link
                      :to="{
                        name: 'customer-bag',
                        params: { subscriptionId: subscriptionId }
                      }"
                      v-if="subscriptionId"
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                  </div>
                  <div
                    v-if="
                      minOption === 'price' &&
                        totalBagPricePreFees < minPrice &&
                        !manualOrder &&
                        !adjustOrder &&
                        !adjustMealPlan
                    "
                    class="menu-btns-container"
                  >
                    <p class="align-right">
                      Please add
                      {{ format.money(remainingPrice, storeSettings.currency) }}
                      more to continue.
                    </p>
                  </div>
                  <div
                    v-if="
                      minOption === 'price' && totalBagPricePreFees >= minPrice
                    "
                  >
                    <router-link
                      to="/customer/bag"
                      v-if="
                        !subscriptionId &&
                          !manualOrder &&
                          !adjustOrder &&
                          !adjustMealPlan
                      "
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>

                    <router-link
                      :to="{
                        name: 'customer-bag',
                        params: { subscriptionId: subscriptionId }
                      }"
                      v-if="subscriptionId"
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                  </div>
                  <div v-if="adjustOrder">
                    <p v-if="!order.pickup">Delivery Day</p>
                    <p v-if="order.pickup">Pickup Day</p>
                    <b-form-select
                      v-if="adjustOrder"
                      v-model="deliveryDay"
                      :options="deliveryDaysOptions"
                      class="w-100 mb-3"
                    ></b-form-select>
                    <b-btn class="menu-bag-btn" @click="adjust"
                      >ADJUST ORDER</b-btn
                    >
                  </div>
                  <div>
                    <router-link
                      to="/store/bag"
                      v-if="!subscriptionId && manualOrder"
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                  </div>
                  <div>
                    <router-link
                      :to="{
                        name: 'store-bag',
                        params: {
                          subscriptionId: subscription.id,
                          mealPlanAdjustment: true
                        }
                      }"
                      v-if="adjustMealPlan"
                    >
                      <b-btn class="menu-bag-btn">NEXT</b-btn>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style></style>
<style lang="scss" scoped></style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";
import Spinner from "../../components/Spinner";
import MealComponentsModal from "../../components/Modals/MealComponentsModal";
import MealPackageComponentsModal from "../../components/Modals/MealPackageComponentsModal";
import MenuBag from "../../mixins/menuBag";
import units from "../../data/units";
import nutrition from "../../data/nutrition";
import format from "../../lib/format";
import SalesTax from "sales-tax";
import keyboardJS from "keyboardjs";
import LightBox from "vue-image-lightbox";
import "vue-image-lightbox/src/components/style.css";
import { Carousel, Slide } from "vue-carousel";

import CategorySlider from "../../components/Customer/Mobile/CategorySlider";
import OutsideDeliveryArea from "../../components/Customer/OutsideDeliveryArea";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

export default {
  components: {
    Spinner,
    SalesTax,
    LightBox,
    Carousel,
    Slide,
    MealComponentsModal,
    MealPackageComponentsModal,
    CategorySlider,
    OutsideDeliveryArea
  },
  mixins: [MenuBag],
  props: {
    preview: {
      default: false
    },
    manualOrder: {
      default: false
    },
    adjustOrder: {
      default: false
    },
    adjustMealPlan: {
      default: false
    },
    order: {
      default: {}
    },
    subscription: {
      default: {}
    },
    subscriptionId: {
      default: null
    }
  },
  data() {
    return {
      search: "",
      deliveryDay: "",
      slickOptions: {
        slidesToShow: 4,
        infinite: false,
        arrows: true,
        prevArrow:
          '<a class="slick-prev"><i class="fa fa-chevron-left"></i></a>',
        nextArrow:
          '<a class="slick-next"><i class="fa fa-chevron-right"></i></a>'
      },
      mealDescription: "",
      loaded: false,
      active: {},
      loading: false,
      viewFilterModal: false,
      showDescriptionModal: false,
      filteredView: false,
      filters: {
        tags: [],
        allergies: []
      },
      meal: null,
      mealPackage: null,
      ingredients: "",
      mealModal: false,
      mealPackageModal: false,
      calories: null,
      totalfat: null,
      satfat: null,
      transfat: null,
      cholesterol: null,
      sodium: null,
      totalcarb: null,
      fibers: null,
      sugars: null,
      proteins: null,
      vitamind: null,
      potassium: null,
      calcium: null,
      iron: null,
      addedsugars: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    storeId() {
      return this.store.id;
    },
    canProgress() {
      return (
        (this.minOption === "meals" &&
          this.total >= this.minimumMeals &&
          !this.preview) ||
        (this.minOption === "price" &&
          this.totalBagPricePreFees >= this.minPrice &&
          !this.preview)
      );
    },
    card() {
      if (this.mobile) {
        return "card border-light mb-0 mt-0 mr-1";
      } else return "";
    },
    cardBody() {
      if (this.mobile) {
        return "card-body border-light mb-0 mt-0 mr-1";
      } else return "";
    },
    desktopCard() {
      if (!this.mobile) {
        return "card";
      } else return "";
    },
    desktopCardBody() {
      if (!this.mobile) {
        return "card-body";
      } else return "";
    },
    storeWebsite() {
      if (!this.storeSettings.website) {
        return null;
      } else {
        let website = this.storeSettings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        return website;
      }
    },
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    description() {
      return this.store.details.description;
    },
    nutrition() {
      return nutrition;
    },
    storeSettings() {
      return this.store.settings;
    },
    minimumOption() {
      return this.minOption;
    },
    minimumMeals() {
      return this.minMeals;
    },
    minimumPrice() {
      return this.minPrice;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "meals";
      }
      return "meal";
    },
    meals() {
      let meals = this.store.meals;
      let filters = this.filters;
      let grouped = {};

      if (!_.isArray(meals)) {
        return [];
      }

      const search = this.search.toLowerCase();

      // Meal filtering logic
      meals = _.filter(meals, meal => {
        if (
          !meal.active ||
          (this.search && !meal.title.toLowerCase().includes(search))
        ) {
          return false;
        }

        return true;
      });

      if (this.filteredView) {
        meals = _.filter(meals, meal => {
          let skip = false;

          if (!skip && filters.tags.length > 0) {
            let hasAllTags = _.reduce(
              filters.tags,
              (has, tag) => {
                if (!has) return false;
                let x = _.includes(meal.tag_titles, tag);

                return x;
              },
              true
            );

            skip = !hasAllTags;
          }

          if (!skip && filters.allergies.length > 0) {
            let hasAllergy = _.reduce(
              meal.allergy_ids,
              (has, allergyId) => {
                if (has) return true;
                let x = _.includes(filters.allergies, allergyId);

                return x;
              },
              false
            );

            skip = hasAllergy;
          }
          return !skip;
        });
      }

      meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (!category) {
            return;
          } else if (!_.has(grouped, category.category)) {
            grouped[category.category] = [meal];
          } else {
            grouped[category.category].push(meal);
          }
        });
      });

      // Find store-defined category sorting
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      // Structure
      grouped = _.map(grouped, (meals, cat) => {
        return {
          category: cat,
          meals,
          order: sorting[cat] || 9999
        };
      });

      // Sort
      return _.orderBy(grouped, "order");
    },
    mealPackages() {
      return _.map(
        _.filter(this.store.packages, mealPackage => {
          return mealPackage.active;
        }) || [],
        mealPackage => {
          mealPackage.meal_package = true;
          return mealPackage;
        }
      );
    },
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (category && !_.includes(grouped, category.category)) {
            grouped.push(category.category);
          }
        });
      });

      let categories = _.orderBy(grouped, cat => {
        return cat in sorting ? sorting[cat] : 9999;
      });

      if (this.storeSettings.meal_packages && this.mealPackages.length) {
        categories.push("Packages");
      }

      return categories;
    },
    tags() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.tags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      });
      return grouped;
    },
    showIngredients() {
      return this.storeSettings.showIngredients;
    }
  },
  mounted() {
    keyboardJS.bind("left", () => {
      if (this.$refs.carousel) {
        console.log(this.$refs.carousel);
        this.$refs.carousel.handleNavigation("backward");
      }
    });
    keyboardJS.bind("right", () => {
      if (this.$refs.carousel) {
        this.$refs.carousel.handleNavigation("forward");
      }
    });
    this.setPickupIfMealPlan();
  },
  beforeDestroy() {
    this.showActiveFilters();
  },
  methods: {
    ...mapActions([
      "refreshSubscriptions",
      "emptyBag",
      "refreshUpcomingOrders"
    ]),
    ...mapMutations(["emptyBag", "setBagMealPlan", "setBagCoupon"]),
    onCategoryVisible(isVisible, index) {
      if (isVisible && this.$refs.categorySlider) {
        this.$refs.categorySlider.goTo(index);
      }
    },
    showActiveFilters() {
      let tags = this.tags;
      this.active = tags.reduce((acc, tag) => {
        acc[tag] = false;
        return acc;
      }, {});

      let allergies = this.allergies;
      this.active = _.reduce(
        allergies,
        (acc, allergy) => {
          acc[allergy] = false;
          return acc;
        },
        {}
      );
    },
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    showMealModal(meal) {
      this.meal = meal;
      this.mealModal = true;
      this.mealDescription = meal.description
        ? meal.description.replace(/\n/g, "<br>")
        : "";

      this.$nextTick(() => {
        this.getNutritionFacts(this.meal.ingredients, this.meal);
        this.$refs.mealGallery.reSlick();
      });
    },
    hideMealModal() {
      this.mealModal = false;

      // Ensure modal is fully closed
      return new Promise(resolve => {
        this.$nextTick(resolve);
      });
    },
    showMealPackageModal(mealPackage) {
      this.mealPackage = { ...mealPackage };
      this.mealPackageModal = true;

      this.$nextTick(() => {
        this.mealPackage.meals.forEach(meal => {
          this.getNutritionFacts(
            meal.ingredients,
            meal,
            this.$refs[`nutritionFacts${meal.id}`]
          );
        });
      });
    },
    hideMealPackageModal() {
      this.mealPackageModal = false;

      // Ensure modal is fully closed
      return new Promise(resolve => {
        this.$nextTick(resolve);
      });
    },
    getNutritionFacts(ingredients, meal, ref = null) {
      const nutrition = this.nutrition.getTotals(ingredients);
      const ingredientList = this.nutrition.getIngredientList(ingredients);

      if (!ref) {
        ref = this.$refs.nutritionFacts;
      }

      $(ref).html("");

      $(ref).nutritionLabel({
        showServingUnitQuantity: false,
        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: this.showIngredients,

        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,

        allowFDARounding: true,
        decimalPlacesForNutrition: 2,

        showPolyFat: false,
        showMonoFat: false,

        valueCalories: nutrition.calories,
        valueFatCalories: nutrition.fatCalories,
        valueTotalFat: nutrition.totalFat,
        valueSatFat: nutrition.satFat,
        valueTransFat: nutrition.transFat,
        valueCholesterol: nutrition.cholesterol,
        valueSodium: nutrition.sodium,
        valueTotalCarb: nutrition.totalCarb,
        valueFibers: nutrition.fibers,
        valueSugars: nutrition.sugars,
        valueProteins: nutrition.proteins,
        valueVitaminD: (nutrition.vitaminD / 20000) * 100,
        valuePotassium_2018: (nutrition.potassium / 4700) * 100,
        valueCalcium: (nutrition.calcium / 1300) * 100,
        valueIron: (nutrition.iron / 18) * 100,
        valueAddedSugars: nutrition.addedSugars,
        showLegacyVersion: false
      });
    },
    filterByCategory(category) {
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.categories, cat => {
        return cat === category;
      });

      i === -1
        ? this.filters.categories.push(category)
        : Vue.delete(this.filters.categories, i);
    },
    filterByTag(tag) {
      this.active[tag] = !this.active[tag];
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.tags, _tag => {
        return tag === _tag;
      });

      i === -1 ? this.filters.tags.push(tag) : Vue.delete(this.filters.tags, i);
    },
    filterByAllergy(allergyId) {
      Vue.set(this.active, allergyId, !this.active[allergyId]);
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.allergies, _allergyId => {
        return _allergyId === allergyId;
      });

      if (i === -1) {
        let allergies = [...this.filters.allergies];
        allergies.push(allergyId);
        Vue.set(this.filters, "allergies", allergies);
      } else {
        Vue.delete(this.filters.allergies, i);
      }
    },
    goToCategory(category) {
      if ($("#xs").is(":visible") || $("#sm").is(":visible")) {
        const top = $(`#${category}`).offset().top;
        $(document).scrollTop(top - 90);
      } else {
        $(".main-menu-area").scrollTop(0);
        const top = $(`#${category}`).position().top;
        $(".main-menu-area").scrollTop(top);
      }
    },
    viewFilters() {
      this.viewFilterModal = true;
    },
    clearFilters() {
      let allergies = this.filters.allergies;
      _.remove(allergies, allergy => _.includes(allergies, allergy));

      let tags = this.filters.tags;
      _.remove(tags, tag => _.includes(tags, tag));

      this.active = _.mapValues(this.active, () => false);
      this.filteredView = false;
    },
    showDescription() {
      this.showDescriptionModal = true;
    },
    getMealGallery(meal) {
      return meal.gallery.map((item, i) => {
        return {
          id: i,
          url: item.url_original,
          src: item.url_original,
          thumb: item.url_thumb
        };
      });
    },
    addMealOrdersToBag() {
      //conact item with meal
      this.order.items.forEach(item => {
        const meal = this.getMeal(item.meal_id);

        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.groupBy(item.components, "meal_component_id"),
          choices => {
            return _.map(choices, "meal_component_option_id");
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(meal, false, item.meal_size_id, components, addons);
        }
      });
    },
    addMealsSubscriptionToBag() {
      //conact item with meal
      this.subscription.items.forEach(item => {
        const meal = this.getMeal(item.meal_id);

        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.groupBy(item.components, "meal_component_id"),
          choices => {
            return _.map(choices, "meal_component_option_id");
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(meal, false, item.meal_size_id, components, addons);
        }
      });
    },
    async adjust() {
      axios
        .post(`/api/me/orders/adjustOrder`, {
          bag: this.bag,
          orderId: this.order.id,
          deliveryDate: this.deliveryDay
        })
        .then(resp => {
          this.$toastr.s("Order Adjusted");
          this.$router.push({ path: "/store/orders" });
          this.refreshUpcomingOrders();
        });
    }
  }
};
</script>
