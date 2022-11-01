<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleDriveTrigger;
use App\Apps\Triggers\GoogleFormTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleForm
{

//    private $client_id = '68667692288-4bkagbc71fb2k0c9dr481ip9ib22iffv.apps.googleusercontent.com';
//    private $client_secret = "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";

    /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    public $dataOptionAction = false;

    public function __construct()
    {
        $gmail = AppsData::where("AppId", "GoogleForm")->first();
        if ($gmail) {
            $appInfo = json_decode($gmail->AppInfo, true);
            $this->client_id = $appInfo['client_id'];
            $this->client_secret = $appInfo['client_secret'];
        }
    }


    public function getTrigger(): array
    {
        return array(
            [
                'id' => 'new_response',
                'name' => Helpers::translate('New Response'),
                'description' => Helpers::translate('Triggers when you create a new folder')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'copy_file',
                'name' => Helpers::translate('Copy File'),
                'description' => Helpers::translate('Copy a file')
            ]
        );
    }


    public function getRefreshToken($refreshToken)
    {
        $data = [
            'refresh_token' => $refreshToken,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token'
        ];

        $url = "https://oauth2.googleapis.com/token";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// execute!
        $response = curl_exec($ch);
        return json_decode($response, 1);
    }

    public function getToken($id)
    {
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->token, true);
        $refreshToken = $this->getRefreshToken($token['refresh_token']);
        return $refreshToken['access_token'];
    }

    public function getUserId($actionAccount)
    {
        $getProfile = Account::find($actionAccount);
        $token = json_decode($getProfile->data, true);
        return $token['sub'];
    }

    public function getCheckupData($accountId, $labelId)
    {
        $getProfile = Account::find($accountId);
        $data = json_decode($getProfile->data, true);
        $access_token = $this->getToken($accountId);
        $trigger = new GoogleFormTrigger($accountId);
        return [
            'access_token' => $access_token,
            'view' => $trigger->$labelId(),
        ];
    }

    public function checkAccount($id)
    {
        if ($access_token = $this->getToken($id)) {
            $url = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $profile = json_decode($response, 1);

            if (isset($profile) && count($profile) != 0) {
                return true;
            }
        }
        return false;
    }

    // Real APIs
    public function getForms($access_token)
    {

        $url = "https://www.googleapis.com/drive/v3/files?access_token=" . $access_token . "&q=" . urlencode("mimeType='application/vnd.google-apps.form'");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, 1)['files'];
    }

    public function getForm($access_token, $formId)
    {

        $url = "https://forms.googleapis.com/v1/forms/{$formId}?access_token=" . $access_token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, 1);
    }

    public function getFormResponse($access_token, $formId)
    {

        $url = "https://forms.googleapis.com/v1/forms/{$formId}/responses?access_token=" . $access_token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, 1);
    }

    public function getFormResponseSingle($access_token, $formId, $responseId)
    {

        $url = "https://forms.googleapis.com/v1/forms/{$formId}/responses/{$responseId}?access_token=" . $access_token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, 1);
    }

    public function processQuestion($access_token, $formId): array
    {
        $form = $this->getForm($access_token, $formId);
        $items = $form['items'];
        $questionsFascinate = [];
        foreach ($items as $item) {
            $title = $item['title'];
            if (isset($item['questionItem'])) {
                $questionItem = $item['questionItem'];
                $questionId = $questionItem['question']['questionId'];
                $questionsFascinate[$questionId] = $title;

            }

            if (isset($item['questionGroupItem'])) {
                $questionItem = $item['questionGroupItem']['questions'];
                foreach ($questionItem as $question) {
                    $questionsFascinate[$question['questionId']] = $title . "[ {$question['rowQuestion']['title']} ]";
                }
            }
        }

        return $questionsFascinate;
    }

    public function getFormResponseWithAnswers($access_token, $formId = "1xUajs7qjjBU-t09rhlocmxv3Tr7N6q0HOVLLW1V-kvs", $responseId = "ACYDBNh7UCJuYSjlOl9EFahwhsfpuqX31vqA78hpoVmPtqxd2cq4oVqi_PoUzEjpjw"): array
    {
        $response = $this->getFormResponseSingle($access_token, $formId, $responseId);
        $questions = $this->processQuestion($access_token, $formId);
        $answers = $response['answers'];
        $return = [];
        foreach ($answers as $answer) {
            $title = $questions[$answer['questionId']];
            if (isset($answer['textAnswers'])) {
                $answerValue = $answer['textAnswers']['answers'][0]['value'];
                $return['string'][$title] = $answerValue;
            }

            if (isset($answer['fileUploadAnswers'])) {
                $answerValue = $answer['fileUploadAnswers']['answers'][0]['fileId'];
                $driver = new GoogleDrive;
                $fileData = $driver->idToFile($access_token, $answerValue);
                foreach ($fileData['file']['Attachment'] as $attachment) {
                    $return['file'][$title] = $attachment;
                }
            }
        }
        return $return;
    }

    public function fileManagerInstance($accountId)
    {
        return [$this->getToken($accountId)];
    }
}
