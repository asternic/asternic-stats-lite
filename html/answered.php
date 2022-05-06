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

// This query shows the hangup cause, how many calls an
// agent hanged up, and a caller hanged up.
$query = "SELECT count(ev.event) AS num, ev.event AS action ";
$query.= "FROM queue_stats AS qs, qname AS q, qevent AS ev WHERE ";
$query.= "qs.qname = q.qname_id and qs.qevent = ev.event_id and qs.datetime >= '%s' and ";
$query.= "qs.datetime <= '%s' and q.queue IN ($queue) AND ";
$query.= "ev.event IN ('COMPLETECALLER', 'COMPLETEAGENT') ";
$query.= "GROUP BY ev.event ORDER BY ev.event";

$hangup_cause["COMPLETECALLER"]=0;
$hangup_cause["COMPLETEAGENT"]=0;
$res = $midb->consulta($query,array($start,$end));
while($row=$midb->fetch_row($res)) {
  $hangup_cause["$row[1]"]=$row[0];
  $total_hangup+=$row[0];
}

$query = "SELECT qs.datetime AS datetime, q.queue AS qname, ag.agent AS qagent, "; 
$query.= "ac.event AS qevent, qs.info1 AS info1, qs.info2 AS info2,  qs.info3 AS info3 ";
$query.= "FROM queue_stats AS qs, qname AS q, qagent AS ag, qevent AS ac WHERE ";
$query.= "qs.qname = q.qname_id AND qs.qagent = ag.agent_id AND qs.qevent = ac.event_id AND ";
$query.= "qs.datetime >= '%s' AND qs.datetime <= '%s' AND ";
$query.= "q.queue IN ($queue) AND ag.agent in ($agent) AND ac.event IN ('COMPLETECALLER', 'COMPLETEAGENT','trANSFER','CONNECT') ORDER BY qs.datetime";

$answer["15"]=0;
$answer["30"]=0;
$answer["45"]=0;
$answer["60"]=0;
$answer["75"]=0;
$answer["90"]=0;
$answer["91+"]=0;

$abandoned         = 0;
$transferidas      = 0;
$totaltransfers    = 0;
$total_hangup      = 0;
$total_calls       = 0;
$total_calls2      = Array();
$total_duration    = 0;
$total_calls_queue = Array();

$res = $midb->consulta($query,array($start,$end));
if($res) {
    while($row=$midb->fetch_row($res)) {
        if($row[3] <> "trANSFER" && $row[3]<>"CONNECT") {
            $total_hold     += $row[4];
            $total_duration += $row[5];
            $total_calls++;
            $total_calls_queue["$row[1]"]++;
        } elseif($row[3]=="trANSFER") {
            $transferidas++;
        }
        if($row[3]=="CONNECT") {

            if ($row[4] >=0 && $row[4] <= 15) {
                $answer["15"]++;
            }

            if ($row[4] >=16 && $row[4] <= 30) {
                $answer["30"]++;
            }

            if ($row[4] >=31 && $row[4] <= 45) {
              $answer["45"]++;
            }

            if ($row[4] >=46 && $row[4] <= 60) {
              $answer["60"]++;
            }

            if ($row[4] >=61 && $row[4] <= 75) {
              $answer["75"]++;
            }

            if ($row[4] >=76 && $row[4] <= 90) {
              $answer["90"]++;
            }

            if ($row[4] >=91) {
              $answer["91+"]++;
            }
        }
    }
} 

if($total_calls > 0) {
    ksort($answer);
    $average_hold     = $total_hold     / $total_calls;
    $average_duration = $total_duration / $total_calls;
    $average_hold     = number_format($average_hold     , 2);
    $average_duration = number_format($average_duration , 2);
} else {
    // There were no calls
    $average_hold = 0;
    $average_duration = 0;
}

$total_duration_print = seconds2minutes($total_duration);
// trANSFERS
$query = "SELECT ag.agent AS agent, qs.info1 AS info1,  qs.info2 AS info2 ";
$query.= "FROM  queue_stats AS qs, qevent AS ac, qagent as ag, qname As q WHERE qs.qevent = ac.event_id ";
$query.= "AND qs.qname = q.qname_id AND ag.agent_id = qs.qagent AND qs.datetime >= '%s' ";
$query.= "AND qs.datetime <= '%s' AND  q.queue IN ($queue)  AND ag.agent in ($agent) AND  ac.event = 'TRANSFER'";


$res = $midb->consulta($query,array($start,$end));
if($res) {
    while($row=$midb->fetch_row($res)) {
        $keytra = "$row[0]^$row[1]@$row[2]";
        $transfers["$keytra"]++;
        $totaltransfers++;
    }
} else {
   $totaltransfers=0;
}

// ABANDONED CALLS
$query = "SELECT  ac.event AS action,  qs.info1 AS info1,  qs.info2 AS info2,  qs.info3 AS info3 ";
$query.= "FROM  queue_stats AS qs, qevent AS ac, qname As q, qagent as ag WHERE ";
$query.= "qs.qevent = ac.event_id AND qs.qname = q.qname_id AND qs.datetime >= '%s' AND ";
$query.= "qs.datetime <= '%s' AND  q.queue IN ($queue)  AND ag.agent in ($agent) AND  ac.event IN ('ABANDON', 'EXITWITHTIMEOUT', 'TRANSFER') ";
$query.= "ORDER BY  ac.event,  qs.info3";

$res = $midb->consulta($query,array($start,$end));

while($row=$midb->fetch_row($res)) {

    if($row[0]=="ABANDON") {
        $abandoned++;
        $abandon_end_pos+=$row[1];
        $abandon_start_pos+=$row[2];
        $total_hold_abandon+=$row[3];
    }
    if($row[0]=="EXITWITHTIMEOUT") {
        $timeout++;
        $timeout_end_pos+=$row[1];
        $timeout_start_pos+=$row[2];
        $total_hold_timeout+=$row[3];
    }
}

if($abandoned > 0) {
    $abandon_average_hold = $total_hold_abandon / $abandoned;
    $abandon_average_hold = number_format($abandon_average_hold,2);

    $abandon_average_start = floor($abandon_start_pos / $abandoned);
    $abandon_average_start = number_format($abandon_average_start,2);

    $abandon_average_end = floor($abandon_end_pos / $abandoned);
    $abandon_average_end = number_format($abandon_average_end,2);
} else {
    $abandoned = 0;
    $abandon_average_hold  = 0;
    $abandon_average_start = 0;
    $abandon_average_end   = 0;
}

// This query shows every call for agents, we collect into a named array the values of holdtime and calltime
$query = "SELECT qs.datetime AS datetime, q.queue AS qname, ag.agent AS qagent, ac.event AS qevent, ";
$query.= "qs.info1 AS info1, qs.info2 AS info2, qs.info3 AS info3  FROM queue_stats AS qs, qname AS q, ";
$query.= "qagent AS ag, qevent AS ac WHERE qs.qname = q.qname_id AND qs.qagent = ag.agent_id AND ";
$query.= "qs.qevent = ac.event_id AND qs.datetime >= '%s' AND qs.datetime <= '%s' AND ";
$query.= "q.queue IN ($queue) AND ag.agent in ($agent) AND ac.event IN ('COMPLETECALLER', 'COMPLETEAGENT') ORDER BY ag.agent";

$res = $midb->consulta($query,array($start,$end));
while($row=$midb->fetch_row($res)) {
    $total_calls2["$row[2]"]++;
    $record["$row[2]"][]=$row[0]."|".$row[1]."|".$row[3]."|".$row[4];
    $total_hold2["$row[2]"]+=$row[4];
    $total_time2["$row[2]"]+=$row[5];
    $grandtotal_hold+=$row[4];
    $grandtotal_time+=$row[5];
    $grandtotal_calls++;
}

$start_parts = preg_split("/ /", $start);
$end_parts   = preg_split("/ /", $end);

$cover_pdf = $lang["$language"]['queue'].": ".$queue."\n";
$cover_pdf.= $lang["$language"]['start'].": ".$start_parts[0]."\n";
$cover_pdf.= $lang["$language"]['end'].": ".$end_parts[0]."\n";
$cover_pdf.= $lang["$language"]['period'].": ".$period." ".$lang["$language"]['days']."\n\n";
$cover_pdf.= $lang["$language"]['answered_calls'].": ".$total_calls." ".$lang["$language"]['calls']."\n";
$cover_pdf.= $lang["$language"]['avg_calltime'].": ".$average_duration." ".$lang["$language"]['secs']."\n";
$cover_pdf.= $lang["$language"]['total'].": ".$total_duration_print." ".$lang["$language"]['minutes']."\n";
$cover_pdf.= $lang["$language"]['avg_holdtime'].": ".$average_hold." ".$lang["$language"]['secs']."\n";

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
                <caption><?php echo $lang["$language"]['answered_calls']?></caption>
                <tbody>
                <tr> 
                  <td><?php echo $lang["$language"]['answered_calls']?></td>
                  <td><?php echo $total_calls?> <?php echo $lang["$language"]['calls']?></td>
                </tr>
                <tr> 
                  <td><?php echo $lang["$language"]['transferred_calls']?></td>
                  <td><?php echo $transferidas?> <?php echo $lang["$language"]['calls']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['avg_calltime']?>:</td>
                  <td><?php echo $average_duration?> <?php echo $lang["$language"]['secs']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['total']?> <?php echo $lang["$language"]['calltime']?>:</td>
                  <td><?php echo $total_duration_print?> <?php echo $lang["$language"]['minutes']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['avg_holdtime']?>:</td>
                  <td><?php echo $average_hold?> <?php echo $lang["$language"]['secs']?></td>
                </tr>
                </tbody>
              </table>
            </td>
        </tr>
        </thead>
        </table>
        <br/>    
        <a id='1'></a>
        <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table1' >
        <caption>
        <a href='#0'><img alt='go up' src='images/go-up.png' class='icon' width=16 height=16 
        <?php 
        tooltip($lang["$language"]['gotop'],200);
        ?> 
        ></a>&nbsp;&nbsp;
        <?php echo $lang["$language"]['answered_calls_by_agent']?>
        </caption>
            <thead>
            <tr>
                  <th><?php echo $lang["$language"]['agent']?></th>
                  <th><?php echo $lang["$language"]['Calls']?></th>
                  <th><?php echo $lang["$language"]['percent']?> <?php echo $lang["$language"]['Calls']?></th>
                  <th><?php echo $lang["$language"]['calltime']?></th>
                  <th><?php echo $lang["$language"]['percent']?> <?php echo $lang["$language"]['calltime']?></th>
                  <th><?php echo $lang["$language"]['avg']?> <?php echo $lang["$language"]['calltime']?></th>
                  <th><?php echo $lang["$language"]['holdtime']?></th>
                  <th><?php echo $lang["$language"]['avg']?> <?php echo $lang["$language"]['holdtime']?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                $header_pdf=array($lang["$language"]['agent'],$lang["$language"]['Calls'],$lang["$language"]['percent'],$lang["$language"]['calltime'],$lang["$language"]['percent'],$lang["$language"]['avg'],$lang["$language"]['holdtime'],$lang["$language"]['avg']);
                $width_pdf=array(25,23,23,23,23,25,25,20);
                $title_pdf=$lang["$language"]['answered_calls_by_agent'];

                $contador=0;
                $query1 = "";
                $query2 = "";
                $data_pdf = array();
                if($total_calls2>0) {
                foreach($total_calls2 as $agent=>$val) {
                    $contavar = $contador +1;
                    $cual = $contador % 2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    $query1 .= "val$contavar=".$total_time2["$agent"]."&var$contavar=$agent&";
                    $query2 .= "val$contavar=".$val."&var$contavar=$agent&";

                    $time_print = seconds2minutes($total_time2["$agent"]);
                    $avg_time = $total_time2["$agent"] / $val;
                    $avg_time = round($avg_time,2);

                    $avg_print = seconds2minutes($avg_time);

                    echo "<tr $odd>\n";
                    echo "<td>$agent</td>\n";
                    echo "<td>$val</td>\n";
                    if($grandtotal_calls > 0) {
                       $percentage_calls = $val * 100 / $grandtotal_calls;
                    } else {
                       $percentage_calls = 0;
                    }
                    $percentage_calls = number_format($percentage_calls,2);
                    echo "<td>$percentage_calls ".$lang["$language"]['percent']."</td>\n";
                    echo "<td>$time_print ".$lang["$language"]['minutes']."</td>\n";
                    if($grandtotal_time > 0) {
                       $percentage_time = $total_time2["$agent"] * 100 / $grandtotal_time;
                    } else {
                       $percentage_time = 0;
                    }
                    $percentage_time = number_format($percentage_time,2);
                    echo "<td>$percentage_time ".$lang["$language"]['percent']."</td>\n";
                    //echo "<td>$avg_time ".$lang["$language"]['secs']."</td>\n";
                    echo "<td>$avg_print ".$lang["$language"]['minutes']."</td>\n";
                    echo "<td>".$total_hold2["$agent"]." ".$lang["$language"]['secs']."</td>\n";
                    $avg_hold = $total_hold2["$agent"] / $val;
                    $avg_hold = number_format($avg_hold,2);
                    echo "<td>$avg_hold ".$lang["$language"]['secs']."</td>\n";
                    echo "</tr>\n";

                    $linea_pdf = array($agent,$val,"$percentage_calls ".$lang["$language"]['percent'],$total_time2["$agent"],"$percentage_time ".$lang["$language"]['percent'],"$avg_time ".$lang["$language"]['secs'],$total_hold2["$agent"]." ".$lang["$language"]['secs'], "$avg_hold ".$lang["$language"]['secs']);
                       $data_pdf[]=$linea_pdf;
                    $contador++;
                }
                
                $query1.="title=".$lang["$language"]['total_time_agent'];
                $query2.="title=".$lang["$language"]['no_calls_agent'];
                }
                ?>
            </tbody>
        </table>
            <?php if($total_calls2>0) {
                print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf);
                }
            ?>

        <br/>    
            <?php
            if($total_calls2>0) {
                echo "<table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>\n";
                echo "<thead>\n";
                echo "<tr><td style='text-align:center; background-color: #fffdf3; width: 100%;'>\n";
                swf_bar($query1,364,220,"chart1",0);
                echo "</td><td style='text-align:center; background-color: #fffdf3; width: 100%;'>\n";
                swf_bar($query2,364,220,"chart2",0);
                echo "</td></tr>\n";
                echo "</thead>\n";
                echo "</table><br/>\n";
            }
            ?>

            <a id='2'></a>
            <table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' class='icon' width=16 height=16 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['call_response']?></caption>
            <thead>
            <tr>
            <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table2' >
                <thead>
                <tr> 
                  <th><?php echo $lang["$language"]['answer']?></th>
                  <th><?php echo $lang["$language"]['count']?></th>
                  <th><?php echo $lang["$language"]['delta']?></th>
                  <th><?php echo $lang["$language"]['percent']?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $countrow=0;
                $partial_total = 0;
                $query2="";
                $total_y_transfer = $answer['15'] + $answer['30'] +  $answer['45'] + $answer['60'] +  $answer['75'] + $answer['90'] +  $answer['91+'];
                foreach($answer as $key=>$val)
                {
                    $newcont = $countrow+1;
                    $query2.="val$newcont=$val&var$newcont=$key%20".$lang["$language"]['secs']."&";
                    $cual = ($countrow%2);
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    echo "<tr $odd>\n";
                    echo "<td>".$lang["$language"]['within']."$key ".$lang["$language"]['secs']."</td>\n";
                    $delta = $val;
                    if($delta > 0) { $delta = "+".$delta;}
                    $partial_total += $val;
                    if($total_y_transfer > 0) {
                    $percent=$partial_total*100/$total_y_transfer;
                    } else {
                    $percent = 0;
                    }
                    $percent=number_format($percent,2);
                    if($countrow==0) { $delta = ""; }
                    echo "<td>$partial_total ".$lang["$language"]['calls']."</td>\n";
                    echo "<td>$delta</td>\n";
                    echo "<td>$percent ".$lang["$language"]['percent']."</td>\n";
                    echo "</tr>\n";
                    $countrow++;
                }
                $query2.="title=".$lang["$language"]['call_response'];
                ?>
                </tbody>
              </table>
              </td>
                <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <?php
                swf_bar($query2,364,220,"chart3",0);
                ?>
                </td>
            </tr>
            </thead>
          </table>
          <br/>
            <a id='3'></a>
            <table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' class='icon' width=16 height=16 

            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['answered_calls_by_queue']?></caption>
            <thead>
            <tr>
              <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table3' >
                <thead>
                <tr> 
                    <th><?php echo $lang["$language"]['queue']?></th>
                    <th><?php echo $lang["$language"]['count']?></th>
                    <th><?php echo $lang["$language"]['percent']?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $countrow=0;
                $query2="";
                if(count($total_calls_queue)==0) {
                        $total_calls_queue[""]=0;
                }
                asort($total_calls_queue);
                foreach($total_calls_queue as $key=>$val) {
                    $cual = $countrow%2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    if($total_calls>0) {
                    $percent = $val * 100 / $total_calls;
                    } else {
                    $percent=0;
                    }
                    $percent =number_format($percent,2);
                    echo "<tr $odd><td>$key</td><td>$val ".$lang["$language"]['calls']."</td><td>$percent %</td></tr>\n";
                    $countrow++;
                    $query2.="var$countrow=$key&val$countrow=$val&";
                }
                $query2.="title=".addslashes($lang[$language]['answered_calls_by_queue']);
                ?>
              </tbody>
              </table>
            </td>
            <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <?php 
                if ($countrow>1) {
                    swf_bar("$query2",364,220,"chart4",0);
                   } 
                ?>
            </td>
            </tr>
            </thead>
            </table>
            <br/>

            <a id='4'></a>
            <table style='width: 99%; border-collapse: separate; border-spacing: 1px;'>
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' class='icon' width=16 height=16 

            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['disconnect_cause']?></caption>
            <thead>
            <tr>
              <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table4' >
                <thead>
                <tr>
                    <th><?php echo $lang["$language"]['cause']?></th>
                    <th><?php echo $lang["$language"]['count']?></th>
                    <th><?php echo $lang["$language"]['count']?></th>
                </tr>
                </thead>
                <tbody>
                <tr> 
                  <td><?php echo $lang["$language"]['agent_hungup']?>:</td>
                  <td><?php echo $hangup_cause["COMPLETEAGENT"]?> <?php echo $lang["$language"]['calls']?></td>
                  <td>
                      <?php
                        if($total_hangup > 0 ) {
                            $percent=$hangup_cause["COMPLETEAGENT"]*100/$total_hangup;
                        } else {
                            $percent=0;
                        }
                        $percent=number_format($percent,2);
                        echo $percent;
                      ?> 
                   <?php echo $lang["$language"]['percent']?></td>
                </tr>
                <tr> 
                  <td><?php echo $lang["$language"]['caller_hungup']?>:</td>
                  <td><?php echo $hangup_cause['COMPLETECALLER']?> <?php echo $lang["$language"]['calls']?></td>
                  <td>
                      <?php
                        if($total_hangup > 0 ) {
                            $percent=$hangup_cause["COMPLETECALLER"]*100/$total_hangup;
                        } else {
                            $percent=0;
                        }
                        $percent=number_format($percent,2);
                        echo $percent;
                      ?> 
                    <?php echo $lang["$language"]['percent']?></td>
                </tr>
                </tbody>
              </table>
            </td>
            <td style='text-align:center; background-color: #fffdf3; width: 100%;'>
                <?php
                $query2 = "var1=".$lang["$language"]['agent']."&val1=".$hangup_cause["COMPLETEAGENT"]."&";
                $query2 .= "var2=".$lang["$language"]['caller']."&val2=".$hangup_cause["COMPLETECALLER"];
                $query2.="&title=".$lang["$language"]['disconnect_cause'];
                swf_bar($query2,364,220,"chart5",0);
                ?>
            </td>
            </tr>
            </thead>
            </table>

            <br/>

            <?php
            if($totaltransfers>0) {
            ?>
            <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" class='sortable' id='table5' >
            <caption><?php echo $lang["$language"]['transfers']?></caption>
            <thead>
            <tr>
                 <th><?php echo $lang["$language"]['agent']?></th>
                 <th><?php echo $lang["$language"]['to']?></th>
                 <th><?php echo $lang["$language"]['count']?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($transfers as $key=>$val) {
                $partes = preg_split("/\^/",$key);
                $agent = $partes[0];
                $extension = $partes[1];
                echo "<tr>\n";
                echo "<td style='padding:0;'>$agent</td>\n";
                echo "<td style='padding:0;'>$extension</td>\n";
                echo "<td style='padding:0;'>$val</td>\n";
                echo "</tr>";
            }
            ?>
            </tbody>
            </table>
        <?php } ?>
</div>
</div>
</div>
<div id='footer'>&copy; Copyright 2007 - <?php echo date('Y');?> by Nicol&aacute;s Gudi&ntilde;o - <a href='http://www.asternic.net'>Asternic Asterisk Tools</a> Licensed under <a href='http://www.opensource.org/licenses/gpl-3.0.html'>GPL3</a></div>
<script src="js/wz_tooltip.js"></script>
</body>
</html>
