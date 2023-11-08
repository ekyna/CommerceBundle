<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin;

use Ekyna\Bundle\ResourceBundle\Action\SerializerTrait;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait StockViewTrait
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait StockViewTrait
{
    use SerializerTrait;

    /**
     * Creates the subject stock view response.
     */
    private function createStockViewResponse(StockSubjectInterface $subject): Response
    {
        $serialized = $this->serializer->serialize($subject, 'json', ['groups' => [Group::STOCK_UNIT]]);

        $response = new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);

        return $response->setPrivate();
    }
}
