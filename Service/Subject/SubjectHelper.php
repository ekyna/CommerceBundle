<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use DateTime;
use Decimal\Decimal;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Subject\CreateSupplierProductAction;
use Ekyna\Bundle\ProductBundle\Form\Type\NewSupplierProductType;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelper as BaseHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

use function Symfony\Component\Translation\t;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends BaseHelper implements SubjectHelperInterface
{
    private array $config;

    public function __construct(
        SubjectProviderRegistryInterface      $registry,
        Features                              $features,
        private readonly ResourceHelper       $resourceHelper,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface  $translator
    ) {
        parent::__construct($registry, $features);
    }

    public function setConfig(array $config): void
    {
        foreach ($config['add_to_cart'] as &$entry) {
            if (!isset($entry['label'])) {
                continue;
            }

            $entry['label'] = t($entry['label'], [], $entry['domain']);
            unset($entry['domain']);
        }

        $this->config = $config;
    }

    /**
     * Renders the 'add to cart' button.
     */
    public function renderAddToCartButton(SubjectInterface $subject, array $options = []): string
    {
        /*
         * TODO Use AvailabilityHelper ? Consistency with sale-item-configure.js ?
         */

        $options = array_replace_recursive(
            $this->addToCartDefaults(),
            $this->config['add_to_cart'],
            $options
        );

        $attr = $options['attr'];
        if (!isset($attr['href'])) {
            if (empty($href = $this->generatePublicUrl($subject))) {
                $href = 'javascript: void(0)';
            }
            $attr['href'] = $href;
        }

        $disabled = false;

        $mode = $subject->getStockMode();
        $min = $subject->getMinimumOrderQuantity();
        if ($min->isZero()) {
            $min = new Decimal(1);
        }
        $aQty = $subject->getAvailableStock();
        $vQty = $subject->getVirtualStock();
        $eda = $subject->getEstimatedDateOfArrival();
        $today = (new DateTime())->setTime(23, 59, 59, 999999);

        /** @see \Ekyna\Component\Commerce\Stock\Helper\AbstractAvailabilityHelper::getAvailability */
        if (!$subject instanceof StockSubjectInterface) {
            $type = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($subject->isQuoteOnly()) {
            $type = $options['quote_only'];
            $disabled = true;
        } elseif ($mode === StockSubjectModes::MODE_DISABLED) {
            $type = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($min <= $aQty) {
            $type = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif (($min <= $vQty) && $eda && ($today < $eda)) {
            $type = $options['pre_order'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($subject->isEndOfLife()) {
            $type = $options['end_of_life'];
            $disabled = true;
        } elseif ($this->features->isEnabled(Features::RESUPPLY_ALERT)) { // TODO Check stock mode ?
            $type = $options['resupply_alert'];
            $attr['data-resupply-alert'] = $this->generateResupplyAlertUrl($subject);
        } else {
            $type = $options['out_of_stock'];
            $disabled = true;
        }

        $classes = explode(' ', $options['class']);
        if ($disabled) {
            $classes[] = 'disabled';
            $attr['disabled'] = 'disabled';
        }
        array_push($classes, ...explode(' ', $type['class']));
        $attr['class'] = implode(' ', array_unique($classes));

        $attributes = '';
        foreach ($attr as $key => $value) {
            $attributes .= " $key=\"$value\"";
        }

        $label = $type['label']->trans($this->translator);

        if (!empty($options['icon'])) {
            $label = sprintf('<i class="%s"></i> ', $options['icon']) . $label;
        }

        return sprintf('<a%s>%s</a>', $attributes, $label);
    }

    /**
     * @inheritDoc
     */
    public function generateAddToCartUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        $type = $path ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->resourceHelper->getUrlGenerator()->generate(
            'ekyna_commerce_subject_add_to_cart',
            [
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getIdentifier(),
            ],
            $type
        );
    }

    /**
     * @inheritDoc
     */
    public function generateResupplyAlertUrl($subject, bool $path = true): ?string
    {
        if (!$this->features->isEnabled(Features::RESUPPLY_ALERT)) {
            return null;
        }

        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        $type = $path ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->resourceHelper->getUrlGenerator()->generate(
            'ekyna_commerce_subject_resupply_alert_subscribe',
            [
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getIdentifier(),
            ],
            $type
        );
    }

    /**
     * @inheritDoc
     */
    public function generatePublicUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        return $this->resourceHelper->generatePublicUrl($subject, !$path);
    }

    /**
     * @inheritDoc
     */
    public function generateImageUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        return $this->resourceHelper->generateImageUrl($subject, !$path);
    }

    /**
     * @inheritDoc
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string
    {
        if (null === $subject = $this->resolveSubject($subject)) {
            return null;
        }

        try {
            return $this->resourceHelper->generateResourcePath($subject, ReadAction::class, [], !$path);
        } catch (Throwable) {
            return null;
        }
    }

    public function getCreateSupplierProductForm(SubjectInterface $subject): FormInterface
    {
        return $this->formFactory->create(NewSupplierProductType::class, null, [
            'action' => $this->resourceHelper->generateResourcePath($subject, CreateSupplierProductAction::class),
        ]);
    }

    /**
     * @param SubjectReferenceInterface|SubjectInterface $subject
     */
    private function resolveSubject($subject): ?SubjectInterface
    {
        if ($subject instanceof SubjectReferenceInterface) {
            if (null === $subject = $this->resolve($subject, false)) {
                return null;
            }
        }

        if (!$subject instanceof SubjectInterface) {
            throw new UnexpectedTypeException($subject, SubjectInterface::class);
        }

        return $subject;
    }

    /**
     * Returns the 'add to cart' button default config.
     */
    private function addToCartDefaults(): array
    {
        return [
            'class'          => '',
            'icon'           => null,
            'attr'           => [],
            'add_to_cart'    => [
                'label' => t('button.add_to_cart', [], 'EkynaCommerce'),
                'class' => 'add_to_cart',
            ],
            'pre_order'      => [
                'label' => t('button.pre_order', [], 'EkynaCommerce'),
                'class' => 'pre_order',
            ],
            'resupply_alert' => [
                'label' => t('button.resupply_alert', [], 'EkynaCommerce'),
                'class' => 'resupply_alert',
            ],
            'out_of_stock'   => [
                'label' => t('stock_subject.availability.long.out_of_stock', [], 'EkynaCommerce'),
                'class' => 'out_of_stock',
            ],
            'quote_only'     => [
                'label' => t('stock_subject.availability.long.quote_only', [], 'EkynaCommerce'),
                'class' => 'quote_only',
            ],
            'end_of_life'    => [
                'label' => t('stock_subject.availability.long.end_of_life', [], 'EkynaCommerce'),
                'class' => 'end_of_life',
            ],
        ];
    }
}
