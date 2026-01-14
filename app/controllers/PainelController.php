<?php
namespace App\Controllers;

use App\Models\Terminal;

class PainelController {



    public function acao($id_caixa, $ip, $arquitetura, $tipo_comando) {
        $terminal = new Terminal();
        
        // Define usuário e senha conforme arquitetura
        if ($arquitetura == 'x86') {
            $user = 'root'; 
            $pass = '1'; 
        } else {
            $user = 'suporte'; 
            $pass = $terminal->gerarSenhaDinamica($id_caixa);
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

}