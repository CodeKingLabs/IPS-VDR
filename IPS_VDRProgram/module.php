<?

class IPS_VDRProgram extends IPSModule
{
    public function Create()
    {
        parent::Create();

        // connect to i/o device
        $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        // register public variables
        $this->RegisterVariableString("TVProgram", "TV Programm", "~HTMLBox");

        // set filter to get program data only
        $MyFuncs = array('getProgram', 'getIrgendwas');

        $Lines = array();
        foreach ($MyFuncs as $Func) {
            $Lines[] = '.*"Action":"' . $Func . '".*';
        }
        $Line = implode('|', $Lines);
        $this->SetReceiveDataFilter("(" . $Line . ")");
        $this->SendDebug("SetFilter", "(" . $Line . ")", 0);
    }


    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);

        // build epg
        if (isset($data->Buffer->events)) {
            $html = '<table style="width:100%" cellspacing="20" cellpadding="20">';
            foreach ($data->Buffer->events AS $epg) {
                $logo = sprintf('http://10.0.0.12/picons/logos/%s.png', strtr(strtolower($epg->channel_name), [
                    ' ' => '',
                    '.' => '',
                    '!' => '',
                    '-' => '',
                    '_' => '',
                    'ü' => 'ue',
                    '+' => 'plus'
                ]));

                if($pos = strpos($epg->description, '(n)')) {
                    $epg->description = trim(substr($epg->description, 0, $pos));
                }

                if($pos = strpos($epg->description, 'Darsteller:')) {
                    $epg->description = trim(substr($epg->description, 0, $pos));
                }

                if($pos = strpos($epg->description, 'Director ')) {
                    $epg->description = trim(substr($epg->description, 0, $pos));
                }

                if($pos = strpos($epg->description, 'Actor ')) {
                    $epg->description = trim(substr($epg->description, 0, $pos));
                }

                $html .= '
                <tr>
                    <td>
                        <img src="' . $logo . '" alt="' . $epg->channel_name . '" style="width:100px" />
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td style="width:40%" nowrap valign="top"><b>' . $epg->title . '</b><br />' . $epg->short_text . '</td>
                                <td>' . date('H:i', $epg->start_time) . ' - ' . date('H:i', $epg->start_time + $epg->duration) . ' Uhr</td>
                            </tr>
                            <tr>
                                <td colspan="2">' . nl2br($epg->description) . '</td>
                            </tr>                            
                        </table>                       
                    </td>
                </tr>
            ';
            }

            $html .= '</table>';
        } else {
            $html = '';
        }


        SetValue($this->GetIDForIdent("TVProgram"), $html);

    }
}

?>
