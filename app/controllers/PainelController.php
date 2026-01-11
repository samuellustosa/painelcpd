<?php
namespace App\Controllers;

use App\Models\Terminal;

class PainelController {


    private $cracha_fiscal = "427605"; 
    private $senha_fiscal  = "1212";

    public function acao($id_caixa, $ip, $arquitetura, $tipo_comando) {
        $terminal = new Terminal();
        
        // Define usuário e senha conforme arquitetura
        if ($arquitetura == 'x86') {
            $user = 'root'; 
            $pass = '1'; 
        } else {
            $user = 'suporte'; 
            $pass = $terminal->gerarSenhaDinamica($id_caixa); // 
        }

        // Mapeia o comando
        $comando = $this->getComandoLinux($arquitetura, $tipo_comando);

        return $terminal->enviarComando($ip, $user, $pass, $comando);
    }
   


    private function getComandoLinux($arq, $acao) {
        $comandos = [
            'x86' => [
                'reiniciar_app' => 'it-restart-application.sh', 
                'reboot'        => 'reboot',                   
                'desligar'      => 'poweroff'                  
            ],
            'x64' => [
                'reiniciar_app' => 'systemctl restart webpdv', 
                'reboot'        => 'sudo init 6',              
                'desligar'      => 'sudo shutdown -h now'     
            ]
        ];
        return $comandos[$arq][$acao];
    }



    public function fecharFiscalAutomático($ip) {
        // Aqui entrará a lógica com o php-webdriver
        // 1. Acessar http://[IP]:9898/normal.html
        // 2. Enviar ESC, esperar 1s, digitar 112 + TAB
        // 3. Inserir $this->cracha_fiscal e $this->senha_fiscal
        // 4. Pressionar Enter
        return "Processando fechamento no IP: $ip";
    }
}