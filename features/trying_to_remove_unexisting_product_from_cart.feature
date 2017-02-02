@cart
Feature: Removing a product from cart which does not exist
    In order to catch human mistakes
    As a Visitor
    I want to be prevented from making mistakes

    Background:
        Given the store operates in "USD" currency

    @domain
    Scenario: Removing a product from cart which does not exist
        Given I have empty cart
        Then I should not be able to remove product "Banana t shirt"
