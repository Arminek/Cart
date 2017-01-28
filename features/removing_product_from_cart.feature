Feature: Removing cart item from cart
    In order to delete some unnecessary cart items
    As a Visitor
    I want to be able to remove cart item

    Background:
        Given the store operates in "USD" currency
        And the store has a product "T-shirt banana" priced at "$12.54"
        And the store has a product "T-shirt apple" priced at "$10.00"
        And I have 10 products "T-shirt banana" and 2 products "T-shirt apple" in the cart

    @domain
    Scenario: Removing one cart item
        When I remove product "T-shirt banana" from the cart
        Then there should be one item in my cart
        And my cart's total should be "$20.00"

    @domain
    Scenario: Removing two cart item
        When I remove product "T-shirt banana" from the cart
        And I remove product "T-shirt apple" from the cart
        Then my cart should be empty
        And my cart's total should be "$0.00"

    @domain
    Scenario: Removing cart item with multiple and different cart items
        When I remove product "T-shirt banana" from the cart
        Then there should be one item in my cart
        And my cart's total should be "$20.00"
