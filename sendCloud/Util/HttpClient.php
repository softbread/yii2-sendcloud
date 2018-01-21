<?php

namespace SendCloud\Util;

use yii\httpclient\Client;

class HttpClient
{
    public function __construct($host)
    {
        $this->host = $host;
    }
    
    /**
     * @param       $url
     * @param array $param
     * @return \yii\httpclient\Response
     */
    public function post($url, array $param)
    {
        $client = new Client(['baseUrl' => $this->host]);
        
        $request = $client->createRequest()
                          ->setUrl($url)
                          ->setMethod('post')
                          ->setData($param);
        
        return $request->send();
    }
    
    /**
     * @param       $url
     * @param array $param
     * @param array $attachments
     * @return \yii\httpclient\Response
     */
    public function postWithAttachments($url, array $param, array $attachments)
    {
        $client = new Client(['baseUrl' => $this->host]);
        $request = $client->createRequest()
                          ->setUrl($url)
                          ->setMethod('post')
                          ->setData($param);
        
        /** @var Attachment $attachment */
        foreach ($attachments as $attachment) {
            $request->addFileContent(
                'attachments',
                $attachment->getContent(),
                [
                    'fileName' => $attachment->getFilename(),
                    'mimeType' => $attachment->getType(),
                ]
            );
        }
        return $request->send();
    }
}
