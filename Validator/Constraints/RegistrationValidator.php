<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\IdentityValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ekyna\Bundle\CommerceBundle\Model\Registration as Model;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class RegistrationValidator
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($registration, Constraint $constraint)
    {
        if (!$registration instanceof Model) {
            throw new InvalidArgumentException("Expected instance of " . Model::class);
        }
        if (!$constraint instanceof Registration) {
            throw new InvalidArgumentException("Expected instance of " . Registration::class);
        }

        /** @var Registration $registration */

        $applyGroup = $registration->getApplyGroup();
        $customer = $registration->getCustomer();

        if (null === $customer->getPhone() && null === $customer->getMobile()) {
            $this
                ->context
                ->buildViolation($constraint->phone_or_mobile_is_mandatory)
                ->atPath('customer.phone')
                ->addViolation();
        }

        if ($applyGroup->isBusiness()) {
            if (empty($customer->getCompany())) {
                $this
                    ->context
                    ->buildViolation($constraint->company_is_mandatory)
                    ->atPath('customer.company')
                    ->addViolation();
            }
            if (empty($customer->getVatNumber())) {
                $this
                    ->context
                    ->buildViolation($constraint->vat_number_is_mandatory)
                    ->atPath('customer.vatNumber')
                    ->addViolation();
            }
        }

        if ($invoiceContact = $registration->getInvoiceContact()) {
            IdentityValidator::validateIdentity(
                $this->context,
                $invoiceContact,
                ['required' => false],
                'invoiceContact'
            );
        }
    }
}
