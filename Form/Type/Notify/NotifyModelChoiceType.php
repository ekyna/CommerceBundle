<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\CommerceBundle\Entity\NotifyModel;
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
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotifyModelChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelChoiceType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $modelClass;

    /**
     * @var string []
     */
    private $locales;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param string $class
     * @param array  $locales
     */
    public function __construct(TranslatorInterface $translator, string $class, array $locales)
    {
        $this->translator = $translator;
        $this->modelClass = $class;
        $this->locales    = $locales;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $locales[Intl::getLocaleBundle()->getLocaleName($locale)] = $locale;
        }

        /** @var SaleInterface $sale */
        $sale = $options['sale'];

        $builder
            ->add('model', EntityType::class, [
                'label'       => 'ekyna_commerce.field.template',
                'placeholder' => 'ekyna_commerce.placeholder.template',
                'class'       => $this->modelClass,
                'required'    => false,
                'select2'     => false,
                'choice_label' => function(NotifyModel $model) {
                    if ($model->getType() === NotificationTypes::MANUAL) {
                        return $model->getSubject();
                    }

                    return $this->translator->trans(
                        sprintf('ekyna_commerce.notify.type.%s.label', $model->getType())
                    );
                },
                'attr'        => [
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

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
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
            throw new InvalidArgumentException("Unexpected sale class.");
        }

        $view->vars['sale_id']   = $sale->getId();
        $view->vars['sale_type'] = $type;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('sale')
            ->setDefaults([
                'required' => false,
                'mapped'   => false,
            ])
            ->setAllowedTypes('sale', SaleInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_notify_model_choice';
    }
}
