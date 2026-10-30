<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/HDGModule.php';
class Zufuehrung extends HDGModule
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
        $this->loadDatapoints('zufuehrung', 'Standard');
        parent::ApplyChanges();
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $JSONData = json_decode($JSONString, true);
        if (array_key_exists('Data', $JSONData)) {
            $data = json_decode($JSONData['Data'], true);

            foreach ($data as $value) {
                $id = (string) $value['id'];
                switch ($id) {
                    case '21006':
                    case '21007':
                        $parts = explode(' ', $value['text']);
                        $this->SetValue($id, $parts[0]);
                        break;
                    case '21008': //Letzte Füllung
                        $parts = explode(' ', $value['text']);
                        //Datum letzter Füllung setzen
                        $this->SetValue('21008Datum', $parts[0]);
                        $menge = substr($parts[1], 0, -2); //kg entfernen
                        $this->SetValue($id, $menge);
                        break;
                    case '21005':
                        $this->SendDebug('Gesamvebrauch', $value['text'], 0);
                        $menge = explode(' ', $value['text']);
                        $this->SetValue($id, (float) $menge[0] / 100);
                        break;
                    case '1411':
                    case '1412':
                    case '1413':
                    case '1414':
                    case '1415':
                    case '1416':
                    case '1417':
                    case '1418':
                        $this->SetValue($id, $value['text'] != 'Aus');
                        break;
                    default:
                        $this->SetValue($id, $value['text']);
                        break;
                }
            }
        }
        return '';
    }
}
