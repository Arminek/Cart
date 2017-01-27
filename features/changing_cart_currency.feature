Feature: All of my cart's values get updated to the currency of my choosing
    In order to know how much I have to pay in my currency
    As a Visitor
    I want to see every cash amount in my chosen currency

    Background:
        Given the store operates in "USD" currency
        And the store has a product "The Pug Mug" priced at "$10.00"
        And the store has a product "The Apple Mug" priced at "$20.00"

    @domain
    Scenario: Changing the currency of my cart
        Given I have product "The Pug Mug" in the cart
        When I switch to the "EUR" currency
        Then my cart's total should be "$20.00"

    @domain
    Scenario: Changing back to the base currency
        Given I have product "The Pug Mug" in the cart
        When I switch to the "EUR" currency
        And I switch back to the "USD" currency
        Then my cart's total should be "$10.00"

    @domain
    Scenario: Changing the currency of my cart more than once
        Given I have product "The Pug Mug" in the cart
        When I switch to the "EUR" currency
        And I switch back to the "USD" currency
        And I switch back to the "EUR" currency
        Then my cart's total should be "$20.00"

    @domain
    Scenario: Changing the currency of my cart with multiple products
        Given I have 10 products "The Pug Mug" in the cart
        And I have product "The Apple Mug" in the cart
        When I switch to the "EUR" currency
        Then my cart's total should be "$240.00"
