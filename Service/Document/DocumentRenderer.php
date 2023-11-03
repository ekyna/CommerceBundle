<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;

/**
 * Class SaleRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property DocumentInterface $subject
 */
class DocumentRenderer extends AbstractRenderer
{
    public function getLastModified(): ?DateTimeInterface
    {
        return $this->subject->getSale()->getUpdatedAt();
    }

    public function getFilename(): string
    {
        if (!empty($number = $this->subject->getSale()->getNumber())) {
            return $this->subject->getType() . '_' . $number;
        }

        return $this->subject->getType();
    }

    protected function supports(object $subject): bool
    {
        return $subject instanceof DocumentInterface;
    }

    protected function getTemplate(): string
    {
        return '@EkynaCommerce/Document/document.html.twig';
    }

    protected function getParameters(): array
    {
        return [
            'date' => new DateTime(),
        ];
    }
}
