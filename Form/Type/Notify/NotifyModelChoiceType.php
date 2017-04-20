<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class NotifyModelChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelChoiceType extends AbstractType
{
    private TranslatorInterface $translator;
    private string              $modelClass;
    private array               $locales;


    public function __construct(TranslatorInterface $translator, string $class, array $locales)
    {
        $this->translator = $translator;
        $this->modelClass = $class;
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $locales[Locales::getName($locale)] = $locale;
        }

        /** @var SaleInterface $sale */
        $sale = $options['sale'];

        $builder
            ->add('model', EntityType::class, [
                'label'        => t('field.template', [], 'EkynaCommerce'),
                'placeholder'  => t('placeholder.template', [], 'EkynaCommerce'),
                'class'        => $this->modelClass,
                'required'     => false,
                'select2'      => false,
                'choice_label' => function (NotifyModelInterface $model): ?string {
                    if ($model->getType() === NotificationTypes::MANUAL) {
                        return $model->getSubject();
                    }

                    return $this->translator->trans(
                        sprintf('notify.type.%s.label', $model->getType()), [], 'EkynaCommerce'
                    );
                },
                'attr'         => [
                    'class' => 'model-choice',
                ],
            ])
            ->add('locale', ChoiceType::class, [
                'choices'           => $locales,
                'preferred_choices' => [$sale->getLocale()],
                'select2'           => false,
                'attr'              => [
                    'class' => 'locale-choice',
                ],
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var SaleInterface $sale */
        $sale = $options['sale'];

        if ($sale instanceof OrderInterface) {
            $type = 'order';
        } elseif ($sale instanceof QuoteInterface) {
            $type = 'quote';
        } elseif ($sale instanceof CartInterface) {
            $type = 'cart';
        } else {
            throw new InvalidArgumentException('Unexpected sale class.');
        }

        $view->vars['sale_id'] = $sale->getId();
        $view->vars['sale_type'] = $type;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('sale')
            ->setDefaults([
                'required' => false,
                'mapped'   => false,
            ])
            ->setAllowedTypes('sale', SaleInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_notify_model_choice';
    }
}
