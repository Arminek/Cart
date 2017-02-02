@cart
Feature: Adding a product to the cart
    In order to select products for purchase
    As a Visitor
    I want to be able to add products to cart

    Background:
        Given the store operates in "USD" currency
        And the store has a product "Oathkeeper" priced at "$30.00"
        And the store has a product "T-shirt banana" priced at "$10.00"

    @domain
    Scenario: Adding a product to the cart
        Given I have empty cart
        When I add product "T-shirt banana" to the cart
        Then I should have 1 "T-shirt banana" in my cart
        And my cart's total should be "$10.00"

    @domain
    Scenario: Adding a multiple products to the cart
        Given I have empty cart
        When I add product "Oathkeeper" to the cart
        And I add product "T-shirt banana" to the cart
        Then there should be two items in my cart
        And my cart's total should be "$40.00"

    @domain
    Scenario: Adding twice the same product to the cart updates its quantity
        Given I have empty cart
        When I add product "T-shirt banana" to the cart
        And I add product "T-shirt banana" to the cart
        Then there should be one item in my cart
        And its quantity should be two
        And my cart's total should be "$20.00"

    @domain
    Scenario: Adding twice the same product to the not empty cart
        Given I have cart with product "T-shirt banana"
        When I add product "T-shirt banana" to the cart
        Then there should be one item in my cart
        And its quantity should be two
        And my cart's total should be "$20.00"

    @domain
    Scenario: Adding different product to the not empty cart
        Given I have cart with product "T-shirt banana"
        When I add product "Oathkeeper" to the cart
        And I add product "Oathkeeper" to the cart
        Then there should be two items in my cart
        And product "Oathkeeper" quantity should be 2
        And product "T-shirt banana" quantity should be 1
        And my cart's total should be "$70.00"
