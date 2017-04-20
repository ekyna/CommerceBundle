<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Invoice;

use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;
use function str_replace;

/**
 * Class InvoiceArchiver
 * @package Ekyna\Bundle\CommerceBundle\Service\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceArchiver
{
    protected ResourceRegistryInterface $resourceRegistry;
    protected FactoryFactoryInterface   $factoryFactory;
    protected ManagerFactoryInterface   $managerFactory;
    protected RendererFactory           $rendererFactory;
    protected TranslatorInterface       $translator;

    public function __construct(
        ResourceRegistryInterface $resourceRegistry,
        FactoryFactoryInterface $factoryFactory,
        ManagerFactoryInterface $managerFactory,
        RendererFactory $rendererFactory,
        TranslatorInterface $translator
    ) {
        $this->resourceRegistry = $resourceRegistry;
        $this->factoryFactory = $factoryFactory;
        $this->managerFactory = $managerFactory;
        $this->rendererFactory = $rendererFactory;
        $this->translator = $translator;
    }

    public function archive(InvoiceInterface $invoice): ResourceEventInterface
    {
        $attachmentId = str_replace('invoice', 'attachment', $this->resourceRegistry->find($invoice)->getId());

        $attachment = $this->factoryFactory->getFactory($attachmentId)->create();

        if (!$attachment instanceof SaleAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, SaleAttachmentInterface::class);
        }

        $path = $this
            ->rendererFactory
            ->createRenderer($invoice)
            ->create();

        $title = sprintf(
            '[archived] %s %s',
            (DocumentTypes::getLabel($invoice->getType()))->trans($this->translator),
            $invoice->getNumber()
        );

        $filename = sprintf('%s-%s.pdf', $invoice->getType(), $invoice->getNumber());

        $attachment
            ->setSale($invoice->getSale())
            ->setTitle($title)
            ->setFile(new File($path))
            ->setRename($filename)
            ->setInternal(true);

        return $this
            ->managerFactory
            ->getManager($attachmentId)
            ->save($attachment);
    }
}
