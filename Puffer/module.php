<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/HDGModule.php';
class Puffer extends HDGModule
{
    public static $Variables = [];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
    }

    public function Destroy(): void
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        //So lange es keine andere Auswahl gibt fest hinterlegt
        $this->loadDatapoints('puffer', '3Temp');
        parent::ApplyChanges();
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $JSONData = json_decode($JSONString, true);
        if (array_key_exists('Data', $JSONData)) {
            $data = json_decode($JSONData['Data'], true);
            foreach ($data as $value) {
                $this->SetValue((string) $value['id'], $value['text']);
            }
        }
        return '';
    }
}
