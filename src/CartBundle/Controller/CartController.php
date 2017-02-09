<?php

declare(strict_types = 1);

namespace SyliusCart\CartBundle\Controller;

use Broadway\CommandHandling\CommandBusInterface;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Command\AddProductToCart;
use SyliusCart\Domain\Command\ChangeProductQuantity;
use SyliusCart\Domain\Command\ClearCart;
use SyliusCart\Domain\Command\InitializeCart;
use SyliusCart\Domain\Command\RemoveProductFromCart;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartController
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function initializeAction(Request $request): Response
    {
        $cartId = Uuid::uuid4();

        $this->tryToHandleCommand(InitializeCart::create($cartId, $request->request->get('currencyCode')));

        return new Response($cartId);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function addProductAction(Request $request): Response
    {
        $this->tryToHandleCommand(AddProductToCart::create(
            $request->request->get('cartId'),
            $request->request->get('productCode'),
            (int) $request->request->get('quantity'),
            (int) $request->request->get('price'),
            $request->request->get('productCurrencyCode')
        ));

        return new Response();
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function changeProductQuantityAction(Request $request): Response
    {
        $this->tryToHandleCommand(ChangeProductQuantity::create(
            $request->request->get('cartId'),
            $request->request->get('productCode'),
            (int) $request->request->get('quantity')
        ));

        return new Response();
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function removeProductAction(Request $request): Response
    {
        $this->tryToHandleCommand(RemoveProductFromCart::create(
            $request->request->get('cartId'),
            $request->request->get('productCode')
        ));

        return new Response();
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function clearAction(Request $request): Response
    {
        $this->tryToHandleCommand(ClearCart::create($request->request->get('cartId')));

        return new Response();
    }

    /**
     * @param $command
     *
     * @throws HttpException
     */
    private function tryToHandleCommand($command)
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (\DomainException $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $exception->getMessage(), $exception);
        }
    }
}
