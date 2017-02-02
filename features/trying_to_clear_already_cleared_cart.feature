@cart
Feature: Clearing cart which is already empty
    In order to catch human mistakes
    As a Visitor
    I want to be prevented from making mistakes

    Background:
        Given the store operates in "USD" currency

    @domain
    Scenario: Clearing cart which is already empty
        Given I have empty cart
        Then I should not be able to clear cart which is already empty
