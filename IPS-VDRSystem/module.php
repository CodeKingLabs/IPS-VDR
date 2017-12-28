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
      $Devices = '<div class="table" style="width:100%">
        <div class="tr" style="border: 1px solid red;">
        <div class="td"><b>Gerät</b></div>
        <div class="td" style="padding-left:20px"><b>Kanal</b></div>
        <div class="td" style="padding-left:20px;text-align:center"><b>Signalstärke</b></div>
        <div class="td" style="padding-left:20px;text-align:center"><b>Signalqualität</b></div>
      </div>';
      foreach ($data->Buffer->vdr->devices as $Device) {
        if($Device->name <> "") {

          $Devices .= '<div class="tr">
                            <div class="td">' . $Device->name . '</div>
                            <div class="td" style="padding-left:20px">' . $Device->channel_nr." ".$Device->channel_name . '</div>
                            <div class="td" style="padding-left:20px;text-align:center">' . $Device->signal_strength . '</div>
                            <div class="td" style="padding-left:20px;text-align:center">' . $Device->signal_quality . '</div>
                      </div>';
        }
      }

        $Devices .= '</div>';

      SetValue($this->GetIDForIdent("Devices") ,$Devices);

    }
  }
}
?>
