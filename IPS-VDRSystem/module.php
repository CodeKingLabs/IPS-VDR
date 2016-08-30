<?
class IPS_VDRSystem extends IPSModule {

  public function Create(){
    //Never delete this line!
    parent::Create();
    //SIS-Handball
    $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    $this->RegisterVariableString("HDD", "HDD", "String");
    $this->RegisterVariableString("FreieMinuten", "FreieMinuten", "String");
    $this->RegisterVariableString("Plugins", "Plugins", "~HTMLBox");
    $this->RegisterVariableString("Devices", "Devices", "~HTMLBox");

    $MyFuncs=array('getSystemInfo','getIrgendwas');

    $Lines = array();
    foreach ($MyFuncs as $Func)
    {
      $Lines[] = '.*"Action":"' . $Func. '".*';
    }
    $Line = implode('|', $Lines);
    $this->SetReceiveDataFilter("(" . $Line . ")");
    $this->SendDebug("SetFilter", "(" . $Line . ")", 0);
  }
  public function ReceiveData($JSONString) {
    $data = json_decode($JSONString);
    if ($data->Action == "getSystemInfo") {
      IPS_LogMessage("VDRSystem", $JSONString);
      SetValue($this->GetIDForIdent("HDD") ,round($data->Buffer->diskusage->free_mb/1024, 2)." GB");
      SetValue($this->GetIDForIdent("FreieMinuten") ,$data->Buffer->diskusage->free_minutes);
      $ResultPlugin = "";
      foreach ($data->Buffer->vdr->plugins as $Plugin) {
        $ResultPlugin .= $Plugin->name." ".$Plugin->version."<br />";
      }
      SetValue($this->GetIDForIdent("Plugins") ,$ResultPlugin);
      $DeviceNumber = 0;
      $Devices = '<table width="100%">
      <td>Gerät</td>
      <td>Kanal</td>
      <td>Signalstärke</td>
      <td>Signalqualität</td>';
      foreach ($data->Buffer->vdr->devices as $Device) {
        if($Device->name <> "") {
          if($DeviceNumber % 2 == 0) {
            $Devices .= '<tr style="background-color:#000000; color:#ffffff;"><td>';
          }
          else {
            $Devices .= '<tr style="background-color:#080808; color:#ffffff;"><td>';
          }
          $Devices .= $Device->name;
          $Devices .= "</td><td>";
          $Devices .= $Device->channel_nr." ".$Device->channel_name;
          $Devices .= "</td><td>";
          $Devices .= $Device->signal_strength;
          $Devices .= "</td><td>";
          $Devices .= $Device->signal_quality;
          $Devices .= "</td>";
          //$DeviceNumber++;
        }
      }
      SetValue($this->GetIDForIdent("Devices") ,$Devices);

    }
  }
}
?>
