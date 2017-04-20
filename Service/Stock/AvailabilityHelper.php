<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Helper\AbstractAvailabilityHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AvailabilityHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityHelper extends AbstractAvailabilityHelper
{
    protected TranslatorInterface $translator;
    protected string              $prefix;

    public function __construct(
        FormatterFactory    $formatterFactory,
        TranslatorInterface $translator,
        int                 $inStockLimit = 100,
        string              $prefix = 'stock_subject.availability.'
    ) {
        parent::__construct($formatterFactory, $inStockLimit);

        $this->translator = $translator;
        $this->prefix = $prefix;
    }

    protected function translate(string $id, array $parameters = [], bool $short = false): string
    {
        return $this
            ->translator
            ->trans($this->prefix . ($short ? 'short.' : 'long.') . $id, $parameters, 'EkynaCommerce');
    }
}
