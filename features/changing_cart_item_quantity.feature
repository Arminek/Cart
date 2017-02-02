@cart
Feature: Changing cart item quantity in the cart
    In order to change quantity of my item in cart
    As a Visitor
    I want to be able to change its quantity

    Background:
        Given the store operates in "USD" currency
        And the store has a product "T-shirt apple" priced at "$30.00"
        And the store has a product "T-shirt banana" priced at "$10.00"

    @domain
    Scenario: Decreasing quantity of existing cart item
        Given I have 10 products "T-shirt banana" and 2 products "T-shirt apple" in the cart
        When I change quantity of "T-shirt banana" to 5
        Then product "T-shirt banana" quantity should be 5
        And my cart's total should be "$110.00"

    @domain
    Scenario: Increasing quantity of existing cart item
        Given I have 10 products "T-shirt banana" and 2 products "T-shirt apple" in the cart
        When I change quantity of "T-shirt apple" to 10
        Then product "T-shirt apple" quantity should be 10
        And my cart's total should be "$400.00"

    @domain
    Scenario: Trying to decrease quantity of existing cart item to zero
        Given I have 10 products "T-shirt banana" and 2 products "T-shirt apple" in the cart
        Then I should not be able to decrease quantity of "T-shirt banana" to "0"
        And my cart's total should be "$160.00"

    @domain
    Scenario: Trying to decrease quantity of existing cart item below zero
        Given I have 10 products "T-shirt banana" and 2 products "T-shirt apple" in the cart
        Then I should not be able to decrease quantity of "T-shirt banana" to "-10"
        And my cart's total should be "$160.00"