<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 08.05.13
 * Time: 5:33
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
// "cool-proxy.net"=>"http://cool-proxy.net/proxies/http_proxy_list/page:",
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;
$url_source="http://cool-proxy.net/proxies/http_proxy_list/page:";
$name_source="cool-proxy.net";
$get_cool_proxy_content= new c_get_content();
$get_cool_proxy_content->set_type_content("html");
$i=1;
if(!$content=$get_cool_proxy_content->get_content($url_source.$i."/sort:working_average/direction:asc")) return array();
if(preg_match_all('#/proxies/http_proxy_list/sort:working_average/direction:asc/page:(?<pagenation>\d*)"#iUm', $content, $matches))
{
    rsort($matches['pagenation']);
    $count_page=$matches['pagenation'][0];
}
else return array();
unset($matches);
do{
    if(preg_match_all('#<td\s*style=\"text.align.left.\s*font.weight.bold.\">(.*)</td>\s*<td>(\d+)</td>#iUm', $content, $matches))
    {
        for($j=0;$j<count($matches[1]);$j++)
        {
            $reg="/<span class=\"\d+\">(\d+)<\/span>/iU";
            if(preg_match_all($reg, $matches[1][$j], $matches_proxy))
            {
                $reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
                $is_ip=$matches_proxy[1][0].".".$matches_proxy[1][1].".".$matches_proxy[1][2].".".$matches_proxy[1][3].":".$matches[2][$j];
                if(preg_match($reg,$is_ip))
                {
                    $tmp_array['proxy']=trim($is_ip);
                    $tmp_array["source_proxy"]=$name_source;
                    $tmp_array["type_proxy"]='http';
                    $proxy['content'][]=$tmp_array;
                }
            }
        }
    }
    $i++;
    if(!$content=$get_cool_proxy_content->get_content($url_source.$i."/sort:working_average/direction:asc")) continue;
}while($i<=$count_page);