<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Class SaleRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function getLastModified()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add subject(s) first.");
        }

        if (1 === count($this->subjects)) {
            /** @var DocumentInterface $subject */
            $subject = reset($this->subjects);

            return $subject->getSale()->getUpdatedAt();
        }

        $date = null;

        /** @var DocumentInterface $s */
        foreach ($this->subjects as $s) {
            if (null === $date || $s->getSale()->getUpdatedAt() > $date) {
               $date = $s->getSale()->getUpdatedAt();
            }
        }

        return $date;
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add document(s) first.");
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

    /**
     * @inheritDoc
     */
    protected function supports($subject)
    {
        return $subject instanceof DocumentInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'EkynaCommerceBundle:Document:document.html.twig';
    }

    /**
     * @inheritdoc
     */
    protected function getParameters()
    {
        return [
            'date' => new \DateTime(),
        ];
    }
}
