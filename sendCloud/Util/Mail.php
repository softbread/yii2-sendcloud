<?php

namespace SendCloud\Util;

use yii\base\InvalidConfigException;

/**
 *
 * @author xjmfc
 *
 * @param $x_smtpapi         http://sendcloud.sohu.com/doc/guide/rule/#x-smtpapi
 * @param $subject           string 邮件标题
 * @param $from              string 发件人
 * @param $content           string 邮件正文
 * @param $attachments       array 邮件附件
 * @param $headers           string 邮件头部信息. JSON 格式, 比如:{"header1": "value1", "header2": "value2"}
 * @param $reply_to          string 设置用户默认的回复邮件地址. 如果 replyTo 没有或者为空, 则默认的回复邮件地址为 from
 * @param $labelId           string 本次发送所使用的标签ID. 此标签需要事先创建
 * @param $resp_email_id     string 是否返回 emailId. 有多个收件人时, 会返回 emailId 的列表, 默认值: true
 * @param $tos               array 收件人地址
 * @param $ccs               array 抄送地址
 * @param $bccs              array 密送地址
 * @param $template_content  TemplateContent 模板信息
 * @param $fromname          string 发件人名称. 显示如: ifaxin客服支持<support@ifaxin.com>
 * @param $plain             string 纯文本内容,仅适合v2版本
 * @param $use_notification  string 默认值: false. 是否使用回执
 * @param $gzip_compress     string 仅适合v1版本,不建议使用
 */
class Mail implements \JsonSerializable
{
    private $from;
    private $subject;
    private $content;
    private $attachments;
    private $headers;
    private $reply_to;
    private $label;
    private $x_smtpapi;
    private $resp_email_id;
    private $use_maillist;
    private $gzip_compress;
    private $tos;
    private $ccs;
    private $bccs;
    private $template_content;
    private $fromname;
    private $plain;
    private $use_notification;
    
    public function __construct($from = null, $to = null, $subject=null, $content = null)
    {
        if (empty ($from) || empty ($subject) || empty ($to)) {
            throw new InvalidConfigException('Missing email parameters (From / To / Subject required)');
        }
        
        $this->setFrom($from);
        $this->setSubject($subject);
        $this->setContent($content);
    }
    
    /**
     * @param $email string single email address
     */
    public function addTo($email)
    {
        $this->tos[] = $email;
    }
    
    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }
    
    public function addCc($email)
    {
        $this->ccs[] = $email;
    }
    
    public function addBcc($email)
    {
        $this->bccs[] = $email;
    }
    
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
    
    public function hasAttachment()
    {
        return sizeof($this->attachments);
    }
    
    /**
     * @return TemplateContent|null
     */
    public function getTemplateContent()
    {
        return $this->template_content;
    }
    
    /**
     * @param TemplateContent $template_content
     */
    public function setTemplateContent(TemplateContent $template_content)
    {
        $this->template_content = $template_content;
    }
    
    public function getPlain()
    {
        return $this->plain;
    }
    
    public function setPlain($plain)
    {
        $this->plain = $plain;
    }
    
    public function getUseNotification()
    {
        return $this->use_notification;
    }
    
    public function setUseNotification($use_notification)
    {
        $this->use_notification = $use_notification;
        
    }
    
    public function jsonSerialize()
    {
        return array_filter([
                                'from'          => $this->getFrom(),
                                'subject'       => $this->getSubject(),
                                'html'          => $this->getContent(),
                                'attachments'   => $this->getAttachments(),
                                'headers'       => $this->getHeaders(),
                                'reply_to'      => $this->getReplyTo(),
                                'label'         => $this->getLabel(),
                                'x_smtpapi'     => $this->getXsmtpApi(),
                                'resp_email_id' => $this->getRespEmailId(),
                                'use_maillist'  => $this->getUseMaillist(),
                                'gzip_compress' => $this->getGzipCompress(),
                                'tos'           => $this->getTos(),
                                'ccs'           => $this->getCcs(),
                                'bccs'          => $this->getBccs(),
                                'fromname'      => $this->getFromName(),
                            ]);
    }
    
    public function getFrom()
    {
        return $this->from;
    }
    
    public function setFrom($email)
    {
        $this->from = $email;
    }
    
    public function getSubject()
    {
        return $this->subject;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getAttachments()
    {
        return $this->attachments;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getReplyTo()
    {
        return $this->reply_to;
    }
    
    public function setReplyTo($reply_to)
    {
        $this->reply_to = $reply_to;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setLabel($label)
    {
        $this->label = $label;
    }
    
    public function getXsmtpApi()
    {
        return $this->x_smtpapi;
    }
    
    public function setXsmtpApi($x_smtpapi)
    {
        $this->x_smtpapi = $x_smtpapi;
    }
    
    public function getRespEmailId()
    {
        return $this->resp_email_id;
    }
    
    public function setRespEmailId($resp_email_id)
    {
        $this->resp_email_id = $resp_email_id;
    }
    
    function getUseMaillist()
    {
        return $this->use_maillist;
    }
    
    function setUseMaillist($use_maillist)
    {
        $this->use_maillist = $use_maillist;
    }
    
    function getGzipCompress()
    {
        return $this->gzip_compress;
    }
    
    function setGzipCompress($gzip_compress)
    {
        $this->gzip_compress = $gzip_compress;
    }
    
    public function getTos()
    {
        return $this->tos;
    }
    
    public function getCcs()
    {
        return $this->ccs;
    }
    
    public function getBccs()
    {
        return $this->bccs;
    }
    
    public function getFromName()
    {
        return $this->fromname;
    }
    
    public function setFromName($fromname)
    {
        $this->fromname = $fromname;
    }
}
