<?php

namespace SendCloud\Sms;

use SendCloud\Util\SmsMsg;
use SendCloud\Util\VoiceMsg;
use yii\httpclient\Client;

class SendCloudClient
{
    const API_HOST = 'https://www.sendcloud.net';
    
    protected $apiUser;
    protected $apiKey;
    protected $client;
    
    public function __construct($apiUser, $apiKey)
    {
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->client = new Client(['baseUrl' => self::API_HOST]);
    }
    
    /**
     * @param SmsMsg $sms
     * @return bool
     */
    public function send(SmsMsg $sms)
    {
        $param = $sms->jsonSerialize();
        $param ['smsUser'] = $this->apiUser;
        $phone = $param['phone'];
        $param['phone'] = implode(";", $phone);
        $param['vars'] = json_encode($param['vars']);
        $param ['signature'] = $this->_getSignature($param);
        $response = $this->client->post('/smsapi/send', $param, ['format' => Client::FORMAT_JSON])
                                 ->send();
        if ($response->isOk) {
            if ($response->data['statusCode'] === 200) {
                return true;
            }
        }
        return false;
    }
    
    private function _getSignature($param)
    {
        $sParamStr = "";
        ksort($param);
        foreach ($param as $sKey => $sValue) {
            if (is_array($sValue)) {
                $value = implode(";", $sValue);
                $sParamStr .= $sKey . '=' . $value . '&';
            } else {
                $sParamStr .= $sKey . '=' . $sValue . '&';
            }
        }
        $sParamStr = trim($sParamStr, '&');
        $sSignature = md5($this->apiKey . "&" . $sParamStr . "&" . $this->apiKey);
        return $sSignature;
    }
    
    /**
     * @param VoiceMsg $sms
     * @return bool
     */
    public function sendVoice(VoiceMsg $sms)
    {
        $param = $sms->jsonSerialize();
        $param ['smsUser'] = $this->apiUser;
        $param ['signature'] = $this->_getSignature($param);
        $response = $this->client->post('/smsapi/sendVoice', $param)
                                 ->send();
        if ($response->isOk) {
            if ($response->data['statusCode'] === 200) {
                return true;
            }
        }
        return false;
    }
}
