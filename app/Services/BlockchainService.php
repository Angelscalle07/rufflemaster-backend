<?php

namespace App\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class BlockchainService
{
    protected $web3;
    protected $contract;

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('http://127.0.0.1:8545', 10)));

        $abi = file_get_contents(base_path('blockchain/abi/RuffleTicket.json'));
        $abi = json_decode($abi, true);

        $this->contract = new Contract($this->web3->provider, $abi);
        $this->contract->at(env('CONTRACT_ADDRESS')); // pon la dirección de despliegue local
    }

    public function mintTicket($to, $rifaId)
    {
        $from = env('WALLET_PUBLIC'); // la cuenta 0 del Hardhat node
        $privateKey = env('WALLET_PRIVATE'); // la clave privada correspondiente

        $txHash = null;
        $this->contract->send('mintTicket', $to, (int)$rifaId, [
            'from' => $from,
            'gas' => '0x2DC6C0',
        ], function ($err, $tx) use (&$txHash) {
            if ($err !== null) {
                throw new \Exception($err->getMessage());
            }
            $txHash = $tx;
        });

        // por ahora usamos el rifaId como tokenId
        return [
            'tx' => $txHash,
            'tokenId' => $rifaId
        ];
    }
}







