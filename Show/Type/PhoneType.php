<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\UnexpectedTypeException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use libphonenumber\PhoneNumber;

/**
 * Class PhoneType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PhoneType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        if ($value && !$value instanceof PhoneNumber) {
            throw new UnexpectedTypeException($value, PhoneNumber::class);
        }

        parent::build($view, $value, $options);
    }

    public static function getName(): string
    {
        return 'commerce_phone';
    }
}
