<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;

/**
 * Class CommonExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonExtension extends \Twig_Extension
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantHelper $constantHelper
     */
    public function __construct(ConstantHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('commerce_gender', [$this, 'getGenderLabel']),
            new \Twig_SimpleFilter('commerce_identity', [$this, 'renderIdentity'], ['is_safe' => ['html']]),
        ];
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_common';
    }
}
