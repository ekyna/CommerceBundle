<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class SubjectExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectExtension extends \Twig_Extension
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var PurchaseCostGuesserInterface
     */
    private $purchaseCostGuesser;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface       $subjectHelper
     * @param PurchaseCostGuesserInterface $purchaseCostGuesser
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        PurchaseCostGuesserInterface $purchaseCostGuesser
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->purchaseCostGuesser = $purchaseCostGuesser;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'subject_get',
                [$this, 'getSubject']
            ),
            new \Twig_SimpleFilter(
                'subject_add_to_cart_url',
                [$this->subjectHelper, 'generateAddToCartUrl']
            ),
            new \Twig_SimpleFilter(
                'subject_add_to_cart_url',
                [$this->subjectHelper, 'generateAddToCartUrl']
            ),
            new \Twig_SimpleFilter(
                'subject_public_url',
                [$this->subjectHelper, 'generatePublicUrl']
            ),
            new \Twig_SimpleFilter(
                'subject_private_url',
                [$this->subjectHelper, 'generatePrivateUrl']
            ),
            new \Twig_SimpleFilter(
                'subject_purchase_cost',
                [$this->purchaseCostGuesser, 'guess']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest(
                'subject_set',
                [$this->subjectHelper, 'hasSubject']
            ),
        ];
    }

    /**
     * Returns the given relative's subject.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Subject\Model\SubjectInterface|object
     */
    public function getSubject(SubjectRelativeInterface $relative)
    {
        return $this->subjectHelper->resolve($relative, false);
    }
}
