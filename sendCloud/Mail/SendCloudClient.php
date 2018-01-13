<?php

namespace SendCloud\Mail;

use SendCloud\Util\Mail;

class SendCloudClient
{
    const HOST_V1 = 'http://sendcloud.sohu.com';
    const HOST_V2 = 'http://api.sendcloud.net/apiv2';
    
    const SEND_URL_V1 = '/webapi/mail.send.json';
    const SEND_TEMPLATE_URL_V1 = '/webapi/mail.send_template.json';
    
    const SEND_URL_V2 = '/mail/send';
    const SEND_TEMPLATE_URL_V2 = '/mail/sendtemplate';
    
    private $client;
    private $api_user;
    private $api_key;
    private $version;
    
    public function __construct($api_user, $api_key, $version = "v2")
    {
        $host = $version === 'v1' ? self::HOST_V1 : self::HOST_V2;
        $this->api_user = $api_user;
        $this->api_key = $api_key;
        $this->version = $version;
        $this->client = new \SendCloud\Util\HttpClient($host);
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
        
        if ($mail->hasAttachment()) {
            $bodyData = $this->wrapBody($mail);
            $response = $this->client->mutilpost($url, $bodyData ['body'],
                                                 $bodyData ['header']);
        } else {
            $param = $this->wrapParam($mail);
            $response = $this->client->post($url, [], $param);
        }
        return $response;
    }
    
    protected function wrapBody(Mail $mail)
    {
        if ($this->version == 'v1') {
            return $this->wrapBody_v1($mail);
        } else {
            return $this->wrapBody_v2($mail);
        }
    }
    
    protected function wrapBody_v1(Mail $mail)
    {
        $header = [];
        $eol = "\r\n";
        $data = '';
        $mime_boundary = md5(time());
        $data .= $this->appendFormParam('api_user', $this->api_user, $mime_boundary);
        $data .= $this->appendFormParam('api_key', $this->api_key, $mime_boundary);
        
        if ($mail->getSubject()) {
            $data .= $this->appendFormParam('subject', $mail->getSubject(), $mime_boundary);
        }
        
        if ($mail->getFrom()) {
            $data .= $this->appendFormParam('from', $mail->getFrom(), $mime_boundary);
        }
        
        if ($mail->getTos()) {
            $data .= $this->appendFormParam('to', implode(";", $mail->getTos()), $mime_boundary);
        }
        
        if ($mail->getBccs()) {
            $data .= $this->appendFormParam('bcc', implode(";", $mail->getBccs()), $mime_boundary);
        }
        
        if ($mail->getCcs()) {
            $data .= $this->appendFormParam('bcc', implode(";", $mail->getCcs()), $mime_boundary);
        }
        
        if ($mail->getXsmtpApi()) {
            $data .= $this->appendFormParam('x_smtpapi', $mail->getXsmtpApi(), $mime_boundary);
        }
        
        if ($mail->getContent()) {
            $data .= $this->appendFormParam('html', $mail->getContent(), $mime_boundary);
        }
        
        if ($mail->getFromName()) {
            $data .= $this->appendFormParam('fromname', $mail->getFromName(), $mime_boundary);
        }
        
        if ($mail->getReplyTo()) {
            $data .= $this->appendFormParam('replyto', $mail->getReplyTo(), $mime_boundary);
        }
        
        if ($mail->getLabel()) {
            $data .= $this->appendFormParam('label', $mail->getLabel(), $mime_boundary);
        }
        
        if ($mail->getRespEmailId()) {
            $data .= $this->appendFormParam('resp_email_id', 'true', $mime_boundary);
        }
        
        if ($mail->getGzipCompress()) {
            $data .= $this->appendFormParam('gzip_compress', 'true', $mime_boundary);
        }
        if ($mail->getUseMaillist()) {
            $data .= $this->appendFormParam('use_maillist', 'true', $mime_boundary);
        }
        
        if ($mail->getHeaders()) {
            $headers = json_encode($mail->getHeaders());
            $data .= $this->appendFormParam('headers', $headers, $mime_boundary);
        }
        
        if ($mail->getTemplateContent()) {
            $template = $mail->getTemplateContent();
            $invokeName = $template->getTemplateInvokeName();
            if ($invokeName) {
                $data .= $this->appendFormParam('template_invoke_name', $invokeName, $mime_boundary);
            }
            
            $substitution = $template->getTemplateVars();
            if ($substitution) {
                $json_substitution = [
                    'to'  => $mail->getTos(),
                    'sub' => $substitution,
                ];
                
                $data .= $this->appendFormParam('substitution_vars', json_encode($json_substitution), $mime_boundary);
            }
        }
        
        if ($mail->getAttachments()) {
            foreach ($mail->getAttachments() as $attach) {
                $content = $attach->getContent();
                $filename = $attach->getFilename();
                $filetype = $attach->getType();
                $data .= $this->appendAttachmentData($filename, $filetype, $content, $mime_boundary);
            }
            $header = [
                'Content-Type' => 'multipart/form-data',
                'boundary'     => $mime_boundary,
            ];
            $data .= "--" . $mime_boundary . "--" . $eol . $eol;
        }
        return [
            'body'   => $data,
            'header' => $header,
        ];
    }
    
    protected function appendFormParam($name, $value, $mime_boundary, $eol = "\r\n")
    {
        $data = '';
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; ';
        $data .= "name=" . $name . $eol . $eol;
        $data .= $value . $eol;
        return $data;
    }
    
    protected function appendAttachmentData($filename, $filetype, $content, $mime_boundary, $eol = "\r\n")
    {
        $data = '';
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="attachments"; filename="' . $filename . '"' . $eol;
        $data .= 'Content-Type: ' . $filetype . $eol;
        $data .= 'Content-Transfer-Encoding: binary' . $eol . $eol;
        $data .= $content . $eol;
        return $data;
    }
    
    protected function wrapBody_v2(Mail $mail)
    {
        $header = [];
        $eol = "\r\n";
        $data = '';
        $mime_boundary = md5(time());
        $data .= $this->appendFormParam('apiUser', $this->api_user, $mime_boundary);
        $data .= $this->appendFormParam('apiKey', $this->api_key, $mime_boundary);
        if ($mail->getSubject()) {
            $data .= $this->appendFormParam('subject', $mail->getSubject(), $mime_boundary);
        }
        if ($mail->getFrom()) {
            $data .= $this->appendFormParam('from', $mail->getFrom(), $mime_boundary);
        }
        if ($mail->getTos()) {
            $data .= $this->appendFormParam('to', implode(',', $mail->getTos()), $mime_boundary);
        }
        
        if ($mail->getBccs()) {
            $data .= $this->appendFormParam('bcc', implode(',', $mail->getBccs()), $mime_boundary);
        }
        
        if ($mail->getCcs()) {
            $data .= $this->appendFormParam('bcc', implode(',', $mail->getCcs()), $mime_boundary);
        }
        
        if ($mail->getXsmtpApi()) {
            $data .= $this->appendFormParam('xsmtpapi', $mail->getXsmtpApi(), $mime_boundary);
        }
        if ($mail->getContent()) {
            $data .= $this->appendFormParam('html', $mail->getContent(), $mime_boundary);
        }
        
        if ($mail->getPlain()) {
            $data .= $this->appendFormParam('plain', $mail->getPlain(), $mime_boundary);
        }
        
        if ($mail->getFromName()) {
            $data .= $this->appendFormParam('fromName', $mail->getFromName(), $mime_boundary);
        }
        
        if ($mail->getReplyTo()) {
            $data .= $this->appendFormParam('replyTo', $mail->getReplyTo(), $mime_boundary);
        }
        
        if ($mail->getLabel()) {
            $data .= $this->appendFormParam('labelId', $mail->getLabel(), $mime_boundary);
        }
        
        if ($mail->getRespEmailId()) {
            $data .= $this->appendFormParam('respEmailId', 'true', $mime_boundary);
        }
        
        
        if ($mail->getUseMaillist()) {
            $data .= $this->appendFormParam('useAddressList', 'true', $mime_boundary);
        }
        
        if ($mail->getHeaders()) {
            $headers = json_encode($mail->getHeaders());
            $data .= $this->appendFormParam('headers', $headers, $mime_boundary);
        }
        
        if ($mail->getUseNotification()) {
            $data .= $this->appendFormParam('useNotification', $header, $mime_boundary);
        }
        
        if ($mail->getTemplateContent()) {
            $template = $mail->getTemplateContent();
            $invokeName = $template->getTemplateInvokeName();
            if ($invokeName) {
                $data .= $this->appendFormParam('templateInvokeName', $invokeName, $mime_boundary);
            }
        }
        
        $attaches = $mail->getAttachments();
        if (!empty($attaches)) {
            foreach ($attaches as $attach) {
                $content = $attach->getContent();
                $filename = $attach->getFilename();
                $filetype = $attach->getType();
                $data .= $this->appendAttachmentData($filename, $filetype, $content, $mime_boundary);
            }
            $header = [
                'Content-Type' => 'multipart/form-data',
                'boundary'     => $mime_boundary,
            ];
            $data .= "--" . $mime_boundary . "--" . $eol . $eol;
        }
        return [
            'body'   => $data,
            'header' => $header,
        ];
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
            $param ['to'] = implode(',', $mail->getTos());
        }
        
        if ($mail->getBccs()) {
            $param ['bcc'] = implode(',', $mail->getBccs());
        }
        
        if ($mail->getCcs()) {
            $param ['cc'] = implode(',', $mail->getCcs());
        }
        if ($mail->getXsmtpApi()) {
            $param ['xsmtpapi'] = $mail->getXsmtpApi();
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
        
        if ($mail->hasAttachment()) {
            $bodyData = $this->wrapBody($mail);
            
            $response = $this->client->mutilpost($url, $bodyData['body'], $bodyData['header']);
        } else {
            $param = $this->wrapParam($mail);
            
            $response = $this->client->post($url, [], $param);
        }
        
        return $response;
    }
}
