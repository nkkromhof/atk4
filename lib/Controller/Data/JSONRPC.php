<?php

/*
 * Implementation of Rest-full API connectivity using JSON. Define your 
 * model with the methods you wish and then set this controller. You 
 * need to specify URL as a table. When you perform interaction with 
 * your model, it will automatically communicate with the remote server.
 *
 * You can call custom methods through $controller->request($model,'method',arguments);
 * Those will not change the state of your model and will simply return 
 * decoded JSON back.
 *
 * NOTE: This is pretty awesome controller!
 */
class Controller_Data_RestAPI extends Controller_Data {
    /* Sends Generic Request */

    public $last_request=null;

    function request($url,$method,$params=null,$id=undefined){
        // if method is array, then the batch is executed. It must be in 
        // format:  TODO: IMPLEMENT
        // array( 
        //  array('method'=>$method, 'params'=>$params),
        //  array('method'=>$method, 'params'=>$params),
        // )

        // Generate ID, to match with response
        if($id===undefined){
            $id=uniqid();
        }

        // Prepare Request
        $request=array();
        $request["jsonrpc"] = "2.0";
        $request["method"] = $method;

        if(!is_null($params))$request["params"] = $params;
        if(!is_null($id))$request['id']=$id;
        $request["ts"] = time();

        // Send Request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $this->last_request=$request;
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request,'','&'));

        $result = curl_exec($ch);
        curl_close($ch);

        // TODO: check for errors

        $result=json_decode($result);

        if($result->id != $id){
            // ERROR
            //$e=$this->exception($result->error->message,$result->error->code);
        }

        // Convert error into exception
        if($result->error){
            $e=$this->exception($result->error->message,$result->error->code);
            if($result->error->data);
            $e->addMoreInfo('data',$result->error->data);
            throw $e;
        }

        // Decode Request
        return $result->result;
    }
}