<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Broadway\Domain\DomainMessage;
use Money\Currency;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Money;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Adapter\AvailableCurrencies\ISOCurrenciesProvider;
use SyliusCart\Domain\Adapter\MoneyConverting\CartMoneyConverter;
use SyliusCart\Domain\Adapter\Exchange\FixedCurrencyExchangeRateProvider;
use SyliusCart\Domain\Event\CartCleared;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartItemQuantityChanged;
use SyliusCart\Domain\Event\CartItemRemoved;
use SyliusCart\Domain\Event\CartRecalculated;
use SyliusCart\Domain\Exception\CartAlreadyEmptyException;
use SyliusCart\Domain\Exception\CartCurrencyMismatchException;
use SyliusCart\Domain\Exception\CartCurrencyNotSupportedException;
use SyliusCart\Domain\Exception\CartItemNotFoundException;
use SyliusCart\Domain\Exception\InvalidCartItemQuantityException;
use SyliusCart\Domain\Exception\InvalidCartItemUnitPriceException;
use SyliusCart\Domain\Exception\ProductCodeCannotBeEmptyException;
use SyliusCart\Domain\Model\Cart;
use SyliusCart\Domain\Model\CartItem;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartContext implements Context
{
    /**
     * @var Currency[]
     */
    private $storeCurrencies = [];

    /**
     * @var Currency
     */
    private $storeOperatingCurrency;

    /**
     * @var array
     */
    private $productCatalogue = [];

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var array
     */
    private $cartEvents = [];

    /**
     * @var array
     */
    private $cartExchangeRateConfiguration = [];

    /**
     * @Given the store operates in :currencyCode currency
     * @Given the store operates in :currencyCode and :secondCurrencyCode currency
     */
    public function theStoreOperatesInCurrency(string ...$currencyCodes): void
    {
        foreach ($currencyCodes as $code) {
            $this->storeCurrencies[$code] = new Currency($code);
        }

        $this->storeOperatingCurrency = reset($this->storeCurrencies);
    }

    /**
     * @Given /^the store has a product "([^"]+)" priced at ("[^"]+")$/
     */
    public function theStoreHasAProductPricedAt(string $productName, int $price): void
    {
        $productCode = $this->getCodeFromName($productName);

        $this->productCatalogue[$productCode] = ['code' => $productCode, 'name' => $productName, 'price' => $price];
    }

    /**
     * @Given I have empty cart
     */
    public function iHaveEmptyCart(): void
    {
        $cartAdapters = $this->getCartAdaptersBasedOnStoreConfiguration();
        $this->cart = Cart::createWithAdapters($cartAdapters['converter'], $cartAdapters['currencies_provider']);
        $this->cart->apply(CartInitialized::occur(Uuid::uuid4(), new Money(0, $this->storeOperatingCurrency)));
    }

    /**
     * @Given I have cart with product :productName
     */
    public function iHaveCartWithProduct(string $productName): void
    {
        $this->initCartWithProducts([$productName => 1]);
    }

    /**
     * @Given I have :firstQuantity products :firstProductName and :secondQuantity products :secondProductName in the cart
     */
    public function iHaveProductsAndProductsInTheCart(
        string $firstQuantity,
        string $firstProductName,
        string $secondQuantity,
        string $secondProductName
    ): void {
        $this->initCartWithProducts([$firstProductName => (int) $firstQuantity, $secondProductName => (int) $secondQuantity]);
    }

    /**
     * @Given the store has convert ratio :ratio between :baseCurrencyCode and :counterCurrencyCode
     */
    public function theStoreHasConvertRatioEqualsBetweenAnd(string $ratio, string $baseCurrencyCode, string $counterCurrencyCode): void
    {
        $this->cartExchangeRateConfiguration[$baseCurrencyCode] = [$counterCurrencyCode => $ratio];
    }

    /**
     * @When I add product :productName to the cart
     * @When I add :quantity products :productName to the cart
     */
    public function iAddProductToTheCart(string $productName, string $quantity = '1'): void
    {
        $code = $this->getCodeFromName($productName);
        $product = $this->findProductByCode($code);

        $this->cart->addCartItem($code, (int) $quantity, $product['price'], $this->storeOperatingCurrency->getCode());
    }

    /**
     * @When I clear my cart
     */
    public function iClearMyCart()
    {
        $this->cart->clear();
    }

    /**
     * @When I remove product :productName from the cart
     */
    public function iRemoveProductFromTheCart(string $productName): void
    {
        $cartItemId = $this->productCatalogue[$this->getCodeFromName($productName)]['cartItemId'];
        $this->cart->removeCartItem((string) $cartItemId);
    }

    /**
     * @When I switch to the :currencyCode currency
     * @When I switch back to the :currencyCode currency
     */
    public function iSwitchToTheCurrency(string $currencyCode): void
    {
        $this->cart->changeCurrency($currencyCode);
        $this->storeOperatingCurrency = $this->storeCurrencies[$currencyCode];
    }

    /**
     * @When I change quantity of :productName to :quantity
     */
    public function iChangeQuantityOfTo(string $productName, string $quantity): void
    {
        $cartItemId = $this->productCatalogue[$this->getCodeFromName($productName)]['cartItemId'];
        $this->cart->changeCartItemQuantity((string) $cartItemId, (int) $quantity);
    }

    /**
     * @Then there should be one item in my cart
     */
    public function thereShouldBeOneItemInMyCart(): void
    {
        $this->assertCartItemCount(1);
    }

    /**
     * @Then there should be two items in my cart
     */
    public function thereShouldBeTwoItemInMyCart(): void
    {
        $this->assertCartItemCount(2);
    }

    /**
     * @Then /^my cart's total should be ("[^"]+")$/
     */
    public function myCartSTotalShouldBe(int $total): void
    {
        $cartRecalculatedEvents = $this->getCartRecalculatedEvents();
        /** @var CartRecalculated $lastCartRecalcualtedEvent */
        $lastCartRecalculatedEvent = end($cartRecalculatedEvents);

        $expectedTotal = new Money($total, $this->storeOperatingCurrency);

        if (!$expectedTotal->equals($lastCartRecalculatedEvent->getNewCartTotal())) {
            throw new \RuntimeException(
                sprintf(
                    'Expected cart total "%s", got "%s"',
                    $expectedTotal->getAmount(),
                    $lastCartRecalculatedEvent->getNewCartTotal()->getAmount())
            );
        }
    }

    /**
     * @Then /^its quantity should be two$/
     */
    public function itsQuantityShouldBeTwo(): void
    {
        $quantityIncreasedEvents = $this->getCartItemQuantityChangedEvents();
        /** @var CartItemQuantityIncreased $quantityIncreasedEvent */
        $quantityIncreasedEvent = end($quantityIncreasedEvents);

        if (2 !== $quantityIncreasedEvent->getNewCartItemQuantity()->getNumber()) {
            throw new \RuntimeException(
                sprintf(
                    'Quantity of this item should be two but is "%s"',
                    $quantityIncreasedEvent->getNewCartItemQuantity()->getNumber()
                )
            );
        }
    }

    /**
     * @Then I should have :quantity :productName in my cart
     */
    public function iShouldHaveInMyCart(string $quantity, string $productName): void
    {
        $cartItemAddedEvents = $this->getCartItemAddedEvents();
        $quantity = CartItemQuantity::create((int) $quantity);
        $productCode = ProductCode::fromString($this->getCodeFromName($productName));

        /** @var CartItemAdded $lastCartItemAddedEvent */
        $lastCartItemAddedEvent = end($cartItemAddedEvents);
        $quantityFromEvent = $lastCartItemAddedEvent->getCartItem()->quantity();
        $productCodeFromEvent = $lastCartItemAddedEvent->getCartItem()->productCode();

        if (!$quantity->equals($quantityFromEvent) && !$productCode->equals($productCodeFromEvent)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected quantity for product "%s" was "%s", but got "%s"',
                    $quantity->getNumber(),
                    $productCode
                )
            );
        }
    }

    /**
     * @Then my cart should be empty
     */
    public function myCartShouldBeEmpty(): void
    {
        $cartClearedEvents = $this->getCartItemClearedEvents();

        if (0 === count($cartClearedEvents)) {
            $this->assertCartItemCount(0);
        }
    }

    /**
     * @Then I should not be able to add product with empty code
     */
    public function iShouldNotBeAbleToAddProductWithEmptyCode(): void
    {
        try {
            $this->cart->addCartItem('', 10, 100, $this->storeOperatingCurrency->getCode());
        } catch (ProductCodeCannotBeEmptyException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able to add product with empty code.');
    }

    /**
     * @Then I should not be able to add product with quantity below zero
     */
    public function iShouldNotBeAbleToAddProductWithQuantityBelowZero(): void
    {
        $catch = false;
        try {
            $this->cart->addCartItem('code', 0, 100, $this->storeOperatingCurrency->getCode());
        } catch (InvalidCartItemQuantityException $exception) {
            $catch = true;
        }

        try {
            $this->cart->addCartItem('code', -10, 100, $this->storeOperatingCurrency->getCode());
        } catch (InvalidCartItemQuantityException $exception) {
            $catch = $catch && true;
        }

        if ($catch) {
            return;
        }

        throw new \RuntimeException('I should not be able to add product with quantity below or equals zero.');
    }

    /**
     * @Then I should not be able to add product in :currencyCode currency
     */
    public function iShouldNotBeAbleToAddProductInCurrency(string $currencyCode): void
    {
        try {
            $this->cart->addCartItem('code', 10, 100, $currencyCode);
        } catch (CartCurrencyMismatchException $exception) {
            return;
        }

        throw new \RuntimeException(sprintf(
            'I should not be able to add product with different currency. Store currency "%s"',
            $this->storeOperatingCurrency->getCode()
        ));
    }

    /**
     * @Then I should not be able to add product :productName
     */
    public function iShouldNotBeAbleToAddProduct(string $productName): void
    {
        $productCode = $this->getCodeFromName($productName);
        $productPrice = $this->productCatalogue[$productCode]['price'];
        try {
            $this->cart->addCartItem($productCode, 10, (int) $productPrice, $this->storeOperatingCurrency->getCode());
        } catch (InvalidCartItemUnitPriceException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able to add product with unit price below zero.');
    }

    /**
     * @Then I should not be able to remove product :productName
     */
    public function iShouldNotBeAbleToRemoveProduct(string $productName): void
    {
        $fakeId = $this->getCodeFromName($productName);

        $catch = false;
        try {
            $this->cart->removeCartItem($fakeId);
        } catch (\InvalidArgumentException $exception) {
            $catch = true;
        }

        try {
            $this->cart->removeCartItem(Uuid::uuid4()->toString());
        } catch (CartItemNotFoundException $exception) {
            $catch = $catch && true;
        }

        if ($catch) {
            return;
        }

        throw new \RuntimeException('I should not be able to remove product which does not exist.');
    }

    /**
     * @Then I should not be able to clear cart which is already empty
     */
    public function iShouldNotBeAbleToClearCartWhichIsAlreadyEmpty()
    {
        try {
            $this->cart->clear();
        } catch (CartAlreadyEmptyException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able clear already empty cart.');
    }

    /**
     * @Then I should not be able to buy products in store with invalid currency
     */
    public function iShouldNotBeAbleToBuyProductsInStoreWithInvalidCurrency()
    {
        $cartAdapters = $this->getCartAdaptersBasedOnStoreConfiguration();
        try {
            Cart::initialize(Uuid::uuid4(), $this->storeOperatingCurrency->getCode(), $cartAdapters['converter'], $cartAdapters['currencies_provider']);
        } catch (CartCurrencyNotSupportedException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able to initialize cart with invalid currency code.');
    }

    /**
     * @Then product :productName quantity should be :quantity
     */
    public function productQuantityShouldBe(string $productName, string $quantity): void
    {
        $addedEvents = $this->getCartItemAddedEvents();
        $productCode = ProductCode::fromString($this->getCodeFromName($productName));
        $quantity = CartItemQuantity::create((int) $quantity);

        $addedEventForProduct = array_filter($addedEvents, function (CartItemAdded $event) use ($productCode) {
            return $event->getCartItem()->productCode()->equals($productCode);
        });

        /** @var CartItemAdded $event */
        $event = end($addedEventForProduct);

        if (!$event->getCartItem()->quantity()->equals($quantity)) {
            throw new \RuntimeException(
                sprintf(
                    'Quantity of this item should be "%s" but is "%s"',
                    $quantity->getNumber(),
                    $event->getCartItem()->quantity()->getNumber()
                )
            );
        }
    }

    /**
     * @Then I should not be able to switch currency to :currencyCode
     */
    public function iShouldNotBeAbleToSwitchCurrencyTo(string $currencyCode): void
    {
        try {
            $this->cart->changeCurrency($currencyCode);
        } catch (UnresolvableCurrencyPairException | CartCurrencyNotSupportedException $exception) {
            return;
        }

        throw new \RuntimeException(sprintf('I should not be able to change currency to "%s"', $currencyCode));
    }

    /**
     * @Then I should not be able to add product :productName to the cart in :currencyCode currency
     */
    public function iShouldNotBeAbleToAddProductToTheCartInCurrency(string $productName, string $currencyCode): void
    {
        $code = $this->getCodeFromName($productName);
        $product = $this->findProductByCode($code);

        try {
            $this->cart->addCartItem($code, 1, $product['price'], $currencyCode);
        } catch (CartCurrencyMismatchException $exception) {
            return;
        }

        throw new \RuntimeException(
            sprintf('I should not be able to add product "%s" in "%s" currency.', $productName, $currencyCode)
        );
    }

    /**
     * @Then /^I should not be able to decrease quantity of "([^"]+)" to "([^"]+)"$/
     */
    public function iShouldNotBeAbleToDecreaseQuantityOfTo(string $productName, string $quantity): void
    {
        $cartItemId = $this->productCatalogue[$this->getCodeFromName($productName)]['cartItemId'];

        try {
            $this->cart->changeCartItemQuantity((string) $cartItemId, (int) $quantity);
        } catch (InvalidCartItemQuantityException $exception) {
            return;
        }

        throw new \RuntimeException(sprintf('I should no be able to change quantity of "%s" to "%s"', $productName, $quantity));
    }

    /**
     * @Transform /^"(\-)?(?:€|£|￥|\$)((?:\d+\.)?\d+)"$/
     */
    public function getPriceFromString(string $sign, string $price): int
    {
        $this->validatePriceString($price);

        if ('-' === $sign) {
            $price *= -1;
        }

        return (int) round($price * 100, 2);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getCodeFromName(string $name): string
    {
        $code = str_replace(' ', '_', $name);
        $code = trim($code);

        return $code;
    }

    /**
     * @param string $price
     *
     * @throws \InvalidArgumentException
     */
    private function validatePriceString(string $price): void
    {
        if (strlen(substr(strrchr($price, '.'), 1)) > 2) {
            throw new \InvalidArgumentException('Price string should not have more than 2 decimal digits.');
        }
    }

    /**
     * @para string $code
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    private function findProductByCode(string $code): array
    {
        if (!isset($this->productCatalogue[$code])) {
            throw new \RuntimeException(
                sprintf(
                    'Product with code "%s" does not exist. Current product catalogue: %s',
                    $code,
                    implode($this->productCatalogue)
                )
            );
        }

        return $this->productCatalogue[$code];
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        if (!empty($this->cartEvents)) {
            return $this->cartEvents;
        }

        $events = [];

        /** @var DomainMessage $message */
        foreach ($this->cart->getUncommittedEvents() as $message) {
            $events[] = $message->getPayload();
        }

        $this->cartEvents = $events;

        return $this->cartEvents;
    }

    /**
     * @return array
     */
    private function getCartItemAddedEvents(): array
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event instanceof CartItemAdded;
        });
    }

    /**
     * @return array
     */
    private function getCartItemRemovedEvents(): array
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event instanceof CartItemRemoved;
        });
    }

    /**
     * @return array
     */
    private function getCartItemClearedEvents(): array
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event instanceof CartCleared;
        });
    }

    /**
     * @return array
     */
    private function getCartRecalculatedEvents(): array
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event instanceof CartRecalculated;
        });
    }

    /**
     * @return array
     */
    private function getCartItemQuantityChangedEvents(): array
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event instanceof CartItemQuantityChanged;
        });
    }

    /**
     * @param int $count
     */
    private function assertCartItemCount(int $count): void
    {
        $cartItemAddedEvents = $this->getCartItemAddedEvents();
        $cartItemRemovedEvents = $this->getCartItemRemovedEvents();
        $addedItemCount = count($cartItemAddedEvents);
        $removedItemCount = count($cartItemRemovedEvents);

        $cartItemCount = $addedItemCount - $removedItemCount;

        if ($count !== $cartItemCount) {
            throw new \RuntimeException(
                sprintf('I have "%s" cart items, but I should have "%s".', count($cartItemAddedEvents), $count)
            );
        }
    }

    /**
     * @param array $products
     */
    private function initCartWithProducts(array $products): void
    {
        $cartAdapters = $this->getCartAdaptersBasedOnStoreConfiguration();
        $this->cart = Cart::createWithAdapters($cartAdapters['converter'], $cartAdapters['currencies_provider']);
        $cartId = Uuid::uuid4();
        $this->cart->apply(CartInitialized::occur($cartId, new Money(0, $this->storeOperatingCurrency)));
        $cartTotal = new Money(0, $this->storeOperatingCurrency);

        foreach ($products as $productName => $quantity) {
            $productCode = ProductCode::fromString($this->getCodeFromName($productName));
            $quantity = CartItemQuantity::create($quantity);
            $price = new Money($this->productCatalogue[(string) $productCode]['price'], $this->storeOperatingCurrency);
            $cartItem = CartItem::create($productCode, $quantity, $price);
            $this->productCatalogue[(string) $productCode]['cartItemId'] = (string) $cartItem->cartItemId();
            $cartTotal = $cartTotal->add($cartItem->subtotal());

            $this->cart->apply(CartItemAdded::occur($cartId, $cartItem));
        }

        $this->cart->apply(CartRecalculated::occur($cartId, $cartTotal));
    }

    /**
     * @return array
     */
    private function getCartAdaptersBasedOnStoreConfiguration(): array
    {
        $exchangeRateProvider = new FixedCurrencyExchangeRateProvider($this->cartExchangeRateConfiguration);
        $availableCurrenciesProvider = new ISOCurrenciesProvider();
        $converter = new CartMoneyConverter($exchangeRateProvider, $availableCurrenciesProvider);

        return ['currencies_provider' => $availableCurrenciesProvider, 'converter' => $converter];
    }
}
