<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Helper\AbstractAvailabilityHelper;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AvailabilityHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityHelper extends AbstractAvailabilityHelper
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $prefix;


    /**
     * Constructor.
     *
     * @param FormatterFactory    $formatterFactory
     * @param TranslatorInterface $translator
     * @param string              $prefix
     * @param int                 $inStockLimit
     */
    public function __construct(
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator,
        $inStockLimit = 100,
        $prefix = 'ekyna_commerce.stock_subject.availability.'
    ) {
        parent::__construct($formatterFactory, $inStockLimit);

        $this->translator = $translator;
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function translate($id, array $parameters = [], $short = false)
    {
        return $this->translator->trans($this->prefix . ($short ? 'short.' : 'long.') . $id, $parameters);
    }
}
