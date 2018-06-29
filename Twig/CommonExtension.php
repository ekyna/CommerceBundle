<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;

/**
 * Class CommonExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var \Twig_TemplateInterface
     */
    private $addressTemplate;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * @inheritdoc
     */
    public function initRuntime(\Twig_Environment $twig)
    {
        /** @var \Twig_TemplateInterface addressTemplate */
        $this->addressTemplate = $twig->loadTemplate('EkynaCommerceBundle:Show:address.html.twig');
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'address',
                [$this, 'renderAddress'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'identity',
                [$this->constantHelper, 'renderIdentity'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'gender',
                [$this->constantHelper, 'getGenderLabel']
            ),
            new \Twig_SimpleFilter(
                'adjustment_mode_label',
                [$this->constantHelper, 'getAdjustmentModeLabel']
            ),
            new \Twig_SimpleFilter(
                'adjustment_type_label',
                [$this->constantHelper, 'getAdjustmentTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'accounting_type_label',
                [$this->constantHelper, 'renderAccountingTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'notify_type_label',
                [$this->constantHelper, 'renderNotifyTypeLabel']
            ),
        ];
    }

    /**
     * Renders the address.
     *
     * @param AddressInterface $address
     * @param bool             $displayPhones
     *
     * @return string
     */
    public function renderAddress(AddressInterface $address, $displayPhones = true)
    {
        return $this->addressTemplate->render([
            'address'        => $address,
            'display_phones' => $displayPhones,
        ]);
    }
}
