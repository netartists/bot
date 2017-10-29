<?php

/**
 * Mail class
 *
 * Class provides functions to send e-mails
 *
 * @author     René Sonntag, info@netartists.de
 * @version    1.0
 */

class Mail {

    /**
     * Sends mail
     */
    function sendMail($mailtext, $betreff, $currencyPair, $imageName) {

        $empfaenger = "rene.sonntag@gmx.de";
        $absender   = "info@netartists.de";
        $antwortan  = "info@netartists.de";

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=iso-8859-1\r\n";

        $header .= "From: $absender\r\n";
        $header .= "Reply-To: $antwortan\r\n";
        // $header .= "Cc: $cc\r\n";  // falls an CC gesendet werden soll
        $header .= "X-Mailer: PHP ". phpversion();


        $mailContent = '<html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <title>' .$currencyPair. ', ' .$mailtext. '</title>
                        </head>
                         
                        <body>
                         
                            <h1>' .$currencyPair. ', ' .$mailtext. '</h1>
                             
                            <img src="https://netartists.de/downloads/tmp/'.$imageName.'" alt="Chart" width="100%" style="display: block;">
                         
                        </body>
                      </html>';

        mail( $empfaenger, $betreff, $mailContent, $header );
    }
}