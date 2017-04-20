<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Newsletter\Model\NewsletterSubscription;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

use function Symfony\Component\Translation\t;

/**
 * Class NewsletterSubscriptionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterSubscriptionType extends AbstractType
{
    private string $audienceClass;

    public function __construct(string $audienceClass)
    {
        $this->audienceClass = $audienceClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', Type\EmailType::class, [
                'label' => t('field.email_address', [], 'EkynaUi'),
            ])
            ->add('firstName', Type\TextType::class, [
                'label'    => t('field.first_name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('lastName', Type\TextType::class, [
                'label'    => t('field.last_name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('birthday', Type\DateType::class, [
                'label'    => t('field.birthday', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('audiences', EntityType::class, [
                'label'         => t('newsletter.audiences', [], 'EkynaCommerce'),
                'class'         => $this->audienceClass,
                'query_builder' => function (AudienceRepositoryInterface $repository): QueryBuilder {
                    return $repository->getFindPublicQueryBuilder()->setParameter('public', true);
                },
                'choice_label'  => 'title',
                'multiple'      => true,
                'expanded'      => true,
                'constraints'   => [
                    new Count([
                        'min'        => 1,
                        'minMessage' => t('newsletter.at_least_one_audience', [], 'EkynaCommerce'),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewsletterSubscription::class,
        ]);
    }
}
