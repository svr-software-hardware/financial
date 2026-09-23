<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

use InvalidArgumentException;

final readonly class OrganizationCertificateData
{
    public function __construct(
        public string $cerFile,
        public string $keyFile,
        #[\SensitiveParameter]
        private string $password,
    ) {
        if (trim($this->cerFile) === '') {
            throw new InvalidArgumentException(
                'La ruta del archivo .cer no puede estar vacía.'
            );
        }

        if (trim($this->keyFile) === '') {
            throw new InvalidArgumentException(
                'La ruta del archivo .key no puede estar vacía.'
            );
        }

        if (!is_file($this->cerFile)) {
            throw new InvalidArgumentException(
                'No se encontró el archivo .cer indicado.'
            );
        }

        if (!is_readable($this->cerFile)) {
            throw new InvalidArgumentException(
                'El archivo .cer no puede ser leído.'
            );
        }

        if (!is_file($this->keyFile)) {
            throw new InvalidArgumentException(
                'No se encontró el archivo .key indicado.'
            );
        }

        if (!is_readable($this->keyFile)) {
            throw new InvalidArgumentException(
                'El archivo .key no puede ser leído.'
            );
        }

        if (trim($this->password) === '') {
            throw new InvalidArgumentException(
                'La contraseña del CSD no puede estar vacía.'
            );
        }
    }

    public function password(): string
    {
        return $this->password;
    }
}