<?php

namespace spec\SyliusCart\Domain\ModelCollection;

use Money\Money;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Exception\CartItemNotFoundException;
use SyliusCart\Domain\Model\CartItem;
use SyliusCart\Domain\ModelCollection\CartItemCollection;
use SyliusCart\Domain\ModelCollection\CartItems;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @mixin Cartitems
 *
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('createEmpty');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CartItems::class);
    }

    function it_is_cart_items_model_collection()
    {
        $this->shouldImplement(CartItemCollection::class);
    }

    function it_can_add_new_cart_item()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));

        $this->add($cartItem);
        $this->exists($cartItem)->shouldReturn(true);
    }

    function it_can_remove_existing_cart_item()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);

        $this->remove($cartItem);
        $this->exists($cartItem)->shouldReturn(false);
    }

    function it_throws_cart_item_not_found_exception_if_cart_item_does_not_exist()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));

        $this->shouldThrow(CartItemNotFoundException::class)->during('remove', [$cartItem]);
    }

    function it_can_find_cart_items_by_product_code()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);

        $this->findOneByProductCode(ProductCode::fromString('Mug'))->shouldReturn($cartItem);
    }

    function it_throws_cart_item_not_found_exception_if_cannot_find_cart_item_by_product_code()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);

        $this->shouldThrow(CartItemNotFoundException::class)->during('findOneByProductCode', [ProductCode::fromString('Book')]);
    }

    function it_can_find_all_cart_items()
    {
        $mugCartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $bookCartItem = CartItem::create(ProductCode::fromString('Book'), CartItemQuantity::create(20), Money::USD(100));
        $this->add($mugCartItem);
        $this->add($bookCartItem);

        $this->findAll()->shouldReturn(
            [(string) $mugCartItem->cartItemId() => $mugCartItem, (string) $bookCartItem->cartItemId() => $bookCartItem]
        );
    }

    function it_can_find_one_by_id()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);

        $this->findOneById($cartItem->cartItemId())->shouldReturn($cartItem);
    }

    function it_throws_cart_item_not_found_exception_if_cannot_find_cart_item_by_id()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);

        $this->shouldThrow(CartItemNotFoundException::class)->during('findOneById', [Uuid::uuid4()]);
    }

    function it_can_clear_cart_items()
    {
        $mugCartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $bookCartItem = CartItem::create(ProductCode::fromString('Book'), CartItemQuantity::create(20), Money::USD(100));
        $this->add($mugCartItem);
        $this->add($bookCartItem);

        $this->clear();

        $this->findAll()->shouldReturn([]);
    }

    function it_can_count_items()
    {
        $mugCartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $bookCartItem = CartItem::create(ProductCode::fromString('Book'), CartItemQuantity::create(20), Money::USD(100));
        $this->add($mugCartItem);
        $this->add($bookCartItem);

        $this->count()->shouldReturn(2);
    }

    function it_checks_if_cart_item_with_given_product_code_exists()
    {
        $mugCartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $bookCartItem = CartItem::create(ProductCode::fromString('Book'), CartItemQuantity::create(20), Money::USD(100));
        $this->add($mugCartItem);
        $this->add($bookCartItem);

        $this->existsWithProductCode(ProductCode::fromString('Mug'))->shouldReturn(true);
        $this->existsWithProductCode(ProductCode::fromString('Book'))->shouldReturn(true);
        $this->existsWithProductCode(ProductCode::fromString('Sticker'))->shouldReturn(false);
    }

    function it_knows_if_is_actually_empty_or_not()
    {
        $this->isEmpty()->shouldReturn(true);
    }

    function it_is_not_empty_if_is_initialized_with_cart_items()
    {
        $cartItem = CartItem::create(ProductCode::fromString('Mug'), CartItemQuantity::create(2), Money::USD(10));
        $this->beConstructedThrough('fromArray', [[(string) $cartItem->cartItemId() => $cartItem]]);
        $this->isEmpty()->shouldReturn(false);
    }
}
