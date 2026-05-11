<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailService;

class ProcessEmailQueue extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:process';
    protected $description = 'Process pending email queue and send notifications';

    public function run(array $params): void
    {
        $service = new EmailService();
        $sent    = $service->processQueue();
        CLI::write("Sent: {$sent} email(s).", 'green');
    }
}
