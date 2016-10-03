<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;

/**
 * Class AddressExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var \Twig_TemplateInterface
     */
    protected $addressTemplate;


    /**
     * @inheritdoc
     */
    public function initRuntime(\Twig_Environment $twig)
    {
        /** @var \Twig_TemplateInterface addressTemplate */
        $this->addressTemplate = $twig->loadTemplate('EkynaCommerceBundle:Address:_render.html.twig'); // TODO config
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('commerce_address', [$this, 'renderAddress'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renders the address.
     *
     * @param AddressInterface $address
     * @param bool $displayPhones
     * @return string
     */
    public function renderAddress(AddressInterface $address, $displayPhones = true)
    {
        return $this->addressTemplate->render([
            'address' => $address,
            'display_phones' => $displayPhones
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_address';
    }
}
