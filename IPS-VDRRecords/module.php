<?

class IPS_VDRRecords extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        //SIS-Handball
        $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
    }

    public function Destroy()
    {
        $this->UnregisterHook('/hook/VDRRecordings' . $this->InstanceID);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->RegisterVariableString("AnzahlAufnahmen", "AnzahlAufnahmen", "String");
        $this->RegisterVariableString("Aufnahmen", "Aufnahmen", "~HTMLBox");

        $MyFuncs = array('getRecordings', 'getIrgendwas');

        $Lines = array();
        foreach ($MyFuncs as $Func) {
            $Lines[] = '.*"Action":"' . $Func . '".*';
        }
        $Line = implode('|', $Lines);
        $this->SetReceiveDataFilter("(" . $Line . ")");
        $this->SendDebug("SetFilter", "(" . $Line . ")", 0);


        $sid = $this->RegisterScript("WebHookVDRRecordings", "WebHookVDRRecordings", '
  <?//Do not delete or modify.
  if ((isset($_GET["RecordingID"])) AND (isset($_GET["Action"])))  {
    VDRRecords_ProcessHookdata(' . $this->InstanceID . ' ,$_GET);
  }
  ', -8);

        IPS_SetHidden($sid, true);
        $this->RegisterHook('/hook/VDRRecordings' . $this->InstanceID, $sid);
    }

    private function getWebhookLink($RecordingID, $Action)
    {
        $spinner = ($Action == 'Delete') ? 'this.parentNode.getElementsByTagName(\'i\')[0].className+=\' visible\';' : '';
        return 'onclick="' . $spinner . 'window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/VDRRecordings' . $this->InstanceID . '?RecordingID=' . $RecordingID . '&Action=' . $Action . '\' })"';
    }

    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        switch ($data->Action) {
            case "getRecordings":
                $Records = '<div class="ipsContainer container nestedEven table" style="width:100%">';
                $i = 0;
                foreach ($data->Buffer->recordings as $Record) {
                    $date = date("d.m.Y H:i", $Record->event_start_time);
                    $title = $Record->event_title;

                    $Records .= '
                        <div class="content tr">
                            <div class="icon td ipsIconTV"></div>
                            <div class="title td">' . $date . '</div>
                            <div class="title td" style="padding-left:20px">' . $title . '</div>
                            <div class="visual td">
                                <div class="ipsContainer enum">
                                    <i class="iconSpinner iconSmallSpinner throbber"></i>
                                    <div class="selected" style="background-color: rgba(255, 0, 0, 0.6);" ' . $this->getWebhookLink($Record->number, "Delete") . '>löschen</div>
                                    <!--<div class="selected" style="background-color: rgba(0, 255, 0, 0.3);" ' . $this->getWebhookLink($Record->number, "Play") . '>abspielen</div>-->
                                </div>
                            </div>
                            <div class="link td"></div>
                        </div>
                    ';

                    $i++;
                }

                if ($i == 0) {
                    $Records .= '
                        <div class="content tr">
                            <div class="td" style="text-align:center;">Aktuell sind keine Aufnahmen vorhanden!</div>
                        </div>
                    ';
                }


                $Records .= "</div>";

                SetValue($this->GetIDForIdent("AnzahlAufnahmen"), count($data->Buffer->recordings));
                SetValue($this->GetIDForIdent("Aufnahmen"), utf8_decode($Records));
                break;
            case "deleteRecordings":
                IPS_LogMessage("VDR", "testS");
                break;

        }
    }

    public function ProcessHookdata($HookData)
    {
        //IPS_LogMessage("hook", "test");
        if ($HookData["Action"] == "Play") {
            IPS_LogMessage("PLayRecording", "Playing " . $HookData["RecordingID"]);
            $this->SendDataToParent(json_encode(Array("DataID" => "{66900AB7-4164-4AB3-9F86-703A38CD5DA0}", "Action" => "playRecord", "Buffer" => $HookData["RecordingID"])));
        }
        if ($HookData["Action"] == "Delete") {
            IPS_LogMessage("Delete", "Deleting " . $HookData["RecordingID"]);
            $this->SendDataToParent(json_encode(Array("DataID" => "{66900AB7-4164-4AB3-9F86-703A38CD5DA0}", "Action" => "DeleteRecords", "Buffer" => $HookData["RecordingID"])));
        }
    }


    private function RegisterHook($WebHook, $TargetID)
    {
        $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
        if (sizeof($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
            $found = false;
            foreach ($hooks as $index => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    if ($hook['TargetID'] == $TargetID)
                        return;
                    $hooks[$index]['TargetID'] = $TargetID;
                    $found = true;
                }
            }
            if (!$found) {
                $hooks[] = Array("Hook" => $WebHook, "TargetID" => $TargetID);
            }
            IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
            IPS_ApplyChanges($ids[0]);
        }
    }

    private function UnregisterHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
        if (sizeof($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
            $found = false;
            foreach ($hooks as $index => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    $found = $index;
                    break;
                }
            }
            if ($found !== false) {
                array_splice($hooks, $index, 1);
                IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
                IPS_ApplyChanges($ids[0]);
            }
        }
    }
}

?>
