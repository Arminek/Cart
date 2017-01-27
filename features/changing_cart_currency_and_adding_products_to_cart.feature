Feature: All of my cart's values get updated to the currency of my choosing
    In order to know how much I have to pay in my currency
    As a Visitor
    I want to see every cash amount in my chosen currency

    Background:
        Given the store operates in "USD" currency
        And the store has a product "The Pug Mug" priced at "$10.00"
        And the store has a product "The Apple Mug" priced at "$20.00"

    @domain
    Scenario: Changing the currency of my cart with multiple products and adding product to the cart
        Given I have product "The Apple Mug" in the cart
        When I switch to the "EUR" currency
        And I add product "The Pug Mug" to the cart
        And I switch back to the "USD" currency
        Then my cart's total should be "$30.00"

    @domain
    Scenario: Changing the currency of my cart with multiple products and adding multiple products to the cart
        Given I have 10 products "The Apple Mug" in the cart
        When I switch to the "EUR" currency
        And I add 5 products "The Pug Mug" to the cart
        And I switch back to the "USD" currency
        Then my cart's total should be "$250.00"
