<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRegisteremail;

class RegisterJob implements ShouldQueue
{
    use Queueable;
    public $firstname;
    public $lastname;
    public $email;
    public $verification;
    /**
     * Create a new job instance.
     */
    public function __construct($firstname, $lastname, $email, $verification)
    {

        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->verification = $verification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $data = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
        ];

        $verification = [
            'code' => $this->verification,
        ];

        Mail::to($this->email)->send(new SendRegisteremail($data, $verification));
    }
}
