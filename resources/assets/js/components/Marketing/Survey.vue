<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <Spinner v-if="isLoading" />
      <b-modal
        v-model="showResponseModal"
        v-if="showResponseModal"
        size="md"
        hide-header
        hide-footer
      >
        <div class="m-3">
          <li v-for="(response, index) in groupedResponses">
            <div v-if="!response.item_name" class="pb-3">
              <div class="strong font-14">{{ response.survey_question }}</div>
              <div v-if="isArray(response.response)">
                {{ formatArray(response.response) }}
              </div>
              <div v-else>{{ response.response }}</div>
              <hr />
            </div>
          </li>
          <li v-for="(response, index) in groupedResponses">
            <div v-if="response.item_name" class="pb-3">
              <p
                class="strong font-17"
                v-html="response.item_name"
                v-if="response.first_instance"
              />
              <div class="strong font-14">{{ response.survey_question }}</div>
              <div v-if="isArray(response.response)">
                {{ formatArray(response.response) }}
              </div>
              <div v-else>{{ response.response }}</div>
              <hr v-if="response.last_instance" />
            </div>
          </li>
        </div>
      </b-modal>
      <b-modal
        v-model="showAddQuestionModal"
        v-if="showAddQuestionModal"
        size="md"
        hide-header
        hide-footer
      >
        <b-form @submit.prevent="addQuestion()">
          <div v-if="nonTextQuestions.length > 0" class="mt-3">
            <p class="mr-1">
              Conditional
              <img
                v-b-popover.hover="
                  'Only show this question if a previous question is answered a certain way.'
                "
                title="Conditional"
                src="/images/store/popover.png"
                class="popover-size"
              />
            </p>

            <c-switch
              color="success"
              variant="pill"
              v-model="newQuestion.conditional"
            />
            <div v-if="newQuestion.conditional" class="d-flex">
              <b-form-select
                v-model="newQuestion.condition_question_id"
                :options="questionOptions"
                class=" pl-1 mr-3 w-180"
                @change="setSelectedConditionQuestion(newQuestion)"
              />
              <b-form-select
                class="pl-1 mr-3 w-180"
                v-model="newQuestion.rating_condition"
                :options="conditionRatingOptions"
                v-if="selectedConditionQuestion.type === 'Rating'"
              />
              <b-form-input
                v-if="selectedConditionQuestion.type === 'Rating'"
                v-model="newQuestion.condition_value"
                class="w-100"
                placeholder="Rating"
              />
              <b-form-select
                class="pl-1 mr-3 w-180"
                v-model="newQuestion.condition_value"
                :options="conditionValueOptions"
                v-if="selectedConditionQuestion.type === 'Selection'"
              />
            </div>
          </div>
          <div v-if="!newQuestion.conditional">
            <p class="mt-3">
              Context (Order or Items)
              <img
                v-b-popover.hover="
                  'Make this question ask about the entire order or ask this same question for each item in the order?'
                "
                title="Order or Items"
                src="/images/store/popover.png"
                class="popover-size"
              />
            </p>
            <b-form-radio-group
              class="mb-3"
              v-model="newQuestion.context"
              :options="[
                { value: 'order', text: 'Order' },
                { value: 'items', text: 'Items' }
              ]"
            ></b-form-radio-group>
          </div>
          <p class="mt-3">
            Question Type
            <img
              v-b-popover.hover="
                'Text: lets customers type their response into a text box. Rating: lets customers select one number from a maximum of your choosing. Selection: lets customers choose one or more responses from your choices.'
              "
              title="Question Type"
              src="/images/store/popover.png"
              class="popover-size"
            />
          </p>
          <b-form-radio-group
            class="mb-3"
            v-model="newQuestion.type"
            :options="[
              { value: 'Text', text: 'Text' },
              { value: 'Rating', text: 'Rating' },
              { value: 'Selection', text: 'Selection' }
            ]"
          ></b-form-radio-group>
          <b-form-group label="Question" class="mt-3">
            <b-form-input
              v-model="newQuestion.question"
              :placeholder="textPlaceholder"
              required
            ></b-form-input>
          </b-form-group>
          <div v-if="newQuestion.type === 'Selection'">
            <p>
              <span class="mr-1">Limit to 1</span>
              <img
                v-b-popover.hover="
                  'Limit the response to 1. You can generally use this for Yes or No questions.'
                "
                title="Limit to 1"
                src="/images/store/popover.png"
                class="popover-size"
              />
            </p>
            <c-switch
              color="success"
              variant="pill"
              v-model="newQuestion.limit"
            />
          </div>
          <div v-if="newQuestion.type == 'Rating'" class="d-flex">
            <div>
              <p>Maximum Rating</p>
              <b-form-input
                v-model="newQuestion.max"
                class="w-100px"
                type="number"
                min="1"
                placeholder="10"
              ></b-form-input>
            </div>
          </div>
          <div v-if="newQuestion.type === 'Selection'" class="mt-3">
            <b-btn
              variant="success"
              @click="newQuestion.options.push('')"
              class="mb-3"
              >Add Option</b-btn
            >
            <li v-for="(option, index) in newQuestion.options" class="mb-2">
              <b-form-input
                type="text"
                v-model="newQuestion.options[index]"
                required
                placeholder="Option"
              ></b-form-input>
            </li>
          </div>
          <p></p>
          <div class="d-flex">
            <b-btn
              variant="secondary"
              class="mt-3 mr-2"
              @click="showAddQuestionModal = false"
              >Back</b-btn
            >
            <b-btn variant="primary" type="submit" class="mt-3">Add</b-btn>
          </div>
        </b-form>
      </b-modal>

      <b-modal
        v-model="showEditQuestionModal"
        v-if="showEditQuestionModal"
        size="md"
        hide-header
        hide-footer
      >
        <b-form @submit.prevent="updateQuestion()">
          <div v-if="questions.length > 0" class="mt-3">
            <p class="mr-1">
              Conditional
              <img
                v-b-popover.hover="
                  'Only show this question if a previous question is answered a certain way.'
                "
                title="Conditional"
                src="/images/store/popover.png"
                class="popover-size"
              />
            </p>
            <c-switch
              color="success"
              variant="pill"
              v-model="question.conditional"
            />
            <div v-if="question.conditional" class="d-flex">
              <b-form-select
                v-model="question.condition_question_id"
                :options="questionOptions"
                class=" pl-1 mr-3 w-180"
                @change="setSelectedConditionQuestion(question)"
              />
              <b-form-select
                class="pl-1 mr-3 w-180"
                v-model="question.rating_condition"
                :options="conditionRatingOptions"
                v-if="selectedConditionQuestion.type === 'Rating'"
              />
              <b-form-input
                v-if="selectedConditionQuestion.type === 'Rating'"
                v-model="question.condition_value"
                class="w-100"
                placeholder="Rating"
              />
              <b-form-select
                class="pl-1 mr-3 w-180"
                v-model="question.condition_value"
                :options="conditionValueOptions"
                v-if="selectedConditionQuestion.type === 'Selection'"
              />
            </div>
          </div>
          <p class="mt-3">
            Context (Order or Items)
            <img
              v-b-popover.hover="
                'Make this question ask about the entire order or ask this same question for each item in the order?'
              "
              title="Order or Items"
              src="/images/store/popover.png"
              class="popover-size"
            />
          </p>
          <div v-if="!question.conditional">
            <b-form-radio-group
              class="mb-3"
              v-model="question.context"
              :options="[
                { value: 'order', text: 'Order' },
                { value: 'items', text: 'Items' }
              ]"
            ></b-form-radio-group>
          </div>
          <p class="mt-3">
            Question Type
            <img
              v-b-popover.hover="
                'Text: lets customers type their response into a text box. Rating: lets customers select one number from a minimum / maximum of your choosing. Selection: lets customers choose one or more responses from your choices.'
              "
              title="Question Type"
              src="/images/store/popover.png"
              class="popover-size"
            />
          </p>
          <b-form-radio-group
            class="mb-3"
            v-model="question.type"
            :options="[
              { value: 'Text', text: 'Text' },
              { value: 'Rating', text: 'Rating' },
              { value: 'Selection', text: 'Selection' }
            ]"
          ></b-form-radio-group>
          <b-form-group label="Question" class="mt-3">
            <b-form-input
              v-model="question.question"
              placeholder="What did you think of your order?"
              required
            ></b-form-input>
          </b-form-group>

          <div v-if="question.type === 'Selection'">
            <p>
              <span class="mr-1">Limit to 1</span>
              <img
                v-b-popover.hover="
                  'Limit the response to 1. You can generally use this for Yes or No questions.'
                "
                title="Limit to 1"
                src="/images/store/popover.png"
                class="popover-size"
              />
            </p>
            <c-switch color="success" variant="pill" v-model="question.limit" />
          </div>
          <div v-if="question.type === 'Rating'" class="d-flex">
            <div>
              <p>Maximum Rating</p>
              <b-form-input
                v-model="question.max"
                class="w-100px"
                type="number"
                min="1"
                placeholder="10"
              ></b-form-input>
            </div>
          </div>
          <div v-if="question.type === 'Selection'" class="mt-3">
            <b-btn
              variant="success"
              @click="question.options.push('')"
              class="mb-3"
              >Add Option</b-btn
            >
            <li v-for="(option, index) in question.options" class="mb-2">
              <b-form-input
                type="text"
                v-model="question.options[index]"
                required
                placeholder="Option"
              ></b-form-input>
            </li>
          </div>
          <div class="d-flex">
            <b-btn
              variant="secondary"
              class="mt-3 mr-2"
              @click="showEditQuestionModal = false"
              >Back</b-btn
            >
            <b-btn variant="primary" type="submit" class="mt-3">Update</b-btn>
          </div>
        </b-form>
      </b-modal>

      <b-form @submit.prevent="updateStoreModules(true)">
        <p class="mt-2">
          <span class="mr-1 mt-2">Enable Customer Survey</span>
          <img
            v-b-popover.hover="
              'Automatically send your customer an email a certain amount of time after each order which links to a survey allowing the customer to give you feedback on their order.'
            "
            title="Customer Survey"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="storeModules.customerSurvey"
          @change.native="updateStoreModules()"
        />
      </b-form>

      <div v-if="storeModules.customerSurvey">
        <p class="mt-2">
          <span class="mr-1 mt-2">Automatically Email Me Responses</span>
          <img
            v-b-popover.hover="
              'They will also always be shown in the table below.'
            "
            title="Email Survey Responses"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <c-switch
          color="success"
          variant="pill"
          size="lg"
          v-model="storeModuleSettings.emailSurveyResponses"
          @change.native="updateStoreModules(false, true)"
        />
        <p class="mt-2">
          <span class="mr-1 mt-2">Days After Delivery to Email Customer</span>
          <img
            v-b-popover.hover="
              'Automatically send out the survey this many days after the order\'s delivery day. Put 0 to not automatically send an email. You can instead manually email the survey on the Orders page.'
            "
            title="Customer Survey"
            src="/images/store/popover.png"
            class="popover-size"
          />
        </p>
        <b-form-input
          class="w-180"
          v-model="storeModuleSettings.customerSurveyDays"
          type="number"
          min="1"
          @input="updateStoreModules(true, true)"
        ></b-form-input>

        <b-tabs class="mt-4">
          <b-tab title="Questions" :active="activeTab('questions')">
            <v-client-table
              :columns="surveyQuestionsColumns"
              :data="questions"
              :options="{
                orderBy: {
                  column: 'created_at',
                  ascending: true
                },
                headings: {},
                filterable: false
              }"
            >
              <div slot="beforeTable" class="mb-2">
                <h5 class="mt-4">Survey Questions</h5>
                <button
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="showAddQuestionModal = true"
                >
                  Add New Question
                </button>
              </div>
              <div slot="context" class="text-nowrap" slot-scope="props">
                <p>
                  {{
                    props.row.context.charAt(0).toUpperCase() +
                      props.row.context.slice(1)
                  }}
                </p>
              </div>
              <div slot="limit" class="text-nowrap" slot-scope="props">
                <p v-if="props.row.type === 'Selection'">
                  {{ props.row.limit ? "Yes" : "No" }}
                </p>
              </div>
              <div slot="conditional" class="text-nowrap" slot-scope="props">
                <p>{{ props.row.conditional ? "Yes" : "No" }}</p>
              </div>
              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-warning btn-sm"
                  @click="
                    (question = props.row), (showEditQuestionModal = true)
                  "
                >
                  Edit
                </button>
                <button
                  class="btn view btn-danger btn-sm"
                  @click="deleteQuestion(props.row.id)"
                >
                  Delete
                </button>
              </div>
            </v-client-table>
          </b-tab>
          <b-tab title="Responses" :active="activeTab('responses')">
            <v-client-table
              :columns="surveyResponsesColumns"
              :data="responses"
              :options="{
                orderBy: {
                  column: 'created_at',
                  ascending: false
                },
                headings: {
                  created_at: 'Submitted',
                  delivery_date: 'Delivery Date'
                },
                filterable: false
              }"
            >
              <div slot="beforeTable" class="mb-2">
                <h5 class="mt-4">Survey Responses</h5>
              </div>
              <div slot="created_at" slot-scope="props">
                {{ moment(props.row.created_at).format("dddd, MMM Do") }}
              </div>
              <div slot="delivery_date" slot-scope="props">
                {{ moment(props.row.delivery_date).format("dddd, MMM Do") }}
              </div>
              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-primary btn-sm"
                  @click="getSurveyResponse(props.row.order_id)"
                >
                  View
                </button>
              </div>
            </v-client-table>
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      showAddQuestionModal: false,
      showEditQuestionModal: false,
      showResponseModal: false,
      surveyQuestionsColumns: [
        "question",
        "context",
        "type",
        "limit",
        "conditional",
        "actions"
      ],
      surveyResponsesColumns: [
        "created_at",
        "customer_name",
        "order_number",
        "delivery_date",
        "actions"
      ],
      newQuestion: {
        context: "order",
        type: "Text",
        limit: true,
        options: [""],
        rating_condition: "Less than"
      },
      question: {},
      selectedConditionQuestion: {},
      groupedResponses: []
    };
  },
  created() {},
  mounted() {
    this.refreshStoreSurveyQuestions();
    this.refreshStoreSurveyResponses();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      storeModules: "storeModules",
      storeModuleSettings: "storeModuleSettings",
      surveyQuestions: "storeSurveyQuestions",
      surveyResponses: "storeSurveyResponses"
    }),
    questions() {
      return this.surveyQuestions && this.surveyQuestions.length > 0
        ? this.surveyQuestions
        : [];
    },
    nonTextQuestions() {
      return this.questions.filter(q => {
        return q.type !== "Text" && q.context === this.newQuestion.context;
      });
    },
    questionOptions() {
      let formattedOptions = [];
      let context = this.newQuestion.context;
      this.questions.forEach(question => {
        if (question.type !== "Text") {
          if (question.context === context) {
            formattedOptions.push({
              value: question.id,
              text: question.question
            });
          }
        }
      });
      return formattedOptions;
    },
    conditionValueOptions() {
      let formattedOptions = [];
      if (this.selectedConditionQuestion.type === "Selection") {
        this.selectedConditionQuestion.options.forEach(option => {
          formattedOptions.push({ value: option, text: option });
        });
      }
      return formattedOptions;
    },
    conditionRatingOptions() {
      return [
        { value: "Less than", text: "Less than" },
        { value: "Equal to", text: "Equal to" },
        { value: "Greater than", text: "Greater than" }
      ];
    },
    responses() {
      return this.surveyResponses && this.surveyResponses.length > 0
        ? this.surveyResponses
        : [];
    },
    textPlaceholder() {
      let context =
        this.newQuestion.context == "order" ? "your order" : "this item";
      if (this.newQuestion.type === "Text") {
        return "What did you like and dislike about " + context + "?";
      } else if (this.newQuestion.type === "Rating") {
        return "Rate " + context;
      } else if (this.newQuestion.type === "Selection") {
        return (
          "Which of the following aspects of " + context + " did you enjoy?"
        );
      }
    }
  },
  methods: {
    ...mapActions({
      refreshStoreSurveyQuestions: "refreshStoreSurveyQuestions",
      refreshStoreSurveyResponses: "refreshStoreSurveyResponses"
    }),
    formatMoney: format.money,
    getSurveyResponse(orderId) {
      axios
        .post("/api/me/getSurveyResponse", { orderId: orderId })
        .then(resp => {
          this.groupedResponses = this.formatResponses(resp.data);
          this.showResponseModal = true;
        });
    },
    formatResponses(responses) {
      let item_names = [];
      responses.forEach((response, index) => {
        if (response.item_name) {
          if (!item_names.includes(response.item_name)) {
            response.first_instance = true;
            responses[index - 1].last_instance = true;
          }
          item_names.push(response.item_name);
        }
      });

      return responses;
    },
    addQuestion() {
      axios
        .post("/api/me/surveyQuestions", { question: this.newQuestion })
        .then(resp => {
          this.refreshStoreSurveyQuestions();
          this.showAddQuestionModal = false;
          this.$toastr.s("Question created");
          this.newQuestion = {
            context: "order",
            type: "Text",
            limit: true,
            options: [""],
            rating_condition: "Less than"
          };
        });
    },
    updateQuestion() {
      if (!this.question.conditional) {
        this.question.condition_question_id = null;
        this.question.rating_condition = null;
        this.question.condition_value = null;
      }
      axios
        .patch("/api/me/surveyQuestions/" + this.question.id, this.question)
        .then(resp => {
          this.question = resp.data;
          this.refreshStoreSurveyQuestions();
          this.showEditQuestionModal = false;
          this.$toastr.s("Question updated");
        });
    },
    deleteQuestion(id) {
      axios.delete("/api/me/surveyQuestions/" + id).then(resp => {
        this.refreshStoreSurveyQuestions();
        this.$toastr.s("Question deleted");
      });
    },
    isArray(response) {
      try {
        JSON.parse(response);
        return true;
      } catch (e) {
        return false;
      }
    },
    formatArray(response) {
      let array = JSON.parse(response);
      if (array.length > 0) {
        return array.join(", ");
      } else {
        return array;
      }
    },
    activeTab(type) {
      if (type === "questions" && this.responses.length === 0) {
        return true;
      } else if (type === "questions" && this.responses.length > 0) {
        return false;
      }

      if (type === "responses" && this.responses.length === 0) {
        return false;
      } else if (type === "responses" && this.responses.length > 0) {
        return true;
      }
    },
    setSelectedConditionQuestion(q) {
      this.selectedConditionQuestion = this.questions.find(question => {
        return question.id == q.condition_question_id;
      });
    },
    updateStoreModules(toast = false, moduleSettings = false) {
      let modules = { ...this.storeModules };

      axios
        .post("/api/me/updateModules", modules)
        .then(response => {
          // this.refreshStoreModules();
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors));
          error = error.join(" ");
          this.$toastr.w(error, "Error");
        });

      if (moduleSettings) {
        let moduleSettings = { ...this.storeModuleSettings };

        axios
          .post("/api/me/updateModuleSettings", moduleSettings)
          .then(response => {
            // this.refreshStoreModuleSettings();
            if (toast) {
              this.$toastr.s("Your settings have been saved.", "Success");
            }
          })
          .catch(response => {
            let error = _.first(Object.values(response.response.data.errors));
            error = error.join(" ");
            this.$toastr.w(error, "Error");
          });
      }
    }
  }
};
</script>
