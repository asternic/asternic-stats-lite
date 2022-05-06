<?php
/*
   Copyright 2007, 2020 Nicolás Gudiño

   This file is part of Asternic Call Center Stats.

    Asternic Call Center Stats is free software: you can redistribute it 
    and/or modify it under the terms of the GNU General Public License as 
    published by the Free Software Foundation, either version 3 of the 
    License, or (at your option) any later version.

    Asternic Call Center Stats is distributed in the hope that it will be 
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Asternic Call Center Stats.  If not, see 
    <http://www.gnu.org/licenses/>.
*/

require_once("config.php");
include("sesvars.php");
?>
<!doctype html>
            
<html lang="en">
<head>      
  <meta charset="utf-8">
            
  <title>Asternic Call Center Stats</title>
  <meta name="description" content="Asternic Call Center Stats Lite Reports">
  <meta name="author" content="asternic.net">
            
  <link rel="stylesheet" href="css/basic.css">
  <link rel="stylesheet" href="css/tab.css">
  <link rel="stylesheet" href="css/table.css">
  <link rel="stylesheet" href="css/fixed-all.css">
                
  <script src="js/sorttable.js"></script>
  <script src="js/Chart.min.js"></script>
                
</head>         
<?php

$graphcolor = "&bgcolor=0xF0ffff&bgcolorchart=0xdfedf3&fade1=ff6600&fade2=ff6314&colorbase=0xfff3b3&reverse=1";
$graphcolorstack = "&bgcolor=0xF0ffff&bgcolorchart=0xdfedf3&fade1=ff6600&colorbase=fff3b3&reverse=1&fade2=0x528252";

// ABANDONED CALLS

$query = "SELECT qs.datetime AS datetime, q.queue AS qname, ag.agent AS qagent, ac.event AS qevent, ";
$query.= "qs.info1 AS info1, qs.info2 AS info2,  qs.info3 AS info3 FROM queue_stats AS qs, qname AS q, ";
$query.= "qagent AS ag, qevent AS ac WHERE qs.qname = q.qname_id AND qs.qagent = ag.agent_id AND ";
$query.= "qs.qevent = ac.event_id AND qs.datetime >= '%s' AND qs.datetime <= '%s' ";
$query.= "AND q.queue IN ($queue,'NONE') AND ac.event IN ('ABANDON', 'EXITWITHTIMEOUT','COMPLETECALLER','COMPLETEAGENT','AGENTLOGIN','AGENTLOGOFF','AGENTCALLBACKLOGIN','AGENTCALLBACKLOGOFF') ";
$query.= "ORDER BY qs.datetime";

$query_comb     = "";
$login          = 0;
$logoff         = 0;
$dias           = Array();
$logout_by_day  = Array();
$logout_by_hour = Array();
$logout_by_dw   = Array();
$login_by_day   = Array();
$login_by_hour  = Array();
$login_by_dw    = Array();

$res = $midb->consulta($query,array($start,$end));

if($midb->num_rows($res)>0) {

    while($row=$midb->fetch_row($res)) {
        $partes_fecha = preg_split("/ /",$row[0]);
        $partes_hora  = preg_split("/:/",$partes_fecha[1]);

        $timestamp = return_timestamp($row[0]);
        $day_of_week = date('w',$timestamp);
            
        $dias[] = $partes_fecha[0];
        $horas[] = $partes_hora[0];

        if($row[3]=="ABANDON" || $row[3]=="EXITWITHTIMEOUT") {
             $unanswered++;
            $unans_by_day["$partes_fecha[0]"]++;
            $unans_by_hour["$partes_hora[0]"]++;
            $unans_by_dw["$day_of_week"]++;
        }
        if($row[3]=="COMPLETECALLER" || $row[3]=="COMPLETEAGENT") {
             $answered++;
            $ans_by_day["$partes_fecha[0]"]++;
            $ans_by_hour["$partes_hora[0]"]++;
            $ans_by_dw["$day_of_week"]++;

            $total_time_by_day["$partes_fecha[0]"]+=$row[5];
            $total_hold_by_day["$partes_fecha[0]"]+=$row[4];

            $total_time_by_dw["$day_of_week"]+=$row[5];
            $total_hold_by_dw["$day_of_week"]+=$row[4];
        
            $total_time_by_hour["$partes_hora[0]"]+=$row[5];
            $total_hold_by_hour["$partes_hora[0]"]+=$row[4];
        }
        if($row[3]=="AGENTLOGIN" || $row[3]=="AGENTCALLBACKLOGIN") {
             $login++;
            $login_by_day["$partes_fecha[0]"]++;
            $login_by_hour["$partes_hora[0]"]++;
            $login_by_dw["$day_of_week"]++;
        }
        if($row[3]=="AGENTLOGOFF" || $row[3]=="AGENTCALLBACKLOGOFF") {
             $logoff++;
            $logout_by_day["$partes_fecha[0]"]++;
            $logout_by_hour["$partes_hora[0]"]++;
            $logout_by_dw["$day_of_week"]++;
        }
    }
    $total_calls = $answered + $unanswered;
    $dias  = array_unique($dias);
    $horas = array_unique($horas);
    asort($dias);
    asort($horas);
} else {
     // No rows returned
    $answered = 0;
    $unanswered = 0;
}


$start_parts = preg_split("/ /", $start);
$end_parts   = preg_split("/ /", $end);

$cover_pdf = $lang["$language"]['queue'].": ".$queue."\n";
$cover_pdf.= $lang["$language"]['start'].": ".$start_parts[0]."\n";
$cover_pdf.= $lang["$language"]['end'].": ".$end_parts[0]."\n";
$cover_pdf.= $lang["$language"]['period'].": ".$period." ".$lang["$language"]['days']."\n\n";
$cover_pdf.= $lang["$language"]['number_answered'].": ".$answered." ".$lang["$language"]['calls']."\n";
$cover_pdf.= $lang["$language"]['number_unanswered'].": ".$unanswered." ".$lang["$language"]['calls']."\n";
$cover_pdf.= $lang["$language"]['agent_login'].": ".$login."\n";
$cover_pdf.= $lang["$language"]['agent_logoff'].": ".$logoff."\n";
?>
<body>
<?php include("menu.php"); ?>
<div id="main">
    <div id="contents">
        <table style="width: 99%; border-collapse: separate; border-spacing: 1px;">
        <thead>
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table style='width: 100%; border: 0; border-collapse: collapse; border-spacing: 0;'>
                <caption><?php echo $lang["$language"]['report_info']?></caption>
                <tbody>
                <tr>
                    <td><?php echo $lang["$language"]['queue']?>:</td>
                    <td><?php echo $queue?></td>
                </tr>
                <tr>
                       <td><?php echo $lang["$language"]['start']?>:</td>
                       <td><?php echo $start_parts[0]?></td>
                </tr>
                <tr>
                       <td><?php echo $lang["$language"]['end']?>:</td>
                       <td><?php echo $end_parts[0]?></td>
                </tr>
                <tr>
                       <td><?php echo $lang["$language"]['period']?>:</td>
                       <td><?php echo $period?> <?php echo $lang["$language"]['days']?></td>
                </tr>
                </tbody>
                </table>

            </td>
            <td style="width: 50%; vertical-align: top;">
                <table style='width: 100%; border: 0; border-collapse: collapse; border-spacing: 0;'>
                <caption><?php echo $lang["$language"]['totals']?></caption>
                <tbody>
                <tr> 
                  <td><?php echo $lang["$language"]['number_answered']?>:</td>
                  <td><?php echo $answered?> <?php echo $lang["$language"]['calls']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['number_unanswered']?>:</td>
                  <td><?php echo $unanswered?> <?php echo $lang["$language"]['calls']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['agent_login']?>:</td>
                  <td><?php echo $login?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['agent_logoff']?>:</td>
                  <td><?php echo $logoff?></td>
                </tr>
                </tbody>
              </table>
            </td>
        </tr>
        </thead>
        </table>
        <br/>    
            <?php
                if(count($dias)<=0) {
                    $dias['']=0;
                }
            ?>
            <a id='1'></a>
            <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table1' >
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' width=16 height=16 class='icon' 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['call_distrib_day']?>
            </caption>
                <thead>
                <tr>
                    <th><?php echo $lang["$language"]['date']?></th>
                    <th><?php echo $lang["$language"]['answered']?></th>
                    <th><?php echo $lang["$language"]['percent_answered']?></th>
                    <th><?php echo $lang["$language"]['unanswered']?></th>
                    <th><?php echo $lang["$language"]['percent_unanswered']?></th>
                    <th><?php echo $lang["$language"]['avg_calltime']?></th>
                    <th><?php echo $lang["$language"]['avg_holdtime']?></th>
                    <th><?php echo $lang["$language"]['login']?></th>
                    <th><?php echo $lang["$language"]['logoff']?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $header_pdf=array($lang["$language"]['date'],$lang["$language"]['answered'],$lang["$language"]['percent_answered'],$lang["$language"]['unanswered'],$lang["$language"]['percent_unanswered'],$lang["$language"]['avg_calltime'],$lang["$language"]['avg_holdtime'],$lang["$language"]['login'],$lang["$language"]['logoff']);
                $width_pdf=array(25,23,23,23,23,25,25,20,20);
                $title_pdf=$lang["$language"]['call_distrib_day'];

                $count=1;
                foreach($dias as $key) {
                    $cual = $count%2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    if(!isset($ans_by_day["$key"])) {
                        $ans_by_day["$key"]=0;
                    }
                    if(!isset($unans_by_day["$key"])) {
                        $unans_by_day["$key"]=0;
                    }
                    if($answered > 0) {
                        $percent_ans   = $ans_by_day["$key"]   * 100 / $answered;
                    } else {
                        $percent_ans = 0;
                    }
                    if($ans_by_day["$key"] >0) {
                        $average_call_duration = $total_time_by_day["$key"] / $ans_by_day["$key"];
                        $average_hold_duration = $total_hold_by_day["$key"] / $ans_by_day["$key"];
                    } else {
                        $average_call_duration = 0;
                        $average_hold_duration = 0;
                    }
                    if($unanswered > 0) {
                        $percent_unans = $unans_by_day["$key"] * 100 / $unanswered;
                    } else {
                        $percent_unans = 0;
                    }
                    $percent_ans   = number_format($percent_ans,  2);
                    $percent_unans = number_format($percent_unans,2);
                    $average_call_duration_print = seconds2minutes($average_call_duration);
                    if($key<>"") {
                    $linea_pdf = array($key,$ans_by_day["$key"],"$percent_ans ".$lang["$language"]['percent'],$unans_by_day["$key"],"$percent_unans ".$lang["$language"]['percent'],$average_call_duration_print,number_format($average_hold_duration,0),$login_by_day["$key"],$logout_by_day["$key"]);

                    echo "<tr $odd>\n";
                    echo "<td>$key</td>\n";
                    echo "<td>".$ans_by_day["$key"]."</td>\n";
                    echo "<td>$percent_ans ".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".$unans_by_day["$key"]."</td>\n";
                    echo "<td>$percent_unans".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".$average_call_duration_print." ".$lang["$language"]['minutes']."</td>\n";
                    echo "<td>".number_format($average_hold_duration,0)." ".$lang["$language"]['secs']."</td>\n";
                    echo "<td>".$login_by_day["$key"]."</td>\n";
                    echo "<td>".$logout_by_day["$key"]."</td>\n";
                    echo "</tr>\n";
                    $count++;
                    $data_pdf[]=$linea_pdf;
                    }
                }
                ?>
            </tbody>
            </table>
            
            <?php
                if($count>1) {
                    print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf); 
                }
            ?>
            <br/>
            
            <a id='2'></a>
            <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table2' >
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' width=16 height=16 class='icon' 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['call_distrib_hour']?>
            </caption>
                <thead>
                <tr>
                                  <th><?php echo $lang["$language"]['hour']?></th>
                                  <th><?php echo $lang["$language"]['answered']?></th>
                                  <th><?php echo $lang["$language"]['percent_answered']?></th>
                                  <th><?php echo $lang["$language"]['unanswered']?></th>
                                  <th><?php echo $lang["$language"]['percent_unanswered']?></th>
                                  <th><?php echo $lang["$language"]['avg_calltime']?></th>
                                  <th><?php echo $lang["$language"]['avg_holdtime']?></th>
                                  <th><?php echo $lang["$language"]['login']?></th>
                                  <th><?php echo $lang["$language"]['logoff']?></th>
                </tr>
                </thead>
                <tbody>
                <?php

                $header_pdf=array($lang["$language"]['hour'],$lang["$language"]['answered'],$lang["$language"]['percent_answered'],$lang["$language"]['unanswered'],$lang["$language"]['percent_unanswered'],$lang["$language"]['avg_calltime'],$lang["$language"]['avg_holdtime'],$lang["$language"]['login'],$lang["$language"]['logoff']);
                $width_pdf=array(25,23,23,23,23,25,25,20,20);
                $title_pdf=$lang["$language"]['call_distrib_hour'];
                $data_pdf = array();

                $query_ans = "";
                $query_unans = "";
                $query_time="";
                $query_hold="";
                for($key=0;$key<24;$key++) {
                    $cual = ($key+1)%2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    if(strlen($key)==1) { $key = "0".$key; }
                    if(!isset($ans_by_hour["$key"])) {
                        $ans_by_hour["$key"]=0;
                        $average_call_duration = 0;
                        $average_hold_duration = 0;
                    } else {
                        $average_call_duration = $total_time_by_hour["$key"] / $ans_by_hour["$key"];
                        $average_hold_duration = $total_hold_by_hour["$key"] / $ans_by_hour["$key"];
                    }
                    if(!isset($unans_by_hour["$key"])) {
                        $unans_by_hour["$key"]=0;
                    }
                    if($answered > 0) {
                        $percent_ans   = $ans_by_hour["$key"]   * 100 / $answered;
                    } else {
                        $percent_ans = 0;
                    }
                    if($unanswered > 0) {
                        $percent_unans = $unans_by_hour["$key"] * 100 / $unanswered;
                    } else {
                        $percent_unans = 0;
                    }
                    $percent_ans   = number_format($percent_ans,  2);
                    $percent_unans = number_format($percent_unans,2);

                    if(!isset($login_by_hour["$key"])) {
                        $login_by_hour["$key"]=0;
                    }
                    if(!isset($logout_by_hour["$key"])) {
                        $logout_by_hour["$key"]=0;
                    }

                    $linea_pdf = array($key,$ans_by_hour["$key"],"$percent_ans ".$lang["$language"]['percent'],$unans_by_hour["$key"],"$percent_unans ".$lang["$language"]['percent'],number_format($average_call_duration,0),number_format($average_hold_duration,0),$login_by_hour["$key"],$logout_by_hour["$key"]);

                    echo "<tr $odd>\n";
                    echo "<td>$key</td>\n";
                    echo "<td>".$ans_by_hour["$key"]."</td>\n";
                    echo "<td>$percent_ans".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".$unans_by_hour["$key"]."</td>\n";
                    echo "<td>$percent_unans".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".number_format($average_call_duration,0)." ".$lang["$language"]['secs']."</td>\n";
                    echo "<td>".number_format($average_hold_duration,0)." ".$lang["$language"]['secs']."</td>\n";
                    echo "<td>".$login_by_hour["$key"]."</td>\n";
                    echo "<td>".$logout_by_hour["$key"]."</td>\n";
                    echo "</tr>\n";
                    $gkey = $key+1;
                    $query_ans  .="var$gkey=$key&val$gkey=".$ans_by_hour["$key"]."&";
                    $query_unans.="var$gkey=$key&val$gkey=".$unans_by_hour["$key"]."&";
                    $query_comb.= "var$gkey=$key%20".$lang["$language"]['hours']."&valA$gkey=".$ans_by_hour["$key"]."&valB$gkey=".$unans_by_hour["$key"]."&";
                    $query_time.="var$gkey=$key&val$gkey=".intval($average_call_duration)."&";
                    $query_hold.="var$gkey=$key&val$gkey=".intval($average_hold_duration)."&";
                    $data_pdf[]=$linea_pdf;
                }
                $query_ans.="title=".$lang["$language"]['answ_by_hour']."$graphcolor";
                $query_unans.="title=".$lang["$language"]['unansw_by_hour']."$graphcolor";
                $query_time.="title=".$lang["$language"]['avg_call_time_by_hr']."$graphcolor";
                $query_hold.="title=".$lang["$language"]['avg_hold_time_by_hr']."$graphcolor";
                $query_comb.="title=".$lang["$language"]['anws_unanws_by_hour']."$graphcolorstack&tagA=".$lang["$language"]['answered_calls']."&tagB=".$lang["$language"]['unanswered_calls'];
                ?>
            </tbody>
            </table>
            <?php
                print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf); 
            ?>

            <br/>

            <table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>
            <thead>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                <hr>
                </td>
            </tr>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_comb,'718','433',"chart1",1);
                    ?>
                </td>
            </tr>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_time,'718','433',"chart3",0);
                    ?>
                </td>
            </tr>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_hold,'718','433',"chart4",0);
                    ?>
                </td>
            </tr>
            </thead>
            </table>

            <br/>

            <a id='3'></a>
            <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table3' >
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' width=16 height=16 class='icon' 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;

            <?php echo $lang["$language"]['call_distrib_week']?>
            </caption>
                <thead>
                <tr>
                    <th><?php echo $lang["$language"]['day']?></th>
                    <th><?php echo $lang["$language"]['answered']?></th>
                    <th><?php echo $lang["$language"]['percent_answered']?></th>
                    <th><?php echo $lang["$language"]['unanswered']?></th>
                    <th><?php echo $lang["$language"]['percent_unanswered']?></th>
                    <th><?php echo $lang["$language"]['avg_calltime']?></th>
                    <th><?php echo $lang["$language"]['avg_holdtime']?></th>
                    <th><?php echo $lang["$language"]['login']?></th>
                    <th><?php echo $lang["$language"]['logoff']?></th>
                </tr>
                </thead>
                <tbody>
                <?php

                $header_pdf=array($lang["$language"]['day'],$lang["$language"]['answered'],$lang["$language"]['percent_answered'],$lang["$language"]['unanswered'],$lang["$language"]['percent_unanswered'],$lang["$language"]['avg_calltime'],$lang["$language"]['avg_holdtime'],$lang["$language"]['login'],$lang["$language"]['logoff']);
                $width_pdf=array(25,23,23,23,23,25,25,20,20);
                $title_pdf=$lang["$language"]['call_distrib_week'];
                $data_pdf = array();


                $query_ans="";
                $query_unans="";
                $query_time="";
                $query_hold="";
                for($key=0;$key<7;$key++) {
                    $cual = ($key+1)%2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    if(!isset($total_time_by_dw["$key"])) {
                        $total_time_by_dw["$key"]=0;
                    }
                    if(!isset($total_hold_by_dw["$key"])) {
                        $total_hold_by_dw["$key"]=0;
                    }
                    if(!isset($ans_by_dw["$key"])) {
                        $ans_by_dw["$key"]=0;
                        $average_call_duration = 0;
                        $average_hold_duration = 0;
                    } else {
                        $average_call_duration = $total_time_by_dw["$key"] / $ans_by_dw["$key"];
                        $average_hold_duration = $total_hold_by_dw["$key"] / $ans_by_dw["$key"];
                    }

                    if(!isset($unans_by_dw["$key"])) {
                        $unans_by_dw["$key"]=0;
                    }
                    if($answered > 0) {
                        $percent_ans   = $ans_by_dw["$key"]   * 100 / $answered;
                    } else {
                        $percent_ans = 0;
                    }
                    if($unanswered > 0) {
                        $percent_unans = $unans_by_dw["$key"] * 100 / $unanswered;
                    } else {
                        $percent_unans = 0;
                    }
                    $percent_ans   = number_format($percent_ans,  2);
                    $percent_unans = number_format($percent_unans,2);

                    if(!isset($login_by_dw["$key"])) {
                        $login_by_dw["$key"]=0;
                    }
                    if(!isset($logout_by_dw["$key"])) {
                        $logout_by_dw["$key"]=0;
                    }

                    $linea_pdf = array($dayp["$key"],$ans_by_dw["$key"],"$percent_ans ".$lang["$language"]['percent'],$unans_by_dw["$key"],"$percent_unans ".$lang["$language"]['percent'],number_format($average_call_duration,0),number_format($average_hold_duration,0),$login_by_dw["$key"],$logout_by_dw["$key"]);

                    echo "<tr $odd>\n";
                    echo "<td>".$dayp["$key"]."</td>\n";
                    echo "<td>".$ans_by_dw["$key"]."</td>\n";
                    echo "<td>$percent_ans".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".$unans_by_dw["$key"]."</td>\n";
                    echo "<td>$percent_unans".$lang["$language"]['percent']."</td>\n";
                    echo "<td>".number_format($average_call_duration,0)." secs</td>\n";
                    echo "<td>".number_format($average_hold_duration,0)." secs</td>\n";
                    echo "<td>".$login_by_dw["$key"]."</td>\n";
                    echo "<td>".$logout_by_dw["$key"]."</td>\n";
                    echo "</tr>\n";
                    $gkey = $key + 1;
                    $query_ans  .="var$gkey=".$dayp["$key"]."&val$gkey=".intval($ans_by_dw["$key"])."&";
                    $query_unans.="var$gkey=".$dayp["$key"]."&val$gkey=".intval($unans_by_dw["$key"])."&";
                    $query_time.="var$gkey=".$dayp["$key"]."&val$gkey=".intval($average_call_duration)."&";
                    $query_hold.="var$gkey=".$dayp["$key"]."&val$gkey=".intval($average_hold_duration)."&";
                    $data_pdf[]=$linea_pdf;
                }
                $query_ans.="title=".$lang["$language"]['answ_by_day']."$graphcolor";
                $query_unans.="title=".$lang["$language"]['unansw_by_day']."$graphcolor";
                $query_time.="title=".$lang["$language"]['avg_call_time_by_day']."$graphcolor";
                $query_hold.="title=".$lang["$language"]['avg_hold_time_by_day']."$graphcolor";
                ?>
            </tbody>
            </table>
            <?php
                print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf); 
            ?>
            <br/>

            <table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>
            <thead>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_ans,359,217,"chart5",0);
                    ?>
                </td>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_unans,359,217,"chart6",0);
                    ?>
                </td>
            </tr>
            <tr>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_time,359,217,"chart7",0);
                    ?>
                </td>
                <td style='text-align:center; background-color: #fffdf3;'>
                    <?php
                    swf_bar($query_hold,359,217,"chart8",0);
                    ?>
                </td>
            </tr>
            </thead>
            </table>

</div>
</div>
</div>
<div id='footer'>&copy; Copyright 2008 - <?php echo date('Y');?> by Nicol&aacute;s Gudi&ntilde;o - <a href='http://www.asternic.net'>Asternic Asterisk Tools</a> Licensed under <a href='http://www.opensource.org/licenses/gpl-3.0.html'>GPL3</a></div>
<script src="js/wz_tooltip.js"></script>
</body>
</html>
