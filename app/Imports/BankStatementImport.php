<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;

class BankStatementImport implements ToArray
{
    use Importable;

    public function array(array $array): void
    {
        //
    }
}