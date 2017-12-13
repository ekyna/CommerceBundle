<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Component\Commerce\Shipment\Gateway\Action\PrintLabel;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LabelsRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelsRenderer
{
    /**
     * @var  EngineInterface
     */
    private $templating;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * Constructor.
     *
     * @param EngineInterface    $templating
     * @param GeneratorInterface $generator
     */
    public function __construct(EngineInterface $templating, GeneratorInterface $generator)
    {
        $this->templating = $templating;
        $this->generator = $generator;
    }

    /**
     * Renders the labels.
     *
     * @param PrintLabel $action
     *
     * @return Response
     */
    public function render(PrintLabel $action)
    {
        $labels = array_map(function($data) {
            return base64_encode($data);
        }, $action->getLabels());

        $content = $this->templating->render('EkynaCommerceBundle:Admin/Common/Shipment:labels.html.twig', [
            'labels' => $labels,
        ]);

        $content = $this->generator->getOutputFromHtml($content, [
            'margin-top'    => "0",
            'margin-right'  => "0",
            'margin-bottom' => "0",
            'margin-left'   => "0",
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
