<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class EmailService
{
    private $mailer;
    private $senderEmail;
    private $senderName;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail = 'no-reply@eco-byte.com',
        string $senderName = 'Eco-Byte'
    ) {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function sendEventReminder(string $recipientEmail, string $recipientName, array $eventData): void
    {
        $email = (new Email())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to(new Address($recipientEmail, $recipientName))
            ->subject('Rappel : Votre événement Eco-Byte a lieu demain !')
            ->html($this->createReminderEmailBody($recipientName, $recipientEmail, $eventData));

        $this->mailer->send($email);
    }

    private function createReminderEmailBody(string $recipientName, string $recipientEmail, array $eventData): string
    {
        // Colors from the application's color scheme
        $darkGreen = '#013220';
        $mediumGreen = '#29AB87';
        $lightGreen = '#ACE1AF';
        $creamWhite = '#FEFEFA';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Rappel d'événement Eco-Byte</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: {$creamWhite};
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background: linear-gradient(135deg, {$darkGreen}, {$mediumGreen});
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .content {
                    background-color: white;
                    padding: 30px;
                    border-radius: 0 0 8px 8px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                }
                .event-details {
                    background-color: #f9f9f9;
                    border-left: 4px solid {$mediumGreen};
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    font-size: 12px;
                    color: #666;
                }
                .btn {
                    display: inline-block;
                    background: linear-gradient(135deg, {$mediumGreen}, {$darkGreen});
                    color: white;
                    text-decoration: none;
                    padding: 12px 25px;
                    border-radius: 50px;
                    font-weight: bold;
                    margin: 20px 0;
                }
                h1, h2 {
                    color: {$darkGreen};
                }
                .highlight {
                    color: {$mediumGreen};
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Rappel d'événement</h1>
                </div>
                <div class="content">
                    <p>Bonjour <span class="highlight">{$recipientName}</span>,</p>
                    
                    <p>Nous sommes ravis de vous rappeler que l'événement <strong>{$eventData['title']}</strong> auquel vous êtes inscrit(e) aura lieu <strong>demain</strong> !</p>
                    
                    <div class="event-details">
                        <h2>{$eventData['title']}</h2>
                        <p><strong>Date et heure :</strong> {$eventData['date']}</p>
                        <p><strong>Lieu :</strong> {$eventData['location']}</p>
                        <p><strong>Description :</strong> {$eventData['description']}</p>
                    </div>
                    
                    <p>N'oubliez pas de vous munir de cette confirmation. Nous sommes impatients de vous y retrouver !</p>
                    
                    <center><a href="{$eventData['mapLink']}" class="btn">Voir sur la carte</a></center>
                    
                    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    
                    <p>Cordialement,<br>L'équipe Eco-Byte</p>
                </div>
                <div class="footer">
                    <p>© 2025 Eco-Byte. Tous droits réservés.</p>
                    <p>Cet email a été envoyé à {$recipientEmail} car vous êtes inscrit(e) à un événement Eco-Byte.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
