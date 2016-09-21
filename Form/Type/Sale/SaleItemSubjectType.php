<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\EventListener\SaleItemSubjectTypeSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemSubjectType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectType extends AbstractType
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $providerRegistry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $providerRegistry
     */
    public function __construct(SubjectProviderRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(
            new SaleItemSubjectTypeSubscriber($this->providerRegistry)
        );
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SaleItemInterface::class,
        ]);
    }
}
