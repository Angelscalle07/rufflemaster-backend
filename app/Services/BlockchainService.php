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
    protected $from;

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('http://host.docker.internal:8545', 10)));

        $abi = file_get_contents(base_path('blockchain/abi/RuffleTicket.json'));
        $abi = json_decode($abi, true);

        $this->contract = new Contract($this->web3->provider, $abi);
        $this->contract->at(env('CONTRACT_ADDRESS')); // pon la dirección de despliegue local
        $this->from = env('DEPLOYER_ADDRESS'); // la cuenta 0 del Hardhat node
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
    public function burnTicket(int $tokenId): array
    {
    $tx = "0x" . bin2hex(random_bytes(16));

    return [
        'tx' => $tx,
        'tokenId' => $tokenId
    ];
    }
}







