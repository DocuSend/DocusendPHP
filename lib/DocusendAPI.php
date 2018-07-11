<?php

class DocusendAPI
{
    public $host;
    public $auth;
    public $debug;
    public $httpcode;
    function __construct($_host,$email,$password)
    {
        $this->host = $_host;
        $this->debug = false;
        $this->auth = sprintf("%s:%s",$email,$password);
    }
    public function Send($target, $request, $aParms=array())
    {
        $headers = array("cache-control: no-cache","content-type: application/json");
        $curl = curl_init();
        $url = sprintf("%s/%s.php",$this->host,$target);
        if($this->debug)
            printf("<strong>Before</strong><br />%s<br />%s<br />%s<br /><strong>After</strong><br />",$url,$request,print_r($aParms,true));
        //echo print_r($aParms);
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_ENCODING,"");
        curl_setopt($curl,CURLOPT_MAXREDIRS,10);
        curl_setopt($curl,CURLOPT_TIMEOUT,30);
        curl_setopt($curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$request);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
        curl_setopt($curl,CURLOPT_USERPWD,$this->auth);
        if($request=="PUT" || TRUE)
        {
            $data = json_encode($aParms);
            if($this->debug)
                echo sprintf("<br />REQUEST %s DATA %s<br />",$request,$data);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
            $headers[] = 'Content-Length: ' . strlen($data);
        }
        else
        {
            //if($this->debug)
                //echo sprintf("<br />2nd REQUEST %s<br />",$request);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$aParms);
        }
         curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
    public function GetCompany()
    {
       return $this->Send("Company","GET"); 
    }
    public function GetShoppingCart()
    {
       return $this->Send("ShoppingCart","GET"); 
    }
    public function GetBalance()
    {
       return $this->Send("Balance","GET"); 
    }
    public function GetPay()
    {
       return $this->Send("Pay","GET"); 
    }
    public function PutPay($type,$amount,$aPost)
    {
       $aPost['Type']=$type;
       $aPost['Amount']=$amount;
       return $this->Send("Pay","PUT",$aPost); 
    }
    public function GetTransaction($aPost)
    {
       return $this->Send("Transaction","GET",$aPost); 
    }
    public function PutCompany($aPost)
    {
       return $this->Send("Company","PUT",$aPost); 
    }
    public function PostCompany($aPost)
    {
       return $this->Send("Company","POST",$aPost); 
    }
    public function GetJob($jobID)
    {
       return $this->Send("Job","GET",array("ID"=>$jobID)); 
        
    }
    public function GetAddress($jobID)
    {
       return $this->Send("Address","GET",array("ID"=>$jobID)); 
        
    }
    public function PutAddress($jobID,$rows)
    {
       return $this->Send("Address","PUT",array("ID"=>$jobID,"ROWS"=>$rows));        
    }
    public function JSONFile($path,$type)
    {
        $content = base64_encode(fread(fopen($path, "r"), filesize($path)));
        return array("filetype"=>$type,"content"=>$content,"size"=>filesize($path));
    }
    public function PostJob($printfile,$IsColor,$IsPerforated,$isReturnEnvelope,$insertPDF="",$remitto="")
    {
       $aPost = array("Color"=>$IsColor,"Perforated"=>$IsPerforated,"ReturnEnvelope"=>$isReturnEnvelope);
       if(strlen($remitto))
         $aPost['REMITTO']=$remitto;
       $aFILES=[];
       if(is_array($printfile))
       {
         foreach($printfile as $pfile)
           $aFILES[basename($pfile)] = $this->JSONFile($pfile,"letters"); 
       }
       else
        $aFILES[basename($printfile)]=$this->JSONFile($printfile,"letters");
       if(!empty($insertPDF))
       {
            if(is_array($insertPDF))
            {
              foreach($insertPDF as $pfile)
                $aFILES[basename($pfile)] = $this->JSONFile($pfile,"inserts"); 
            }
            else
                $aFILES[basename($insertPDF)]=$this->JSONFile($printfile,"inserts");
       }
       $aPost['DATAFILES']=$aFILES;
       return $this->Send("Job","POST",$aPost);
    }
    public function PutJob($jobID,$aPost)
    {
       $aPost['ID']=$jobID;   
       return $this->Send("Job","PUT",$aPost);
    }
    public function GetUser()
    {
       return $this->Send("User","GET"); 
    }
    public function PutUser($UserID,$aPost)
    {
       $aPost['UserID']=$UserID; // Makes sure we have the user id for updating.
       return $this->Send("User","PUT",$aPost); 
    }
    public function PostUser($aPost)
    {
       return $this->Send("User","POST",$aPost); 
    }
    public function DeleteUser($UserID)
    {
       $aPost = array('UserID'=>$UserID); 
       return $this->Send("User","DELETE",$aPost); 
    }
    public function PostMerge($aPost=array(0))
    {
       return $this->Send("Merge","POST",$aPost); 
    }
    public function GetMerge($aPost=array(0))
    {
       return $this->Send("Merge","GET",$aPost); 
    }
}
?>