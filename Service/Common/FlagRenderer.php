<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FlagRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FlagRenderer
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Renders the sale flags.
     */
    public function renderSaleFlags(SaleInterface $sale, array $options = []): string
    {
        $options = array_replace([
            'badge' => true,
        ], $options);

        $template = $options['badge']
            ? '<span title="%s" class="label label-%s"><i class="fa fa-%s"></i></span>'
            : '<i title="%s" class="text-%s fa fa-%s"></i>';

        $flags = '';

        if (SaleSources::SOURCE_WEBSITE === $sale->getSource()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('sale.source.website', [], 'EkynaCommerce'),
                'default',
                'sitemap'
            );
        } elseif (SaleSources::SOURCE_COMMERCIAL === $sale->getSource()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('sale.source.commercial', [], 'EkynaCommerce'),
                'default',
                'briefcase'
            );
        } // TODO marketplace

        if ($sale instanceof OrderInterface && $sale->isFirst()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('sale.flag.first_order', [], 'EkynaCommerce'),
                'success',
                'thumbs-o-up'
            );
        }

        if ($sale->isSample()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('field.sample', [], 'EkynaCommerce'),
                'info',
                'cube'
            );

            if ($sale->canBeReleased()) {
                $flags .= sprintf(
                    $template,
                    $this->translator->trans('sale.flag.can_be_released', [], 'EkynaCommerce'),
                    'danger',
                    'check-circle-o'
                );
            }
        }

        if (!empty($sale->getPreparationNote())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('sale.field.preparation_note', [], 'EkynaCommerce'),
                'warning',
                'check-square-o'
            );
        }

        if (!empty($sale->getComment())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('field.comment', [], 'EkynaUi'),
                'danger',
                'comment'
            );
        }

        return $flags;
    }
}
