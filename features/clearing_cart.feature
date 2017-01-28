Feature: Clearing cart
    In order to quick start shopping again
    As a Visitor
    I want to be able to clear my cart

    Background:
        Given the store operates in "USD" currency
        And the store has a product "T-shirt banana" priced at "$12.54"
        And the store has a product "T-shirt apple" priced at "$10.00"
        And I have 10 products "T-shirt banana" in the cart
        And I have 2 products "T-shirt apple" in the cart

    @domain @todo
    Scenario: Clearing cart
        When I clear my cart
        Then my cart should be empty
        And my cart's total should be "$0.00"
