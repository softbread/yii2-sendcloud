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

use SendCloud\Util\Attachment;
use SendCloud\Util\Mail as SendCloudMail;
use SendCloud\Util\Mimetypes;
use SendCloud\Util\TemplateContent;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    
    /**
     * @var string Sendcloud login
     */
    public $api_user;
    
    /**
     * @var string Sendcloud password
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
            if ($message->getFromName()) {
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
            
            foreach ($message->getAttachments() as $attachment) {
                $file = $attachment['File'];
                $handle = fopen($file, 'rb');
                $content = fread($handle, filesize($file));
                $filetype = Mimetypes::getInstance()
                                     ->fromFilename($file);
                
                $attachment = new Attachment();
                
                $attachment->setType($filetype);
                $attachment->setContent($content);
                $attachment->setFilename($attachment['Name']);
                
                $sendCloudMail->addAttachment($attachment);
                fclose($handle);
            }
            
            $templateName = $message->getTemplateName();
            if ($templateName === null) {
                $data = $message->getHtmlBody();
                if ($data !== null) {
                    $sendCloudMail->setContent($data);
                }
                $data = $message->getTextBody();
                if ($data !== null) {
                    $sendCloudMail->setPlain($data);
                }
            } else {
                $templateContent = new TemplateContent();
                $templateContent->setTemplateInvokeName($templateName);
                $templateVars = $message->getTemplateVars();
                foreach ($templateVars as $key => $var) {
                    $templateContent->addVars($key, $var);
                }
                $sendCloudMail->setTemplateContent($templateContent);
            }
            $result = $client->send($sendCloudMail);
            return $result->getStatusCode() == 200;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
