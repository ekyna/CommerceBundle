<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Common\Util\Formatter;
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
     * @param Formatter           $formatter
     * @param TranslatorInterface $translator
     * @param string              $prefix
     */
    public function __construct(
        Formatter $formatter,
        TranslatorInterface $translator,
        $prefix = 'ekyna_commerce.stock_subject.availability.'
    ) {
        parent::__construct($formatter);

        $this->translator = $translator;
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function translate($id, array $parameters = [])
    {
        return $this->translator->trans($this->prefix . $id, $parameters);
    }
}
