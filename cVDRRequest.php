<?
class cVDRRequest
{
    /**
     * @var string
     */
    private $Host;
    /**
     * @var int
     */
    private $Port;

    /**
     * cVDRRequest constructor.
     */
    public function __construct($host, $port)
    {
        $this->Host = $host;
        $this->Port = $port;

    }

    public function get($parameter) {
        $json = file_get_contents("http://".$this->Host.":".$this->Port."/".$parameter);
        $obj = json_decode($json);
        return $obj;
    }

    public function set($parameter) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$this->Host.":".$this->Port."/".$parameter);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_exec($ch);
    }

    public function delete($parameter) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$this->Host.":".$this->Port."/".$parameter);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($ch);
    }

    public function getRequest($parameter) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$this->Host.":".$this->Port."/".$parameter);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);
    }


}
?>
