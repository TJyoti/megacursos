<?php

/**
 * Breadcrumbs Class
 *
 * @package     MegaCursos
 * @subpackage  Libraries
 * @category    Agile Integration
 * @author      Jyoti
 */


define("AGILE_DOMAIN", "megacursos");
define("AGILE_USER_EMAIL", "webapps@megacursos.com");
define("AGILE_REST_API_KEY", "vrm1mdkfg14r73u7hmsfg1hlr7");

class Agile {
    /* curl function */

    public function curl_wrap($entity, $data, $method, $content_type) {
        if ($content_type == NULL) {
            $content_type = "application/json";
        }

        $agile_url = "https://" . AGILE_DOMAIN . ".agilecrm.com/dev/api/" . $entity;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
        switch ($method) {
            case "POST":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "GET":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type : $content_type;", 'Accept : application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, AGILE_USER_EMAIL . ':' . AGILE_REST_API_KEY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $cha = @fopen("AgileProcessLOG.log", "a+");
        @fwrite($cha, ProcessId . "\t" . date("Y-m-d H:i:s") . "\t" . $data . "\t" . $output . "\n");
        @fclose($cha);
        return $output;
    }

    /* create new Contact */

    public function new_agilecontact($contact_json) {

        $contact_json = $contact_json;
        $result = $this->curl_wrap("contacts", $contact_json, "POST", "application/json");
        return $result;
    }

    /* Add tags if exist */

    public function add_agiletags($email, $tags) {
        $fields = array(
            'email' => urlencode($email),
            'tags' => urlencode($tags)
        );
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }

        $result = $this->curl_wrap("contacts/email/tags/add", rtrim($fields_string, '&'), "POST", "application/x-www-form-urlencoded");
        return $result;
    }

    /* get email if exist */

    public function getdata_email($email, $data, $tags) {
        $details = json_decode($this->curl_wrap("contacts/search/email/$email", null, "GET", "application/json"));
        $id = $details->id;
        if ($id) {
            return $this->add_agiletags($email, $tags);
        } else {
            return $this->new_agilecontact($data);
        }
    }

}
