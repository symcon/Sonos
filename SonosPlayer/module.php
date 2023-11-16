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
        $this->RegisterPropertyInteger('UpdateInterval', 10);

        $this->RegisterTimer('SONOS_UpdateStatus', 0, 'SONOS_UpdateStatus($_IPS[\'TARGET\']);');

        $this->RegisterAttributeString('GroupID', '');
        $this->RegisterAttributeString('Groups', '');

        //Create profiles
        $this->RegisterProfileIntegerEx('Control.SONOS', 'Information', '', '', [
            [0, 'Prev',  '', -1],
            [1, 'Play',  '', -1],
            [2, 'Pause', '', -1],
            //[3, "Stop",  "", -1],
            [4, 'Next',  '', -1]
        ]);

        //Create variables
        $this->RegisterVariableString('Service', $this->Translate('Service'), '', 1);
        $this->RegisterVariableString('Artist', $this->Translate('Artist'), '', 2);
        $this->RegisterVariableString('Track', $this->Translate('Track'), '', 3);
        $this->RegisterVariableString('Album', $this->Translate('Album'), '', 4);

        $this->RegisterVariableInteger('Control', $this->Translate('Control'), 'Control.SONOS', 5);
        $this->EnableAction('Control');

        $this->RegisterVariableInteger('Volume', $this->Translate('Volume'), 'Intensity.100', 6);
        $this->EnableAction('Volume');

        $this->RegisterVariableBoolean('Mute', $this->Translate('Mute'), '~Switch', 7);
        $this->EnableAction('Mute');

        $this->RegisterVariableInteger('GroupVolume', $this->Translate('Group Volume'), 'Intensity.100', 15);
        $this->EnableAction('GroupVolume');

        $this->RegisterVariableBoolean('GroupMute', $this->Translate('Group Mute'), '~Switch', 16);
        $this->EnableAction('GroupMute');

        //Media Image
        if (!@IPS_GetObjectIDByIdent('MediaImage', $this->InstanceID)) {
            $MediaID = IPS_CreateMedia(1);
            IPS_SetParent($MediaID, $this->InstanceID);
            IPS_SetIdent($MediaID, 'MediaImage');
            IPS_SetPosition($MediaID, 0);
            IPS_SetName($MediaID, $this->Translate('Cover'));
            $ImageFile = IPS_GetKernelDir() . 'media' . DIRECTORY_SEPARATOR . 'Sonos_' . $this->InstanceID;
            IPS_SetMediaFile($MediaID, $ImageFile, false);
            $Content = file_get_contents(__DIR__ . '/../libs/noCover.png');
            IPS_SetMediaContent($this->GetIDForIdent('MediaImage'), base64_encode($Content));
        }
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->updateGroups();
        $this->RegisterVariableInteger('Groups', $this->Translate('Groups'), 'Groups.SONOS', 8);
        $this->EnableAction('Groups');
        $this->SetTimerInterval('SONOS_UpdateStatus', $this->ReadPropertyInteger('UpdateInterval') * 1000);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Control':
                switch ($Value) {
                    case 0:
                        $this->skipToPreviousTrack();
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
                        $this->skipToNextTrack();
                        break;
                }
                break;
            case 'Volume':
                $this->setVolume(intval($Value));
                break;
            case 'GroupVolume':
                $this->setGroupVolume(intval($Value));
                break;
            case 'Mute':
                $this->setMute($Value);
                break;
            case 'GroupMute':
                $this->setGroupMute($Value);
                break;
            case 'Groups':
                $PlayerID = $this->ReadPropertyString('PlayerID');
                switch ($Value) {
                    case 0:
                        $GroupID = $this->ReadAttributeString('GroupID');
                        $this->modifyGroupMembers($GroupID, [], [$PlayerID]);
                        break;
                    default:
                        $Groups = json_decode($this->ReadAttributeString('Groups'), false);
                        $GroupID = $Groups[$Value][4];
                        $this->modifyGroupMembers($GroupID, [$PlayerID], []);
                        break;
                }
                break;
        }
    }

    public function UpdateStatus()
    {
        $this->getMetadataStatus();
        $this->getVolume();
        $this->getGroupVolume();
        $this->updateGroups();
        $this->refreshGroupValue();
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

    private function getMetadataStatus()
    {
        $this->updateGroupID();

        $result = $this->getData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playbackMetadata');

        if (property_exists($result, 'currentItem')) {
            $this->SetValue('Artist', $result->currentItem->track->artist->name);
            $this->SetValue('Track', $result->currentItem->track->name);
            $this->SetValue('Album', $result->currentItem->track->album->name);
            $Content = file_get_contents($result->currentItem->track->imageUrl);
        } else {
            $this->SetValue('Artist', $result->container->name);
            if (property_exists($result, 'streamInfo')) {
                $this->SetValue('Track', $result->streamInfo);
            } else {
                $this->SetValue('Track', '-');
            }
            $this->SetValue('Album', '-');
            $Content = file_get_contents(__DIR__ . '/../libs/noCover.png');
        }

        $this->SetValue('Service', $result->container->service->name);

        IPS_SetMediaContent($this->GetIDForIdent('MediaImage'), base64_encode($Content));
        IPS_SendMediaEvent($this->GetIDForIdent('MediaImage'));
    }

    private function getVolume()
    {
        $this->updateGroupID();

        $result = $this->getData('/v1/players/' . $this->ReadPropertyString('PlayerID') . '/playerVolume');

        $this->SetValue('Volume', $result->volume);
        $this->SetValue('Mute', $result->muted);
    }

    private function refreshGroupValue()
    {
        $GroupID = $this->ReadAttributeString('GroupID');
        $groups = json_decode($this->ReadAttributeString('Groups'), false);
        foreach ($groups as $group) {
            if ($group[4] == $GroupID) {
                $this->SetValue('Groups', $group[0]);
            }
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

    private function skipToNextTrack()
    {
        if ($this->GetValue('Control') != 1) {
            echo 'Skipping is only available while playing';
            return;
        }

        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/skipToNextTrack');
    }

    private function skipToPreviousTrack()
    {
        if ($this->GetValue('Control') != 1) {
            echo 'Skipping is only available while playing';
            return;
        }

        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/playback/skipToPreviousTrack');
    }

    private function setVolume($Volume)
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $result = $this->postData('/v1/players/' . $this->ReadPropertyString('PlayerID') . '/playerVolume', json_encode(
            [
                'volume' => $Volume
            ]
        ));

        $this->SetValue('Volume', $Volume);
    }

    private function setMute($Mute)
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change
        $this->updateGroupID();

        $result = $this->postData('/v1/players/' . $this->ReadPropertyString('PlayerID') . '/playerVolume/mute', json_encode(
            [
                'muted' => $Mute
            ]
        ));

        $this->SetValue('Mute', $Mute);
    }

    private function getGroupVolume()
    {
        $this->updateGroupID();

        $result = $this->getData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/groupVolume');

        $this->SetValue('GroupVolume', $result->volume);
        $this->SetValue('GroupMute', $result->muted);
    }

    private function setGroupMute($Mute)
    {
        $this->updateGroupID();

        $result = $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/groupVolume/mute', json_encode(
            [
                'muted' => $Mute
            ]
        ));
        $this->SetValue('GroupMute', $Mute);
    }

    private function setGroupVolume($Volume)
    {
        $this->updateGroupID();

        $result = $this->postData('/v1/groups/' . $this->ReadAttributeString('GroupID') . '/groupVolume', json_encode(
            [
                'volume' => $Volume
            ]
        ));
        $this->SetValue('GroupVolume', $Volume);
    }

    private function updateGroups()
    {
        //we should remove this and replace it with a subscribe approach that automatically updated the GroupID upon change

        $Associations = [];
        if ($this->ReadPropertyString('HouseholdID') != '') {
            $this->updateGroupID();

            $groups = $this->getData('/v1/households/' . $this->ReadPropertyString('HouseholdID') . '/groups');

            $Association[0] = 0;
            $Association[1] = 'None';
            $Association[2] = '';
            $Association[3] = -1;
            $Association[4] = '';
            $Associations[] = $Association;

            $i = 1;
            foreach ($groups->groups as $group) {
                $Association[0] = $i;
                $Association[1] = $group->name;
                $Association[2] = '';
                $Association[3] = -1;
                $Association[4] = $group->id;
                $Associations[] = $Association;
                $i++;
            }

            $countOldAssociations = count(IPS_GetVariableProfile('Groups.SONOS')['Associations']);
            if ($countOldAssociations > count($Associations)) {
                if (IPS_VariableProfileExists('Groups.SONOS')) {
                    for ($i = 0; $i <= $countOldAssociations - 1; $i++) {
                        IPS_SetVariableProfileAssociation('Groups.SONOS', $i, '', '', -1);
                    }
                }
            }
        }
        $this->WriteAttributeString('Groups', json_encode($Associations));
        $this->RegisterProfileIntegerEx('Groups.SONOS', 'Information', '', '', $Associations);
    }

    private function modifyGroupMembers($GroupID, $MembersToAdd, $MembersToRemove)
    {
        $result = $this->postData('/v1/groups/' . $GroupID . '/groups/modifyGroupMembers', json_encode(
            [
                'playerIdsToAdd'    => $MembersToAdd,
                'playerIdsToRemove' => $MembersToRemove
            ]
        ));
        $this->updateGroupID();
        $this->updateGroups();
        $this->refreshGroupValue();
    }
}
