<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

$ip = $_POST['ip'] ?? null;

if (!$ip) {
    die(json_encode(['status' => 'erro', 'mensagem' => 'IP não fornecido.']));
}

$urlPdv = "http://{$ip}:9898/normal.html";
$host = 'http://localhost:4444'; 
$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);

try {
    $driver->get($urlPdv);

    // 1. Limpeza inicial
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
    sleep(1); 

    // 2. Aciona a Função: 112 + TAB (como você corrigiu)
    $driver->getKeyboard()->sendKeys("112");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
    sleep(1); // Aguarda o sistema abrir a tela de login fiscal

    // 3. Login Fiscal: Crachá + ENTER
    $driver->getKeyboard()->sendKeys("SEU_CRACHA_AQUI");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);
    
    sleep(1); // Aguarda o campo de senha ser liberado

    // 4. Senha Fiscal: Senha + ENTER
    $driver->getKeyboard()->sendKeys("SUA_SENHA_AQUI");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);

    echo json_encode(['status' => 'sucesso', 'mensagem' => "PDV $ip: Login fiscal realizado com 112+TAB."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => "Erro no PDV $ip: " . $e->getMessage()]);
} finally {
    $driver->quit();
}