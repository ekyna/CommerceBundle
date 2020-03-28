<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Exception;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * Class PdfGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PdfGenerator
{
    /**
     * Micro service API url.
     *
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $token;


    /**
     * Constructor.
     *
     * @param string $endpoint
     * @param string $token
     */
    public function __construct(string $endpoint, string $token)
    {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    /**
     * Generates a PDF form the given URL.
     *
     * @param string $url
     * @param array  $options
     *
     * @return string
     */
    public function generateFromUrl(string $url, array $options = []): string
    {
        return $this->generate(array_replace($options, ['url' => $url]));
    }

    /**
     * Generates a PDF form the given URL.
     *
     * @param string $html
     * @param array  $options
     *
     * @return string
     */
    public function generateFromHtml(string $html, array $options = []): string
    {
        return $this->generate(array_replace($options, ['html' => $html]));
    }

    private function generate(array $options): string
    {
        $options = array_replace_recursive([
            'orientation' => 'portrait',
            'format'      => 'A4',
            'paper'       => [
                'width'  => null,
                'height' => null,
                'unit'   => 'in',
            ],
            'margins'     => [
                'top'    => 6,
                'right'  => 6,
                'bottom' => 6,
                'left'   => 6,
                'unit'   => 'mm',
            ],
            'header'      => null,
            'footer'      => null,
        ], $options);

        $client = new Client();

        for ($i = 2; $i >= 0; $i--) {
            try {
                $response = $client->request('GET', $this->endpoint, [
                    // TODO Timeouts
                    'json'    => $options,
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->token,
                    ],
                ]);

                if (200 !== $response->getStatusCode()) {
                    usleep(50000);
                    continue;
                }

                return $response->getBody()->getContents();
            } catch (Exception $e) {
                if (0 == $i) {
                    throw new RuntimeException("Failed to generate PDF.");
                }
            }
        }

        throw new RuntimeException("Failed to generate PDF.");
    }
}
