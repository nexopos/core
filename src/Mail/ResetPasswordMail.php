<?php

namespace Ns\Mail;

use Ns\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( User $user )
    {
        $this->user = $user;
        }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from( ns()->option->get( 'ns_store_email', 'contact@nexopos.com' ), ns()->option->get( 'ns_store_name', env( 'APP_NAME' ) ) )
            ->to( $this->user->email )
            ->markdown( 'ns::mails/reset-password-mail' );
    }
}
