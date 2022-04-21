<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Migration;

use Doctrine\DBAL\Connection;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Countries;
use Throwable;

use function array_key_exists;
use function array_keys;
use function array_search;
use function ctype_digit;
use function is_float;
use function is_numeric;
use function is_string;
use function json_decode;
use function json_encode;
use function preg_match;
use function strlen;
use function strpos;
use function strval;
use function substr;
use function unserialize;

/**
 * Class AddressMigrator
 * @package Ekyna\Bundle\CommerceBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressMigrator
{
    private Connection       $connection;
    private PhoneNumberUtil  $phoneNumberUtil;
    private ?LoggerInterface $logger = null;

    private ?array $countries = null;

    private const SHIPMENT_KEY_MAP = [
        'firstName'  => 'first_name',
        'lastName'   => 'last_name',
        'postalCode' => 'postal_code',
    ];

    public function __construct(Connection $connection, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->connection = $connection;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function log(string $message): void
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->debug($message);
    }

    public function migrate(): void
    {
        $this->log("Migrating shipment addresses:\n");

        $this->migrateColumn([
            'table'   => 'commerce_order_shipment',
            'column'  => 'sender_address',
            'closure' => fn(&$data) => $this->migrateShipmentAddress($data),
        ]);

        $this->migrateColumn([
            'table'   => 'commerce_order_shipment',
            'column'  => 'receiver_address',
            'closure' => fn(&$data) => $this->migrateShipmentAddress($data),
        ]);

        $this->log("\n\nMigrating invoice addresses:\n");

        $this->migrateColumn([
            'table'   => 'commerce_order_invoice',
            'column'  => 'invoice_address',
            'closure' => fn(&$data) => $this->migrateInvoiceAddress($data),
        ]);

        $this->migrateColumn([
            'table'   => 'commerce_order_invoice',
            'column'  => 'delivery_address',
            'closure' => fn(&$data) => $this->migrateInvoiceAddress($data),
        ]);

        $this->log("\n\nMigrating invoice customers:\n");

        $this->migrateColumn([
            'table'   => 'commerce_order_invoice',
            'column'  => 'customer',
            'closure' => fn(&$data) => $this->migrateInvoiceCustomer($data),
        ]);

        $this->log("\n\n");
    }

    private function migrateColumn(array $config): void
    {
        $update = $this
            ->connection
            ->prepare("UPDATE {$config['table']} SET {$config['column']}=:data WHERE id=:id LIMIT 1");

        $result = $this
            ->connection
            ->executeQuery("SELECT id, {$config['column']} FROM {$config['table']} WHERE {$config['column']} IS NOT NULL");

        while (false !== $shipment = $result->fetchAssociative()) {
            $address = json_decode($shipment[$config['column']], true);

            if (!$config['closure']($address)) {
                $this->log('.');

                continue;
            }

            $this->log('x');

            $update->executeStatement([
                'id'   => $shipment['id'],
                'data' => json_encode($address),
            ]);
        }
    }

    private function removeEmptyKeys(array &$address): bool
    {
        $changed = false;

        foreach (array_keys($address) as $key) {
            if (is_string($address[$key])) {
                if (0 !== strlen($address[$key])) {
                    continue;
                }
            } elseif (null !== $address[$key]) {
                continue;
            }

            unset($address[$key]);

            $changed = true;
        }

        return $changed;
    }

    private function migrateShipmentAddress(array &$address): bool
    {
        $changed = $this->removeEmptyKeys($address);

        // Convert keys from camelcase to underscore
        foreach (self::SHIPMENT_KEY_MAP as $old => $new) {
            if (!array_key_exists($old, $address)) {
                continue;
            }

            $address[$new] = $address[$old];
            unset($address[$old]);

            $changed = true;
        }

        // Convert phone numbers from serialization to international format
        foreach (['phone', 'mobile'] as $key) {
            if (!array_key_exists($key, $address)) {
                continue;
            }

            try {
                if (false === $number = unserialize($address[$key])) {
                    continue;
                }
            } catch (Throwable $exception) {
                continue;
            }

            if (!$number instanceof PhoneNumber) {
                throw new UnexpectedTypeException($number, PhoneNumber::class);
            }

            $address[$key] = $this->phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL);

            $changed = true;
        }

        // Converts country id to country code
        if (ctype_digit(strval($address['country']))) {
            $address['country'] = $this->migrateCountryIdToCode($address['country']);
        }

        // Convert floats to strings
        foreach (['longitude', 'latitude'] as $key) {
            if (!array_key_exists($key, $address) || !is_float($address[$key])) {
                continue;
            }

            $address[$key] = (string)$address[$key];

            $changed = true;
        }

        return $changed;
    }

    private function migrateInvoiceAddress(array &$address): bool
    {
        $changed = $this->removeEmptyKeys($address);

        $changed = $this->migrateFullName($address) || $changed;

        // Converts country name to country code
        if (!preg_match('~^[A-Z]{2}$~', (string)$address['country'])) {
            if (is_numeric($address['country'])) {
                $address['country'] = $this->migrateCountryIdToCode($address['country']);
            } else {
                $address['country'] = $this->migrateCountryNameToCode($address['country']);
            }

            $changed = true;
        }

        return $changed;
    }

    private function migrateInvoiceCustomer(array &$customer): bool
    {
        $changed = $this->removeEmptyKeys($customer);

        return $this->migrateFullName($customer) || $changed;
    }

    private function migrateFullName(array &$data): bool
    {
        if (!isset($data['full_name'])) {
            return false;
        }

        if (0 === $pos = strpos($data['full_name'], ' ')) {
            throw new RuntimeException('Failed to split full name.');
        }

        $data['first_name'] = substr($data['full_name'], 0, $pos);
        $data['last_name'] = substr($data['full_name'], $pos + 1);

        unset($data['full_name']);

        return true;
    }

    private function migrateCountryNameToCode(string $value): string
    {
        if (preg_match('~^[A-Z]{2}$~', $value)) {
            return $value;
        }

        foreach (['fr', 'en', 'es'] as $locale) {
            $countries = Countries::getNames($locale);

            if (false !== $code = array_search($value, $countries, true)) {
                return $code;
            }
        }

        throw new RuntimeException('Failed to transform country name into country code.');
    }

    private function migrateCountryIdToCode(int $id): string
    {
        foreach ($this->loadCountries() as $country) {
            if ($country['id'] === $id) {
                return $country['code'];
            }
        }

        throw new RuntimeException('Failed to transform country id into country code.');
    }

    private function loadCountries(): array
    {
        if (null !== $this->countries) {
            return $this->countries;
        }

        $countries = $this
            ->connection
            ->executeQuery('SELECT id, code, name FROM commerce_country ORDER BY name;')
            ->fetchAllAssociative();

        foreach ($countries as &$country) {
            $country['id'] = (int)$country['id'];
        }

        return $this->countries = $countries;
    }
}
