<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FlagRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FlagRenderer
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Renders the sale flags.
     *
     * @param SaleInterface $sale
     * @param array         $options
     *
     * @return string
     */
    public function renderSaleFlags(SaleInterface $sale, array $options = [])
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
                $this->translator->trans('ekyna_commerce.sale.source.website'),
                'default',
                'sitemap'
            );
        } elseif (SaleSources::SOURCE_COMMERCIAL === $sale->getSource()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.source.commercial'),
                'default',
                'briefcase'
            );
        } // TODO marketplace

        if ($sale instanceof OrderInterface && $sale->isFirst()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.flag.first_order'),
                'success',
                'thumbs-o-up'
            );
        }

        if ($sale->isSample()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.field.sample'),
                'info',
                'cube'
            );

            if ($sale->canBeReleased()) {
                $flags .= sprintf(
                    $template,
                    $this->translator->trans('ekyna_commerce.sale.flag.can_be_released'),
                    'danger',
                    'check-circle-o'
                );
            }
        }

        if (!empty($sale->getPreparationNote())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.field.preparation_note'),
                'warning',
                'check-square-o'
            );
        }

        if (!empty($sale->getComment())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_core.field.comment'),
                'danger',
                'comment'
            );
        }

        return $flags;
    }
}