<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Payum\Offline\Constants;

/**
 * Class CancelAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Cancel $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model[Constants::FIELD_STATUS] = Constants::STATUS_CANCELED;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Cancel
            && $request->getModel() instanceof \ArrayAccess;
    }
}
