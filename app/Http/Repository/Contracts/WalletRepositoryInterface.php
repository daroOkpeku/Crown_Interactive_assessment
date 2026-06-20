<?php


namespace App\Http\Repository\Contracts;

interface WalletRepositoryInterface
{
    public function  fundwallet($validated);
    public function wallettransfer($validated);
}
