<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ShipmentAddressTransformer;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\Address;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ShipmentAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressType extends AbstractType
{
    /**
     * @var ShipmentAddressTransformer
     */
    private $transformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;


    /**
     * Constructor.
     *
     * @param ShipmentAddressTransformer $transformer
     * @param ValidatorInterface         $validator
     */
    public function __construct(ShipmentAddressTransformer $transformer, ValidatorInterface $validator)
    {
        $this->transformer = $transformer;
        $this->validator   = $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['admin_mode']) {
            $builder->add('information', TextareaType::class, [
                'label'    => 'ekyna_core.field.information',
                'required' => false,
            ]);
        }

        $builder->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if (null === $form->getData()) {
                return;
            }

            /** @var ShipmentAddress $address */
            $address = $form->getNormData();

            // Validate the form in group "Default"
            $violations = $this->validator->validate($address, [new Address()]);

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $scope = $form;
                if (null !== $path = $violation->getPropertyPath()) {
                    if (null !== $child = $this->matchChild($form, $path)) {
                        $scope = $child;
                    }
                }

                $scope->addError(new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getPlural(),
                    $violation
                ));
            }
        }, 2048); // Pre form validation
    }

    /**
     * Finds the form child matching the violation property path.
     *
     * @param FormInterface $form
     * @param string        $path
     *
     * @return null|FormInterface
     */
    private function matchChild(FormInterface $form, $path)
    {
        foreach ($form->all() as $child) {
            if ($child->getPropertyPath() === $path || $child->getName() === $path) {
                return $child;
            }
            if ($child->getConfig()->getInheritData()) {
                if (null !== $match = $this->matchChild($child, $path)) {
                    return $match;
                }
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ShipmentAddress::class,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
