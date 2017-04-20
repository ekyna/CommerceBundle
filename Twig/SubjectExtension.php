<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
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
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'subject_get',
                [SubjectHelper::class, 'resolve']
            ),
            new TwigFilter(
                'subject_add_to_cart_button',
                [SubjectHelper::class, 'renderAddToCartButton'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'subject_add_to_cart_url',
                [SubjectHelper::class, 'generateAddToCartUrl']
            ),
            new TwigFilter(
                'subject_availability_alert_url',
                [SubjectHelper::class, 'generateAvailabilityAlertUrl']
            ),
            new TwigFilter(
                'subject_public_url',
                [SubjectHelper::class, 'generatePublicUrl']
            ),
            new TwigFilter(
                'subject_private_url',
                [SubjectHelper::class, 'generatePrivateUrl']
            ),
            new TwigFilter(
                'subject_create_supplier_product_form',
                [SubjectHelper::class, 'getCreateSupplierProductForm']
            ),
            new TwigFilter(
                'subject_purchase_cost',
                [PurchaseCostGuesserInterface::class, 'guess']
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('subject', function ($subject) {
                return $subject instanceof SubjectInterface;
            }),
            new TwigTest(
                'subject_set',
                [SubjectHelper::class, 'hasSubject']
            ),
            new TwigTest('quote_only', function(StockSubjectInterface $subject) {
                return $subject->isQuoteOnly();
            }),
            new TwigTest('in_stock', function(StockSubjectInterface $subject) {
                if ($subject->isQuoteOnly()) {
                    return false;
                }

                if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                    return true;
                }

                if (0 < $subject->getAvailableStock()) {
                    return true;
                }

                return false;
            }),
            new TwigTest('pre_order', function(StockSubjectInterface $subject) {
                if ($subject->isQuoteOnly()) {
                    return false;
                }

                if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                    return false;
                }

                if (0 < $aQty = $subject->getAvailableStock()) {
                    return false;
                }

                if ((0 < $vQty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
                    $today = new DateTime();
                    $today->setTime(23, 59, 59, 999999);
                    if (($today < $eda) && (0 < $vQty - $aQty)) {
                        return true;
                    }
                }

                return false;
            }),
            new TwigTest('end_of_life', function(StockSubjectInterface $subject) {
                if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                    return false;
                }

                if ((0 < $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
                    $today = new DateTime();
                    $today->setTime(23, 59, 59, 999999);
                    if ($today < $eda) {
                        return false;
                    }
                }

                return $subject->isEndOfLife();
            }),
        ];
    }
}
