<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\Chrome\ChromeOptions; // Importado para performance

$ip = $_POST['ip'] ?? null;

if (!$ip) {
    die(json_encode(['status' => 'erro', 'mensagem' => 'IP não fornecido.']));
}

$urlPdv = "http://{$ip}:9898/normal.html";
$host = 'http://localhost:4444'; 
$capabilities = DesiredCapabilities::chrome();

// Otimização de performance para o CPD
$options = new ChromeOptions();
$options->addArguments(['--headless']); // Roda sem abrir janela para ser mais rápido
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

try {
    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->get($urlPdv);

    // --- LÓGICA DE INTELIGÊNCIA ---
    // Captura todo o texto visível na tela do PDV
    $corpoPagina = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    $textoTela = strtoupper($corpoPagina); // Converte para maiúsculo para facilitar a busca

    // Lista de palavras que indicam que o caixa está ocupado
    $palavrasBloqueio = ['VENDA', 'ITEM', 'TOTAL', 'SUBTOTAL', 'CUPOM'];

    foreach ($palavrasBloqueio as $palavra) {
        if (strpos($textoTela, $palavra) !== false) {
            $driver->quit();
            die(json_encode([
                'status' => 'pulado', 
                'mensagem' => "PDV $ip pulado: O caixa parece estar em operação (Venda Detectada)."
            ]));
        }
    }
    // ------------------------------

    // Se passou pela verificação, inicia os comandos
    // 1. Limpeza inicial
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
    sleep(1); 

    // 2. Aciona a Função: 112 + TAB
    $driver->getKeyboard()->sendKeys("112");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
    sleep(1); 

    // 3. Login Fiscal: Crachá + ENTER
    // Lembre-se de colocar seus dados reais aqui amanhã na empresa
    $driver->getKeyboard()->sendKeys("SEU_CRACHA_AQUI");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);
    sleep(1); 

    // 4. Senha Fiscal: Senha + ENTER
    $driver->getKeyboard()->sendKeys("SUA_SENHA_AQUI");
    $driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);

    echo json_encode(['status' => 'sucesso', 'mensagem' => "PDV $ip: Fechamento iniciado com sucesso."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => "Erro no PDV $ip: " . $e->getMessage()]);
} finally {
    if (isset($driver)) {
        $driver->quit();
    }
}