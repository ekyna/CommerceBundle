<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\Address as AddressConstraint;
use Ekyna\Component\Commerce\Common\Model\Address as AddressModel;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ArrayAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArrayAddressType extends AbstractType
{
    private DataTransformerInterface $transformer;
    private ValidatorInterface       $validator;

    public function __construct(DataTransformerInterface $transformer, ValidatorInterface $validator)
    {
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            if (null === $form->getData()) {
                return;
            }

            /** @var AddressInterface $address */
            $address = $form->getNormData();

            // Validate the form in group "Default"
            $violations = $this->validator->validate($address, [new AddressConstraint()]);

            /** @var ConstraintViolationInterface $violation */
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
     */
    private function matchChild(FormInterface $form, string $path): ?FormInterface
    {
        foreach ($form->all() as $child) {
            if ($child->getPropertyPath() === $path || $child->getName() === $path) {
                return $child;
            }

            if (!$child->getConfig()->getInheritData()) {
                continue;
            }

            if (null !== $match = $this->matchChild($child, $path)) {
                return $match;
            }
        }

        return null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AddressModel::class);
    }

    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
