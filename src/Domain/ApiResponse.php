<?php

namespace Rezzza\Jadd\Domain;

class ApiResponse
{
    private $statusCode;

    private $contentType;

    private $body;

    private $headers;

    public function __construct($statusCode, $contentType, $body, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->setContentType($contentType);
        if (strlen($body) > 0) {
            $this->body = json_encode($this->cleanJson($body));
        }
        $this->setHeaders($headers);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function mergeBody($body)
    {
        if (strlen($body) > 0) {
            $this->body += $this->cleanJson($body);
        }
    }

    private function cleanJson($json)
    {
        return array_filter(
            json_decode($json, true),
            function ($value) {
                if (is_string($value)) {
                    return strlen($value) > 0;
                }

                if (is_array($value)) {
                    return count($value) > 0;
                }
            }
        );
    }

    private function setHeaders(array $headers)
    {
        $this->headers = Endpoint::cleanHeaders($headers);
    }

    private function setContentType($contentType)
    {
        if (is_array($contentType)) {
            $contentType = implode(', ', $contentType);
        }
        $this->contentType = $contentType;
    }
}
