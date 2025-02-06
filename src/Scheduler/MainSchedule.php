<?php

namespace App\Scheduler;

use App\Message\SendEmailMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('DailyMail')]
final class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                // @TODO - Create a Message to schedule
                RecurringMessage::cron('* * * * *', new SendEmailMessage()),
            )
            ->stateful($this->cache)
        ;
    }
}
