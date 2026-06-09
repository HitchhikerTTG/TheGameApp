<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\RecuerdaModel;
use App\Models\UserModel;
use App\Models\ClubMembersModel;

class AuthCheckFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->has('loggedInUser')) {
            return;
        }

        $token = $request->getCookie('remember_me');

        if ($token) {
            $userUniId = (new RecuerdaModel())
                ->verifyRememberMeToken($token, $request->getUserAgent()->getAgentString());

            if ($userUniId) {
                $this->restoreSession($userUniId);
                return;
            }
        }

        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'session_expired']);
        }

        session()->setFlashData('fail', 'Musisz być zalogowanym użytkownikiem, aby móc korzystać z tej części serwisu.');
        return redirect()->to('/auth');
    }

    private function restoreSession(string $userUniId): void
    {
        session()->set('loggedInUser', $userUniId);

        $user = model(UserModel::class)->getGameUserData($userUniId);
        if (!$user) {
            return;
        }

        session()->set('username', $user['nick']);

        $userClub = model(ClubMembersModel::class)->getClubsByUser($userUniId);
        $clubName = $userClub['Nazwa'] ?? 'Poczekalnia';

        session()->set('club_hash', hash('sha256', $clubName));
        session()->set('club', $clubName);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
