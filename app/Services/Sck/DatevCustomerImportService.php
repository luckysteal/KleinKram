<?php

namespace App\Services\Sck;

use App\Models\Sck\SckCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DatevCustomerImportService
{
    /**
     * Import customers from DATEV's standard Debitoren/Kreditoren export.
     *
     * @return array{created: int, updated: int, unchanged: int, skipped_creditors: int, skipped_invalid: int}
     */
    public function import(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            throw new InvalidArgumentException('Die DATEV-Datei ist leer oder konnte nicht gelesen werden.');
        }

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $this->toUtf8($contents));
        rewind($stream);

        $datevHeader = $this->readRow($stream);
        if (! $this->isDatevCustomerHeader($datevHeader)) {
            fclose($stream);
            throw new InvalidArgumentException('Bitte einen DATEV-Standardexport „Debitoren/Kreditoren“ (Formatkategorie 16) auswählen.');
        }

        $columnRow = $this->readRow($stream);
        $columns = $this->columnMap($columnRow);
        foreach (['account', 'company_name', 'person_name', 'unspecified_name', 'addressee_type'] as $required) {
            if (! array_key_exists($required, $columns)) {
                fclose($stream);
                throw new InvalidArgumentException('Die DATEV-Spaltenüberschriften sind unvollständig. Es fehlt eine Standardspalte für Konto, Name oder Adressatentyp.');
            }
        }

        $result = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped_creditors' => 0, 'skipped_invalid' => 0];

        DB::transaction(function () use ($stream, $columns, &$result) {
            while (($row = $this->readRow($stream)) !== false) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $account = preg_replace('/\D/', '', $this->value($row, $columns, 'account'));
                if ($account === '' || trim($account, '0') === '' || strlen($account) > 9) {
                    $result['skipped_invalid']++;

                    continue;
                }
                if ($this->isCreditorAccount($account)) {
                    $result['skipped_creditors']++;

                    continue;
                }

                $name = $this->customerName($row, $columns);
                if ($name === '') {
                    $result['skipped_invalid']++;

                    continue;
                }

                [$street, $houseNumber] = $this->splitStreet($this->value($row, $columns, 'street'));
                $data = [
                    'datev_account' => $account,
                    'name' => $name,
                    'street' => $street ?: null,
                    'house_number' => $houseNumber ?: null,
                    'postal_code' => $this->nullableValue($row, $columns, 'postal_code'),
                    'city' => $this->nullableValue($row, $columns, 'city'),
                    'country_code' => strtoupper($this->value($row, $columns, 'country')) ?: 'DE',
                    'phone' => $this->nullableValue($row, $columns, 'phone'),
                    'email' => $this->nullableValue($row, $columns, 'email'),
                ];

                $customer = $this->findExistingCustomer($account, $data);
                if (! $customer) {
                    SckCustomer::create($data + ['status' => 'active']);
                    $result['created']++;

                    continue;
                }

                if ($customer->trashed()) {
                    $customer->restore();
                }

                $addressChanged = collect(['street', 'house_number', 'postal_code', 'city', 'country_code'])
                    ->contains(fn (string $field) => (string) $customer->{$field} !== (string) $data[$field]);
                if ($addressChanged) {
                    $data['latitude'] = null;
                    $data['longitude'] = null;
                }

                $customer->fill($data);
                if ($customer->isDirty()) {
                    $customer->save();
                    $result['updated']++;
                } else {
                    $result['unchanged']++;
                }
            }
        });

        fclose($stream);

        return $result;
    }

    private function readRow($stream): array|false
    {
        return fgetcsv($stream, null, ';', '"', '');
    }

    private function toUtf8(string $contents): string
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            return substr($contents, 3);
        }
        if (str_starts_with($contents, "\xFF\xFE")) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        }
        if (str_starts_with($contents, "\xFE\xFF")) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        }

        return mb_check_encoding($contents, 'UTF-8')
            ? $contents
            : mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
    }

    private function isDatevCustomerHeader(array|false $row): bool
    {
        if (! $row || count($row) < 4) {
            return false;
        }

        $marker = ltrim(trim((string) $row[0]), "\xEF\xBB\xBF");

        return in_array($marker, ['EXTF', 'DTVF'], true)
            && (string) $row[2] === '16'
            && trim((string) $row[3]) === 'Debitoren/Kreditoren';
    }

    private function columnMap(array|false $headers): array
    {
        if (! $headers) {
            return [];
        }

        $aliases = [
            'konto' => 'account',
            'nameadressatentypunternehmen' => 'company_name',
            'nameadressatentypnaturlperson' => 'person_name',
            'nameadressatentypnaturlicheperson' => 'person_name',
            'vornameadressatentypnaturlperson' => 'first_name',
            'vornameadressatentypnaturlicheperson' => 'first_name',
            'nameadressatentypkeineangabe' => 'unspecified_name',
            'adressatentyp' => 'addressee_type',
            'strasse' => 'street',
            'postleitzahl' => 'postal_code',
            'ort' => 'city',
            'land' => 'country',
            'telefon' => 'phone',
            'email' => 'email',
        ];

        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii(trim((string) $header))));
            if (isset($aliases[$normalized])) {
                $map[$aliases[$normalized]] = $index;
            }
        }

        return $map;
    }

    private function customerName(array $row, array $columns): string
    {
        $company = $this->value($row, $columns, 'company_name');
        $lastName = $this->value($row, $columns, 'person_name');
        $firstName = $this->value($row, $columns, 'first_name');
        $unspecified = $this->value($row, $columns, 'unspecified_name');
        $person = trim($firstName.' '.$lastName);

        return match ($this->value($row, $columns, 'addressee_type')) {
            '1' => $person ?: ($unspecified ?: $company),
            '2', '' => $company ?: ($unspecified ?: $person),
            default => $unspecified ?: ($company ?: $person),
        };
    }

    private function splitStreet(string $address): array
    {
        $address = trim($address);
        if (preg_match('/^(.+?)\s+(\d+[a-zA-Z]?(?:\s*[-\/]\s*\d+[a-zA-Z]?)?)$/u', $address, $matches)) {
            return [trim($matches[1]), preg_replace('/\s+/', '', $matches[2])];
        }

        return [$address, ''];
    }

    private function findExistingCustomer(string $account, array $data): ?SckCustomer
    {
        $customer = SckCustomer::withTrashed()->where('datev_account', $account)->first();
        if ($customer) {
            return $customer;
        }

        if ($data['email']) {
            $matches = SckCustomer::withTrashed()->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->get();
            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        $matches = SckCustomer::withTrashed()
            ->where('name', $data['name'])
            ->where('postal_code', $data['postal_code'])
            ->where('city', $data['city'])
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function isCreditorAccount(string $account): bool
    {
        $firstDigit = (int) substr(ltrim($account, '0'), 0, 1);

        return $firstDigit >= 7;
    }

    private function value(array $row, array $columns, string $key): string
    {
        if (! isset($columns[$key])) {
            return '';
        }

        return trim((string) ($row[$columns[$key]] ?? ''));
    }

    private function nullableValue(array $row, array $columns, string $key): ?string
    {
        return ($value = $this->value($row, $columns, $key)) !== '' ? $value : null;
    }

    private function rowIsEmpty(array $row): bool
    {
        return count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0;
    }
}
