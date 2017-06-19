<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\UserBundle\Model\GroupRepositoryInterface;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends ResourceFormType
{
    /**
     * @var string
     */
    private $customerGroupClass;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var GroupRepositoryInterface
     */
    private $userGroupRepository;


    /**
     * Constructor.
     *
     * @param string $customerClass
     * @param string $customerGroupClass
     * @param string $userClass
     */
    public function __construct($customerClass, $customerGroupClass, $userClass)
    {
        parent::__construct($customerClass);

        $this->customerGroupClass = $customerGroupClass;
        $this->userClass = $userClass;
    }

    /**
     * Sets the user group repository.
     *
     * @param GroupRepositoryInterface $repository
     */
    public function setUserGroupRepository(GroupRepositoryInterface $repository)
    {
        $this->userGroupRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerGroup', ResourceType::class, [
                'label'        => 'ekyna_commerce.customer_group.label.plural',
                'class'        => $this->customerGroupClass,
                'allow_new'    => true,
                'choice_label' => 'name',
            ])
            ->add('parent', ResourceType::class, [
                'label'     => 'ekyna_commerce.customer.field.parent',
                'class'     => $this->dataClass,
                'allow_new' => true,
                'required'  => false,
            ])
            ->add('user', ResourceType::class, [
                'label'     => 'ekyna_user.user.label.singular',
                'allow_new' => true,
                'class'     => $this->userClass,
                'required'  => false,
            ])
            ->add('inCharge', ResourceType::class, [
                'label'         => 'ekyna_commerce.customer.field.in_charge',
                'allow_new'     => false,
                'class'         => $this->userClass,
                'required'      => false,
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('u');

                    return $qb
                        ->andWhere($qb->expr()->in('u.group', ':groups'))
                        ->setParameter('groups', $this->userGroupRepository->findByRole('ROLE_ADMIN'))
                        ->orderBy('u.username', 'ASC');
                },
            ])
            ->add('email', Type\EmailType::class, [
                'label' => 'ekyna_core.field.email',
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('identity', IdentityType::class)
            ->add('phone', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.phone',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.mobile',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('vatNumber', VatNumberType::class, [
                'label'    => 'ekyna_commerce.pricing.field.vat_number',
                'required' => false,
            ])
            ->add('vatValid', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.pricing.field.vat_valid',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('paymentTerm', PaymentTermChoiceType::class)
            ->add('outstandingLimit', Type\NumberType::class, [
                'label' => 'ekyna_commerce.customer.field.outstanding_limit',
                'scale' => 2,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ]);
    }
}
