<?php

namespace SendCloud\Mail;

use SendCloud\Util\TemplateContent;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\mail\BaseMessage;
use yii\mail\MailerInterface;

class Message extends BaseMessage
{
    /**
     * @var string|array from
     */
    protected $from;
    
    /**
     * @var array
     */
    protected $to = [];
    /**
     * @var string|array reply to
     */
    protected $replyTo;
    /**
     * @var array
     */
    protected $cc = [];
    /**
     * @var array
     */
    protected $bcc = [];
    /**
     * @var string
     */
    protected $subject;
    /**
     * @var string
     */
    protected $textBody;
    /**
     * @var string
     */
    protected $htmlBody;
    /**
     * @var array
     */
    protected $attachments = [];
    
    /**
     * @var TemplateContent
     */
    protected $templateContent;
    
    /**
     * @var string temporary attachment directory
     */
    protected $attachmentsTmdDir;
    
    /**
     * @var string
     */
    protected $templateName;
    
    /**
     * @var array
     */
    protected $templateVars = [];
    
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return parent::__toString(); // TODO: Change the autogenerated stub
    }
    
    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        throw new NotSupportedException();
    }
    
    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        throw new NotSupportedException();
    }
    
    public function getTextBody()
    {
        return $this->getTextBody();
    }
    
    /**
     * @inheritDoc
     */
    public function setTextBody($text)
    {
        $this->textBody = $text;
        return $this;
    }
    
    public function getHtmlBody()
    {
        return $this->getHtmlBody();
    }
    
    /**
     * @inheritDoc
     */
    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function attachContent($content, array $options = [])
    {
        if (!isset($options['fileName']) || empty($options['fileName'])) {
            throw new InvalidParamException('Filename is missing');
        }
        $filePath = $this->getTempDir() . '/' . $options['fileName'];
        if (file_put_contents($filePath, $content) === false) {
            throw new InvalidConfigException('Cannot write file \'' . $filePath . '\'');
        }
        $this->attach($filePath, $options);
        return $this;
    }
    
    /**
     * @return string temporary directory to store contents
     * @throws InvalidConfigException
     */
    protected function getTempDir()
    {
        if ($this->attachmentsTmdDir === null) {
            $uid = uniqid();
            $this->attachmentsTmdDir = Yii::getAlias('@app/runtime/' . $uid . '/');
            $status = true;
            if (file_exists($this->attachmentsTmdDir) === false) {
                $status = mkdir($this->attachmentsTmdDir, 0755, true);
            }
            if ($status === false) {
                throw new InvalidConfigException('Directory [\'' . $this->attachmentsTmdDir . '\'] cannot be created');
            }
        }
        return $this->attachmentsTmdDir;
    }
    
    /**
     * @inheritDoc
     */
    public function attach($fileName, array $options = [])
    {
        $attachment = [
            'File' => $fileName,
        ];
        if (!empty($options['fileName'])) {
            $attachment['Name'] = $options['fileName'];
        } else {
            $attachment['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
        }
        $this->attachments[] = $attachment;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function embedContent($content, array $options = [])
    {
        if (isset($options['fileName']) === false || empty($options['fileName'])) {
            throw new InvalidParamException('fileName is missing');
        }
        $filePath = $this->getTempDir() . '/' . $options['fileName'];
        if (file_put_contents($filePath, $content) === false) {
            throw new InvalidConfigException('Cannot write file \'' . $filePath . '\'');
        }
        $cid = $this->embed($filePath, $options);
        return $cid;
    }
    
    /**
     * @inheritDoc
     */
    public function embed($fileName, array $options = [])
    {
        $embed = [
            'File' => $fileName,
        ];
        if (!empty($options['fileName'])) {
            $embed['Name'] = $options['fileName'];
        } else {
            $embed['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
        }
        $embed['ContentID'] = 'cid:' . uniqid();
        $this->attachments[] = $embed;
        return $embed['ContentID'];
    }
    
    /**
     * @inheritDoc
     */
    public function toString()
    {
        return json_encode(array_filter([
                                            'from'        => $this->getFrom(),
                                            'subject'     => $this->getSubject(),
                                            'html_body'   => $this->htmlBody,
                                            'text_body'   => $this->textBody,
                                            'attachments' => $this->getAttachments(),
                                            'reply_to'    => $this->getReplyTo(),
                                            'tos'         => $this->getTo(),
                                            'ccs'         => $this->getCc(),
                                            'bccs'        => $this->getBcc(),
                                            'fromname'    => $this->getFromName(),
                                        ])
        );
    }
    
    /**
     * @inheritDoc
     */
    public function getFrom()
    {
        reset($this->from);
        list($email, $name) = each($this->from);
        
        // make sure $this->from following the format of ['email' => 'name']
        if (is_numeric($email) === true) {
            return $name;
        }
        return $email;
    }
    
    /**
     * @inheritDoc
     */
    public function setFrom($from)
    {
        if (is_string($from) === true) {
            $from = [$from];
        }
        $this->from = $from;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * @inheritDoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    public function getAttachments()
    {
        return empty($this->attachments) ? [] : $this->attachments;
    }
    
    /**
     * @inheritDoc
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }
    
    /**
     * @inheritDoc
     */
    public function setReplyTo($replyTo)
    {
        if (is_string($replyTo) === true) {
            $replyTo = [$replyTo];
        }
        $this->replyTo = $replyTo;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getTo()
    {
        return $this->normalizeEmails($this->to);
    }
    
    /**
     * @inheritDoc
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }
    
    /**
     * @param $emailsData
     * @return null|array
     *
     * This method will tranform any accepted email format into ['email' => 'name']
     */
    protected function normalizeEmails($emailsData)
    {
        $emails = null;
        if (empty($emailsData) === false) {
            if (is_array($emailsData) === true) {
                foreach ($emailsData as $key => $email) {
                    if (is_int($key) === true) {
                        $emails[$email] = null;
                    } else {
                        $emails[$key] = $email;
                    }
                }
            } elseif (is_string($emailsData) === true) {
                $emails[$emailsData] = null;
            }
        }
        return $emails;
    }
    
    /**
     * @inheritDoc
     */
    public function getCc()
    {
        return $this->normalizeEmails($this->cc);
    }
    
    /**
     * @inheritDoc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getBcc()
    {
        return $this->normalizeEmails($this->bcc);
    }
    
    /**
     * @inheritDoc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }
    
    public function getFromName()
    {
        reset($this->from);
        list($email, $name) = each($this->from);
        if (is_numeric($email) === false) {
            return $name;
        }
        
        return null;
    }
    
    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }
    
    /**
     * @param $templateName
     * @return $this
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }
    
    /**
     * @param array $vars
     * @return $this
     */
    public function setTemplateVars(array $vars)
    {
        $this->templateVars = $vars;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function send(MailerInterface $mailer = null)
    {
        $result = parent::send($mailer);
        
        //TODO: clean up tmpdir after ourselves
        return $result;
    }
    
}
