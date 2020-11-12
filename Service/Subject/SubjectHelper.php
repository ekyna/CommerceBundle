<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelper as BaseHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends BaseHelper
{
    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     * @param EventDispatcherInterface         $eventDispatcher
     * @param Features                         $features
     * @param ResourceHelper                   $resourceHelper
     * @param TranslatorInterface              $translator
     * @param array                            $config
     */
    public function __construct(
        SubjectProviderRegistryInterface $registry,
        EventDispatcherInterface $eventDispatcher,
        Features $features,
        ResourceHelper $resourceHelper,
        TranslatorInterface $translator,
        array $config = []
    ) {
        parent::__construct($registry, $eventDispatcher, $features);

        $this->resourceHelper = $resourceHelper;
        $this->translator     = $translator;
        $this->config         = $config;
    }

    /**
     * Renders the 'add to cart' button.
     *
     * @param SubjectInterface $subject
     * @param array            $options
     *
     * @return string
     */
    public function renderAddToCartButton(SubjectInterface $subject, array $options = []): string
    {
        $options = array_replace_recursive(
            $this->addToCartDefaults(),
            $this->config['add_to_cart'],
            $options
        );

        $attr = $options['attr'];
        if (!isset($attr['href'])) {
            if (empty($href = $this->generatePublicUrl($subject))) {
                $href = "javascript: void(0)";
            }
            $attr['href'] = $href;
        }

        $type     = null;
        $disabled = false;

        $mode = $subject->getStockMode();
        if (0 === $min = $subject->getMinimumOrderQuantity()) {
            $min = 1;
        }
        $aQty  = $subject->getAvailableStock();
        $vQty  = $subject->getVirtualStock();
        $eda   = $subject->getEstimatedDateOfArrival();
        $today = (new \DateTime())->setTime(23, 59, 59, 999999);

        /** @see \Ekyna\Component\Commerce\Stock\Helper\AbstractAvailabilityHelper::getAvailability */
        if (!$subject instanceof StockSubjectInterface) {
            $type                     = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($subject->isQuoteOnly()) {
            $type     = $options['quote_only'];
            $disabled = true;
        } elseif ($mode === StockSubjectModes::MODE_DISABLED) {
            $type                     = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($min <= $aQty) {
            $type                     = $options['add_to_cart'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif (($min <= $vQty) && $eda && ($today < $eda)) {
            $type                     = $options['pre_order'];
            $attr['data-add-to-cart'] = $this->generateAddToCartUrl($subject);
        } elseif ($subject->isEndOfLife()) {
            $type     = $options['end_of_life'];
            $disabled = true;
        } elseif ($this->features->isEnabled(Features::RESUPPLY_ALERT)) { // TODO Check stock mode ?
            $type                        = $options['resupply_alert'];
            $attr['data-resupply-alert'] = $this->generateResupplyAlertUrl($subject);
        } else {
            $type     = $options['out_of_stock'];
            $disabled = true;
        }

        $classes = explode(' ', $options['class']);
        if ($disabled) {
            $classes[]        = 'disabled';
            $attr['disabled'] = 'disabled';
        }
        array_push($classes, ...explode(' ', $type['class']));
        $attr['class'] = implode(' ', array_unique($classes));

        $attributes = '';
        foreach ($attr as $key => $value) {
            $attributes .= " $key=\"$value\"";
        }

        $label = $this->translator->trans($type['label']);

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
        $subject = $this->resolveSubject($subject);

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

        $subject = $this->resolveSubject($subject);

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
        $subject = $this->resolveSubject($subject);

        return $this->resourceHelper->generateResourcePath($subject, 'show', [], !$path);
    }

    /**
     * Resolves the subject.
     *
     * @param SubjectReferenceInterface|SubjectInterface $subject
     *
     * @return SubjectInterface|null
     */
    private function resolveSubject($subject): ?SubjectInterface
    {
        if ($subject instanceof SubjectReferenceInterface) {
            if (null === $subject = $this->resolve($subject, false)) {
                return null;
            }
        }

        if (!$subject instanceof SubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . SubjectInterface::class);
        }

        return $subject;
    }

    /**
     * Returns the 'add to cart' button default config.
     *
     * @return array
     */
    private function addToCartDefaults(): array
    {
        return [
            'class'        => '',
            'icon'         => null,
            'attr'         => [],
            'add_to_cart'  => [
                'label' => 'ekyna_commerce.button.add_to_cart',
                'class' => 'add_to_cart',
            ],
            'pre_order'    => [
                'label' => 'ekyna_commerce.button.pre_order',
                'class' => 'pre_order',
            ],
            'out_of_stock' => [
                'label' => 'ekyna_commerce.stock_subject.availability.long.out_of_stock',
                'class' => 'out_of_stock',
            ],
            'quote_only'   => [
                'label' => 'ekyna_commerce.stock_subject.availability.long.quote_only',
                'class' => 'quote_only',
            ],
            'end_of_life'  => [
                'label' => 'ekyna_commerce.stock_subject.availability.long.end_of_life',
                'class' => 'end_of_life',
            ],
        ];
    }
}
