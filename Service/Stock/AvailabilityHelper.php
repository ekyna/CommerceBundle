<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Common\View\Formatter;
use Ekyna\Component\Commerce\Stock\Helper\AbstractAvailabilityHelper;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
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
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @var string
     */
    protected $prefix;


    /**
     * Constructor.
     *
     * @param TranslatorInterface     $translator
     * @param LocaleProviderInterface $localeProvider
     * @param string                  $defaultCurrency
     * @param string                  $prefix
     */
    public function __construct(
        TranslatorInterface $translator,
        LocaleProviderInterface $localeProvider,
        $defaultCurrency,
        $prefix = 'ekyna_commerce.stock_subject.availability.'
    ) {
        $this->translator = $translator;
        $this->localeProvider = $localeProvider;
        $this->defaultCurrency = $defaultCurrency;
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function getFormatter()
    {
        if (null !== $this->formatter) {
            return $this->formatter;
        }

        return $this->formatter = new Formatter(
            $this->localeProvider->getCurrentLocale(),
            $this->defaultCurrency
        );
    }

    /**
     * @inheritdoc
     */
    public function translate($id, array $parameters = [])
    {
        return $this->translator->trans($this->prefix . $id, $parameters);
    }
}
