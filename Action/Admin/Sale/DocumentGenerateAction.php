<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class DocumentGenerateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DocumentGenerateAction extends AbstractSaleAction implements RoutingActionInterface
{
    use HelperTrait;
    use FlashTrait;
    use ManagerTrait;

    private DocumentGenerator $documentGenerator;

    public function __construct(DocumentGenerator $documentGenerator)
    {
        $this->documentGenerator = $documentGenerator;
    }

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $redirect = $this->redirect($this->generateResourcePath($sale));

        $type = $this->request->attributes->get('type');

        try {
            $attachment = $this
                ->documentGenerator
                ->generate($sale, $type);
        } catch (InvalidArgumentException) {
            $this->addFlash(t('sale.message.already_exists', [], 'EkynaCommerce'), 'warning');

            return $redirect;
        } catch (PdfException) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $redirect;
        }

        $event = $this
            ->getManager($attachment)
            ->save($attachment);

        $this->addFlashFromEvent($event);

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_document_generate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_document_generate',
                'path'     => '/document/generate/{type}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.generate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'print',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => '[a-z]+',
        ]);
    }
}
