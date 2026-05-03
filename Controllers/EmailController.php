<?php

// app/Controllers/EmailController.php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Postmark;
use App\Models\UserModel;
use App\Models\TabelaModel;
use App\Models\TypyModel;
use App\Models\OdpowiedziModel;

class EmailController extends BaseController
{
    public function sendEmail()
    {
        $postmark = new Postmark();
        
        $from = 'ogloszenia@jakiwynik.com';
        $to = 'wit@nirski.com';
        $subject = 'Rusza typer na piłkarskie Euro';


        // Paths of the email templates
        //$htmlContentPath = FCPATH . '/maile/pierwszyMail.html';
        //$textContentPath = FCPATH . '/maile/pierwszyMail.txt';

        // Fetch contents using file_get_contents
        $htmlBody = file_get_contents($htmlContentPath);
        $textBody = file_get_contents($textContentPath);

        if ($htmlBody === false || $textBody === false) {
            echo 'Failed to fetch email templates.';
            return;
        }

        if ($postmark->sendEmail($from, $to, $subject, $htmlBody, $textBody, $messageStream)) {
            echo 'Email sent successfully.';
        } else {
            echo 'Failed to send email.';
        }
    }

    public function wyslijDoLudzi()
{
    $file = WRITEPATH . 'logs/test_log.log';
    file_put_contents($file, "Zaczalem sprawdzać wysyłkę: \n", FILE_APPEND);

    $allowedUserId = '198f5ea6e48586932127e563c0bbea83'; // Replace with the actual user ID that is allowed to send emails
    $loggedInUserId = session()->get('loggedInUser');

    // Check if the logged-in user is allowed to send emails
    if ($loggedInUserId !== $allowedUserId) {
        file_put_contents($file, "Unauthorized access attempt.\n", FILE_APPEND);
        echo 'You are not authorized to perform this action.';
        return;
    }

    // Check if the confirmation step is completed
    if (!$this->request->getPost('confirm')) {
        file_put_contents($file, "Displaying confirmation form.\n", FILE_APPEND);
        // Display confirmation form
        echo '<form method="post" action="">
                <input type="hidden" name="confirm" value="1">
                <button type="submit">Are you sure you want to send the emails?</button>
              </form>';
        return;
    }

    $userModel = new UserModel();
    $postmark = new Postmark();
    
    $from = 'ogloszenia@jakiwynik.com';
    $replyto = 'wit@nirski.com';
    $subject = 'Kilka informacji dla aktywnie typujących';

    // Paths of the email templates
    $htmlContentPath = FCPATH . '/maile/drugiMail.html';

    // Fetch contents using file_get_contents
    $htmlBody = file_get_contents($htmlContentPath);

    if ($htmlBody === false) {
        file_put_contents($file, "Failed to fetch email templates.\n", FILE_APPEND);
        echo 'Failed to fetch email templates.';
        return;
    }

    // $users = $userModel->getActiveUsersInTournament(2);

    $users = [
        ['email' => 'wit@nirski.com'],
        ['email' => 'wit.nirski@gmail.com'],
        ['email' => 'polecajka@nirski.pl']
    ];
    file_put_contents($file, "wiemy do kogo slac: \n", FILE_APPEND);

    foreach ($users as $user) {
        $to = $user['email'];
        file_put_contents($file, "do ".$user['email']."\n", FILE_APPEND);
        if ($postmark->sendEmail($from, $to, $replyto, $subject, $htmlBody)) {
            echo "Email sent successfully to $to.<br>";
        } else {
            echo "Failed to send email to $to.<br>";
        }
    }
}

private function replacePlaceholders($template, $placeholders) {
    foreach ($placeholders as $key => $value) {
        $template = str_replace("{" . $key . "}", $value, $template);
    }
    return $template;
}

public function wyslijPersonalnieDoLudzi()
{

    $file = WRITEPATH . 'logs/test_log.log';
    file_put_contents($file, "Zaczalem sprawdzać wysyłkę: \n", FILE_APPEND);

    $allowedUserId = '198f5ea6e48586932127e563c0bbea83'; // Replace with the actual user ID that is allowed to send emails
    $loggedInUserId = session()->get('loggedInUser');

    // Check if the logged-in user is allowed to send emails
    if ($loggedInUserId !== $allowedUserId) {
        file_put_contents($file, "Unauthorized access attempt.\n", FILE_APPEND);
        echo 'You are not authorized to perform this action.';
        return;
    }

    $userModel = new UserModel();
    $postmark = new Postmark();
    $model = model(TabelaModel::class);
    $tabelaDanych = $model->gimmeTabelaGraczy(2);

    // Check if the confirmation step is completed
    if (!$this->request->getPost('confirm')) {
        // Display confirmation form
        echo '<form method="post" action="">
                <input type="hidden" name="confirm" value="1">
                <button type="submit">Are you sure you want to send the emails?</button>
              </form>';
        
        return;
    }

    $from = 'ogloszenia@jakiwynik.com';
    $replyto = 'wit@nirski.com';
    $subject = 'Krótkie podsumowanie ⚽️ Typera Euro 2024 🏆';

    // Paths of the email templates
    $htmlContentPath = FCPATH . '/maile/piatyMail.html';

    // Fetch contents using file_get_contents
    $htmlBody = file_get_contents($htmlContentPath);

    if ($htmlBody === false) {
        file_put_contents($file, "Failed to fetch email templates.\n", FILE_APPEND);
        echo 'Failed to fetch email templates.';
        return;
    }

    // Fetch the list of users to send emails to
    $doNichSlemy = $userModel->prepActiveUsersInTournament(2);
    $doNichSlemyUzupelnione = $this->uzupelnijDane($doNichSlemy, $tabelaDanych);

    file_put_contents($file, "wiemy do kogo slac: \n", FILE_APPEND);

    // Filter to send email only to user with id 25
    $doNichSlemyUzupelnione = array_filter($doNichSlemyUzupelnione, function($user) {
        return $user['id'] == 25;
    });

    foreach ($doNichSlemyUzupelnione as $user) {
        $to = $user['email'];
        file_put_contents($file, "do ".$user['email']."\n", FILE_APPEND);

        // Prepare placeholders
        $placeholders = [
            'nick' => $user['nick'],
            'wszystkichTypow' =>$user['wszystkieTypy'],
            'dokladnychTrafien' => $user['dokladneTrafienia'],
            'totolotek'=>$user['totolotek'],
            'wszystkieOdpowiedzi'=>$user['liczbaOdpowiedzi'],
            'prawidloweOdpowiedzi'=>$user['liczbaPrawidlowychOdpowiedzi'],
            'punkty' => $user['punkty'],
            //'zlotyPilk' => $user['usedGoldenBall'] == 0 ? 'a Ty masz jeszcze możliwość użycia złotej piłki' : '',
            'miejsce' => $user['pozycja'],
            
        ];

        // Replace placeholders in the email body
        $personalizedHtmlBody = $this->replacePlaceholders($htmlBody, $placeholders);

        if ($postmark->sendEmail($from, $to, $replyto, $subject, $personalizedHtmlBody)) {
            echo "Email sent successfully to $to.<br>";
        } else {
            echo "Failed to send email to $to.<br>";
        }
    }
}


// Funkcja do uzupełniania danych
function uzupelnijDane($doNichSlemy, $tabelaDanych) {
    $typyModel = new TypyModel();
    $odpowiedziModel = new OdpowiedziModel();

    // Mapujemy tabelaDanych na tablicę z uid jako klucz
    $mapaDanych = [];
    foreach ($tabelaDanych as $dane) {
        $mapaDanych[$dane['uid']] = $dane;
    }

    // Przechodzimy przez DoNichSlemy i uzupełniamy dane
    foreach ($doNichSlemy as &$user) {
        $uid = $user['id'];
        if (isset($mapaDanych[$uid])) {
            $user['wszystkieTypy'] = $typyModel->liczbaTypow($user['uniID'], 2);
            $user['punkty'] = $mapaDanych[$uid]['punkty'];
            $user['punktyZaMecze'] = $mapaDanych[$uid]['punktyZaMecze'];
            $user['dokladneTrafienia'] = $mapaDanych[$uid]['dokladneTrafienia'];
            $user['totolotek']=$typyModel->liczbaTotolotkowychTypow($user['uniID'], 2);
            $user['usedGoldenBall'] = $typyModel->usedGoldenBall($user['uniID']);
            $user['liczbaOdpowiedzi'] = $odpowiedziModel->liczbaOdpowiedzi($user['uniID']);
            $user['liczbaPrawidlowychOdpowiedzi'] = $odpowiedziModel->liczbaPrawidlowychOdpowiedzi($user['uniID']);            
        } else {
            $user['wszystkieTypy'] = 0;
            $user['punkty'] = 0;
            $user['punktyZaMecze'] = 0;
            $user['dokladneTrafienia'] = 0;
            $user['totolotek']=0;
            $user['usedGoldenBall'] = 0;
            $user['liczbaOdpowiedzi'] = 0;
            $user['liczbaPrawidlowychOdpowiedzi'] = 0;
        }
    }

    // Sortowanie graczy według punktów malejąco
    usort($doNichSlemy, function($a, $b) {
        return $b['punkty'] - $a['punkty'];
    });

    // Obliczanie pozycji
    $previousPoints = null;
    $rank = 0;
    foreach ($doNichSlemy as $index => &$user) {
        if ($previousPoints !== $user['punkty']) {
            $rank = $index + 1; // Dodajemy 1, aby pozycja zaczynała się od 1
            $previousPoints = $user['punkty'];
        }
        $user['pozycja'] = $rank;
    }

    return $doNichSlemy;
}


}
?>


