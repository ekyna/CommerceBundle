<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice\ArchiverTrait;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler\ActionAutoConfigurePass as BasePass;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;

/**
 * Class ActionAutoConfigurePass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionAutoConfigurePass extends BasePass
{
    protected function getAutoconfigureMap(): array
    {
        return [
            XhrTrait::class => [
                'setViewBuilder' => 'ekyna_commerce.builder.view',
            ],
            ArchiverTrait::class => [
                'setInvoiceArchiver' => 'ekyna_commerce.archiver.invoice',
            ],
        ];
    }
}
