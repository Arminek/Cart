Feature: Adding a simple product of given quantity to the cart
    In order to buy multiple items at once
    As a Visitor
    I want to be able to add a simple product with stated quantity to the cart

    Background:
        Given the store operates in "USD" currency
        And the store has a product "T-shirt banana" priced at "$50.59"
        And the store has a product "T-shirt apple" priced at "$12.30"

    @domain
    Scenario: Adding a multiple simple products to the cart
        Given I have empty cart
        When I add 5 products "T-shirt banana" to the cart
        Then there should be one item in my cart
        And I should have 5 "T-shirt banana" in my cart
        And my cart's total should be "$252.95"

    @domain
    Scenario: Adding a multiple simple products to the cart
        Given I have empty cart
        When I add 5 products "T-shirt banana" to the cart
        And I add 4 products "T-shirt apple" to the cart
        Then there should be two items in my cart
        And my cart's total should be "$302.15"
