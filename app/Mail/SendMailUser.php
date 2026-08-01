<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $anexo;

 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($anexo)
    {
        $this->anexo = $anexo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('nao-responda@steriliza.com.br')
            ->subject("Steriliza - Nota Fiscal Eletrônica")
            ->view('emails.nota')
            ->attachData($this->anexo, "nfse.pdf");
    }
}
