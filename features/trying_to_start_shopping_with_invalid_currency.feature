@cart
Feature: Start shopping with invalid currency
    In order to catch human mistakes
    As a Visitor
    I want to be prevented from making mistakes

    Background:
        Given the store operates in "ABCDE" currency

    @domain
    Scenario: Start shopping with invalid currency
        Then I should not be able to buy products in store with invalid currency
