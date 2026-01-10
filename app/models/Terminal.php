<?php
namespace App\Models;

use phpseclib3\Net\SSH2;

class Terminal {
    // Regra de Negócio: Senha Dinâmica x64 
    public function gerarSenhaDinamica($id_pdv) {
        $dia = (int) date('d'); 
        $mes = (int) date('m'); 

        // Concatena dia + mês sem zeros à esquerda
        $numero = $dia . $mes; 

        // Soma com o PDV
        $somaFinal = (int)$numero + (int)$id_pdv;

        return "pdv@" . $somaFinal;
    }

    // Execução de Comandos via SSH (Porta 22)
    public function enviarComando($ip, $user, $pass, $comando) {
        $ssh = new SSH2($ip, 22);
        if (!$ssh->login($user, $pass)) {
            return "Falha na autenticação";
        }
        return $ssh->exec($comando);
    }

    // Status visual via ICMP (Ping)
    public function verificarStatus($ip) {

    $ping = exec("ping -n 1 -w 1000 " . escapeshellarg($ip), $outcome, $status);
    return ($status === 0) ? 'online' : 'offline';
}
}