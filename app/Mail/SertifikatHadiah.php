<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Email "sertifikat hadiah" untuk penerima adopsi hadiah: pengirim
// menghadiahkan adopsi pohon, penerima membuka tautan sertifikat publik.
class SertifikatHadiah extends Mailable
{
    use Queueable, SerializesModels;

    public $toName;
    public $fromName;
    public $pohon;
    public $link;

    public function __construct($toName, $fromName, $pohon, $link)
    {
        $this->toName = $toName;
        $this->fromName = $fromName;
        $this->pohon = $pohon;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Anda Menerima Hadiah Adopsi Pohon 🌳')
            ->view('emails.sertifikat-hadiah');
    }
}
