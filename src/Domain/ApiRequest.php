<?php

namespace Rezzza\Jadd\Domain;

class ApiRequest
{
    private $method;

    private $uri;

    private $contentType;

    private $headers;

    private $body;

    private $jsonSchema;

    public function __construct($method, $uri, $contentType, array $headers, $body = null)
    {
        $this->method = $method;
        $this->uri = parse_url($uri, PHP_URL_PATH);
        $this->setContentType($contentType);
        $this->headers = $headers;
        if (strlen($body) > 0) {
            $this->body = $body;
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getJsonSchema()
    {
        return $this->jsonSchema;
    }

    public function setJsonSchema($jsonSchema)
    {
        $this->jsonSchema = $jsonSchema;
    }

    public function needToBeDocumented()
    {
        return count($this->headers) > 0 || null !== $this->jsonSchema || null !== $this->getBody();
    }

    private function setContentType($contentType)
    {
        if (is_array($contentType)) {
            $contentType = implode(', ', $contentType);
        }
        $this->contentType = $contentType;
    }
}
