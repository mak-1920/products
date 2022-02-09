<?php

declare(strict_types=1);

namespace App\Services\Import\Loggers;

use App\Entity\ImportStatus;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailLogger implements LoggerInterface
{
    private string $from;

    private string $to;

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function created(ImportStatus $status): void
    {
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function beforeProcessing(ImportStatus $status): void
    {
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     *
     * @throws TransportExceptionInterface
     */
    public function afterProcessing(ImportStatus $status): void
    {
        $mail = (new Email())
            ->from($this->from)
            ->to($this->to)
            ->subject('import')
            ->text((string) $status);

        $this->mailer->send($mail);
    }

    /**
     * @param string $from
     *
     * @return $this
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param string $to
     *
     * @return $this
     */
    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }
}
