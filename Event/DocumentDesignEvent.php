<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\DocumentDesign;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;

/**
 * Class DocumentDesignEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentDesignEvent
{
    public function __construct(
        private readonly DocumentInterface $document,
        private readonly DocumentDesign    $design,
    ) {
    }

    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }

    public function getDesign(): DocumentDesign
    {
        return $this->design;
    }
}
