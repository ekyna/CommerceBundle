<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use ArrayAccess;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

use function array_key_exists;

/**
 * Class SubjectLabel
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectLabel implements ArrayAccess
{
    public ?string $designation = null;
    public ?string $reference   = null;
    public ?string $barcode     = null;
    public ?string $geocode     = null;
    private array  $data        = [];

    public function __construct(public readonly SubjectInterface $subject)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
