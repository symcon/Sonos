<?php

declare(strict_types=1);
    class SonosGroup extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            //Connect to available splitter or create a new one
            $this->ConnectParent('{B82CE443-9F35-81F0-9D04-B6323D558CCA}');

            $this->RegisterPropertyString('HouseholdID', '');
            $this->RegisterPropertyString('GroupID', '');
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }

        public function Play()
        {
            $result = $this->SendDataToParent(json_encode([
                'DataID'   => '{1E587107-664D-BA29-59E0-D9167875BE7E}',
                'Endpoint' => '/v1/groups/' . $this->ReadPropertyString('GroupID') . '/playback/play',
                'Payload'  => '{}'
            ]));

            if ($result == '{}') {
                echo 'Playing! This very cool!';
            }
        }
    }
