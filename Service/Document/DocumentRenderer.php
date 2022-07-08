<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\LogicException;

use function count;
use function is_null;
use function reset;

/**
 * Class SaleRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentRenderer extends AbstractRenderer
{
    public function getLastModified(): ?DateTimeInterface
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first.');
        }

        if (1 === count($this->subjects)) {
            /** @var DocumentInterface $subject */
            $subject = reset($this->subjects);

            return $subject->getSale()->getUpdatedAt();
        }

        $date = null;

        /** @var DocumentInterface $s */
        foreach ($this->subjects as $s) {
            if (is_null($date) || ($s->getSale()->getUpdatedAt() > $date)) {
                $date = $s->getSale()->getUpdatedAt();
            }
        }

        return $date;
    }

    public function getFilename(): string
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first.');
        }

        if (1 < count($this->subjects)) {
            return 'documents';
        }

        /** @var DocumentInterface $subject */
        $subject = reset($this->subjects);

        if (!empty($number = $subject->getSale()->getNumber())) {
            return $subject->getType() . '_' . $number;
        }

        return $subject->getType();
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
