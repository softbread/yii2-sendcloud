<?php

namespace SendCloud\Util;

/**
 * 短信消息
 *
 * @param templateId     模板ID
 * @param msgType        0表示短信, 1表示彩信, 默认值为0
 * @param phone          收信人手机号,多个手机号用逗号,分隔, 号码最多不能超过100
 * @param vars           替换变量的json串
 * @param 签名             ;合法性验证
 * @author xjm
 *
 */
class SmsMsg implements \JsonSerializable
{
    private $template_id;
    private $msg_type;
    /**
     *
     * @var array
     */
    private $phone;
    /**
     *
     * @var array
     */
    private $vars;
    private $signature;
    private $timestamp;
    
    public function addPhoneList($phone)
    {
        if (!is_array($phone)) {
            array_push($this->phone, $phone);
        } else {
            foreach ($phone as $key => $value) {
                $this->phone [] = $value;
            }
        }
    }
    
    public function addVars($key, $value)
    {
        $this->vars [$key] = $value;
    }
    
    public function addMapVars($map)
    {
        foreach ($map as $key => $value) {
            $this->vars [$key] = $value;
        }
    }
    
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    /* phone array(1,2,3) */
    
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
    
    public function jsonSerialize()
    {
        return array_filter([
                                'templateId' => $this->getTemplateId(),
                                'msgType'    => $this->getMsgType(),
                                'phone'      => $this->getPhoneList(),
                                'vars'       => $this->getVars(),
                                'signature'  => $this->getSignature(),
                            ]);
    }
    
    public function getTemplateId()
    {
        return $this->template_id;
    }
    
    public function setTemplateId($template_id)
    {
        $this->template_id = $template_id;
    }
    
    public function getMsgType()
    {
        return $this->msg_type;
    }
    
    public function setMsgType($msgType)
    {
        $this->msg_type = $msgType;
    }
    
    public function getPhoneList()
    {
        return $this->phone;
    }
    
    public function getVars()
    {
        return $this->vars;
    }
    
    public function getSignature()
    {
        return $this->signature;
    }
    
    public function setSignature($signature)
    {
        $this->setSignature($signature);
    }
}

