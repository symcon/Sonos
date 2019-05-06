<?php

declare(strict_types=1);
    class SonosPlayer extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            //Connect to available splitter or create a new one
            $this->ConnectParent('{B82CE443-9F35-81F0-9D04-B6323D558CCA}');

            $this->RegisterPropertyString('HouseholdID', '');
            $this->RegisterPropertyString('PlayerID', '');
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }
    }
