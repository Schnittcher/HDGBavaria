<?php

declare(strict_types=1);

class HDGModule extends IPSModuleStrict
{
    private const PRESENTATION_VALUE = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

    public function Create(): void
    {
        parent::Create();
        $this->ConnectParent('{C164EB47-32B6-C0DA-71E0-E5569EA8D842}');

        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Node'         => $Variable[4],
                'Action'       => $Variable[5],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[6]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $NewRows = static::$Variables;
        $NewPos = 0;
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            $VariableActive = (bool) $Variable['Keep'];
            $Type = constant($Variable['VarType']);

            $this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Type, $this->getPresentation((string) $Variable['Profile'], $Type), (int) $Variable['Pos'], $VariableActive);
            if ($Variable['Action'] && $VariableActive) {
                $this->EnableAction($Variable['Ident']);
            }
            foreach ($NewRows as $Index => $Row) {
                if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                    unset($NewRows[$Index]);
                }
            }
            if ($NewPos < $Variable['Pos']) {
                $NewPos = $Variable['Pos'];
            }
        }

        if (count($NewRows) != 0) {
            foreach ($NewRows as $NewVariable) {
                $Variables[] = [
                    'Ident'        => str_replace(' ', '', $NewVariable[0]),
                    'Name'         => $this->Translate($NewVariable[1]),
                    'VarType'      => $NewVariable[2],
                    'Profile'      => $NewVariable[3],
                    'Node'         => $NewVariable[4],
                    'Action'       => $NewVariable[5],
                    'Pos'          => ++$NewPos,
                    'Keep'         => $NewVariable[6]
                ];
            }
            IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
            IPS_ApplyChanges($this->InstanceID);
        }
    }

    public function resetVariables(): void
    {
        $NewRows = static::$Variables;
        $Variables = [];
        foreach ($NewRows as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Node'         => $Variable[4],
                'Action'       => $Variable[5],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[6]
            ];
        }
        IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
        IPS_ApplyChanges($this->InstanceID);
    }

    protected function SetValue(string $Ident, mixed $Value): bool
    {
        if (@$this->GetIDForIdent($Ident)) {
            $this->SendDebug('SetValue :: ' . $Ident, $Value, 0);
            return parent::SetValue($Ident, $Value);
        }
        return false;
    }

    /**
     * Lädt die Datenpunkte einer Gruppe aus datapoints.json in die Variablenliste der Instanz.
     */
    protected function loadDatapoints(string $Group, string $TypeName): void
    {
        static::$Variables = [];
        $datapoints = json_decode(file_get_contents(__DIR__ . '/datapoints.json'), true);
        foreach ($datapoints[$Group] as $entry) {
            if ($entry['typeName'] == $TypeName) {
                foreach ($entry['nodes'] as $node) {
                    static::$Variables[] = [$node['ident'], $node['name'], $node['type'], $node['profile'], $node['dataid'], $node['action'], $node['keep']];
                }
            }
        }
    }

    /**
     * Bildet das bisherige Profil des Datenpunkts auf eine native Darstellung (Wertanzeige) ab.
     * Alle Datenpunkte sind schreibgeschützt.
     */
    private function getPresentation(string $Profile, int $Type): array
    {
        $Presentation = ['PRESENTATION' => self::PRESENTATION_VALUE];
        switch ($Profile) {
            case '~Temperature':
                return $Presentation + ['SUFFIX' => ' °C', 'DIGITS' => 1];
            case '~Electricity':
                return $Presentation + ['SUFFIX' => ' kWh', 'DIGITS' => 2];
            case '~Intensity.100':
                return $Presentation + ['SUFFIX' => ' %', 'DIGITS' => 0];
            case 'HDG.KG':
                return $Presentation + ['SUFFIX' => ' kg'];
            case 'HDG.Tonne':
                return $Presentation + ['SUFFIX' => ' t', 'DIGITS' => 2];
            case '~Switch':
                return $Presentation + ['OPTIONS' => json_encode([
                    ['Value' => false, 'Caption' => $this->Translate('Off'), 'IconActive' => false, 'IconValue' => '', 'ColorActive' => true, 'ColorValue' => 16077123, 'ContentColorActive' => false],
                    ['Value' => true, 'Caption' => $this->Translate('On'), 'IconActive' => false, 'IconValue' => '', 'ColorActive' => true, 'ColorValue' => 1692672, 'ContentColorActive' => false]
                ])];
        }
        if ($Type == VARIABLETYPE_FLOAT) {
            return $Presentation + ['DIGITS' => 2];
        }
        return $Presentation;
    }
}
