<?php

namespace App\Model;

use Rain\Tpl;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


class Mailer
{
    const USERNAME = 'jpferrazsoares@gmail.com';
    const PASSWORD = '***';
    const NAME_FROM =  'Impeto Store';

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = [])
    {
        $config = array(
            "tpl_dir"       => $_SERVER['DOCUMENT_ROOT'].'/App/view/views/email/',
            "cache_dir"     => $_SERVER['DOCUMENT_ROOT'].'/App/view/views-cache/',
            "debug"         => false // set to false to improve the speed
        );



        Tpl::configure( $config );

        $tpl = new Tpl;

        foreach ($data as $key => $value)
        {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName);


        $this->mail = new PHPMailer();
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->Port = 465;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = Mailer::USERNAME;
        $this->mail->Password = Mailer::PASSWORD;
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
        $this->mail->addAddress($toAddress, $toName);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($html);
        $this->mail->AltBody = 'This is a plain-text message body';
        $this->mail->addAttachment('images/phpmailer_mini.png');
    }

    public function send()
    {
        return $this->mail->send();


    }
}