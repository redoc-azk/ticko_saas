<?php

namespace App\Dto;

use App\Entity\Participants;
use Symfony\Component\Validator\Constraints as Assert;

class ParticipantsDto
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $nom_prenoms,
        // Mail address
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
        // Internation Phone Indicator
        public readonly string $indicatif,
        // Phone number
        public readonly string $numero,
        // Gender
        public readonly string $sexe,
        // Profession
        #[Assert\NotBlank]
        public readonly string $profession,
        // Entreprise
        public readonly string $entreprise,
        // Ville
        #[Assert\NotBlank]
        public readonly string $ville,
        // Pays
        #[Assert\NotBlank]
        public readonly string $pays,
    ) {
    }

    public function toEntity() : Participants
    {
        return (new Participants())
            ->setNomPrenoms($this->nom_prenoms)
            ->setEmail($this->email)
            ->setIndicatifTelephonique($this->indicatif)
            ->setNumero($this->numero)
            ->setSexe($this->sexe)
            ->setProfession($this->profession)
            ->setEntreprise($this->entreprise)
            ->setVille($this->ville)
            ->setPays($this->pays)
        ;
    }
}