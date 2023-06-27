<?php

namespace App\Message;

use App\Entity\Participants;

class InvitationMail
{
    private $participant;

    public function __construct(
        Participants $participant
    )
    {
        $this->participant = $participant;
    }

    public function getParticipant()
    {
        return $this->participant;
    }
}