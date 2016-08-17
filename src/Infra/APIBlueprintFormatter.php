<?php

namespace Rezzza\Jadd\Infra;

use Rezzza\Jadd\Domain\OutputFormatter;
use Rezzza\Jadd\Infra\Filesystem\FileReader;

class APIBlueprintFormatter implements OutputFormatter
{
    private $fileReader;

    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function formatRoutes(array $routes)
    {
        $output = $this->addTitle('Your project', 1);

        foreach ($routes as $route) {
            $output .= $this->addTitle(
                sprintf(
                    '%s [%s %s]',
                    $route->getDescription(),
                    $route->getMethod(),
                    $route->getPath()
                ),
                2
            );

            if (count($route->getVariables()) > 0) {
                $output .= $this->addSubtitle('Parameters', 1);
                foreach ($route->getVariables() as $variable) {
                    $output .= $this->addSubtitle($variable, 2, false);
                }
                $output .= "\n";
            }

            $request = $route->getRequest();
            if ($request->needToBeDocumented()) {
                $output .= $this->addSubtitle(sprintf('Request (%s)', $request->getContentType()), 1);
                $output .= $this->outputKeyValue('Headers', $request->getHeaders());

                $jsonSchema = $request->getJsonSchema();
                if (null !== $jsonSchema) {
                    $output .= $this->addSubtitle('Schema', 2);
                    $output .= $this->addIndentation($this->fileReader->read($jsonSchema), 12);
                    $output .= "\n\n";
                }

                if (null !== $request->getBody()) {
                    $output .= $this->addSubtitle('Body', 2);
                    $output .= $this->addIndentation($this->prettyJson($request->getBody()), 12);
                    $output .= "\n\n";
                }
            }

            foreach ($route->getAvailableResponses() as $response) {
                $output .= $this->addSubtitle(
                    sprintf('Response %s (%s)', $response->getStatusCode(), $response->getContentType()),
                    1
                );
                $output .= $this->outputKeyValue('Headers', $response->getHeaders());

                if (null !== $response->getBody()) {
                    $output .= $this->addSubtitle('Body', 2);
                    $output .= $this->addIndentation($this->prettyJson($response->getBody()), 12);
                    $output .= "\n\n";
                }
            }
        }

        return $output;
    }

    private function addTitle($title, $level)
    {
        return sprintf("%s %s\n\n", str_repeat('#', $level), $title);
    }

    private function addSubtitle($title, $level, $jumpLine = true)
    {
        return sprintf("%s+ %s\n", str_repeat(' ', ($level - 1) * 4), $title).($jumpLine ? "\n" : '');
    }

    private function addIndentation($subject, $nbSpace)
    {
        $newString = '';
        $separator = "\r\n";
        $line = strtok($subject, $separator);
        $newString .= str_pad('', $nbSpace).$line.$separator;

        while ($line !== false) {
            $line = strtok($separator);
            $newString .= str_pad('', $nbSpace).$line.$separator;
        }

        return rtrim($newString);
    }

    private function outputKeyValue($subtitle, array $data)
    {
        if (count($data) <= 0) {
            return '';
        }

        $output = $this->addSubtitle($subtitle, 2);

        foreach ($data as $header => $value) {
            $output .= $this->addIndentation(sprintf("%s: %s", $header, implode(', ', $value)), 12);
        }
        $output .= "\n\n";

        return $output;
    }

    private function prettyJson($json)
    {
        return json_encode(json_decode($json, true), JSON_PRETTY_PRINT);
    }
}
