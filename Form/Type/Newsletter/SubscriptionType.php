<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use Ekyna\Component\Commerce\Newsletter\Model\Subscription;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class SubscribeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractType
{
    /**
     * @var string
     */
    private $audienceClass;


    /**
     * Constructor.
     *
     * @param string $audienceClass
     */
    public function __construct(string $audienceClass)
    {
        $this->audienceClass = $audienceClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, [
                'label' => 'ekyna_core.field.email_address',
            ])
            ->add('firstName', Type\TextType::class, [
                'label'    => 'ekyna_core.field.first_name',
                'required' => false,
            ])
            ->add('lastName', Type\TextType::class, [
                'label'    => 'ekyna_core.field.last_name',
                'required' => false,
            ])
            ->add('birthday', Type\DateType::class, [
                'label'    => 'ekyna_core.field.birthday',
                'required' => false,
            ])
            ->add('audiences', EntityType::class, [
                'label'         => 'ekyna_commerce.newsletter.audiences',
                'class'         => $this->audienceClass,
                'query_builder' => function (AudienceRepositoryInterface $repository) {
                    return $repository->getFindPublicQueryBuilder()->setParameter('public', true);
                },
                'choice_label'  => 'title',
                'multiple'      => true,
                'expanded'      => true,
                'constraints'   => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'ekyna_commerce.newsletter.at_least_one_audience'
                    ]),
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}
