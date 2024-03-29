<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/HDGModule.php';
    class Heizkessel extends HDGModule
    {
        public static $Variables = [];

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyString('Modell', '-');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
            $datapoints = json_decode(file_get_contents(__DIR__ . '/../libs/datapoints.json'), true);

            foreach ($datapoints['heizkessel'] as $key => $heizkessel) {
                if ($heizkessel['typeName'] == $this->ReadPropertyString('Modell')) {
                    foreach ($heizkessel['nodes'] as $keyNode => $node) {
                        $Variable = [$node['ident'], $node['name'], $node['type'], $node['profile'], $node['dataid'], $node['action'], $node['keep']];
                        array_push(self::$Variables, $Variable);
                    }
                }
            }

            parent::ApplyChanges();
        }

        public function ReceiveData($JSONString)
        {
            $this->SendDebug('JSON', $JSONString, 0);
            $JSONData = json_decode($JSONString, true);
            if (array_key_exists('Data', $JSONData)) {
                $data = json_decode($JSONData['Data'], true);

                foreach ($data as $key => $value) {
                    switch ($value['id']) {
                    case 22069:
                        $mwh = substr($value['text'], 0, -3); //MWh entfernen
                        $this->SendDebug('mwh', $mwh, 0);
                        $this->SetValue($value['id'], floatval($mwh) * 1000);
                        break;
                    default:
                    $this->SetValue($value['id'], $value['text']);
                        break;
                }
                }
            }
        }
    }