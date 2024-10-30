<?php

class ServiceNowException extends Exception { }

class ServiceNow {

  public function __construct( $instance, $username, $password ) {
    $this->auth = array(
      'username' => $username,
      'password' => $password,
      'instance' => $instance);
    $this->table_endpoint = "https://$instance.service-now.com/api/now/table";
  }

  public function get_user( $user ) {
    $res = $this->request($this->table_endpoint .
                          '/sys_user?sysparm_query=' .
                          http_build_query($user, '', '^'));
    if( $res['status_code'] == 200 && $res['data'] )
      return $res['data']['result'][0];
    return NULL;
  }

  public function create_user( $user ) {
    $res = $this->request($this->table_endpoint . '/sys_user', $user);
    if( $res['status_code'] == 201 && $res['data'] )
      return $res['data']['result'];
    return NULL;
  }

  public function create_incident( $incident ) {
    $res = $this->request($this->table_endpoint . '/incident', $incident);
    if( $res['status_code'] == 201 && $res['data'] )
      return $res['data']['result'];
    return NULL;
  }

  function request($url, $data=null) {
    $ch = curl_init();
    $timeout = 5;

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));

    if( $data ) {
      $data = json_encode($data);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
      ));
      curl_setopt($ch, CURLOPT_VERBOSE, true);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD,
                "{$this->auth['username']}:{$this->auth['password']}");

    $res = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array(
      "status_code" => $status_code,
      "data" => json_decode($res, true)
    );
  }
}
