<?php

declare(strict_types=1);

include_once __DIR__ . '/../libs/profile.php';
include_once __DIR__ . '/../libs/data.php';

class SonosPlayer extends IPSModule
{
    use ProfileHelper;
    use DataHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{B82CE443-9F35-81F0-9D04-B6323D558CCA}');

        $this->RegisterPropertyString('HouseholdID', '');
        $this->RegisterPropertyString('PlayerID', '');

        $this->RegisterAttributeString('GroupID', '');

        //Create profiles
        $this->RegisterProfileIntegerEx('Control.SONOS', 'Information', '', '', [
            [0, 'Prev',  '', -1],
            [1, 'Play',  '', -1],
            [2, 'Pause', '', -1],
            //[3, "Stop",  "", -1],
            [4, 'Next',  '', -1]
        ]);

        //Create variables
        $this->RegisterVariableInteger('Control', 'Control', 'Control.SONOS');
        $this->EnableAction('Control');

        $this->RegisterVariableInteger('Volume', 'Volume', 'Intensity.100');
        $this->EnableAction('Volume');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Control':
                switch ($Value) {
                    case 0:
                        $this->SkipToPreviousTrack();
                        break;
                    case 1:
                        $this->Play();
                        break;
                    case 2:
                        $this->Pause();
                        break;
                    //case 3:
                    //    $this->Stop();
                    //    break;
                    case 4:
                        $this->SkipToNextTrack();
                        break;
                }
                break;
            case 'Volume':
                $this->SetVolume(intval($Value));
                break;
        }
    }

    private function updateGroupID()
    {
        $groups = $this->getData('/v1/households/' . $this->ReadPropertyString('HouseholdID') . '/groups');

        foreach ($groups->groups as $group) {
            foreach ($group->playerIds as $player) {
                if ($player == $this->ReadPropertyString('PlayerID')) {
                    $this->WriteAttributeString('GroupID', $group->id);
                    return;
                }
            }
        }

        throw new Exception('Cannot update GroupID. Player cannot be found in any group.');
    }

    private function Play()
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/play');

        $this->SetValue('Control', 1);
    }

    private function Pause()
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/pause');

        $this->SetValue('Control', 2);
    }

    //private function Stop()
    //{
    //    //there is no dedicated stop command. just treat it like pause
    //    $this->Pause();
    //}

    private function SkipToNextTrack()
    {
        if ($this->GetValue('Control') != 1) {
            echo 'Skipping is only available while playing';
            return;
        }

        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/skipToNextTrack');
    }

    private function SkipToPreviousTrack()
    {
        if ($this->GetValue('Control') != 1) {
            echo 'Skipping is only available while playing';
            return;
        }

        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/skipToPreviousTrack');
    }

    private function SetVolume($Volume)
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $result = $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/groupVolume', json_encode(
            [
                'volume' => $Volume
            ]
        ));

        $this->SetValue('Volume', $Volume);
    }
    
    public function PlayClip()
    {

        $result = $this->postData('/v1/players/' . $this->ReadPropertyString('PlayerID') . '/audioClip', json_encode(
            [
                'name'      => 'Test',
                'appId'     => 'de.symcon.app',
                'streamUrl' => 'http://www.moviesoundclips.net/effects/animals/wolf-howls.mp3',
                'clipType'  => 'CUSTOM'
            ]
        ));

    }
    
}
