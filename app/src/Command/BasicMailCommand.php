<?php
namespace App\Command;
use App\Entity\Participants;
use App\Message\InvitationMail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'redoc:basic-mail')]
class BasicMailCommand extends Command
{
    protected static $defaultDescription = 'Send a basic mail to test mail service';

    public function __construct(
        private MailerInterface $mailer,
        private MessageBusInterface $bus
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('mail', 'm', InputOption::VALUE_REQUIRED, 'Mail to send to')
            ->addOption('scan-code', 's', InputOption::VALUE_REQUIRED, 'Scan code of the participant')
            ->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'Name of the participant')
        ;
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mail = $input->getOption('mail') ?? 'azizkamadou17@gmail.com';
        $participant = (new Participants())
            ->setNomPrenoms(
                $input->getOption('name') ?? "KAMAGATE Amadou Aziz"
            )
            ->setScanCode($input->getOption('scan-code') ?? "123456789")
            ->setEmail($mail)
        ;
        $output->writeln([
            '==========================================',
            "Sending mail to : {$mail}",
            '==========================================',
            '',
        ]);

        $this->bus->dispatch(
            new InvitationMail(
                $participant
            )
        );

        /*$this->mailer->send(
            (new Email())
                ->from(
                    new Address('info@risquepays.gouv.cd', 'Info Risque Pays RDC')
                )
                ->to($mail)
                ->subject('Hello world')
                ->text('Hello world')
        );*/
        return Command::SUCCESS;
    }
}