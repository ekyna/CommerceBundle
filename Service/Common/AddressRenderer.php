<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class AddressRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressRenderer
{
    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
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
        return $this->templating->render('@EkynaCommerce/Show/address.html.twig', [
            'address'        => $address,
            'display_phones' => $displayPhones,
        ]);
    }
}
