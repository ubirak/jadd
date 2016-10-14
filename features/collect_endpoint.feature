Feature: Collect endpoints when running tests

    In order to keep up to date my API documentation
    As a jadd user
    I should collect endpoints result from tests

    Scenario: Run behat tests
        Given a file named "behat.yml" with:
            """
            default:
                extensions:
                    Rezzza\MocoBehatExtension\MocoExtension:
                        json_file: features/fixtures.yml
                        hostname: 127.0.0.1
                        port: 9999
                    Rezzza\RestApiBehatExtension\Extension:
                        rest:
                            base_url: http://127.0.0.1:9999
                suites:
                    default:
                        contexts:
                            - FeatureContext
                            - Rezzza\RestApiBehatExtension\RestApiContext
                            - Rezzza\MocoBehatExtension\MocoContext
            """
        And a file named "features/bootstrap/FeatureContext.php" with:
            """
            <?php
            use Behat\Behat\Context\Context;
            use Rezzza\MocoBehatExtension\MocoWriter;
            use Behat\Gherkin\Node\PyStringNode;
            use Rezzza\RestApiBehatExtension\Rest\RestApiBrowser;
            use Rezzza\Jadd\Domain\EndpointCollector;

            class FeatureContext implements Context
            {
                private $mocoWriter;
                public function __construct(MocoWriter $mocoWriter, RestApiBrowser $restApiBrowser)
                {
                    $this->mocoWriter = $mocoWriter;
                    $restApiBrowser->useHttpClient(
                        new \Http\Client\Common\PluginClient(
                            new \Http\Client\Curl\Client,
                            [new \Rezzza\Jadd\Infra\Http\CollectEndpointPlugin]
                        )
                    );
                    EndpointCollector::reset();
                }
                /**
                 * @Given I mock the following endpoint:
                 */
                public function mockHttpCall(PyStringNode $endpoint)
                {
                    $json = json_decode((string) $endpoint, true);
                    $this->mocoWriter->mockHttpCall(
                        $json['request'],
                        $json['response']
                    );
                    $this->mocoWriter->writeForMoco();
                }
            }
            """
        And a file named "features/call_moco.feature" with:
            """
            Feature: Call Moco
                In order to mock external call
                As a feature runner
                I need to call moco
                @moco
                Scenario: Call moco
                    Given I mock the following endpoint:
                        '''
                        {
                            "request": {
                                "uri": "/hotels/wrong",
                                "method": "GET",
                                "headers": {
                                    "content-type" : "application/json"
                                }
                            },
                            "response": {
                                "status" : 502,
                                "text": "Bad Gateway"
                            }
                        }
                        '''
                    And I mock the following endpoint:
                        '''
                        {
                            "request": {
                                "uri": "/hotels",
                                "method": "POST",
                                "headers": {
                                    "content-type" : "application/json"
                                }
                            },
                            "response": {
                                "status" : 201,
                                "headers": {
                                    "Content-Type": "application/json",
                                    "Location": "/hotels/123"
                                }
                            }
                        }
                        '''
                    And I mock the following endpoint:
                        '''
                        {
                            "request": {
                                "uri": "/hotels",
                                "method": "POST",
                                "headers": {
                                    "content-type" : "text/html"
                                }
                            },
                            "response": {
                                "status" : 400,
                                "headers": {
                                    "Content-Type": "application/json"
                                },
                                "json": {"errors": ["invalid data"]}
                            }
                        }
                        '''
                    And I mock the following endpoint:
                        '''
                        {
                            "request": {
                                "uri": "/hotels/123",
                                "method": "GET"
                            },
                            "response": {
                                "status": 200,
                                "headers": {
                                    "Content-Type": "application/json"
                                },
                                "json": {"name": "hotel blue"}
                            }
                        }
                        '''
                    When I set "Content-Type" header equal to "application/json"
                    And I send a GET request to "/hotels/wrong"
                    And I send a POST request to "/hotels" with body:
                        '''
                        {"name":"hotel blue"}
                        '''
                    And I set "Content-Type" header equal to "text/html"
                    And I send a POST request to "/hotels"
                    When I set "Content-Type" header equal to "application/json"
                    And I send a GET request to "/hotels/123"
                    Then print response
            """
        And I start moco
        When I run behat "features/call_moco.feature"
        Then the tests should have collected the following endpoints:
            """
            POST,/hotels,application/json,[],"{""name"":""hotel blue""}",201,application/json,"{""Location"":[""\/hotels\/123""]}",
            POST,/hotels,text/html,[],,400,application/json,[],"{""errors"":[""invalid data""]}"
            GET,/hotels/123,application/json,[],,200,application/json,[],"{""name"":""hotel blue""}"
            """
