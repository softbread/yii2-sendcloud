<?php
/**
 * Mail.php
 *
 * PHP version 5.6+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @package   sweelix\sendgrid
 */

namespace SendCloud\Mail;

use SendCloud\Util\Mail as SendCloudMail;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    
    /**
     * @var string Sendgrid login
     */
    public $api_user;
    
    /**
     * @var string Sendgrid password
     */
    public $api_key;
    
    /**
     * @inheritdoc
     */
    public $messageClass = 'SendCloud\Mail\Message';
    
    /**
     * @param \SendCloud\Mail\Message $message
     * @return bool
     * @throws InvalidConfigException
     * @throws \Exception
     */
    protected function sendMessage($message)
    {
        try {
            if ($this->api_user === null || $this->api_key === null) {
                throw new InvalidConfigException('API user or API key missing');
            }
            
            $client = new SendCloudClient($this->api_user, $this->api_key);
            
            if ($client === null) {
                throw new InvalidParamException('Email transport must be configured');
            }
            
            $sendCloudMail = new SendCloudMail();
            
            $replyTo = $message->getReplyTo();
            if ($replyTo !== null) {
                $sendCloudMail->setReplyTo($replyTo);
            }
            $sendCloudMail->setFrom($message->getFrom());
            if ($message->getFromName() !== null) {
                $sendCloudMail->setFromName($message->getFromName());
            }
            foreach ($message->getTo() as $email => $name) {
                $sendCloudMail->addTo($email);
            }
            foreach ($message->getCc() as $email => $name) {
                $sendCloudMail->addCc($email);
            }
            foreach ($message->getBcc() as $email => $name) {
                $sendCloudMail->addBcc($email);
            }
            $sendCloudMail->setSubject($message->getSubject());
            foreach ($message->getHeaders() as $header) {
                list($key, $value) = each($header);
                $sendCloudMail->addHeader($key, $value);
            }
            foreach ($message->getAttachments() as $attachment) {
                $cid = isset($attachment['ContentID']) ? $attachment['ContentID'] : null;
                
                
                $sendCloudMail->addAttachment($attachment['File'], $attachment['Name'], $cid);
            }
            
            $templateId = $message->getTemplateId();
            if ($templateId === null) {
                $data = $message->getHtmlBody();
                if ($data !== null) {
                    $sendCloudMail->setHtml($data);
                }
                $data = $message->getTextBody();
                if ($data !== null) {
                    $sendCloudMail->setText($data);
                }
            } else {
                $sendCloudMail->setTemplateId($templateId);
                // trigger html template
                $sendCloudMail->setHtml(' ');
                // trigger text template
                $sendCloudMail->setText(' ');
                $templateModel = $message->getTemplateModel();
                if (empty($templateModel) === false) {
                    $sendCloudMail->setSubstitutions($message->getTemplateModel());
                }
            }
            $result = $client->send($sendCloudMail);
            /* @var \yii\httpclient\Response $result */
            return $result->getStatusCode() == 200;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
