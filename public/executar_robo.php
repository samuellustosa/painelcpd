<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;

// Recebe dados do formulário ou usa os padrões do PainelController
$ip = $_POST['ip'] ?? null;
$cracha = $_POST['cracha'] ?? "427605";
$senha  = $_POST['senha'] ?? "1212";

if (!$ip) {
    die(json_encode(['status' => 'erro', 'mensagem' => 'IP não fornecido.']));
}

$urlPdv = "http://{$ip}:9898/normal.html";
$host = 'http://localhost:4444'; 
$capabilities = DesiredCapabilities::chrome();

$options = new ChromeOptions();
$options->addArguments(['--headless', '--no-sandbox', '--disable-dev-shm-usage']); 
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

try {
    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->get($urlPdv);

    // Lendo a variável situacao: 3 = Vendendo, 4 = Recebendo
    $situacao = $driver->executeScript("return vue.data.situacao;");

    if ($situacao == 3 || $situacao == 4) {
        $driver->quit();
        die(json_encode([
            'status' => 'pulado', 
            'mensagem' => "PDV $ip ignorado: Existe uma venda em andamento (Status: $situacao)."
        ]));
    }

    
    // Limpa qualquer tela aberta com ESC
    $driver->executeScript("pdv.putCmd('{ESC}');");
    sleep(3); 

    $driver->executeScript("pdv.putCmd('{ESC}');");
    sleep(3); 

    // Limpa qualquer tela aberta com ESC
    $driver->executeScript("pdv.putCmd('{ESC}');");
    sleep(4); 


    // Envia 112 + TAB (O comando de TAB  é {99991})
    $driver->executeScript("pdv.putCmd('112{99991}');");
    sleep(2); 

    // Envia o Crachá + ENTER
    $driver->executeScript("pdv.putCmd('{$cracha}{ENTER}');");
    sleep(2); 

    // Envia a Senha + ENTER
    $driver->executeScript("pdv.putCmd('{$senha}{ENTER}');");

    echo json_encode(['status' => 'sucesso', 'mensagem' => "PDV $ip: Comando enviado."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => "Erro no PDV $ip: " . $e->getMessage()]);
} finally {
    if (isset($driver)) {
        $driver->quit();
    }
}