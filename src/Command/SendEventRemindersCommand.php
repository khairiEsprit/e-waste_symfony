<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Participation;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-event-reminders',
    description: 'Send reminder emails to participants one day before their registered events',
)]
class SendEventRemindersCommand extends Command
{
    private $entityManager;
    private $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sending event reminders for events happening tomorrow');

        // Calculate tomorrow's date (start and end)
        $tomorrow = new \DateTime('tomorrow');
        $tomorrowStart = clone $tomorrow;
        $tomorrowStart->setTime(0, 0, 0);
        $tomorrowEnd = clone $tomorrow;
        $tomorrowEnd->setTime(23, 59, 59);

        // Find events happening tomorrow
        $events = $this->entityManager->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->where('e.date BETWEEN :start AND :end')
            ->setParameter('start', $tomorrowStart)
            ->setParameter('end', $tomorrowEnd)
            ->getQuery()
            ->getResult();

        if (empty($events)) {
            $io->info('No events found for tomorrow.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d events scheduled for tomorrow.', count($events)));
        $remindersSent = 0;

        foreach ($events as $event) {
            $io->section(sprintf('Processing event: %s', $event->getTitle()));

            // Find participants who haven't received a reminder yet
            $participants = $this->entityManager->getRepository(Participation::class)
                ->createQueryBuilder('p')
                ->where('p.event = :event')
                ->andWhere('p.reminderSent = :reminderSent')
                ->setParameter('event', $event)
                ->setParameter('reminderSent', false)
                ->getQuery()
                ->getResult();

            if (empty($participants)) {
                $io->info('No participants found or all participants have already received reminders.');
                continue;
            }

            $io->info(sprintf('Sending reminders to %d participants.', count($participants)));

            foreach ($participants as $participant) {
                try {
                    // Prepare event data for the email
                    $eventData = [
                        'title' => $event->getTitle(),
                        'date' => $event->getDate()->format('d/m/Y à H:i'),
                        'location' => $event->getLocation(),
                        'description' => $event->getDescription() ?: 'Aucune description disponible.',
                        'mapLink' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event->getLocation()),
                    ];

                    // Send the reminder email
                    $this->emailService->sendEventReminder(
                        $participant->getEmail(),
                        $participant->getFirstName() . ' ' . $participant->getLastName(),
                        $eventData
                    );

                    // Mark the reminder as sent
                    $participant->setReminderSent(true);
                    $this->entityManager->persist($participant);
                    
                    $remindersSent++;
                    $io->writeln(sprintf(
                        'Reminder sent to %s %s (%s)',
                        $participant->getFirstName(),
                        $participant->getLastName(),
                        $participant->getEmail()
                    ));
                } catch (\Exception $e) {
                    $io->error(sprintf(
                        'Failed to send reminder to %s %s (%s): %s',
                        $participant->getFirstName(),
                        $participant->getLastName(),
                        $participant->getEmail(),
                        $e->getMessage()
                    ));
                }
            }

            // Flush changes to the database after processing each event
            $this->entityManager->flush();
        }

        if ($remindersSent > 0) {
            $io->success(sprintf('Successfully sent %d reminder emails.', $remindersSent));
        } else {
            $io->info('No reminder emails were sent.');
        }

        return Command::SUCCESS;
    }
}
