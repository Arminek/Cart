[![Build Status](https://travis-ci.com/Arminek/Cart.svg?token=8ZLRHEY2aPJvQgqmQCxh&branch=master)](https://travis-ci.com/Arminek/Cart)
# SyliusCart

It is a event sourced cart.

Quick Installation
------------------

```bash
$ composer install
$ bin/console do:da:cr
$ bin/console broadway:event-store:schema:init
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
It has fixed `8a05b7c2-5624-4f0d-a025-6c4001148526` id for testing purposes.
```bash
$ bin/console sylius:cart:initialize #First of all you need to init your cart to get cart id
$ bin/console sylius:cart:add-product
$ bin/console sylius:cart:change-product-quantity
$ bin/console sylius:cart:remove-product-from-cart
$ bin/console sylius:cart:clear
$ bin/console sylius:event-store:load #To load all recorded events
```

If you want to reset event stream simple run this commands
```bash
$ bin/console broadway:event-store:schema:drop
$ bin/console broadway:event-store:schema:init
$ bin/console sylius:cart:initialize
```

Architecture overview:
![alt tag](https://github.com/Arminek/Cart/raw/master/Architecture.jpg)


MIT License
-----------

Authors
-------

 - Arkadiusz Krakowiak arkadiusz.krakowiak@lakion.com
