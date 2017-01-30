# SyliusCart

It is a event sourced cart.

Quick Installation
------------------

```bash
$ composer install
$ php app/console do:da:cr
$ php app/console do:sch:cr
$ php app/console syl:fix:lo
$ php app/console broadway:event-store:schema:init
$ php app/console server:run
```

You can run Behat using the following commands:

```bash
$ bin/behat
```

To run Phpspec

```bash
$ bin/phpspec run
```

Let's start playing with this awesome cart.
```bash
$ app/console sylius:cart:initialize #First of all you need to init your cart to get cart id
$ app/console sylius:cart:add-cart-item
$ app/console sylius:cart:change-currency
$ app/console sylius:cart:item-quantity-change
$ app/console sylius:cart:remove-cart-item
$ app/console sylius:cart:clear
$ app/console sylius:event-store:load #To load all recorded events
```

If you want to reset event stream simple run this commands
```bash
$ app/console broadway:event-store:schema:drop
$ app/console broadway:event-store:schema:init
$ app/console sylius:cart:initialize
```

Exchange adapter has fixed currencies pair for handling currency changing:
```php
    ['EUR' => ['USD' => 2.5]]
    ['USD' => ['EUR' => 0.4]]
``

It has fixed `8a05b7c2-5624-4f0d-a025-6c4001148526` id for testing purposes.

MIT License
-----------

Authors
-------

 - Arkadiusz Krakowiak arkadiusz.krakowiak@lakion.com
