<?php

namespace App\MessageHandler;

use App\Entity\Participants;
use App\Message\InvitationMail;
use App\Repository\ParticipantsRepository;
use App\Service\GetQRService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;

#[AsMessageHandler]
class InvitationMailHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantsRepository $participantsRepository,
        private MailerInterface $mailer,
        private GetQRService $getQRService
    )
    {
    }


    public function __invoke(InvitationMail $invitationMail)
    {
        try {
            // Add delay 2 seconds
            sleep(1);
            // Get the participant from the notification
            $participant = $invitationMail->getParticipant();
            // fetch the participant from the database
            //$participant = $this->participantsRepository->find($participant);
            dump('[' . ($participant->getId() ?? 'null_id') . ' - Start] ' . $participant->getNomPrenoms() . ' ' . $participant->getEmail());
            // send the mail
            $mail = (new TemplatedEmail())
                ->from(
                    new Address('info@risquepays.gouv.cd', 'Info Risque Pays RDC')
                )
                ->to($participant->getEmail())
                ->subject('LES CONFÉRENCES RISQUE PAYS RDC 2023 | Votre invitation')
                ->htmlTemplate('mail/new_qr_mail.html.twig')
                // generate the QR code and attach it to the mail
                ->attachPart(
                    (new DataPart(
                        (($this->getQRService)($participant))->getString(),
                        'qr_code',
                        'image/png'
                    ))->asInline()
                )->attachPart(
                    (new DataPart(
                    // dsfds.png in images in public folder
                        (fopen(__DIR__ . '/../../public/assets/dsfds.jpg', 'r')),
                        'banner',
                        'image/png'
                    ))->asInline()
                )
                ->context([
                    'name' => $participant->getNomPrenoms(),
                    'qrCode' => $this->getQRService->getContent($participant),
                ])
            ;
            $this->mailer->send($mail);
            // set the participant as notified
            $participant->setMailSendedAt(new \DateTimeImmutable());
            // save the participant
            //$this->entityManager->persist($participant);
            //$this->entityManager->flush();
            dump('[' . ($participant->getId() ?? 'null_id') . ' - End] ' . $participant->getNomPrenoms() . ' ' . $participant->getEmail()
            );
        } catch (\Exception $e) {
            // dump the exception
            dump($e);
        }
    }
}