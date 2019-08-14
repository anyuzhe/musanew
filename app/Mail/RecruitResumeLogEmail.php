<?php

namespace App\Mail;

use App\Models\RecruitResumeLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitResumeLogEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = '招聘简历更新';
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RecruitResumeLog $log)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.recruitResumeLogEmail');
    }
}
