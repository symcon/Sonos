<?php

declare(strict_types=1);

include_once __DIR__ . '/../libs/profile.php';
include_once __DIR__ . '/../libs/data.php';

class SonosPlayer extends IPSModule
{
    use ProfileHelper;
    use DataHelper;

    private $mediaCover = 'iVBORw0KGgoAAAANSUhEUgAAAZAAAAGQCAIAAAAP3aGbAAAACXBIWXMAAC4jAAAuIwF4pT92AAAGXWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDUgNzkuMTYzNDk5LCAyMDE4LzA4LzEzLTE2OjQwOjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdEV2dD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlRXZlbnQjIiB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoV2luZG93cykiIHhtcDpDcmVhdGVEYXRlPSIyMDE5LTExLTI3VDEyOjAyOjMzKzAxOjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDE5LTExLTI3VDEyOjAyOjMzKzAxOjAwIiB4bXA6TW9kaWZ5RGF0ZT0iMjAxOS0xMS0yN1QxMjowMjozMyswMTowMCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo2ZmQ4MmQzNy1kZDZiLTNkNDAtYjljYS00ODM3M2E4YjkyNTIiIHhtcE1NOkRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDo3ZmFlYjdkOS1hNWZmLWQyNDgtOWYzMy00NjcwNmJmNDJjMTYiIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo4MjBhZTlkMS01YmMyLTcxNGQtYjU5NC00MTIwYzA4NGQ1MmQiIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiIGRjOmZvcm1hdD0iaW1hZ2UvcG5nIj4gPHhtcE1NOkhpc3Rvcnk+IDxyZGY6U2VxPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0iY3JlYXRlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDo4MjBhZTlkMS01YmMyLTcxNGQtYjU5NC00MTIwYzA4NGQ1MmQiIHN0RXZ0OndoZW49IjIwMTktMTEtMjdUMTI6MDI6MzMrMDE6MDAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKSIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6NmZkODJkMzctZGQ2Yi0zZDQwLWI5Y2EtNDgzNzNhOGI5MjUyIiBzdEV2dDp3aGVuPSIyMDE5LTExLTI3VDEyOjAyOjMzKzAxOjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoV2luZG93cykiIHN0RXZ0OmNoYW5nZWQ9Ii8iLz4gPC9yZGY6U2VxPiA8L3htcE1NOkhpc3Rvcnk+IDxwaG90b3Nob3A6VGV4dExheWVycz4gPHJkZjpCYWc+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iTm8gQ292ZXIiIHBob3Rvc2hvcDpMYXllclRleHQ9Ik5vIENvdmVyIi8+IDwvcmRmOkJhZz4gPC9waG90b3Nob3A6VGV4dExheWVycz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4+KpO6AAAUqklEQVR4nO3dfVAUZ4LH8c7VEQtQCiKwBozAQoIeoKtZMVAmE0UuL24STeHmqkIqb5s10brcbpIto1nqkvKipOpMdt2KGzdvWnH3XqSiyeXlckgMOQsW3egpeOqKATwhRjTMokAR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DI5hAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQQbAAyCBYAGQQLAAyCBYAGQQLgAyCBUAGwQIgg2ABkEGwAMggWABkECwAMggWABkEC4AMggVABsECIINgAZBBsADIIFgAZBAsADIIFgAZBAuADIIFQAbBAiCDYAGQ8f+DZVpdk4PxiwAAAABJRU5ErkJggg==';

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
        $this->RegisterVariableString('Service', 'Service', '', 1);
        $this->RegisterVariableString('Artist', 'Artist', '', 2);
        $this->RegisterVariableString('Track', 'Track', '', 3);
        $this->RegisterVariableString('Album', 'Album', '', 4);

        $this->RegisterVariableInteger('Control', 'Control', 'Control.SONOS', 5);
        $this->EnableAction('Control');

        $this->RegisterVariableInteger('Volume', 'Volume', 'Intensity.100', 6);
        $this->EnableAction('Volume');

        $this->RegisterVariableBoolean('Mute', 'Mute', '~Switch', 7);
        $this->EnableAction('Mute');

        //Media Image
        if (!@IPS_GetObjectIDByIdent('MediaImage', $this->InstanceID)) {
            $MediaID = IPS_CreateMedia(1);
            IPS_SetParent($MediaID, $this->InstanceID);
            IPS_SetIdent($MediaID, 'MediaImage');
            IPS_SetPosition($MediaID, 0);
            IPS_SetName($MediaID, $this->Translate('Media Image'));
            $ImageFile = IPS_GetKernelDir() . 'media' . DIRECTORY_SEPARATOR . 'Sonos_' . $this->InstanceID;
            IPS_SetMediaFile($MediaID, $ImageFile, false);
            IPS_SetMediaContent($this->GetIDForIdent('MediaImage'), base64_encode($this->mediaCover));
        }
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->UpdateGroups();
        $this->RegisterVariableInteger('Groups', 'Groups', 'Groups.SONOS', 8);
        $this->EnableAction('Groups');
        $this->SetTimerInterval('SONOS_UpdateStatus', $this->ReadPropertyInteger('UpdateInterval') * 1000);
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
