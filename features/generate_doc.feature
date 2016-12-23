Feature: Generate documentation from routing file and tests

    In order to share my API documentation with other people
    As a jadd user
    I should run documentation generation

    Scenario: Run documentation generation without collecting
        Given the tests have collected the following endpoints:
            """
            """
        And my routing file "routing.yml" looks like:
            """
            hotel_get:
                path: /hotels/{hotelId}
                defaults: { _controller: ui.controller.hotel_controller:getHotelAction }
                methods: [GET]
                options:
                    _documentation:
                        description: Fetch a hotel

            room_type_add:
                path: /hotels/{hotelId}/room-types
                defaults:
                    _controller: ui.controller.hotel_controller:addRoomType
                methods: [POST]
                options:
                    _documentation:
                        description: Add room type on hotel

            """
        When I generate the documentation from the routing file "routing.yml"
        Then it should fail with:
            """
            No endpoint collected before running documentation generation
            """

    Scenario: Run documentation generation after collecting data from tests
        Given the tests have collected the following endpoints:
            """
            POST,/hotels,application/json,[],"{""name"": ""hotel blue""}",201,application/json,"{""Location"":[""\/hotels\/123""]}",
            POST,/hotels,,[],,400,application/json,[],"{""errors"": [""invalid data""]}"
            GET,/hotels/123,,[],,200,application/json,[],"{""name"": ""hotel blue""}"
            """
        And a file named "json_schema/request/hotel_register.json" with:
            """
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "additionalProperties": false,
                "properties": {
                    "name": {
                        "type": "string",
                        "minLength": 1
                    }
                },
                "required": [
                    "name"
                ]
            }
            """
        And my routing file "routing.yml" looks like:
            """
            hotel_register:
                path: /hotels
                defaults:
                    _controller: ui.controller.hotel_controller:postHotelAction
                    _jsonSchema: { request: json_schema/request/hotel_register.json }
                methods: [POST]
                options:
                    _documentation:
                        description: Register a hotel

            hotel_get:
                path: /hotels/{hotelId}
                defaults: { _controller: ui.controller.hotel_controller:getHotelAction }
                methods: [GET]
                options:
                    _documentation:
                        description: Fetch a hotel

            room_type_add:
                path: /hotels/{hotelId}/room-types
                defaults:
                    _controller: ui.controller.hotel_controller:addRoomType
                methods: [POST]
                options:
                    _documentation:
                        description: Add room type on hotel

            """
        When I generate the documentation from the routing file "routing.yml"
        Then the documentation should be like
            """
            # Your project

            ## Register a hotel [POST /hotels]

            + Request (application/json)

                + Schema

                        {
                            "$schema": "http://json-schema.org/draft-04/schema#",
                            "type": "object",
                            "additionalProperties": false,
                            "properties": {
                                "name": {
                                    "type": "string",
                                    "minLength": 1
                                }
                            },
                            "required": [
                                "name"
                            ]
                        }

                + Body

                        {
                            "name": "hotel blue"
                        }

            + Response 201 (application/json)

                + Headers

                        Location: /hotels/123

            + Response 400 (application/json)

                + Body

                        {
                            "errors": [
                                "invalid data"
                            ]
                        }

            ## Fetch a hotel [GET /hotels/{hotelId}]

            + Parameters

                + hotelId

            + Response 200 (application/json)

                + Body

                        {
                            "name": "hotel blue"
                        }
            """
