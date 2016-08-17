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

Of course you have tests for your API. So why not listening them and record all the cases you test ?

### Consolidate routing file

Then just need to run the `bin/jadd generate <myRoutingFile> <outputFile>`

It will parse your routes and consolidate the responses with tests recording.

Best place would be in `onSuccess` hook of your CI.

## Routing supported

- Symfony YAML

## Output supported

- [APIBlueprint](https://apiblueprint.org/)
