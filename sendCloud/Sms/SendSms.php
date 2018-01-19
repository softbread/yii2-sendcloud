<?php

namespace SendCloud\Sms;

use SendCloud\Util\SmsMsg;
use SendCloud\Util\VoiceMsg;
use yii\base\Component;
use yii\base\InvalidParamException;

class SendSms extends Component
{
    /**
     * 0 普通短信
     */
    const TYPE_SMS = 0;
    
    /**
     * 1 彩信
     */
    const TYPE_MMS = 1;
    
    /**
     * 2国际短信
     */
    const TYPE_INTERNAT_SMS = 2;
    
    /**
     * 3语音
     */
    const TYPE_VOICE = 3;
    
    /**
     * @var string Sendcloud SMS API user
     */
    public $apiUser;
    
    /**
     * @var string Sendcloud SMS API KEY
     */
    public $apiKey;
    
    protected $template;
    protected $vars;
    protected $to;
    protected $code;
    
    public function send($type = 0)
    {
        switch ($type) {
            case self::TYPE_SMS:
                $smsMsg = new SmsMsg();
                $smsMsg->addPhoneList($this->getTo());
                
                $vars = $this->getVars();
                if (!empty($vars)) {
                    foreach ($vars as $key => $var) {
                        $smsMsg->addVars($key, $var);
                    }
                }
                $smsMsg->setTemplateId($this->getTemplate());
                $smsMsg->setTimestamp(time());
                $smsClient = new SendCloudClient($this->apiUser, $this->apiKey);
                return $smsClient->send($smsMsg);
            case self::TYPE_VOICE:
                $voiceMsg = new VoiceMsg();
                $voiceMsg->setPhone($this->getTo());
                $voiceMsg->setCode($this->getCode());
                $voiceMsg->setTimestamp(time());
                $smsClient = new SendCloudClient($this->apiUser, $this->apiKey);
                return $smsClient->sendVoice($voiceMsg);
            default:
                throw new InvalidParamException('Invalid SMS type');
        }
    }
    
    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }
    
    /**
     * @param $to
     * @return $this
     */
    public function setTo($to)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        $this->to = $to;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getVars()
    {
        return $this->vars;
    }
    
    /**
     * @param $vars
     * @return $this
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
