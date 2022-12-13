<?php
namespace App\Service;

use Twig\Environment as Twig_Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailService{
    private $mailerSendFrom;
    private $mailerSendFromName;
    private $mailer;
    private $twig;
    private $parameterBag;



    public function __construct($mailerSendFrom, $mailerSendFromName, MailerInterface $mailer, Twig_Environment $twig, ParameterBagInterface $parameterBag)
    {
        $this->mailerSendFrom = $mailerSendFrom;
        $this->mailerSendFromName = $mailerSendFromName;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
    }

    
    public function sendMail(array $mail, array $attachmentsPath = [], ?string $to = null, array $embeddedImages = [])
    {
        $email = (new Email())
            ->from(new Address($this->mailerSendFrom, $this->mailerSendFromName))
            ->subject($mail['subject'])
            ->html($mail['body']);

        $to = $to === null ? $mail['to'] : $to;    
        if(gettype($to) == "array"){
            $email = $email->to(...$to);
        } else {
            $email = $email->to($to);
        }

        foreach($attachmentsPath as $path){
            $email->attachFromPath($this->parameterBag->get('kernel.project_dir')."/public/files/".$path);
        }
        foreach($embeddedImages as $key => $value){
            $email->embedFromPath($this->parameterBag->get('kernel.project_dir')."/public/".$value, $key);
        }
        $this->mailer->send($email);
    }

    
    public function renderTwig($filePath, $options = []){
        return $this->twig->render($filePath, $options);
    }

    
}