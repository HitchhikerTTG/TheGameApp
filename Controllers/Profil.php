<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\TurniejeModel;
use App\Models\KtoWCoGraModel;
use App\Libraries\Common;
$session = \Config\Services::session();


class Profil extends BaseController
{

    protected $_key;
    protected $_secret;



public function gdzieGram($userID, $wszystkieTurnieje) {
    $ktoWCoGraModel = new KtoWCoGraModel();
    $userTournaments = $ktoWCoGraModel->getUserTournaments($userID);

    $userTournamentsIDs = [];
    foreach ($userTournaments as $tournament) {
        $userTournamentsIDs[] = $tournament['turniejID'];
    }

    $segregatedTournaments = [
        'active' => null,
        'isActiveParticipant' => false, // Dodana flaga informująca o udziale w aktywnym turnieju
        'participated' => [],
        'notParticipated' => [],
    ];

    foreach ($wszystkieTurnieje as $turniej) {
        if ($turniej['Active']) {
            $segregatedTournaments['active'] = $turniej;
            // Sprawdzamy, czy użytkownik bierze udział w aktywnym turnieju
            if (in_array($turniej['ID'], $userTournamentsIDs)) {
                $segregatedTournaments['isActiveParticipant'] = true;
            }
            continue;
        }

        if (in_array($turniej['ID'], $userTournamentsIDs)) {
            $segregatedTournaments['participated'][] = $turniej;
        } else {
            $segregatedTournaments['notParticipated'][] = $turniej;
        }
    }

    return $segregatedTournaments;
}

    public function dodajMnieDoTurnieju($userID, $turniejID) {
    $ktoWCoGraModel = new \App\Models\KtoWCoGraModel();
    $userModel = model(UserModel::class);


    $result = $ktoWCoGraModel->addUserTTTournament($userID, $turniejID);

    $userModel->changeActiveTournamentFlag($userID, True);

    if ($result) {
        // Jeśli użytkownik został pomyślnie dodany do turnieju
        session()->setFlashdata('success', 'Pomyślnie dołączyłeś do turnieju. Ić typować. Go! Go! Go!');
    } else {
        // Jeśli użytkownik jest już przypisany do turnieju lub wystąpił błąd
        session()->setFlashdata('error', 'Nie udało się dołączyć do turnieju. Może już w nim uczestniczysz. Albo Wit coś z ten tegesił');
    }

    // Przekierowanie użytkownika z powrotem do strony, z której przyszedł, lub do innego miejsca
    return redirect()->to('/profil'); // Zastąp '/miejscePrzekierowania' odpowiednim URL
}

    public function zapiszPreferencje()
    {
    $userModel = model(UserModel::class);
    $loggedInUserId = session()->get('loggedInUser');

    $userModel->where('uniID', $loggedInUserId)->set([
        'notify_bet_saved' => (int)($this->request->getPost('notify_bet_saved') === 'on'),
        'notify_reminder'  => (int)($this->request->getPost('notify_reminder') === 'on'),
        'digest_optin' => (int)($this->request->getPost('digest_optin') === 'on'),

    ])->update();


    session()->setFlashdata('success', 'Preferencje zapisane.');
    return redirect()->to('/profil');
    }

    public function zapiszEmoji()
    {
        $loggedInUserId = session()->get('logged_in_user_uniID');
        if (!$loggedInUserId) {
            return redirect()->to('/auth');
        }

        $emoji = mb_substr(trim($this->request->getPost('emoji') ?? ''), 0, 2);

        model(\App\Models\UserModel::class)
            ->where('uniID', $loggedInUserId)
            ->set(['emoji' => $emoji])
            ->update();

        session()->setFlashdata('success', 'Emoji zapisane.');
        return redirect()->to('/profil');
    }


    public function index()
    {
        $common = new Common();
        $tournaments = $common->loadTournaments();

#        $userModel = new UserModel();   
        $userModel = model(UserModel::class);

        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->getGameUserData($loggedInUserId);
 #       $userBuilder = $userModel->builder();
 #       $userBuilder->where('activated',1);
    
        $gdzieGram = $this->gdzieGram($userInfo['id'],$tournaments);

        $data=[
        'title'=>'Profil użytkownika '.$userInfo['nick'],
        'tournaments' => $tournaments,
        'userInfo' => $userInfo,
        'gdzieGram' => $gdzieGram
        ];

        return
            view('typowanie/header', $data) 
            .view('profil/profil',$data);    
    }


}