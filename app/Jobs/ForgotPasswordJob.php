<?php

namespace App\Jobs;

use App\Mail\SendForgetMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordJob implements ShouldQueue
{
    use Queueable;
    public $code;
    public $email;
   
    public function __construct($code, $email)
    {
        $this->code = $code;
        $this->email = $email;
    }

    
    public function handle(): void
    {
        $data = [
            'email' => $this->email,
            'code' => $this->code
        ];
        Mail::to($this->email)->send(new SendForgetMail($data,));
    }
}
