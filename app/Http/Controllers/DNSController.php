<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Dns\Dns;
use Location;

function getdata($url,$args=false) 
{ 
    $unparsed_json = file_get_contents($url);
    $json_object = json_decode($unparsed_json);
    return $json_object;
}

class DNSController extends Controller
{
    //
    function view() {
        return view("dns");
    }

    function dns(Request $request): Response {
        $dns = new Dns();

        $domain = $request->domain;
        $dns_server = $request->dns_server;
        $categoris = $request->categories;

        $dns = (new Dns)->useNameserver($dns_server);

        $records = $dns->getRecords($domain, $categoris, [$dns_server]);
        $data = [];

        // print_r($records);
        // die();
        // $ws = new \IP2Proxy\WebService('31E410CAC7ED00ADE750E7241B35C1CF',  'PX11', false);

        foreach ($records as $record) {
            $rec = [
                'host' => $record->host(),
                'ttl' => $record->ttl(),
                'class' => $record->class(),
                'type' => $record->type(),
            ];
            if ($record->type() == "A") {
                $rec['ip'] = $record->ip();
                $result=getdata("https://api.ip2location.io/?key=31E410CAC7ED00ADE750E7241B35C1CF&ip=".$rec['ip']);
                $rec['country'] = $result->country_code;
                $rec['asn'] = 'ASN'.$result->asn;
                $rec['as'] = $result->as;
            }
            if ($record->type() == "AAAA") {
                $rec['target'] = $record->ipv6();
                $result=getdata("https://api.ip2location.io/?key=31E410CAC7ED00ADE750E7241B35C1CF&ip=".$rec['target']);
                $rec['country'] = $result->country_code;
                $rec['asn'] = 'ASN'.$result->asn;
                $rec['as'] = $result->as;
            }
            if ($record->type() == "MX") {
                $rec['pri'] = $record->pri();
                $rec['target'] = $record->target();
                $ip = gethostbyname($rec['target']);
                $result=getdata("https://api.ip2location.io/?key=31E410CAC7ED00ADE750E7241B35C1CF&ip=".$ip);
                $rec['ip'] = $ip;
                $rec['country'] = $result->country_code;
                $rec['asn'] = 'ASN'.$result->asn;
                $rec['as'] = $result->as;
            }
            if ($record->type() == "SOA") {
                $rec['mname'] = $record->mname();
                $rec['rname'] = $record->mname();
                $rec['serial'] = $record->serial();
                $rec['refresh'] = $record->refresh();
                $rec['retry'] = $record->retry();
                $rec['expire'] = $record->expire();
                $rec['minimum_ttl'] = $record->minimum_ttl();
            }
            if ($record->type() == "TXT")
                $rec['txt'] = $record->txt();                        
            if ($record->type() == "NS") {
                $rec['target'] = $record->target();
                $ip = gethostbyname($rec['target']);
                $result=getdata("https://api.ip2location.io/?key=31E410CAC7ED00ADE750E7241B35C1CF&ip=".$ip);
                $rec['ip'] = $ip;
                $rec['country'] = $result->country_code;
                $rec['asn'] = 'ASN'.$result->asn;
                $rec['as'] = $result->as;
            }

            array_push($data, $rec);
        }

        // $whoisserver = LookupDomainName($domain);
        // $ip = gethostbyname($domain);

        // $response = \Location::get($ip);
        // $countryCode = $response->countryCode;

        return response(['records' => $data]);
    }
}
