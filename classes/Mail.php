<?php

class mailClass {

    /**
     * Sends mail
     */
    function sendMail($mailtext, $betreff) {

        $empfaenger = "rene.sonntag@gmx.de";
        $absender   = "info@netartists.de";
        $antwortan  = "info@netartists.de";

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=iso-8859-1\r\n";

        $header .= "From: $absender\r\n";
        $header .= "Reply-To: $antwortan\r\n";
        // $header .= "Cc: $cc\r\n";  // falls an CC gesendet werden soll
        $header .= "X-Mailer: PHP ". phpversion();

        mail( $empfaenger, $betreff, $mailtext, $header );

        return 0;
    }
}