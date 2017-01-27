Feature: Adding a simple product of given quantity to the cart
    In order to buy multiple items at once
    As a Visitor
    I want to be able to add a simple product with stated quantity to the cart

    Background:
        Given the store operates in "USD" currency

    @domain
    Scenario: Adding a multiple simple products to the cart
        Given the store has a product "T-shirt banana" priced at "$10.00"
        When I add 5 products "T-shirt banana" to the cart
        Then there should be one item in my cart
        And my cart's total should be "$50.00"

    @domain
    Scenario: Adding a multiple simple products to the cart
        Given the store has a product "T-shirt banana" priced at "$10.00"
        And the store has a product "T-shirt apple" priced at "$20.00"
        When I add 5 products "T-shirt banana" to the cart
        And I add 4 products "T-shirt apple" to the cart
        Then there should be two item in my cart
        And my cart's total should be "$130.00"
