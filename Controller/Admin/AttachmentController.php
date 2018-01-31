<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AttachmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttachmentController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function homeAction()
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * Download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function downloadAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\AttachmentInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('VIEW', $resource);

        $fs = $this->get('local_commerce_filesystem');
        if (!$fs->has($resource->getPath())) {
            throw new NotFoundHttpException('File not found');
        }
        $file = $fs->get($resource->getPath());

        $response = new Response($file->read());
        $response->setPrivate();

        $response->headers->set('Content-Type', $file->getMimetype());
        $header = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $resource->guessFilename()
        );
        $response->headers->set('Content-Disposition', $header);

        return $response;
    }

    /**
     * Archive action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function archiveAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\AttachmentInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $resource
            ->setType(null)
            ->setInternal(true)
            ->setTitle('[archived] ' . $resource->getTitle());

        $this->getOperator()->update($resource);

        return $this->redirect($this->generateResourcePath($this->getParentResource($context)));
    }
}
