<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_replace;
use function array_replace_recursive;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class FlagRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FlagRenderer
{
    // CUSTOMER
    private const PROSPECT      = 1;
    private const CUSTOMER      = 2;
    private const NATIONAL      = 3;
    private const INTERNATIONAL = 4;
    private const UNKNOWN       = 5;

    // ORDER
    private const WEBSITE     = 10;
    private const COMMERCIAL  = 11;
    private const MARKETPLACE = 12;
    private const FIRST       = 20;
    private const SAMPLE      = 21;
    private const SUPPORT     = 22;
    private const RELEASABLE  = 30;
    private const PREPARATION = 31;
    private const COMMENT     = 40;

    private static function getDefaults(): array
    {
        return [
            // CUSTOMER
            self::PROSPECT      => [
                'label' => t('value.prospect', [], 'EkynaCommerce'),
                'theme' => 'orange',
                'icon'  => 'search',
            ],
            self::CUSTOMER      => [
                'label' => t('customer.label.singular', [], 'EkynaCommerce'),
                'theme' => 'green',
                'icon'  => 'user',
            ],
            self::NATIONAL      => [
                'label' => t('value.national', [], 'EkynaCommerce'),
                'theme' => 'blue',
                'icon'  => 'map-marker',
            ],
            self::INTERNATIONAL => [
                'label' => t('value.international', [], 'EkynaCommerce'),
                'theme' => 'purple',
                'icon'  => 'globe',
            ],
            self::UNKNOWN       => [
                'label' => t('value.unknown', [], 'EkynaUi'),
                'theme' => 'grey',
                'icon'  => 'question',
            ],

            // ORDER
            self::WEBSITE       => [
                'label' => t('sale.source.website', [], 'EkynaCommerce'),
                'theme' => 'grey',
                'icon'  => 'sitemap',
            ],
            self::COMMERCIAL    => [
                'label' => t('sale.source.commercial', [], 'EkynaCommerce'),
                'theme' => 'grey',
                'icon'  => 'briefcase',
            ],
            self::MARKETPLACE   => [
                'label' => t('sale.source.marketplace', [], 'EkynaCommerce'),
                'theme' => 'grey',
                'icon'  => 'briefcase',
            ],
            self::FIRST         => [
                'label' => t('sale.flag.first_order', [], 'EkynaCommerce'),
                'theme' => 'teal',
                'icon'  => 'thumbs-o-up',
            ],
            self::SAMPLE        => [
                'label' => t('field.sample', [], 'EkynaCommerce'),
                'theme' => 'purple',
                'icon'  => 'cube',
            ],
            self::SUPPORT        => [
                'label' => t('field.support', [], 'EkynaCommerce'),
                'theme' => 'amber',
                'icon'  => 'wrench',
            ],
            self::RELEASABLE    => [
                'label' => t('sale.flag.can_be_released', [], 'EkynaCommerce'),
                'theme' => 'danger',
                'icon'  => 'check-circle-o',
            ],
            self::PREPARATION   => [
                'label' => t('sale.field.preparation_note', [], 'EkynaCommerce'),
                'theme' => 'warning',
                'icon'  => 'check-square-o',
            ],
            self::COMMENT       => [
                'label' => t('field.comment', [], 'EkynaUi'),
                'theme' => 'danger',
                'icon'  => 'comment',
            ],
        ];
    }

    private readonly array $config;
    private string         $template;

    public function __construct(
        private readonly TranslatorInterface $translator,
        array                                $config = []
    ) {
        $this->config = array_replace_recursive(self::getDefaults(), $config);
    }

    public function renderCustomerFlags(CustomerInterface $customer, array $options = []): string
    {
        $options = array_replace([
            'badge' => true,
        ], $options);

        $this->setTemplate($options['badge']);

        $flags = $this->renderFlag($customer->isProspect() ? self::PROSPECT : self::CUSTOMER);

        $flags .= $this->renderFlag(
            match ($customer->isInternational()) {
                true    => self::INTERNATIONAL,
                false   => self::NATIONAL,
                default => self::UNKNOWN,
            }
        );

        return $flags;
    }

    /**
     * Renders the sale flags.
     */
    public function renderSaleFlags(SaleInterface $sale, array $options = []): string
    {
        $options = array_replace([
            'badge'    => true,
            'customer' => true,
        ], $options);

        $this->setTemplate($options['badge']);

        $flags = $this->renderFlag(
            match ($sale->getSource()) {
                SaleSources::SOURCE_COMMERCIAL  => self::COMMERCIAL,
                SaleSources::SOURCE_MARKETPLACE => self::MARKETPLACE,
                default                         => self::WEBSITE,
            }
        );

        if ($sale instanceof OrderInterface) {
            if ($sale->isFirst()) {
                $flags .= $this->renderFlag(self::FIRST);
            }

            if ($sale->isSample()) {
                $flags .= $this->renderFlag(self::SAMPLE);

                if ($sale->canBeReleased()) {
                    $flags .= $this->renderFlag(self::RELEASABLE);
                }
            }

            if ($sale->isSupport()) {
                $flags .= $this->renderFlag(self::SUPPORT);
            }
        }

        if (!empty($sale->getPreparationNote())) {
            $flags .= $this->renderFlag(self::PREPARATION);
        }

        if (!empty($sale->getComment())) {
            $flags .= $this->renderFlag(self::COMMENT);
        }

        if ($options['customer'] && ($customer = $sale->getCustomer())) {
            $flags .= $this->renderCustomerFlags($customer, $options);
        }

        return $flags;
    }

    private function setTemplate(bool $badge): void
    {
        $this->template = $badge
            ? '<span title="%s" class="label label-%s"><i class="fa fa-%s"></i></span>'
            : '<i title="%s" class="text-%s fa fa-%s"></i>';
    }

    private function renderFlag(int $value): string
    {
        $parameters = $this->config[$value];

        if ($parameters['label'] instanceof TranslatableInterface) {
            $parameters['label'] = $parameters['label']->trans($this->translator);
        }

        return sprintf(
            $this->template,
            $parameters['label'],
            $parameters['theme'],
            $parameters['icon'],
        );
    }
}
