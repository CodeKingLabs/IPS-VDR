<?
class IPS_VDRRemote extends IPSModule {

  public function Create(){
    //Never delete this line!
    parent::Create();
    //SIS-Handball
    $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
  }
  public function Destroy()
  {
    $this->UnregisterHook('/hook/VDRRemote' . $this->InstanceID);
  }
  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    $this->RegisterVariableString("Fernbedienung", "Fernbedienung", "~HTMLBox");

    $MyFuncs=array('getRecordings','getIrgendwas');

    $Lines = array();
    foreach ($MyFuncs as $Func)
    {
      $Lines[] = '.*"Action":"' . $Func. '".*';
    }
    $Line = implode('|', $Lines);
    $this->SetReceiveDataFilter("(" . $Line . ")");
    $this->SendDebug("SetFilter", "(" . $Line . ")", 0);


    $sid = $this->RegisterScript("WebHookVDRRemote", "WebHookVDRRemote", '<? //Do not delete or modify.
    if (isset($_GET["button"]))
    VDRRemote_ExecuteButton(' . $this->InstanceID . ',$_GET["button"]);
    ', -8);
    IPS_SetHidden($sid, true);
    $this->RegisterHook('/hook/VDRRemote' . $this->InstanceID, $sid);
    $remoteID = $this->RegisterVariableString("Remote", "Remote", "~HTMLBox", 1);
    include 'generateRemote.php';
    SetValueString($remoteID, $remote);
  }

  public function ExecuteButton($Button) {
  
    $this->SendDataToParent(json_encode(Array("DataID" => "{66900AB7-4164-4AB3-9F86-703A38CD5DA0}", "Action" => "remoteButtonClick", "Buffer" => $Button)));
  }


  public function ProcessHookdata($HookData) {
    //IPS_LogMessage("hook", "test");
    if ($HookData["Action"] == "Play") {
      IPS_LogMessage("PLayRecording", "Läuft!");
      $this->SendDataToParent(json_encode(Array("DataID" => "{66900AB7-4164-4AB3-9F86-703A38CD5DA0}", "Action" => "playRecord", "Buffer" => $HookData["RecordingID"])));
    }
    if ($HookData["Action"] == "Delete") {
      IPS_LogMessage("Delete", "Läuft!");
      $this->SendDataToParent(json_encode(Array("DataID" => "{66900AB7-4164-4AB3-9F86-703A38CD5DA0}", "Action" => "DeleteRecords", "Buffer" => $HookData["RecordingID"])));
    }
  }



  private function RegisterHook($WebHook, $TargetID)
  {
    $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
    if (sizeof($ids) > 0)
    {
      $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
      $found = false;
      foreach ($hooks as $index => $hook)
      {
        if ($hook['Hook'] == $WebHook)
        {
          if ($hook['TargetID'] == $TargetID)
          return;
          $hooks[$index]['TargetID'] = $TargetID;
          $found = true;
        }
      }
      if (!$found)
      {
        $hooks[] = Array("Hook" => $WebHook, "TargetID" => $TargetID);
      }
      IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
      IPS_ApplyChanges($ids[0]);
    }
  }

  private function UnregisterHook($WebHook)
  {
    $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
    if (sizeof($ids) > 0)
    {
      $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
      $found = false;
      foreach ($hooks as $index => $hook)
      {
        if ($hook['Hook'] == $WebHook)
        {
          $found = $index;
          break;
        }
      }
      if ($found !== false)
      {
        array_splice($hooks, $index, 1);
        IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
        IPS_ApplyChanges($ids[0]);
      }
    }
  }
}
?>
