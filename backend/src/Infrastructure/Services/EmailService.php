<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->smtpHost = $_ENV['SMTP_HOST'] ?? 'smtp.example.com';
        $this->smtpPort = (int)($_ENV['SMTP_PORT'] ?? 587);
        $this->smtpUsername = $_ENV['SMTP_USERNAME'] ?? '';
        $this->smtpPassword = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@example.com';
        $this->fromName = $_ENV['SMTP_FROM_NAME'] ?? 'Password Reset';
    }

    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        $resetUrl = "http://localhost/reset-password.html?token={$token}";
        
        $subject = 'Сброс пароля';
        $body = "
            <p>Здравствуйте, {$user->getName()}!</p>
            <p>Вы запросили сброс пароля. Перейдите по ссылке ниже, чтобы установить новый пароль:</p>
            <p><a href=\"{$resetUrl}\">Сбросить пароль</a></p>
            <p>Ссылка действительна в течение 1 часа.</p>
            <p>Если вы не запрашивали сброс пароля, проигнорируйте это письмо.</p>
        ";

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    private function sendEmail(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);
        
        try {
            // Настройка SMTP
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $this->smtpPort;
            $mail->Timeout = 20;
            $mail->SMTPDebug = 2;

            // Настройка кодировки
            $mail->CharSet = 'UTF-8';
            
            // От кого
            $mail->setFrom($this->fromEmail, $this->fromName);
            
            // Кому
            $mail->addAddress($to);
            
            // Тема и тело письма
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            // Отправка

            $mail->send();
            return true;
            
        } catch (PHPMailerException $e) {
            error_log('Ошибка отправки email: ' . $e->errorMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Ошибка отправки email: ' . $e->getMessage());
            return false;
        }
    }
}
