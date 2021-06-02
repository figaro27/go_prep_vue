<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\SurveyQuestion;
use App\Store;
use App\User;
use App\SurveyResponse;
use App\StoreModuleSetting;
use App\Mail\Store\OrderFeedback;
use Illuminate\Support\Facades\Mail;

class SurveyController extends Controller
{
    public function getSurvey(Request $request)
    {
        $order = Order::where('order_number', $request->orderNumber)->first();
        $surveyQuestions = SurveyQuestion::where(
            'store_id',
            $order->store_id
        )->get();
        $store = Store::where('id', $order->store_id)->first();

        $responded = SurveyResponse::where('order_id', $order->id)->first();
        if ($responded) {
            return 'responded';
        }

        $data = [
            'order' => $order,
            'surveyQuestions' => $surveyQuestions
        ];

        return $data;
    }

    public function submitSurvey(Request $request)
    {
        $order = Order::where('id', $request->orderId)->first();
        $surveyResponses = $request->surveyResponses;
        $responses = [];

        foreach ($surveyResponses as $i => $response) {
            $decoded = false;
            if (is_array($response)) {
                $decoded = true;
                $response = json_encode($response);
            }
            $surveyQuestion = SurveyQuestion::where('id', $i)->first();
            if ($this->passesCondition($i, $surveyResponses)) {
                $newSurveyResponse = new SurveyResponse();
                $newSurveyResponse->store_id = $order->store_id;
                $newSurveyResponse->user_id = $order->user_id;
                $newSurveyResponse->customer_name = $order->customer_name;
                $newSurveyResponse->order_id = $order->id;
                $newSurveyResponse->order_number = $order->order_number;
                $newSurveyResponse->delivery_date = $order->delivery_date;
                $newSurveyResponse->survey_question_id = $i;
                $newSurveyResponse->survey_question = $surveyQuestion->question;
                $newSurveyResponse->response = $response;
                $newSurveyResponse->save();

                if ($decoded) {
                    $newSurveyResponse->response = implode(
                        ', ',
                        json_decode($newSurveyResponse->response)
                    );
                }
                $newSurveyResponse->sortingOrder = $surveyQuestion->condition_question_id
                    ? $surveyQuestion->condition_question_id
                    : $surveyQuestion->id;
                $responses[] = $newSurveyResponse;
            }
        }

        $surveyItemResponses = $request->surveyItemResponses;
        $itemResponses = [];

        foreach ($surveyItemResponses as $i => $itemResponse) {
            $index =
                strpos($i, ' | Question ID: ') + strlen(' | Question ID: ');
            $questionId = substr($i, $index);
            $question = SurveyQuestion::where('id', $questionId)->first();
            $title = explode(" | Question ID: ", $i, 2);
            $item = $title[0];

            $decoded = false;
            if (is_array($itemResponse)) {
                $decoded = true;
                $itemResponse = json_encode($itemResponse);
            }
            if (
                $this->passesItemCondition($questionId, $itemResponses, $item)
            ) {
                $newSurveyResponse = new SurveyResponse();
                $newSurveyResponse->store_id = $order->store_id;
                $newSurveyResponse->user_id = $order->user_id;
                $newSurveyResponse->customer_name = $order->customer_name;
                $newSurveyResponse->order_id = $order->id;
                $newSurveyResponse->order_number = $order->order_number;
                $newSurveyResponse->delivery_date = $order->delivery_date;
                $newSurveyResponse->survey_question_id = $question->id;
                $newSurveyResponse->survey_question = SurveyQuestion::where(
                    'id',
                    $question->id
                )
                    ->pluck('question')
                    ->first();
                $newSurveyResponse->item_name = $item;
                $newSurveyResponse->response = $itemResponse;
                $newSurveyResponse->save();

                if ($decoded) {
                    $newSurveyResponse->response = implode(
                        ', ',
                        json_decode($newSurveyResponse->response)
                    );
                }
                if ($question->condition_question_id) {
                    $newSurveyResponse->condition_question_id =
                        $question->condition_question_id;
                }

                $itemResponses[] = $newSurveyResponse;
            }
        }

        usort($responses, function ($a, $b) {
            return strcmp($a->sortingOrder, $b->sortingOrder);
        });

        $store = Store::where('id', $order->store_id)
            ->with('moduleSettings')
            ->first();
        if ($store->moduleSettings->emailSurveyResponses) {
            $storeEmail = User::where('id', $store->user_id)
                ->pluck('email')
                ->first();
            $email = new OrderFeedback([
                'email' => $storeEmail,
                'surveyResponses' => $responses,
                'surveyItemResponses' => $itemResponses,
                'order' => $order
            ]);
            Mail::send($email);
        }
    }

    public function passesCondition($questionId, $surveyResponses)
    {
        $surveyQuestion = SurveyQuestion::where('id', $questionId)->first();
        if (!$surveyQuestion->conditional) {
            return true;
        }
        $conditionValue = $surveyQuestion->condition_value;
        $ratingCondition = $surveyQuestion->rating_condition;

        if ($ratingCondition) {
            if (
                $ratingCondition == 'Less than' &&
                $conditionValue >
                    $surveyResponses[$surveyQuestion->condition_question_id]
            ) {
                return true;
            }
            if (
                $ratingCondition == 'Equal to' &&
                $conditionValue ==
                    $surveyResponses[$surveyQuestion->condition_question_id]
            ) {
                return true;
            }
            if (
                $ratingCondition == 'Greater than' &&
                $conditionValue <
                    $surveyResponses[$surveyQuestion->condition_question_id]
            ) {
                return true;
            }
        }

        if (
            $conditionValue ===
            $surveyResponses[$surveyQuestion->condition_question_id]
        ) {
            return true;
        }
        return false;
    }

    public function passesItemCondition($questionId, $surveyResponses, $item)
    {
        $surveyQuestion = SurveyQuestion::where('id', $questionId)->first();
        if (!$surveyQuestion->conditional) {
            return true;
        }
        $conditionValue = $surveyQuestion->condition_value;
        $ratingCondition = $surveyQuestion->rating_condition;

        if ($ratingCondition) {
            if ($ratingCondition == 'Less than') {
                foreach ($surveyResponses as $i => $response) {
                    if (
                        $response['survey_question_id'] ===
                            $surveyQuestion->condition_question_id &&
                        $response['response'] <
                            $surveyQuestion->condition_value &&
                        $response['item_name'] === $item
                    ) {
                        return true;
                    }
                }
            }
            if ($ratingCondition == 'Equal to') {
                foreach ($surveyResponses as $i => $response) {
                    if (
                        $response['survey_question_id'] ===
                            $surveyQuestion->condition_question_id &&
                        $response['response'] ==
                            $surveyQuestion->condition_value &&
                        $response['item_name'] === $item
                    ) {
                        return true;
                    }
                }
            }
            if ($ratingCondition == 'Greater than') {
                foreach ($surveyResponses as $i => $response) {
                    if (
                        $response['survey_question_id'] ===
                            $surveyQuestion->condition_question_id &&
                        $response['response'] >
                            $surveyQuestion->condition_value &&
                        $response['item_name'] === $item
                    ) {
                        return true;
                    }
                }
            }
        }

        foreach ($surveyResponses as $i => $response) {
            if (
                $response['survey_question_id'] ===
                    $surveyQuestion->condition_question_id &&
                $response['response'] === $surveyQuestion->condition_value &&
                $response['item_name'] === $item
            ) {
                return true;
            }
        }
        return false;
    }
}
