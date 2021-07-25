<?php

use App\UserWithdrawal;
use App\acceptEmail;
use App\Authorization;
use App\AuthorizationException;
use App\Database;
use App\SearchUsers;
use App\FillingOut_BC;
use App\FillingOut_P;
use App\FillingOut_IAN;
use App\FillingOut_OMS;
use App\FillingOut_OldOMS;
use App\FillingOut_IP;
use App\FillingOut_M;
use App\Session;
use App\ConfirmData;
use App\Editing;
use App\EditingAdmin;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader(array('templates', 'templates\insurance', 'templates\individual-account-numbers', 'templates\identification-documents', 'templates\primary-documents', 'templates\admin', 'templates\edit-documents'));
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$session = new Session();
$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($session)
{
    $session->start();
    $response = $handler->handle($request);
    $session->save();
    return $response;
};

$app->add($sessionMiddleware);

$config = include_once 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

$database = new Database($dsn, $username, $password);
$authorization = new Authorization($database, $session);

$acceptEmail = new acceptEmail($database);

$UserWithdrawal = new UserWithdrawal($database);
$fillingOut_BC = new FillingOut_BC($database, $session);
$fillingOut_P = new FillingOut_P($database, $session);
$fillingOut_IAN = new FillingOut_IAN($database, $session);
$fillingOut_OMS = new FillingOut_OMS($database, $session);
$fillingOut_oldOMS = new FillingOut_OldOMS($database, $session);
$fillingOut_IP = new FillingOut_IP($database, $session);
$fillingOut_M = new FillingOut_M($database, $session);
$SearchUsers = new SearchUsers($database, $session);
$ConfirmData = new ConfirmData($database);
$Editing = new Editing($database, $session);
$EditingAdmin = new EditingAdmin($database, $session);

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }  else {
        $body = $twig->render('index-admin.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
}
});

$app->get('/documents-users', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session, $UserWithdrawal) {
    if ($session->getData('user')  ['role'] === 'admin') {
        $body = $twig->render('index-documents-users.twig', [
            'user' => $session->getData('user'),
            'users' => $UserWithdrawal->users()
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }  elseif ($session->getData('user')  === NULL) {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->get('/all-users', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }  else {
        $body = $twig->render('all-users.twig', [
            'user' => $session->getData('user'),
            'users' => $UserWithdrawal->conclusion()
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('login.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/login-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session){
    $params = (array) $request->getParsedBody();
try {
    $authorization->login($params['email'], $params['password']);
} catch(AuthorizationException $exception) {
    $session->setData('message', $exception->getMessage());
    $session->setData('form', $params);
    return $response->withHeader('Location', '/login')->withStatus(302);
}
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/register', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session) {
    $body = $twig->render('register.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/register-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session) {
    $params = (array) $request->getParsedBody();
    try {
        $authorization->register($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/register')->withStatus(302);
    }
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/email-confirmed', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session, $acceptEmail) {
    $body = $twig->render('email-confirmed.twig', [
        'message' => $session->flush('message'),
    ]);
    $params = (array) $request->getParsedBody();
    $acceptEmail->accept($params);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/prim-doc', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('prim-doc.twig', [
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/primary-documents', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('primary-documents.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('primary-documents.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('primary-documents.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $UserWithdrawal->birth_certificates_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/primary-documents-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_BC, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_BC->birthCertificates($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/primary-documents')->withStatus(302);
    }
    return $response->withHeader('Location', '/primary-documents')->withStatus(302);
});

$app->post('/primary-documents-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataBC($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/primary-documents')->withStatus(302);
    }
    return $response->withHeader('Location', '/primary-documents?user_id=' . $UserWithdrawal->birth_certificates_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-primary-documents', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('primary-documents.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-primary-documents.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-primary-documents.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $UserWithdrawal->birth_certificates_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-primary-documents-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($Editing, $session){
    $params = (array) $request->getParsedBody();
    try {
        $Editing->editBC($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-primary-documents')->withStatus(302);
    }
    return $response->withHeader('Location', '/primary-documents')->withStatus(302);
});

$app->get('/identification-documents', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('identification-documents.twig', [
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/passport', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('passport.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('passport.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'passport' => $session->getData('passport'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }  else {
        $body = $twig->render('passport.twig', [
            'user' => $session->getData('user'),
            'passport' => $UserWithdrawal->passport_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/passport-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_P, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_P->passport($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/passport')->withStatus(302);
});

$app->post('/passport-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataP($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/passport?user_id=' . $UserWithdrawal->passport_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-passport', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('passport.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-passport.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'passport' => $session->getData('passport'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }  else {
        $body = $twig->render('edit-passport.twig', [
            'user' => $session->getData('user'),
            'passport' => $UserWithdrawal->passport_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-passport-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($Editing, $session){
    $params = (array) $request->getParsedBody();
    try {
        $Editing->editP($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/passport')->withStatus(302);
});

$app->get('/international-passport', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('international-passport.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('international-passport.twig', [
            'user' => $session->getData('user'),
            'international_passport' => $session->getData('international_passport'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('international-passport.twig', [
            'user' => $session->getData('user'),
            'international_passport' => $UserWithdrawal->international_passport_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/international-passport-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_IP, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_IP->international_passport($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/international-passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/international-passport')->withStatus(302);
});

$app->post('/international-passport-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataIP($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/international-passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/international-passport?user_id=' . $UserWithdrawal->international_passport_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-international-passport', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('international-passport.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-international-passport.twig', [
            'user' => $session->getData('user'),
            'international_passport' => $session->getData('international_passport'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-international-passport.twig', [
            'user' => $session->getData('user'),
            'international_passport' => $UserWithdrawal->international_passport_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-international-passport-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($Editing, $session){
    $params = (array) $request->getParsedBody();
    try {
        $Editing->editIP($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-international-passport')->withStatus(302);
    }
    return $response->withHeader('Location', '/international-passport')->withStatus(302);
});

$app->get('/military', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('military.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('military.twig', [
            'user' => $session->getData('user'),
            'military' => $session->getData('military'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('military.twig', [
            'user' => $session->getData('user'),
            'military' => $UserWithdrawal->military_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/military-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_M, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_M->military($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/military')->withStatus(302);
    }
    return $response->withHeader('Location', '/military')->withStatus(302);
});

$app->post('/military-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataM($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/military')->withStatus(302);
    }
    return $response->withHeader('Location', '/military?user_id=' . $UserWithdrawal->military_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-military', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('military.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-military.twig', [
            'user' => $session->getData('user'),
            'military' => $session->getData('military'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-military.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'military' => $UserWithdrawal->military_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-military-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($Editing, $session){
    $params = (array) $request->getParsedBody();
    try {
        $Editing->editM($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-military')->withStatus(302);
    }
    return $response->withHeader('Location', '/military')->withStatus(302);
});

$app->get('/ian', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('IAN.twig', [
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/individual-account-numbers', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('individual-account-numbers.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('individual-account-numbers.twig', [
            'user' => $session->getData('user'),
            'individual_account_numbers' => $session->getData('individual_account_numbers'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('individual-account-numbers.twig', [
            'user' => $session->getData('user'),
            'individual_account_numbers' => $UserWithdrawal->individual_account_numbers_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/individual-account-numbers-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_IAN, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_IAN->individAccNumbers($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/individual-account-numbers')->withStatus(302);
    }
    return $response->withHeader('Location', '/individual-account-numbers')->withStatus(302);
});

$app->post('/individual-account-numbers-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataIAN($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/individual-account-numbers')->withStatus(302);
    }
    return $response->withHeader('Location', '/individual-account-numbers?user_id=' . $UserWithdrawal->individual_account_numbers_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-individual-account-numbers', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('index.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-individual-account-numbers.twig', [
            'user' => $session->getData('user'),
            'individual_account_numbers' => $session->getData('individual_account_numbers'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-individual-account-numbers.twig', [
            'user' => $session->getData('user'),
            'individual_account_numbers' => $UserWithdrawal->individual_account_numbers_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-individual-account-numbers-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($session, $Editing){

    $params = (array) $request->getParsedBody();
    try {
        $Editing->editIAN($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-individual-account-numbers')->withStatus(302);
    }
    return $response->withHeader('Location', '/individual-account-numbers')->withStatus(302);
});

$app->get('/insurance', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('insurance.twig', [
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/oms', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('OMS.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('OMS.twig', [
            'user' => $session->getData('user'),
            'policy_oms' => $session->getData('policy_oms'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('OMS.twig', [
            'user' => $session->getData('user'),
            'policy_oms' => $UserWithdrawal->policy_oms_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/oms-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_OMS, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_OMS->policy_oms($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/oms')->withStatus(302);
});

$app->post('/oms-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataOMS($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/oms?user_id=' . $UserWithdrawal->policy_oms_user() ['people_id'])->withStatus(302);
});

$app->get('/old-oms', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('oldOMS.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('oldOMS.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'policy_oldoms' => $session->getData('policy_oldoms'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('oldOMS.twig', [
            'user' => $session->getData('user'),
            'policy_oldoms' => $UserWithdrawal->policy_oldoms_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/old-oms-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($fillingOut_oldOMS, $session){
    $params = (array) $request->getParsedBody();
    try {
        $fillingOut_oldOMS->policy_oldOms($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/old-oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/old-oms')->withStatus(302);
});

$app->post('/old-oms-confirm-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($ConfirmData, $session, $UserWithdrawal){

    $params = (array) $request->getParsedBody();
    try {
        $ConfirmData->confirmDataoldOMS($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/old-oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/old-oms?user_id=' . $UserWithdrawal->policy_oldoms_user() ['people_id'])->withStatus(302);
});

$app->get('/edit-oms', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('edit-OMS.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-OMS.twig', [
            'user' => $session->getData('user'),
            'policy_oms' => $session->getData('policy_oms'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-OMS.twig', [
            'user' => $session->getData('user'),
            'policy_oms' => $UserWithdrawal->policy_oms_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-oms-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($session, $Editing){

    $params = (array) $request->getParsedBody();
    try {
        $Editing->editOMS($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/oms')->withStatus(302);
});

$app->get('/edit-old-oms', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('edit-oldOMS.twig', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('edit-oldOMS.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'policy_oldoms' => $session->getData('policy_oldoms'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('edit-oldOMS.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'policy_oldoms' => $UserWithdrawal->policy_oldoms_user(),
            'users' => $UserWithdrawal->users(),
            'message' => $session->flush('message'),
            'form' => $session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/edit-old-oms-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($session, $Editing){

    $params = (array) $request->getParsedBody();
    try {
        $Editing->editOldOMS($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/edit-old-oms')->withStatus(302);
    }
    return $response->withHeader('Location', '/old-oms')->withStatus(302);
});

$app->get('/search-for-users', function (ServerRequestInterface $request, ResponseInterface $response) use($twig, $session, $UserWithdrawal) {
    if ($session->getData('user') === NULL) {
        $body = $twig->render('/', [
            'user' => $session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    } elseif ($session->getData('user')  ['role'] === 'user') {
        $body = $twig->render('/', [

        ]);
        $response->getBody()->write($body);
        return $response;
    } else {
        $body = $twig->render('search-for-users.twig', [
            'user' => $session->getData('user'),
            'birth_certificates' => $session->getData('birth_certificates'),
            'message' => $session->flush('message'),
            'form' => $session->flush('form'),
            'search_users' => $session->flush('search_users')
        ]);
        $response->getBody()->write($body);
        return $response;
    }
});

$app->post('/search-for-users-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($SearchUsers, $session){
    $params = (array) $request->getParsedBody();
    try {
        $SearchUsers->searchUsers($params);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/search-for-users')->withStatus(302);
    }
    return $response->withHeader('Location', '/search-for-users')->withStatus(302);
});

$app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response) use ($session){

    $session->setData('user', null);
    $session->setData('birth_certificates', null);
    $session->setData('passport', null);
    $session->setData('individual_account_numbers', null);
    $session->setData('policy_oms', null);
    $session->setData('policy_oldoms', null);
    $session->setData('international_passport', null);
    $session->setData('military', null);

    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->run();
