<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailService;

class SendReminders extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:reminders';
    protected $description = 'Send daily reminder emails for upcoming unbet matches';

    public function run(array $params): void
    {
        $service = new EmailService();
        $sent    = $service->sendReminders();
        CLI::write("Sent: {$sent} reminder email(s).", 'green');
    }
}
