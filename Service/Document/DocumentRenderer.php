<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class SaleRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentRenderer extends AbstractRenderer
{
    /**
     * @var DocumentInterface
     */
    private $document;


    /**
     * Constructor.
     *
     * @param DocumentInterface $document
     */
    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * @inheritDoc
     */
    public function getLastModified()
    {
        return $this->document->getSale()->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->document->getType() . '_' . $this->document->getSale()->getNumber();
    }

    /**
     * @inheritdoc
     */
    protected function getContent()
    {
        return $this->renderView('EkynaCommerceBundle:Document:document.html.twig', [
            'logo_path' => $this->logoPath,
            'document'  => $this->document,
            'date'      => new \DateTime(),
        ]);
    }
}
