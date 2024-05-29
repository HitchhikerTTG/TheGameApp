<?php 
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\RecuerdaModel;

class AuthCheckFilter implements FilterInterface
{
      public function before(RequestInterface $request, $arguments = null)
    {
        // Sprawdź, czy użytkownik jest zalogowany przez sesję
        if(!session()->has('loggedInUser'))
        {
            // Pobierz ciasteczko remember_me, jeśli istnieje
            $token = $request->getCookie('remember_me');
            $userAgent = $request->getUserAgent(); // Pobieranie bieżącego user agent
            if ($token) {
                // Utwórz instancję modelu do weryfikacji tokenu
                $recuerdaModel = new RecuerdaModel();

                // Sprawdź ważność i istnienie tokenu w bazie danych wraz z user_agent
                $userUniId = $recuerdaModel->verifyRememberMeToken($token, $userAgent->getAgentString());
                
                if ($userUniId) {
                    // Zaloguj użytkownika na podstawie uniID
                    session()->set('loggedInUser', $userUniId);
                    return; // Jeśli token jest ważny, kontynuuj bez przekierowania
                }
            }

            // Jeśli nie ma sesji i token nie jest ważny, przekieruj do strony logowania
            session()->setFlashData('fail', 'Musisz być zalogowanym użytkownikiem, aby móc korzystać z tej części serwisu.');
            return redirect()->to('/auth');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}
?>


