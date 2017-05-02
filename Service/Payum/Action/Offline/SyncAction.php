<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;
use Payum\Offline\Constants;

/**
 * Class SyncAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SyncAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model[Constants::FIELD_STATUS]) && $model[Constants::FIELD_STATUS] === Constants::STATUS_CANCELED) {
            return;
        }

        if (true == $model[Constants::FIELD_PAID]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_CAPTURED;
        } else {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;
        }

        return;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Sync
            && $request->getModel() instanceof \ArrayAccess;
    }
}
