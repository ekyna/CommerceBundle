<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CustomerController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerController
{
    private CustomerRepositoryInterface $customerRepository;
    private Filesystem                  $filesystem;

    public function __construct(CustomerRepositoryInterface $customerRepository, Filesystem $filesystem)
    {
        $this->customerRepository = $customerRepository;
        $this->filesystem = $filesystem;
    }

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

        $helper = new FilesystemHelper($this->filesystem);

        if (!$helper->fileExists($logo->getPath(), false)) {
            throw new NotFoundHttpException('');
        }

        return $helper
            ->createFileResponse($logo->getPath(), $request, true)
            ->setPrivate();
    }
}
