@cart
Feature: Changing currency validation
    In order to catch human mistakes
    As a Visitor
    I want to be prevented from making mistakes

    Background:
        Given the store operates in "USD" and "EUR" currency
        And the store has convert ratio 2.5 between "USD" and "EUR"
        And the store has convert ratio 0.4 between "EUR" and "USD"
        And the store has a product "The Pug Mug" priced at "$10.00"
        And the store has a product "The Apple Mug" priced at "$20.00"

    @domain
    Scenario: Changing the currency of my cart without conversion ratio
        Given I have cart with product "The Pug Mug"
        Then I should not be able to switch currency to "PLN"
        And my cart's total should be "€10.00"

    @domain
    Scenario: Changing the currency of my cart to not existing currency
        Given I have cart with product "The Pug Mug"
        Then I should not be able to switch currency to "ABCDE"
        And my cart's total should be "€10.00"

    @domain
    Scenario: Adding product in different currency
        Given I have cart with product "The Pug Mug"
        When I switch to the "EUR" currency
        Then I should not be able to add product "The Apple Mug" to the cart in "USD" currency
        And my cart's total should be "€25.00"
