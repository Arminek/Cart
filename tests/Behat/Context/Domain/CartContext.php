<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Broadway\Domain\DomainMessage;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\ISOCurrenciesProvider;
use SyliusCart\Domain\Event\CartCleared;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartItemQuantityChanged;
use SyliusCart\Domain\Event\CartItemRemoved;
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
     * @var Money
     */
    private $actualCartTotal;

    /**
     * @var CartItem[]
     */
    private $actualCartItems = [];

    /**
     * @var array
     */
    private $cartProjectors = [];

    public function __construct()
    {
        $this->cartProjectors = [
            CartInitialized::class => function (CartInitialized $event) {
                $this->actualCartTotal =  new Money(0, $event->getCartCurrency());
            },
            CartItemAdded::class => function (CartItemAdded $event) {
                $productCode = (string) $event->getCartItem()->productCode();

                if (isset($this->actualCartItems[$productCode])) {
                    $existingCartItem = $this->actualCartItems[$productCode];

                    $oldCartItemTotal = $existingCartItem->unitPrice()
                        ->multiply($existingCartItem->quantity()->getNumber())
                    ;
                    $this->actualCartTotal = $this->actualCartTotal->subtract($oldCartItemTotal);

                    $newQuantity = $existingCartItem->quantity()->add($event->getCartItem()->quantity());

                    $newCartItem = new CartItem($existingCartItem->productCode(), $newQuantity, $existingCartItem->unitPrice());

                    $cartItemTotal = $newCartItem->unitPrice()->multiply($newCartItem->quantity()->getNumber());
                    $this->actualCartTotal = $this->actualCartTotal->add($cartItemTotal);

                    $this->actualCartItems[$productCode] = $newCartItem;

                }

                if (!isset($this->actualCartItems[$productCode])) {
                    $cartItem = $event->getCartItem();
                    $this->actualCartItems[$productCode] = $cartItem;

                    $cartItemTotal = $cartItem->unitPrice()->multiply($cartItem->quantity()->getNumber());

                    $this->actualCartTotal = $this->actualCartTotal->add($cartItemTotal);
                }
            },
            CartCleared::class => function (CartCleared $event) {
                $this->actualCartItems = [];
                $this->actualCartTotal = new Money(0, $this->storeOperatingCurrency);
            },
            CartItemQuantityChanged::class => function (CartItemQuantityChanged $event) {
                $productCode = (string) $event->getProductCode();
                $existingCartItem = $this->actualCartItems[$productCode];
                $oldCartItemTotal = $existingCartItem->unitPrice()
                    ->multiply($existingCartItem->quantity()->getNumber())
                ;
                $this->actualCartTotal = $this->actualCartTotal->subtract($oldCartItemTotal);


                $newCartItem = new CartItem($existingCartItem->productCode(), $event->getNewCartItemQuantity(), $existingCartItem->unitPrice());
                $this->actualCartItems[(string) $event->getProductCode()] = $newCartItem;

                $cartItemTotal = $newCartItem->unitPrice()->multiply($newCartItem->quantity()->getNumber());
                $this->actualCartTotal = $this->actualCartTotal->add($cartItemTotal);
            },
            CartItemRemoved::class => function (CartItemRemoved $event) {
                $productCode = (string) $event->getProductCode();

                $existingCartItem = $this->actualCartItems[$productCode];
                $oldCartItemTotal = $existingCartItem->unitPrice()
                    ->multiply($existingCartItem->quantity()->getNumber())
                ;
                $this->actualCartTotal = $this->actualCartTotal->subtract($oldCartItemTotal);

                unset($this->actualCartItems[$productCode]);
            },

        ];
    }

    /**
     * @Given the store operates in :currencyCode currency
     */
    public function theStoreOperatesInCurrency($currencyCode): void
    {
        $this->storeOperatingCurrency = new Currency($currencyCode);
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
        $this->cart = Cart::createWithAdapters($this->getAvailableCurrenciesProvider());
        $this->cart->apply(CartInitialized::occur(Uuid::uuid4(), $this->storeOperatingCurrency));
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
     * @When I add product :productName to the cart
     * @When I add :quantity products :productName to the cart
     */
    public function iAddProductToTheCart(string $productName, string $quantity = '1'): void
    {
        $code = $this->getCodeFromName($productName);
        $product = $this->findProductByCode($code);

        $this->cart->addProductToCart($code, (int) $quantity, $product['price'], $this->storeOperatingCurrency->getCode());
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
        $this->cart->removeProductFromCart($this->getCodeFromName($productName));
    }

    /**
     * @When I change quantity of :productName to :quantity
     */
    public function iChangeQuantityOfTo(string $productName, string $quantity): void
    {
        $this->cart->changeProductQuantity($this->getCodeFromName($productName), (int) $quantity);
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
        $this->applyEvents();
        $expectedTotal = new Money($total, $this->storeOperatingCurrency);

        if (!$expectedTotal->equals($this->actualCartTotal)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected cart total "%s", got "%s"',
                    $expectedTotal->getAmount(),
                    $this->actualCartTotal->getAmount())
            );
        }
    }

    /**
     * @Then I should have :quantity :productName in my cart
     */
    public function iShouldHaveInMyCart(string $quantity, string $productName): void
    {
        $this->applyEvents();
        $productCode = $this->getCodeFromName($productName);

        if ((int) $quantity !== $this->actualCartItems[$productCode]->quantity()->getNumber()) {
            throw new \RuntimeException(
                sprintf(
                    'Expected quantity for product "%s" was "%s", but got "%s"',
                    $productName,
                    $quantity,
                    $this->actualCartItems[$productCode]->quantity()->getNumber()
                )
            );
        }
    }

    /**
     * @Then my cart should be empty
     */
    public function myCartShouldBeEmpty(): void
    {
        $this->assertCartItemCount(0);
    }

    /**
     * @Then I should not be able to add product with empty code
     */
    public function iShouldNotBeAbleToAddProductWithEmptyCode(): void
    {
        try {
            $this->cart->addProductToCart('', 10, 100, $this->storeOperatingCurrency->getCode());
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
            $this->cart->addProductToCart('code', 0, 100, $this->storeOperatingCurrency->getCode());
        } catch (InvalidCartItemQuantityException $exception) {
            $catch = true;
        }

        try {
            $this->cart->addProductToCart('code', -10, 100, $this->storeOperatingCurrency->getCode());
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
            $this->cart->addProductToCart('code', 10, 100, $currencyCode);
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
            $this->cart->addProductToCart($productCode, 10, (int) $productPrice, $this->storeOperatingCurrency->getCode());
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
        $fakeCode = $this->getCodeFromName($productName);

        try {
            $this->cart->removeProductFromCart($fakeCode);
        } catch (CartItemNotFoundException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able to remove product which does not exist.');
    }

    /**
     * @Then I should not be able to buy products in store with invalid currency
     */
    public function iShouldNotBeAbleToBuyProductsInStoreWithInvalidCurrency()
    {
        try {
            Cart::initialize(Uuid::uuid4(), $this->storeOperatingCurrency->getCode(), $this->getAvailableCurrenciesProvider());
        } catch (CartCurrencyNotSupportedException $exception) {
            return;
        }

        throw new \RuntimeException('I should not be able to initialize cart with invalid currency code.');
    }

    /**
     * @Then I should not be able to add product :productName to the cart in :currencyCode currency
     */
    public function iShouldNotBeAbleToAddProductToTheCartInCurrency(string $productName, string $currencyCode): void
    {
        $code = $this->getCodeFromName($productName);
        $product = $this->findProductByCode($code);

        try {
            $this->cart->addProductToCart($code, 1, $product['price'], $currencyCode);
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
        try {
            $this->cart->changeProductQuantity($this->getCodeFromName($productName), (int) $quantity);
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
        $events = [];

        /** @var DomainMessage $message */
        foreach ($this->cart->getUncommittedEvents() as $message) {
            $events[] = $message->getPayload();
        }

        return $events;
    }

    /**
     * @param int $count
     */
    private function assertCartItemCount(int $count): void
    {
        $this->applyEvents();

        if (count($this->actualCartItems) !== $count) {
            throw new \RuntimeException(
                sprintf('I have "%s" cart items, but I should have "%s".', count($this->actualCartItems), $count)
            );
        }
    }

    /**
     * @param array $products
     */
    private function initCartWithProducts(array $products): void
    {
        $this->cart = Cart::createWithAdapters($this->getAvailableCurrenciesProvider());
        $cartId = Uuid::uuid4();
        $this->cart->apply(CartInitialized::occur($cartId, $this->storeOperatingCurrency));

        foreach ($products as $productName => $quantity) {
            $productCode = ProductCode::fromString($this->getCodeFromName($productName));
            $quantity = CartItemQuantity::create($quantity);
            $price = new Money($this->productCatalogue[$this->getCodeFromName($productName)]['price'], $this->storeOperatingCurrency);
            $cartItem = CartItem::create($productCode, $quantity, $price);

            $this->cart->apply(CartItemAdded::occur($cartId, $cartItem));
        }
    }

    /**
     * @return AvailableCurrenciesProviderInterface
     */
    private function getAvailableCurrenciesProvider(): AvailableCurrenciesProviderInterface
    {
        return new ISOCurrenciesProvider();
    }

    private function applyEvents(): void
    {
        $events = $this->getEvents();

        foreach ($events as $event) {
            if (isset($this->cartProjectors[get_class($event)])) {
                $this->cartProjectors[get_class($event)]($event);
            }
        }
    }
}
