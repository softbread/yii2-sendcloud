<?php

namespace SendCloud\Mail;

use SendCloud\Util\Attachment;
use SendCloud\Util\HttpClient;
use SendCloud\Util\Mail;

class SendCloudClient
{
    const HOST_V1 = 'http://sendcloud.sohu.com';
    const HOST_V2 = 'http://api.sendcloud.net/apiv2';
    
    const SEND_URL_V1 = '/webapi/mail.send.json';
    const SEND_TEMPLATE_URL_V1 = '/webapi/mail.send_template.json';
    
    const SEND_URL_V2 = '/mail/send';
    const SEND_TEMPLATE_URL_V2 = '/mail/sendtemplate';
    
    protected $client;
    protected $api_user;
    protected $api_key;
    protected $version;
    
    public function __construct($api_user, $api_key, $version = "v2")
    {
        $host = $version === 'v1' ? self::HOST_V1 : self::HOST_V2;
        $this->api_user = $api_user;
        $this->api_key = $api_key;
        $this->version = $version;
        $this->client = new HttpClient($host);
    }
    
    /**
     * @param Mail $mail
     * @return \yii\httpclient\Response
     */
    public function send(Mail $mail)
    {
        if ($mail->getTemplateContent()) {
            return $this->sendTemplate($mail);
        }
        
        return $this->sendCommon($mail);
    }
    
    /**
     * @param Mail $mail
     * @return \yii\httpclient\Response
     */
    public function sendTemplate(Mail $mail)
    {
        $url = self::HOST_V2 . self::SEND_TEMPLATE_URL_V2;
        
        if ($this->version == 'v1') {
            $url = self::HOST_V1 . self::SEND_TEMPLATE_URL_V1;
        }
        
        $param = $this->wrapParam($mail);
        
        if ($mail->hasAttachment()) {
            $response = $this->client->postWithAttachments($url, $param, $mail->getAttachments());
        } else {
            $response = $this->client->post($url, $param);
        }
        return $response;
    }
    
    protected function wrapParam(Mail $mail)
    {
        if ($this->version == 'v1') {
            return $this->wrapParam_v1($mail);
        } else {
            return $this->wrapParam_v2($mail);
        }
    }
    
    protected function wrapParam_v1(Mail $mail)
    {
        $param = [];
        if ($this->api_user) {
            $param ['api_user'] = $this->api_user;
        }
        if ($this->api_key) {
            $param ['api_key'] = $this->api_key;
        }
        if ($mail->getSubject()) {
            $param ['subject'] = $mail->getSubject();
        }
        if ($mail->getFrom()) {
            $param ['from'] = $mail->getFrom();
        }
        if ($mail->getTos()) {
            $param ['to'] = implode(";", $mail->getTos());
        }
        
        if ($mail->getBccs()) {
            $param ['bcc'] = implode(";", $mail->getBccs());
        }
        
        if ($mail->getCcs()) {
            $param ['cc'] = implode(";", $mail->getCcs());
        }
        if ($mail->getXsmtpApi()) {
            $param ['x_smtpapi'] = implode(";", $mail->getXsmtpApi());
        }
        if ($mail->getContent()) {
            $param ['html'] = $mail->getContent();
        }
        if ($mail->getFromName()) {
            $param ['fromname'] = $mail->getFromName();
        }
        if ($mail->getReplyTo()) {
            $param ['replyto'] = $mail->getReplyTo();
        }
        if ($mail->getLabel()) {
            $param ['label'] = $mail->getLabel();
        }
        if ($mail->getRespEmailId()) {
            $param ['resp_email_id'] = 'true';
        }
        if ($mail->getGzipCompress()) {
            $param ['gzip_compress'] = 'true';
        }
        if ($mail->getUseMaillist()) {
            $param ['use_maillist'] = 'true';
        }
        if ($mail->getHeaders()) {
            $headers = json_encode($mail->getHeaders());
            $param ['headers'] = $headers;
        }
        
        if ($mail->getTemplateContent()) {
            $template = $mail->getTemplateContent();
            $invokeName = $template->getTemplateInvokeName();
            if ($invokeName) {
                $param ['template_invoke_name'] = $invokeName;
            }
            
            $substitution = $template->getTemplateVars();
            if ($substitution) {
                $json_substitution = [
                    'to'  => $mail->getTos(),
                    'sub' => $substitution,
                ];
                
                $param ['substitution_vars'] = json_encode($json_substitution);
                unset ($param ['to']);
            }
        }
        
        return $param;
    }
    
    protected function wrapParam_v2(Mail $mail)
    {
        $param = [];
        if ($this->api_user) {
            $param ['apiUser'] = $this->api_user;
        }
        if ($this->api_key) {
            $param ['apiKey'] = $this->api_key;
        }
        if ($mail->getSubject()) {
            $param ['subject'] = $mail->getSubject();
        }
        if ($mail->getFrom()) {
            $param ['from'] = $mail->getFrom();
        }
        if ($mail->getTos()) {
            $param ['to'] = implode(';', $mail->getTos());
        }
        
        // Send Template Email doesn't support BCC
        if ($mail->getBccs()) {
            $param ['bcc'] = implode(';', $mail->getBccs());
        }
        // Send Template Email doesn't support CC
        if ($mail->getCcs()) {
            $param ['cc'] = implode(';', $mail->getCcs());
        }
        if ($mail->getContent()) {
            $param ['html'] = $mail->getContent();
        }
        if ($mail->getFromName()) {
            $param ['fromName'] = $mail->getFromName();
        }
        if ($mail->getReplyTo()) {
            $param ['replyTo'] = $mail->getReplyTo();
        }
        if ($mail->getLabel()) {
            $param ['labelId'] = $mail->getLabel();
        }
        if ($mail->getRespEmailId()) {
            $param ['respEmailId'] = 'true';
        }
        
        if ($mail->getUseMaillist()) {
            $param ['useAddressList'] = 'true';
        }
        if ($mail->getUseNotification()) {
            $param ['useNotification'] = 'true';
        }
        if ($mail->getHeaders()) {
            $headers = json_encode($mail->getHeaders());
            $param ['headers'] = $headers;
        }
        if ($mail->getPlain()) {
            $param ['plain'] = $mail->getPlain();
        }
        
        if ($mail->getTemplateContent()) {
            $template = $mail->getTemplateContent();
            $invokeName = $template->getTemplateInvokeName();
            if ($invokeName) {
                $param ['templateInvokeName'] = $invokeName;
                
                $templateVars = $template->getTemplateVars();
                if ($templateVars) {
                    $xsmtpApi = [
                        'to'  => $mail->getTos(),
                        'sub' => $templateVars,
                    ];
                    $param['xsmtpapi'] = json_encode($xsmtpApi);
                    
                    // x-smtp-api will overwrite $param['to'];
                    unset($param['to']);
                }
            }
        }
        
        return $param;
    }
    
    /**
     * @param Mail $mail
     * @return \yii\httpclient\Response
     */
    public function sendCommon(Mail $mail)
    {
        $url = self::HOST_V2 . self::SEND_URL_V2;
        
        if ($this->version == 'v1') {
            $url = self::HOST_V1 . self::SEND_URL_V1;
        }
        
        $params = $this->wrapParam($mail);
        
        if ($mail->hasAttachment()) {
            /** @var Attachment[] $attachments */
            $attachments = $mail->getAttachments();
            
            $response = $this->client->postWithAttachments($url, $params, $attachments);
        } else {
            $response = $this->client->post($url, $params);
        }
        
        return $response;
    }
}
