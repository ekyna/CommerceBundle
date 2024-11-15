<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Migration;

/**
 * Interface DescriptionConverterInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface DescriptionConverterInterface
{
    public function convert(array &$result, string &$description): void;
}
