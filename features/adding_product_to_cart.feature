Feature: Adding a simple product to the cart
    In order to select products for purchase
    As a Visitor
    I want to be able to add products to cart

    Background:
        Given the store operates in "USD" currency

    @domain
    Scenario: Adding a simple product to the cart
        Given the store has a product "T-shirt banana" priced at "$12.54"
        When I add this product to the cart
        Then there should be one item in my cart
        And my cart's total should be "$12.54"

    @domain
    Scenario: Adding a multiple products to the cart
        Given the store has a product "Oathkeeper" priced at "$99.99"
        And the store has a product "T-shirt banana" priced at "$12.01"
        When I add product "Oathkeeper" to the cart
        And I add product "T-shirt banana" to the cart
        Then there should be two items in my cart
        And my cart's total should be "$112.00"

    @domain
    Scenario: Adding a twice the same product to the cart updates its quantity
        Given the store has a product "T-shirt banana" priced at "$10.00"
        When I add product "T-shirt banana" to the cart
        And I add product "T-shirt banana" to the cart
        Then there should be one item in my cart
        And my cart's total should be "$20.00"

    @domain
    Scenario: Adding a twice the same product to the not empty cart
        Given the store has a product "Oathkeeper" priced at "$30.00"
        And the store has a product "T-shirt banana" priced at "$10.00"
        And I have product "Oathkeeper" in the cart
        When I add product "T-shirt banana" to the cart
        And I add product "T-shirt banana" to the cart
        Then there should be two items in my cart
        And my cart's total should be "$50.00"
