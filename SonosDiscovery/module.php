<?php

declare(strict_types=1);

include_once __DIR__ . '/../libs/data.php';

class SonosDiscovery extends IPSModule
{
    use DataHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{B82CE443-9F35-81F0-9D04-B6323D558CCA}');
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        if ($this->HasActiveParent()) {
            $result = $this->getData('/v1/households');

            foreach ($result->households as $household) {
                $groups = $this->getData('/v1/households/' . $household->id . '/groups');

                $names = [];
                foreach ($groups->players as $player) {
                    $names[] = $player->name;
                }

                $data->actions[0]->values[] = [
                    'address'    => $household->id,
                    'name'       => 'Sonos (' . implode(', ', $names) . ')',
                    'instanceID' => $this->searchHouseholdConfigurator($household->id),
                    'create'     => [
                        'moduleID'      => '{751A1E6A-76D5-4EF1-B15D-D7A0CECC75D0}',
                        'configuration' => [
                            'HouseholdID' => $household->id
                        ]
                    ]
                ];
            }
        }

        return json_encode($data);
    }

    private function searchHouseholdConfigurator($householdID)
    {
        $ids = IPS_GetInstanceListByModuleID('{751A1E6A-76D5-4EF1-B15D-D7A0CECC75D0}');
        foreach ($ids as $id) {
            if (IPS_GetProperty($id, 'HouseholdID') == $householdID) {
                return $id;
            }
        }

        return 0;
    }
}
