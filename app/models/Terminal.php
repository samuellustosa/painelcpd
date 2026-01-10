<?php
namespace App\Models;

use phpseclib3\Net\SSH2;

class Terminal {
    // Regra de Negócio: Senha Dinâmica x64 
    public function gerarSenhaDinamica($id_pdv) {
        $dia = date('d');
        $mes = date('m');
        
        // Concatenação: Dia + Mês
        $concatenacao = (int)($dia . $mes); 
        // Soma: Concatenação + ID_PDV 
        $soma = $concatenacao + (int)$id_pdv; 
        
        return "pdv@" . $soma; // Senha Final
    }

    // Execução de Comandos via SSH (Porta 22)
    public function enviarComando($ip, $user, $pass, $comando) {
        $ssh = new SSH2($ip, 22);
        if (!$ssh->login($user, $pass)) {
            return "Falha na autenticação";
        }
        return $ssh->exec($comando);
    }

    // Status visual via ICMP (Ping) [cite: 5, 23]
    public function verificarStatus($ip) {
        $ping = exec("ping -c 1 -W 1 " . escapeshellarg($ip), $outcome, $status);
        return ($status === 0) ? 'online' : 'offline';
    }
}