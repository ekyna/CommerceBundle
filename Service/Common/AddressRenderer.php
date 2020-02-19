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
     * @param array            $config
     *
     * @return string
     */
    public function renderAddress(AddressInterface $address, array $config = [])
    {
        $config = array_replace([
            'display_phones' => true,
            'locale'         => null,
        ], $config);

        return $this->templating->render('@EkynaCommerce/Show/address.html.twig', [
            'address' => $address,
            'config'  => $config,
        ]);
    }
}
