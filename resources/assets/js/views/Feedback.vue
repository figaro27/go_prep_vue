<template>
  <div>
    <div class="main-customer-container box-shadow top-fill">
      <div class="row" v-if="!responded && !showThankYou">
        <div class="col-md-12" v-if="order">
          <p class="strong font-20">Hi {{ order.customer_firstname }},</p>
          <p class="font-17">
            Thank you for your order #{{ order.order_number }} from
            {{ order.store_name }} delivered on
            {{ moment(order.delivery_date).format("MM/DD/YYYY") }}.
          </p>
          <p class="font-17">
            We'd really appreciate if you took a few moments to fill out this
            survey below in order to help us try to improve the quality of our
            goods and services provided to you.
          </p>
        </div>
        <div class="col-md-12 mt-1" v-if="surveyQuestions">
          <b-form @submit.prevent="submitSurvey">
            <li
              v-for="(question, id) in surveyQuestions"
              :key="question.id"
              class="mb-2"
              v-if="question.context === 'order' && conditionMet(question)"
            >
              <div class="strong mb-3 pt-2">
                {{ question.question }}
              </div>

              <b-form-group class="surveyLabel">
                <b-form-textarea
                  v-if="question.type === 'Text'"
                  v-model="surveyResponses[question.id]"
                  rows="3"
                  required
                />
                <!-- <b-form-rating
                  v-if="question.type === 'Rating'"
                  v-model="surveyResponses[question.id]"
                  :stars="question.max"
                  show-value
                  inline
                  no-border
                  variant="warning"
                  class="stars"
                  size="lg"
                  required
                /> -->
                <b-form-group v-if="question.type === 'Rating'">
                  <b-form-radio-group
                    v-model="surveyResponses[question.id]"
                    :options="ratingOptions(question)"
                    required
                  ></b-form-radio-group>
                </b-form-group>
                <b-form-radio-group
                  v-if="question.type === 'Selection' && question.limit"
                  :options="getRadioOptions(question)"
                  v-model="surveyResponses[question.id]"
                  required
                />

                <b-form-checkbox-group
                  v-if="question.type === 'Selection' && !question.limit"
                  v-model="surveyResponses[question.id]"
                  :options="question.options"
                />
              </b-form-group>
            </li>
            <div v-if="surveyItemQuestions.length > 0" class="mt-3">
              <hr class="pt-3" />
              <li
                v-for="(question, index) in surveyItemQuestions"
                v-if="itemConditionMet(question)"
              >
                <div
                  class="strong font-17 mb-3 pt-2"
                  v-html="question.item"
                  v-if="question.first"
                />
                <b-form-group>
                  <div class="strong mt-2">{{ question.question }}</div>
                  <b-form-textarea
                    v-if="question.type === 'Text'"
                    v-model="
                      surveyItemResponses[
                        question.item + ' | Question ID: ' + question.id
                      ]
                    "
                    required
                  />
                  <!-- <b-form-rating
                    v-if="question.type === 'Rating'"
                    v-model="
                      surveyItemResponses[
                        question.item + ' | Question ID: ' + question.id
                      ]
                    "
                    :stars="question.max"
                    show-value
                    inline
                    no-border
                    variant="warning"
                    class="stars"
                    size="lg"
                    required
                  ></b-form-rating> -->
                  <b-form-group v-if="question.type === 'Rating'">
                    <b-form-radio-group
                      v-model="
                        surveyItemResponses[
                          question.item + ' | Question ID: ' + question.id
                        ]
                      "
                      :options="ratingOptions(question)"
                      required
                    ></b-form-radio-group>
                  </b-form-group>

                  <b-form-radio-group
                    v-if="question.type === 'Selection' && question.limit"
                    :options="getRadioOptions(question)"
                    v-model="
                      surveyItemResponses[
                        question.item + ' | Question ID: ' + question.id
                      ]
                    "
                    required
                  />

                  <b-form-checkbox-group
                    v-if="question.type === 'Selection' && !question.limit"
                    v-model="
                      surveyItemResponses[
                        question.item + ' | Question ID: ' + question.id
                      ]
                    "
                    :options="question.options"
                  />
                </b-form-group>
              </li>
            </div>

            <b-btn variant="primary" type="submit" class="mt-3">Submit</b-btn>
          </b-form>
        </div>
      </div>
      <div v-if="!responded && showThankYou">
        <p class="d-flex d-center">Thank you for submitting your feedback.</p>
        <p
          class="d-flex d-center link-color"
          @click="$router.replace('/customer/menu')"
        >
          Please check out our menu here.
        </p>
      </div>
      <div v-if="responded">
        <p class="d-flex d-center">
          You have already submitted feedback for this order.
        </p>
        <p
          class="d-flex d-center link-color"
          @click="$router.replace('/customer/menu')"
        >
          Please check out our menu here.
        </p>
      </div>
      <div v-if="userMismatch">
        <p class="d-flex d-center">
          Please log into the user account associated with this order to give
          feedback.
        </p>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from "../lib/format.js";
export default {
  components: {},
  data() {
    return {
      order: null,
      surveyQuestions: null,
      surveyResponses: {},
      surveyItemQuestions: [],
      surveyItemResponses: {},
      showThankYou: false,
      responded: false,
      userMismatch: false
    };
  },
  created() {},
  mounted() {
    this.loadSurvey();
  },
  methods: {
    ...mapMutations(["setViewedStore"]),
    loadSurvey() {
      let orderNumber = this.$route.query.order;
      axios
        .post("api/guest/getSurvey", { orderNumber: orderNumber })
        .then(resp => {
          if (resp.data === "responded") {
            this.responded = true;
            return;
          }
          if (this.user.id && this.user.id !== resp.data.order.user_id) {
            this.userMismatch = true;
            return;
          }
          this.order = resp.data.order;
          this.surveyQuestions = this.orderSurveyQuestions(
            resp.data.surveyQuestions
          );
          this.addSurveyItemQuestions();
        });
    },
    orderSurveyQuestions(questions) {
      let orderedQuestions = [];
      questions.forEach(question => {
        if (!question.condition_question_id) {
          orderedQuestions.push(question);
        } else {
          let relatedQuestion = questions.find(q => {
            return q.id === question.condition_question_id;
          });
          let index = orderedQuestions.indexOf(relatedQuestion);
          orderedQuestions.splice(index + 1, 0, question);
        }
      });
      return orderedQuestions;
    },
    addSurveyItemQuestions() {
      let items = [];
      let itemQuestions = [];
      this.orderItems.forEach((item, parent_index) => {
        this.surveyQuestions.forEach((question, index) => {
          if (question.context === "items" && !question.conditional) {
            if (!items.includes(item)) {
              question.first = true;
            }
            items.push(item);

            let itemQuestion = { ...question };
            itemQuestion.item = item.meal;

            itemQuestions.push(itemQuestion);
          }
        });
      });

      this.orderItems.forEach((item, parent_index) => {
        this.surveyQuestions.forEach((question, index) => {
          let newQuestion = { ...question };
          if (newQuestion.context === "items" && newQuestion.conditional) {
            newQuestion.item = item.meal;
            let relatedQuestion = itemQuestions.find(q => {
              return (
                q.id === newQuestion.condition_question_id &&
                q.item === newQuestion.item
              );
            });

            let index = itemQuestions.indexOf(relatedQuestion);

            itemQuestions.splice(index + 1, 0, newQuestion);
          }
        });
      });
      console.log(itemQuestions);
      this.surveyItemQuestions = itemQuestions;
    },
    getRadioOptions(question) {
      let options = [];
      question.options.forEach(option => {
        options.push({ value: option, text: option });
      });
      return options;
    },
    submitSurvey() {
      axios
        .post("api/guest/submitSurvey", {
          surveyResponses: this.surveyResponses,
          surveyItemResponses: this.surveyItemResponses,
          orderId: this.order.id
        })
        .then(resp => {
          this.showThankYou = true;
        });
    },
    conditionMet(question) {
      if (question.condition_question_id) {
        let conditionQuestion = this.surveyQuestions.find(q => {
          return q.id === question.condition_question_id;
        });
        let conditionQuestionResponse = this.surveyResponses[
          conditionQuestion.id
        ];

        if (!question.rating_condition) {
          if (!conditionQuestion.limit) {
            if (
              conditionQuestionResponse &&
              conditionQuestionResponse.includes(question.condition_value)
            ) {
              return true;
            }
          } else {
            if (question.condition_value === conditionQuestionResponse) {
              return true;
            }
          }
        } else {
          let passed = false;
          switch (question.rating_condition) {
            case "Less than":
              passed =
                conditionQuestionResponse < question.condition_value
                  ? true
                  : false;
              break;
            case "Equal to":
              passed =
                conditionQuestionResponse == question.condition_value
                  ? true
                  : false;
              break;
            case "Greater than":
              passed =
                conditionQuestionResponse > question.condition_value
                  ? true
                  : false;
              break;
          }
          return passed;
        }
      } else {
        return true;
      }
    },
    itemConditionMet(question) {
      if (question.condition_question_id) {
        let conditionQuestion = this.surveyItemQuestions.find(q => {
          return q.id === question.condition_question_id;
        });
        let index =
          question.item + " | Question ID: " + question.condition_question_id;
        let conditionQuestionResponse = this.surveyItemResponses[index];

        if (!question.rating_condition) {
          if (question.condition_value === conditionQuestionResponse) {
            return true;
          }
        } else {
          let passed = false;
          switch (question.rating_condition) {
            case "Less than":
              passed =
                conditionQuestionResponse < question.condition_value
                  ? true
                  : false;
              break;
            case "Equal to":
              passed =
                conditionQuestionResponse == question.condition_value
                  ? true
                  : false;
              break;
            case "Greater than":
              passed =
                conditionQuestionResponse > question.condition_value
                  ? true
                  : false;
              break;
          }
          return passed;
        }
      } else {
        return true;
      }
    },
    ratingOptions(question) {
      let options = [];
      for (let i = 1; i <= question.max; i++) {
        options.push({
          text: i,
          value: i
        });
      }
      return options;
    }
  },
  computed: {
    ...mapGetters({
      user: "user",
      store: "viewedStore"
    }),
    orderItems() {
      let data = [];
      let order = this.order;

      // Sorting items by delivery dates
      let meal_package_items = _.orderBy(
        order.meal_package_items,
        "delivery_date"
      );
      let items = _.orderBy(order.items, "delivery_date");

      meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            delivery_date: moment(meal_package_item.delivery_date).format(
              "dddd, MMM Do"
            ),
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package.default_size_title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            ),
            meal_package: true
          });
        } else {
          data.push({
            delivery_date: moment(meal_package_item.delivery_date).format(
              "dddd, MMM Do"
            ),
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package_size.title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(meal_package_item.price, order.currency),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              order.currency
            ),
            meal_package: true
          });
        }
        items.forEach(item => {
          if (
            item.meal_package_order_id === meal_package_item.id &&
            !item.hidden
          ) {
            const title = item.html_title;

            data.push({
              // delivery_date: item.delivery_date
              //   ? moment(item.delivery_date.date).format("dddd, MMM Do")
              //   : null,
              delivery_date: item.delivery_date
                ? moment(item.delivery_date.date).format("dddd, MMM Do")
                : null,
              //meal: meal.title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal:
                item.added_price > 0
                  ? "In Package " +
                    "(" +
                    this.store.settings.currency_symbol +
                    item.added_price +
                    ")"
                  : "In Package"
            });
          }
        });
      });

      items.forEach(item => {
        if (item.meal_package_order_id === null && !item.hidden) {
          const title = item.customTitle ? item.customTitle : item.html_title;
          data.push({
            delivery_date: item.delivery_date
              ? moment(item.delivery_date.date).format("dddd, MMM Do")
              : null,
            //meal: meal.title,
            meal: title,
            quantity: item.quantity,
            unit_price:
              item.attached || item.free
                ? "Included"
                : format.money(item.unit_price, order.currency),
            subtotal:
              item.attached || item.free
                ? "Included"
                : format.money(item.price, order.currency)
          });
        }
      });

      order.line_items_order.forEach(lineItem => {
        data.push({
          delivery_date: lineItem.delivery_date
            ? moment(item.delivery_date.date).format("dddd, MMM Do")
            : null,
          size: lineItem.size,
          meal: lineItem.title,
          quantity: lineItem.quantity,
          unit_price: format.money(lineItem.price, order.currency),
          subtotal: format.money(
            lineItem.price * lineItem.quantity,
            order.currency
          )
        });
      });

      return _.filter(data);
    }
  }
};
</script>
