<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CustomerController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerController
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;


    /**
     * Constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param FilesystemInterface         $filesystem
     */
    public function __construct(CustomerRepositoryInterface $customerRepository, FilesystemInterface $filesystem)
    {
        $this->customerRepository = $customerRepository;
        $this->filesystem         = $filesystem;
    }

    /**
     * Customer logo action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function logo(Request $request): Response
    {
        if (empty($number = $request->attributes->get('customerNumber'))) {
            throw new NotFoundHttpException();
        }

        if (!$customer = $this->customerRepository->findOneByNumber($number)) {
            throw new NotFoundHttpException();
        }

        if (!$logo = $customer->getBrandLogo()) {
            throw new NotFoundHttpException();
        }

        if (!$this->filesystem->has($logo->getPath())) {
            throw new NotFoundHttpException();
        }
        $file = $this->filesystem->get($logo->getPath());

        $response = new Response($file->read());
        $response->setPrivate();

        $response->headers->set('Content-Type', $file->getMimetype());
        $header = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $logo->guessFilename()
        );
        $response->headers->set('Content-Disposition', $header);

        return $response;
    }
}
