<?php

namespace App\Model;

use DateTime;

class Nota
{
    public function __construct(
        private string $text = '',
        private string $data_creacio = '',
        private ?int $id = null,
        private ?int $usuari_id = null,
        private ?int $lead_id = null,
    ) {}

    private string $nomUsuari = "";

    public function getNomUsuari(): string
    {
        return $this->nomUsuari;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuariId(): ?int
    {
        return $this->usuari_id;
    }

    public function getLeadId(): ?int
    {
        return $this->lead_id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getDataCreacio(): string
    {
        return (new DateTime($this->data_creacio))->format('d/m/Y H:i');
    }
}
