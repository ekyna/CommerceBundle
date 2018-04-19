<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;

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
                'gender',
                [$this, 'getGenderLabel']
            ),
            new \Twig_SimpleFilter(
                'identity',
                [$this, 'renderIdentity'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'address',
                [$this, 'renderAddress'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'adjustment_type_label',
                [$this, 'getAdjustmentTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'adjustment_mode_label',
                [$this, 'getAdjustmentModeLabel']
            ),
        ];
    }

    /**
     * Returns the gender label.
     *
     * @param string $gender
     * @param bool   $long
     *
     * @return string
     */
    public function getGenderLabel($gender, $long = false)
    {
        return $this->constantHelper->getGenderLabel($gender, $long);
    }

    /**
     * Renders the identity.
     *
     * @param IdentityInterface $identity
     * @param bool              $long
     *
     * @return string
     */
    public function renderIdentity(IdentityInterface $identity, $long = false)
    {
        return $this->constantHelper->renderIdentity($identity, $long);
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

    /**
     * Returns the adjustment type label.
     *
     * @param string $type
     *
     * @return string
     */
    public function getAdjustmentTypeLabel($type)
    {
        return $this->constantHelper->getAdjustmentTypeLabel($type);
    }

    /**
     * Returns the adjustment mode label.
     *
     * @param string $mode
     *
     * @return string
     */
    public function getAdjustmentModeLabel($mode)
    {
        return $this->constantHelper->getAdjustmentModeLabel($mode);
    }
}
