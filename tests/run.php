<?php

declare(strict_types=1);

use App\Http\Requests\OpenCashRegisterRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\CashRegister;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = 'true';
$_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = ':memory:';
$_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'array';
$_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = 'array';
$_ENV['QUEUE_CONNECTION'] = $_SERVER['QUEUE_CONNECTION'] = 'sync';
$_ENV['MAIL_MAILER'] = $_SERVER['MAIL_MAILER'] = 'array';

putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_STORE=array');
putenv('QUEUE_CONNECTION=sync');
putenv('MAIL_MAILER=array');

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

config([
    'app.env' => 'testing',
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
    'session.driver' => 'array',
    'cache.default' => 'array',
    'queue.default' => 'sync',
]);

DB::purge('sqlite');
DB::reconnect('sqlite');

final class AssertionFailed extends RuntimeException {}

function assertTrue(mixed $condition, string $message): void
{
    if (! $condition) {
        throw new AssertionFailed($message);
    }
}

function assertFalse(mixed $condition, string $message): void
{
    assertTrue(! $condition, $message);
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new AssertionFailed($message.' Esperado: '.var_export($expected, true).'. Obtido: '.var_export($actual, true).'.');
    }
}

function assertContainsText(string $needle, string $haystack, string $message): void
{
    if (! str_contains($haystack, $needle)) {
        throw new AssertionFailed($message." Texto nao encontrado: {$needle}.");
    }
}

function assertNotContainsText(string $needle, string $haystack, string $message): void
{
    if (str_contains($haystack, $needle)) {
        throw new AssertionFailed($message." Texto inesperado encontrado: {$needle}.");
    }
}

function assertDatabaseHas(string $table, array $where, string $message): void
{
    assertTrue(DB::table($table)->where($where)->exists(), $message);
}

function refreshDatabase(): void
{
    Schema::dropIfExists('transactions');
    Schema::dropIfExists('cash_registers');
    Schema::dropIfExists('users');
    Schema::dropIfExists('categories');

    Schema::create('categories', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('role')->default('operador');
        $table->string('status')->default('ativo');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('cash_registers', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('status')->default('aberto');
        $table->decimal('opening_balance', 10, 2)->default(0);
        $table->decimal('closing_balance', 10, 2)->nullable();
        $table->timestamp('opened_at')->nullable();
        $table->timestamp('closed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('transactions', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
        $table->string('type');
        $table->decimal('amount', 10, 2);
        $table->string('description');
        $table->string('payment_method', 50)->nullable();
        $table->string('bank_name', 255)->nullable();
        $table->string('bank_account', 100)->nullable();
        $table->string('receipt_path', 500)->nullable();
        $table->date('competencia_date')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

function createUser(string $email, string $role = 'admin', string $status = 'ativo'): User
{
    return User::create([
        'name' => ucfirst(strstr($email, '@', true)),
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => $status,
    ]);
}

final class TestClient
{
    private array $cookies = [];

    public function request(string $method, string $uri, array $parameters = []): Response
    {
        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $this->cookies,
            [],
            ['HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']
        );

        $kernel = app(HttpKernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->isCleared()) {
                unset($this->cookies[$cookie->getName()]);

                continue;
            }

            $this->cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $response;
    }
}

function login(TestClient $client, string $email = 'admin@example.com'): void
{
    createUser($email);

    $response = $client->request('POST', '/login', [
        'email' => $email,
        'password' => 'password',
    ]);

    assertSameValue(302, $response->getStatusCode(), 'Login valido deve redirecionar.');
    assertContainsText('/dashboard', $response->headers->get('Location', ''), 'Login valido deve ir para o dashboard.');
}

function streamedContent(Response $response): string
{
    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

$tests = [
    'unit: User identifica perfil admin e conta bloqueada' => function (): void {
        $admin = new User(['role' => 'admin', 'status' => 'ativo']);
        $blockedOperator = new User(['role' => 'operador', 'status' => 'bloqueado']);

        assertTrue($admin->isAdmin(), 'Usuario admin deve ser reconhecido como admin.');
        assertFalse($admin->isBlocked(), 'Usuario ativo nao deve ser reconhecido como bloqueado.');
        assertFalse($blockedOperator->isAdmin(), 'Operador nao deve ser reconhecido como admin.');
        assertTrue($blockedOperator->isBlocked(), 'Usuario bloqueado deve ser reconhecido como bloqueado.');
    },

    'unit: regras de validacao de abertura de caixa' => function (): void {
        $rules = (new OpenCashRegisterRequest)->rules();

        assertSameValue(['required', 'numeric', 'min:0'], $rules['opening_balance'], 'Saldo inicial deve ser obrigatorio, numerico e nao negativo.');
    },

    'unit: regras de validacao de lancamento financeiro' => function (): void {
        $rules = (new StoreTransactionRequest)->rules();

        assertSameValue(['required', 'in:entrada,saida'], $rules['type'], 'Tipo deve aceitar somente entrada ou saida.');
        assertSameValue(['required', 'numeric', 'min:0.01'], $rules['amount'], 'Valor do lancamento deve ser positivo.');
        assertSameValue(['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], $rules['receipt'], 'Comprovante deve aceitar apenas imagens ou PDF ate 10 MB.');
    },

    'integration: login bloqueia usuario inativo' => function (): void {
        createUser('bloqueado@example.com', 'operador', 'bloqueado');
        $client = new TestClient;

        $response = $client->request('POST', '/login', [
            'email' => 'bloqueado@example.com',
            'password' => 'password',
        ]);

        assertSameValue(302, $response->getStatusCode(), 'Tentativa de login bloqueado deve redirecionar de volta.');
        $errors = $response->getSession()?->get('errors');

        assertTrue($errors?->has('email'), 'Login bloqueado deve registrar erro no campo de e-mail.');
        assertContainsText('bloqueada', $errors->first('email'), 'Mensagem deve informar que a conta esta bloqueada.');
    },

    'integration: admin abre caixa e duplicidade e impedida' => function (): void {
        $client = new TestClient;
        login($client);

        $response = $client->request('POST', '/caixa/abrir', ['opening_balance' => '150.00']);

        assertSameValue(302, $response->getStatusCode(), 'Abertura de caixa deve redirecionar.');
        assertSameValue(1, CashRegister::count(), 'Abertura deve criar um unico caixa.');
        assertDatabaseHas('cash_registers', [
            'status' => 'aberto',
            'opening_balance' => 150.00,
        ], 'Caixa aberto deve ser persistido com saldo inicial.');

        $secondResponse = $client->request('POST', '/caixa/abrir', ['opening_balance' => '200.00']);

        assertSameValue(302, $secondResponse->getStatusCode(), 'Tentativa duplicada deve redirecionar com erro.');
        assertSameValue(1, CashRegister::count(), 'Sistema nao deve permitir mais de um caixa aberto.');
    },

    'integration: lancamentos alteram saldo e fechamento consolida caixa' => function (): void {
        $client = new TestClient;
        login($client);
        $client->request('POST', '/caixa/abrir', ['opening_balance' => '100.00']);

        $entrada = $client->request('POST', '/lancamentos', [
            'type' => 'entrada',
            'amount' => '250.00',
            'description' => 'Doacao JP II',
            'payment_method' => 'Doacoes',
            'competencia_date' => '2026-05-01',
            'notes' => 'Recebimento confirmado',
        ]);
        $saida = $client->request('POST', '/lancamentos', [
            'type' => 'saida',
            'amount' => '40.00',
            'description' => 'Compra de materiais',
            'payment_method' => 'Materiais',
            'competencia_date' => '2026-05-02',
            'notes' => 'Despesa operacional',
        ]);

        assertSameValue(302, $entrada->getStatusCode(), 'Registro de entrada deve redirecionar.');
        assertSameValue(302, $saida->getStatusCode(), 'Registro de saida deve redirecionar.');
        assertSameValue(2, Transaction::count(), 'Duas movimentacoes devem ser registradas.');
        assertDatabaseHas('transactions', [
            'type' => 'entrada',
            'amount' => 250.00,
            'description' => 'Doacao JP II',
            'payment_method' => 'Doacoes',
        ], 'Entrada deve ser gravada com categoria e descricao.');

        $close = $client->request('POST', '/caixa/fechar');
        $cashRegister = CashRegister::firstOrFail();

        assertSameValue(302, $close->getStatusCode(), 'Fechamento deve redirecionar.');
        assertSameValue('fechado', $cashRegister->status, 'Caixa deve ficar fechado apos fechamento.');
        assertSameValue('310.00', (string) $cashRegister->closing_balance, 'Saldo final deve considerar abertura + entradas - saidas.');
    },

    'integration: exportacao CSV respeita filtros' => function (): void {
        $client = new TestClient;
        login($client);
        $client->request('POST', '/caixa/abrir', ['opening_balance' => '0']);
        $register = CashRegister::firstOrFail();

        Transaction::create([
            'cash_register_id' => $register->id,
            'type' => 'entrada',
            'amount' => 90,
            'description' => 'Doacao JP II',
            'payment_method' => 'Doacoes',
        ]);
        Transaction::create([
            'cash_register_id' => $register->id,
            'type' => 'saida',
            'amount' => 25,
            'description' => 'Compra de materiais',
            'payment_method' => 'Materiais',
        ]);

        $response = $client->request('GET', '/extrato/exportar-csv?type=entrada&search=Doacao');
        $content = streamedContent($response);

        assertSameValue(200, $response->getStatusCode(), 'CSV filtrado deve responder com sucesso.');
        assertContainsText('Doacao JP II', $content, 'CSV deve incluir entrada filtrada.');
        assertNotContainsText('Compra de materiais', $content, 'CSV nao deve incluir saida fora do filtro.');
    },

    'integration: admin cria usuario e operador nao acessa gestao' => function (): void {
        $client = new TestClient;
        login($client);

        $response = $client->request('POST', '/usuarios', [
            'name' => 'Operador Teste',
            'email' => 'operador@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'operador',
            'status' => 'ativo',
        ]);

        assertSameValue(302, $response->getStatusCode(), 'Criacao de usuario deve redirecionar.');
        assertDatabaseHas('users', [
            'email' => 'operador@example.com',
            'role' => 'operador',
            'status' => 'ativo',
        ], 'Usuario operador deve ser persistido.');

        refreshDatabase();
        $operatorClient = new TestClient;
        createUser('operador@example.com', 'operador');
        $operatorClient->request('POST', '/login', [
            'email' => 'operador@example.com',
            'password' => 'password',
        ]);

        $forbidden = $operatorClient->request('GET', '/usuarios');

        assertSameValue(403, $forbidden->getStatusCode(), 'Operador nao deve acessar gestao de usuarios.');
    },
];

$failed = 0;

foreach ($tests as $name => $test) {
    refreshDatabase();

    try {
        $test();
        echo "PASS {$name}".PHP_EOL;
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL {$name}".PHP_EOL;
        echo '  '.$exception->getMessage().PHP_EOL;
    }
}

echo PHP_EOL.count($tests).' testes executados, '.$failed.' falha(s).'.PHP_EOL;

exit($failed > 0 ? 1 : 0);
