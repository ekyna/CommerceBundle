<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\AuthorizationTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RefreshStockAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RefreshStockAction extends AbstractAction implements AdminActionInterface
{
    use AuthorizationTrait;
    use ManagerTrait;
    use TemplatingTrait;

    private StockSubjectUpdaterInterface $stockSubjectUpdater;

    public function __construct(StockSubjectUpdaterInterface $stockSubjectUpdater)
    {
        $this->stockSubjectUpdater = $stockSubjectUpdater;
    }

    public function __invoke(): Response
    {
        $subject = $this->context->getResource();

        if (!$subject instanceof StockSubjectInterface) {
            throw new NotFoundHttpException();
        }

        if (1 === $this->request->query->getInt('no-update')) {
            return $this->respond($subject);
        }

        if (!$this->isGranted(Permission::UPDATE, $subject)) {
            return $this->respond($subject);
        }

        if ($this->stockSubjectUpdater->update($subject)) {
            $event = $this->getManager($subject)->save($subject);

            if ($event->hasErrors()) {
                throw new RuntimeException('Failed to update subject stock data.');
            }
        }

        return $this->respond($subject);
    }

    private function respond(StockSubjectInterface $subject): Response
    {
        /** @see \Ekyna\Bundle\CommerceBundle\Action\Admin\StockViewTrait::createStockViewResponse */
        // TODO return $this->createStockViewResponse($subject);

        $response = $this->render($this->options['template'], [
            'subject'    => $subject,
            'stock_view' => true,
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_subject_refresh_stock',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_refresh_stock',
                'path'    => '/refresh-stock',
                'methods' => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.reload',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'refresh',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Subject/response.xml.twig',
            ],
        ];
    }
}
