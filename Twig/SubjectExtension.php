<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * Class SubjectExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectExtension extends AbstractExtension
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
            new TwigFilter(
                'subject_get',
                [$this, 'getSubject']
            ),
            new TwigFilter(
                'subject_add_to_cart_url',
                [$this->subjectHelper, 'generateAddToCartUrl']
            ),
            new TwigFilter(
                'subject_add_to_cart_url',
                [$this->subjectHelper, 'generateAddToCartUrl']
            ),
            new TwigFilter(
                'subject_public_url',
                [$this->subjectHelper, 'generatePublicUrl']
            ),
            new TwigFilter(
                'subject_private_url',
                [$this->subjectHelper, 'generatePrivateUrl']
            ),
            new TwigFilter(
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
            new TwigTest('subject', function ($subject) {
                return $subject instanceof SubjectInterface;
            }),
            new TwigTest(
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
     * @return SubjectInterface|object
     */
    public function getSubject(SubjectRelativeInterface $relative)
    {
        return $this->subjectHelper->resolve($relative, false);
    }
}
