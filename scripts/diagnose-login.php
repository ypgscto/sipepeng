<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'APP_ENV: '.config('app.env').PHP_EOL;
echo 'DB: '.config('database.default').' @ '.config('database.connections.mysql.host').':'.config('database.connections.mysql.port').'/'.config('database.connections.mysql.database').PHP_EOL;
echo 'fallback: '.(config('sipepeng_siakad_auth.allow_local_fallback') ? 'true' : 'false').PHP_EOL;
echo 'user_count: '.App\Models\User::count().PHP_EOL;

$email = 'superadmin@sipepeng.test';
$user = App\Models\User::where('email', $email)->first();
echo 'demo_user: '.($user ? "found (#{$user->id})" : 'MISSING').PHP_EOL;

if ($user) {
    echo 'password_ok: '.(Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'yes' : 'NO').PHP_EOL;
    echo 'allowed_login: '.($user->is_allowed_login ? 'yes' : 'no').PHP_EOL;
    echo 'roles: '.implode(',', $user->roleCodes()).PHP_EOL;
}

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'login' => $email,
    'password' => 'password',
]);
$request->headers->set('Accept', 'text/html');

/** @var Illuminate\Contracts\Http\Kernel $http */
$http = $app->make(Illuminate\Contracts\Http\Kernel::class);
$session = $app->make('session');
$session->start();
$request->setLaravelSession($session);
$token = $session->token();
$request->merge(['_token' => $token]);
$request->headers->set('X-CSRF-TOKEN', $token);

$response = $http->handle($request);
echo 'login_http_status: '.$response->getStatusCode().PHP_EOL;
echo 'redirect: '.$response->headers->get('Location', '(none)').PHP_EOL;

if ($response->isRedirect() && str_contains((string) $response->headers->get('Location'), 'dashboard')) {
    echo 'SIMULATED_LOGIN: SUCCESS'.PHP_EOL;
} else {
    echo 'SIMULATED_LOGIN: FAILED'.PHP_EOL;
    $content = $response->getContent();
    if (preg_match('/These credentials do not match/', $content)) {
        echo 'error: auth.failed'.PHP_EOL;
    }
}
