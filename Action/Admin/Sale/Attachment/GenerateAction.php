<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Attachment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class GenerateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Rename Refresh and move DocumentGenerateAction (rename to GenerateAction) into this folder.
 */
class GenerateAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    private DocumentGenerator $documentGenerator;

    public function __construct(DocumentGenerator $documentGenerator)
    {
        $this->documentGenerator = $documentGenerator;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof AttachmentInterface) {
            throw new UnexpectedTypeException($resource, AttachmentInterface::class);
        }

        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();

        $redirect = $this->redirectToReferer($this->generateResourcePath($sale));

        $type = $resource->getType();

        if (!DocumentUtil::isSaleSupportsDocumentType($sale, $type)) {
            return $redirect;
        }

        // Archive the current attachment
        $resource
            ->setType(null)
            ->setInternal(true)
            ->setTitle('[archived] ' . $resource->getTitle());

        $event = $this->getManager()->save($resource);
        if ($event->hasErrors()) {
            $this->addFlashFromEvent($event);

            return $redirect;
        }

        // Generates a new attachment
        try {
            $attachment = $this
                ->documentGenerator
                ->generate($sale, $type);
        } catch (InvalidArgumentException $e) {
            $this->addFlash(t('sale.message.already_exists', [], 'EkynaCommerce'), 'warning');

            return $redirect;
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $redirect;
        }

        $event = $this->getManager()->save($attachment);
        if ($event->hasErrors()) {
            $this->addFlashFromEvent($event);
        }

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_attachment_generate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_generate',
                'path'     => '/generate',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.generate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'reload',
            ],
        ];
    }
}
