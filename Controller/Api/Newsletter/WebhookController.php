<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter;

use Ekyna\Component\Commerce\Newsletter\Webhook\HandlerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebhookController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WebhookController
{
    private HandlerRegistry $registry;

    public function __construct(HandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Newsletter webhook action.
     */
    public function __invoke(Request $request): Response
    {
        if (!$this->registry) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$this->registry->has($name = $request->attributes->get('name'))) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->registry->get($name)->handle($request);
    }
}
