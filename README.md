# JADD
Jadd : Another Documentation Dumper

## Why ?

- Because we love reading documentation on REST API.
- We really don't like writing documentation.
- We really don't like updating documentation.
- Annotations are evil

## How ?

2 steps are needed

### Collect the responses during functional tests

Of course you have tests for your API. So why not connecting to it and record all the cases you test ?

If you use [php-http](http://php-http.org), we provide a [middleware to collect the data](https://github.com/rezzza/jadd/blob/master/src/Infra/Http/CollectEndpointPlugin.php) during your tests. [Have a look on the test](https://github.com/rezzza/jadd/blob/master/features/collect_endpoint.feature#L41) to be sure how to use it.

### Consolidate routing file

Then just need to run the `bin/jadd generate <myRoutingFile> <outputFile>`

It will parse your routes and consolidate the responses with tests recording.

Best place would be in `onSuccess` hook of your CI.

## Install

For now, we support only install on PHP project through composer :
```
composer require --dev rezzza/jadd:dev-master
```

## Routing supported

- Symfony YAML

## Output supported

- [APIBlueprint](https://apiblueprint.org/)
