<?php

declare(strict_types=1);

class SonosConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{B82CE443-9F35-81F0-9D04-B6323D558CCA}');

        $this->RegisterPropertyString('HouseholdID', '');
    }

    private function searchPlayerDevice($playerID)
    {
        $ids = IPS_GetInstanceListByModuleID('{53915378-C64C-4566-8369-618B6ECDB5B4}');
        foreach ($ids as $id) {
            if (IPS_GetProperty($id, 'PlayerID') == $playerID) {
                return $id;
            }
        }

        return 0;
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        if ($this->HasActiveParent()) {
            
            $groups = json_decode($this->SendDataToParent(json_encode([
                'DataID'   => '{1E587107-664D-BA29-59E0-D9167875BE7E}',
                'Endpoint' => '/v1/households/' . $this->ReadPropertyString('HouseholdID') . '/groups:1',
                'Payload'  => ''
            ])));

            foreach ($groups->players as $player) {
                $data->actions[0]->values[] = [
                    'address'    => $player->id,
                    'name'       => $player->name,
                    'instanceID' => $this->searchPlayerDevice($player->id),
                    'create'     => [
                        'moduleID'      => '{53915378-C64C-4566-8369-618B6ECDB5B4}',
                        'configuration' => [
                            'HouseholdID' => $this->ReadPropertyString('HouseholdID'),
                            'PlayerID'    => $player->id
                        ]
                    ]
                ];
            }

        }

        return json_encode($data);
    }
}
