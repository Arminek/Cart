<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Money\Money;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Adapter\AvailableCurrencies\ISOCurrenciesProvider;
use SyliusCart\Domain\Adapter\Exchange\FixedCurrencyExchangeRateProvider;
use SyliusCart\Domain\Adapter\MoneyConverting\CartMoneyConverter;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartRecalculated;
use SyliusCart\Domain\Model\Cart;
use SyliusCart\Domain\Model\CartItem;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;
use Tests\SyliusCart\Behat\Service\SharedStorageInterface;
use Tests\SyliusCart\Behat\Service\StringInflector;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartContext implements Context
{
    /**
     * @var RepositoryInterface
     */
    private $cartRepository;

    /**
     * @var UuidGeneratorInterface
     */
    private $mockedUuidGenerator;

    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @param RepositoryInterface $cartRepository
     * @param UuidGeneratorInterface $mockedUuidGenerator
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(
        RepositoryInterface $cartRepository,
        UuidGeneratorInterface $mockedUuidGenerator,
        SharedStorageInterface $sharedStorage
    ) {
        $this->cartRepository = $cartRepository;
        $this->mockedUuidGenerator = $mockedUuidGenerator;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given I have empty cart
     */
    public function iHaveEmptyCart(): void
    {
        $cartAdapters = $this->getCartAdaptersBasedOnStoreConfiguration();
        $cart = Cart::createWithAdapters($cartAdapters['converter'], $cartAdapters['currencies_provider']);
        $cartId = Uuid::fromString($this->mockedUuidGenerator->generate());
        $cart->apply(CartInitialized::occur($cartId, new Money(0, $this->sharedStorage->get('currency'))));

        $this->cartRepository->save($cart);
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
     * @return array
     */
    private function getCartAdaptersBasedOnStoreConfiguration(): array
    {
        try {
            $config = $this->sharedStorage->get('store_exchange_rate_configuration');
        } catch (\InvalidArgumentException $exception) {
            $config = [];
        }

        $exchangeRateProvider = new FixedCurrencyExchangeRateProvider($config);
        $availableCurrenciesProvider = new ISOCurrenciesProvider();
        $converter = new CartMoneyConverter($exchangeRateProvider, $availableCurrenciesProvider);

        return ['currencies_provider' => $availableCurrenciesProvider, 'converter' => $converter];
    }

    /**
     * @param array $products
     */
    private function initCartWithProducts(array $products): void
    {
        $cartAdapters = $this->getCartAdaptersBasedOnStoreConfiguration();
        $cart = Cart::createWithAdapters($cartAdapters['converter'], $cartAdapters['currencies_provider']);
        $cartId = Uuid::fromString($this->mockedUuidGenerator->generate());
        $cart->apply(CartInitialized::occur($cartId, new Money(0, $this->sharedStorage->get('currency'))));
        $cartTotal = new Money(0, $this->sharedStorage->get('currency'));
        $productCatalogue = $this->sharedStorage->get('product_catalogue');

        foreach ($products as $productName => $quantity) {
            $productCode = ProductCode::fromString(StringInflector::nameToCode($productName));
            $quantity = CartItemQuantity::create($quantity);
            $price = new Money($productCatalogue[(string) $productCode]['price']->getAmount(), $this->sharedStorage->get('currency'));
            $cartItem = CartItem::create($productCode, $quantity, $price);
            $productCatalogue[(string) $productCode]['cartItemId'] = (string) $cartItem->cartItemId();
            $cartTotal = $cartTotal->add($cartItem->subtotal());

            $cart->apply(CartItemAdded::occur($cartId, $cartItem));
        }

        $cart->apply(CartRecalculated::occur($cartId, $cartTotal));

        $this->sharedStorage->set('product_catalogue', $productCatalogue);

        $this->cartRepository->save($cart);
    }
}
