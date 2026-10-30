<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/HDGModule.php';
class Heizkessel extends HDGModule
{
    public static $Variables = [];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Modell', '-');
    }

    public function Destroy(): void
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        $this->loadDatapoints('heizkessel', $this->ReadPropertyString('Modell'));
        parent::ApplyChanges();
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $JSONData = json_decode($JSONString, true);
        if (array_key_exists('Data', $JSONData)) {
            $data = json_decode($JSONData['Data'], true);

            foreach ($data as $value) {
                switch ($value['id']) {
                    case 22069:
                        $mwh = substr($value['text'], 0, -3); //MWh entfernen
                        $this->SendDebug('mwh', $mwh, 0);
                        $this->SetValue((string) $value['id'], floatval($mwh) * 1000);
                        break;
                    default:
                        $this->SetValue((string) $value['id'], $value['text']);
                        break;
                }
            }
        }
        return '';
    }
}
