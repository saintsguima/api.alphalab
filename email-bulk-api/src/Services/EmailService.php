<?php
namespace App\Services;

use App\Config\Env;
use PHPMailer\PHPMailer\PHPMailer;

final class EmailService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host     = Env::get('SMTP_HOST', 'smtp.hostinger.com');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = Env::get('SMTP_USER', '');
        $this->mail->Password = Env::get('SMTP_PASS', '');
        $this->mail->Port     = (int) Env::get('SMTP_PORT', '587');
        $secure               = Env::get('SMTP_SECURE', 'tls');
        if ($secure) {
            $this->mail->SMTPSecure = $secure;
        }
        // 'tls' ou 'ssl'

        $from     = Env::get('MAIL_FROM', '');
        $fromName = Env::get('MAIL_FROM_NAME', 'No-Reply');
        $this->mail->setFrom($from, $fromName);

        $replyTo = Env::get('MAIL_REPLY_TO', '');
        if ($replyTo) {
            $this->mail->addReplyTo($replyTo);
        }

        $this->mail->CharSet = 'UTF-8';
        $this->mail->isHTML(true); // envia HTML + AltBody
    }

    public function enviar(string $paraEmail, string $paraNome, string $assunto, string $bodyHtml, string $bodyTxt): void
    {
        $this->mail->clearAllRecipients();
        $this->mail->Subject = $assunto;
        $this->mail->Body    = $bodyHtml;
        $this->mail->AltBody = $bodyTxt;
        $this->mail->addAddress($paraEmail, $paraNome);

        if (! $paraEmail) {
            throw new \RuntimeException('Email do destinatário vazio.');
        }

        $this->mail->send();
    }
}
