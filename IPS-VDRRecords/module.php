<?
class IPS_VDRRecords extends IPSModule {

  public function Create(){
    //Never delete this line!
    parent::Create();
    //SIS-Handball
    $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
  }
  public function Destroy()
  {
    $this->UnregisterHook('/hook/VDRRecordings' . $this->InstanceID);
  }
  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    $this->RegisterVariableString("AnzahlAufnahmen", "AnzahlAufnahmen", "String");
    $this->RegisterVariableString("Aufnahmen", "Aufnahmen", "~HTMLBox");



    $MyFuncs=array('getRecordings','getIrgendwas');

    $Lines = array();
    foreach ($MyFuncs as $Func)
    {
      $Lines[] = '.*"Action":"' . $Func. '".*';
    }
    $Line = implode('|', $Lines);
    $this->SetReceiveDataFilter("(" . $Line . ")");
    $this->SendDebug("SetFilter", "(" . $Line . ")", 0);


  $sid = $this->RegisterScript("WebHookVDRRecordings", "WebHookVDRRecordings", '
  <?//Do not delete or modify.
  if ((isset($_GET["RecordingID"])) AND (isset($_GET["Action"])))  {
    vdr_ProcessHookdata(' . $this->InstanceID . ' ,$_GET);
  }
  ', -8);
  IPS_SetHidden($sid, true);
  $this->RegisterHook('/hook/VDRRecordings' . $this->InstanceID, $sid);
}

private function getWebhookLink($RecordingID, $Action) {
  return 'onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/VDRRecordings' . $this->InstanceID . '?RecordingID=' . $RecordingID . '&Action=' . $Action  . '\' })"';

}

public function ReceiveData($JSONString) {

  $data = json_decode($JSONString);
  IPS_LogMessage("case",$data->Action);
  switch ($data->Action) {
    case "getRecordings":
    $Records = '<table width="100%">';
    $i = 0;
    IPS_LogMessage("VDR",utf8_decode($JSONString));
    foreach ($data->Buffer->recordings as $Record) {



      if($i % 2 == 0) {
        $Records .= '<tr style="background-color:#000000; color:#ffffff;"><td>';
      }
      else {
        $Records .= '<tr style="background-color:#080808; color:#ffffff;"><td>';
      }


      $Records .= date("d.m.Y H:i",$Record->event_start_time)." ";
      $Records .= $Record->event_title."<br /> ".str_replace("#","",$Record->event_short_text);
      $Records .= "</td><td>";
      $Records.= '<div class="button" style="border:1px solid #df0909; -webkit-border-radius: 3px; -moz-border-radius: 3px;border-radius: 3px;font-size:12px;font-family:arial, helvetica, sans-serif; padding: 10px 10px 10px 10px; text-decoration:none; display:inline-block;text-shadow: -1px -1px 0 rgba(0,0,0,0.3);font-weight:bold; color: #FFFFFF;
      background-color: #f62b2b; background-image: -webkit-gradient(linear, left top, left bottom, from(#f62b2b), to(#d20202));
      background-image: -webkit-linear-gradient(top, #f62b2b, #d20202);
      background-image: -moz-linear-gradient(top, #f62b2b, #d20202);
      background-image: -ms-linear-gradient(top, #f62b2b, #d20202);
      background-image: -o-linear-gradient(top, #f62b2b, #d20202);
      background-image: linear-gradient(to bottom, #f62b2b, #d20202);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#f62b2b, endColorstr=#d20202);"'.$this->getWebhookLink($Record->number, "Delete").'>Löschen';
      $Records.="</div> ";


      $Records.= '<div class="button" style="border:1px solid #8bcf54; -webkit-border-radius: 3px; -moz-border-radius: 3px;border-radius: 3px;font-size:12px;font-family:arial, helvetica, sans-serif; padding: 10px 10px 10px 10px; text-decoration:none; display:inline-block;text-shadow: -1px -1px 0 rgba(0,0,0,0.3);font-weight:bold; color: #FFFFFF;
      background-color: #a9db80; background-image: -webkit-gradient(linear, left top, left bottom, from(#a9db80), to(#96c56f));
      background-image: -webkit-linear-gradient(top, #a9db80, #96c56f);
      background-image: -moz-linear-gradient(top, #a9db80, #96c56f);
      background-image: -ms-linear-gradient(top, #a9db80, #96c56f);
      background-image: -o-linear-gradient(top, #a9db80, #96c56f);
      background-image: linear-gradient(to bottom, #a9db80, #96c56f);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#a9db80, endColorstr=#96c56f);"'.$this->getWebhookLink($Record->number, "Play").'>Abspielen';
      $Records.="</div></td></tr>";
      $i++;



    }
    $Records.="</table>";
    SetValue($this->GetIDForIdent("AnzahlAufnahmen") ,count($data->Buffer->recordings));
    SetValue($this->GetIDForIdent("Aufnahmen") ,utf8_decode($Records));
    break;
    case "deleteRecordings":
    IPS_LogMessage("VDR", "testS");
    break;

  }
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
