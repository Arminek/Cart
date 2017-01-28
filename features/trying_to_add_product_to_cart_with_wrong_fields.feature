Feature: Adding a product to the cart with wrong fields
    In order to catch human mistakes
    As a Visitor
    I want to be prevented from making mistakes

    Background:
        Given the store operates in "USD" currency
        And the store has a product "Oathkeeper" priced at "$30.00"
        And the store has a product "T-shirt banana" priced at "$10.00"
        And the store has a product "Banana below zero" priced at "-$10.00"

    @domain
    Scenario: Adding a product to the cart with empty code
        Given I have empty cart
        Then I should not be able to add product with empty code

    @domain
    Scenario: Adding a products to the cart with wrong quantity
        Given I have empty cart
        Then I should not be able to add product with quantity below zero

    @domain
    Scenario: Adding a products to the cart in different currency
        Given I have empty cart
        Then I should not be able to add product in "EUR" currency

    @domain
    Scenario: Adding a products to the cart with price below zero
        Given I have empty cart
        Then I should not be able to add product "Banana below zero"
