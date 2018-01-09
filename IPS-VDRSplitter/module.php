<?

require_once(__DIR__ . "/../cVDRRequest.php");

class IPS_VDRIO extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();
		//SVDR Zugangsdaten
		$this->RegisterPropertyString("host", "");
		$this->RegisterPropertyString("port", "2001");
		$this->RegisterTimer("UpdateRecordings", 5000, 'VDRIO_getRecords($_IPS[\'TARGET\']);');
		$this->RegisterTimer("UpdateSystemInfo", 5000, 'VDRIO_getSystemInfo($_IPS[\'TARGET\']);');

		$this->RegisterTimer("UpdateProgram", 5000/*60000 * 30*/, 'VDRIO_getProgram($_IPS[\'TARGET\']);');
	}
	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
	}

	public function ForwardData($JSONString) {
		$data = json_decode($JSONString);
		//IPS_LogMessage("deleteRecords1", $JSONString);
		if ($data->Action == "DeleteRecords") {
			$this->deleteRecords($data->Buffer);
		}
		if ($data->Action == "playRecord") {
			$this->playRecording($data->Buffer);
		}
		if ($data->Action == "remoteButtonClick") {
			$this->remoteButtonClick($data->Buffer);
		}
	}

	public function getSystemInfo() {
		$Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
		$SystemInfo = $Request->get("info.json");
		$this->SendDataToChildren(json_encode(Array("DataID" => "{A09538DA-3DAB-4E0B-93FF-30C0E3B374D6}", "Action"=> "getSystemInfo", "Buffer" => $SystemInfo)));
	}

    public function getRecords() {
        $Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
        $Records = $Request->get("recordings.json");

        IPS_LogMessage("RecordsReceivedSplitter", count($Records->recordings));
        $this->SendDataToChildren(json_encode(Array("DataID" => "{A09538DA-3DAB-4E0B-93FF-30C0E3B374D6}", "Action"=> "getRecordings", "Buffer" => $Records)));
    }

    public function getProgram() {
        $Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
        $Program = $Request->get("events.json?chevents=1");

        $this->SendDataToChildren(json_encode(Array("DataID" => "{A09538DA-3DAB-4E0B-93FF-30C0E3B374D6}", "Action"=> "getProgram", "Buffer" => $Program)));
    }

	public function deleteRecords($RecordingID) {
		$Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
		$Request->delete("recordings/".$RecordingID."?syncId=Symcon");
		//$this->SendDataToChildren(json_encode(Array("DataID" => "{A09538DA-3DAB-4E0B-93FF-30C0E3B374D6}", "Action"=> "deleteRecordings", "Buffer" => "test")));
	}
	public function playRecording($RecordingID) {
		$Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
		$Request->getRequest("recordings/play/".$RecordingID);
	}

	public function remoteButtonClick($Button) {
		$Request = new cVDRRequest($this->ReadPropertyString("host"), $this->ReadPropertyString("port"));
		$Request->set("remote/".$Button);
	}
}

?>
