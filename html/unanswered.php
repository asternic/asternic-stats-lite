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


// ABANDONED CALLS

$query = "SELECT qs.datetime AS datetime, q.queue AS qname, ag.agent AS qagent, ac.event AS qevent, ";
$query.= "qs.info1 AS info1, qs.info2 AS info2,  qs.info3 AS info3 FROM queue_stats AS qs, qname AS q, ";
$query.= "qagent AS ag, qevent AS ac WHERE qs.qname = q.qname_id AND qs.qagent = ag.agent_id AND ";
$query.= "qs.qevent = ac.event_id AND qs.datetime >= '$start' AND qs.datetime <= '$end' ";
$query.= "AND q.queue IN ($queue) AND ac.event IN ('ABANDON', 'EXITWITHTIMEOUT') ORDER BY qs.datetime";
$res = consulta_db($query,$DB_DEBUG,$DB_MUERE);

$abandon_calls_queue = Array();
$abandon=0;
$timeout=0;

if(db_num_rows($res)>0) {

while($row=db_fetch_row($res)) {

    if($row[3]=="ABANDON") {
         $abandoned++;
        $abandon_end_pos+=$row[4];
        $abandon_start_pos+=$row[5];
        $total_hold_abandon+=$row[6];
    }
    if($row[3]=="EXITWITHTIMEOUT") {
         $timeout++;
    }
    $abandon_calls_queue["$row[1]"]++;
}

if($abandoned > 0) {
    $abandon_average_hold = $total_hold_abandon / $abandoned;
} else {
    $abandon_average_hold = 0;
}
$abandon_average_hold = number_format($abandon_average_hold,0);

if($abandoned > 0) {
    $abandon_average_start = round($abandon_start_pos / $abandoned);
} else {
    $abandon_average_start = 0;
}
$abandon_average_start = number_format($abandon_average_start,0);

if($abandoned > 0) {
    $abandon_average_end = floor($abandon_end_pos / $abandoned);
} else {
    $abandon_average_end = 0;
}
$abandon_average_end = number_format($abandon_average_end,0);

$total_abandon = $abandoned + $timeout;

} else {
     // No rows returned
    $abandoned = 0;
    $timeout = 0;
    $abandon_average_hold  = 0;
    $abandon_average_start = 0;
    $abandon_average_end   = 0;
    $total_abandon         = 0;
}


$start_parts = split(" ", $start);
$end_parts   = split(" ", $end);

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
                <caption><?php echo $lang["$language"]['unanswered_calls']?></caption>
                <tbody>
                <tr> 
                  <td><?php echo $lang["$language"]['number_unanswered']?>:</td>
                  <td><?php echo $total_abandon?> <?php echo $lang["$language"]['calls']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['avg_wait_before_dis']?>:</td>
                  <td><?php echo $abandon_average_hold?> <?php echo $lang["$language"]['secs']?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['avg_queue_pos_at_dis']?>:</td>
                  <td><?php echo $abandon_average_end?></td>
                </tr>
                <tr>
                  <td><?php echo $lang["$language"]['avg_queue_start']?>:</td>
                  <td><?php echo $abandon_average_start?></td>
                </tr>
                </tbody>
              </table>
            </td>
        </tr>
        </thead>
        </table>
        <br/>    

        <a id='1'></a>
        <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" id='table1' >
        <caption>
        <a href='#0'><img alt='go up' src='images/go-up.png' width=16 height=16 class='icon' 
        <?php 
        tooltip($lang["$language"]['gotop'],200);
        ?>
        ></a>&nbsp;&nbsp;
        <?php echo $lang["$language"]['disconnect_cause']?>
        </caption>
            <thead>
            <tr>
              <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <table style="width: 99%; border-collapse: separate; border-spacing: 1px;">
                <thead>
                <tr>
                    <th><?php echo $lang["$language"]['cause']?></th>
                    <th><?php echo $lang["$language"]['count']?></th>
                    <th><?php echo $lang["$language"]['percent']?></th>
                </tr>
                </thead>
                <tbody>
                <tr> 
                  <td><?php echo $lang["$language"]['user_abandon']?></td>
                  <td><?php echo $abandoned?> <?php echo $lang["$language"]['calls']?></td>
                  <td>
                      <?php
                        if($total_abandon > 0 ) {
                            $percent=$abandoned*100/$total_abandon;
                        } else {
                            $percent=0;
                        }
                        $percent=number_format($percent,2);
                        echo $percent;
                      ?> 
                   <?php echo $lang["$language"]['percent']?></td>
                </tr>
                <tr> 
                  <td><?php echo $lang["$language"]['timeout']?></td>
                  <td><?php echo $timeout?> <?php echo $lang["$language"]['calls']?></td>
                  <td>
                      <?php
                        if($total_abandon > 0 ) {
                            $percent=$timeout*100/$total_abandon;
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
            <td style="text-align: center; background-color: #fffdf3;">
                <?php
                $query2 = "var1=".$lang["$language"]['abandon']."&val1=".$abandoned."&";
                $query2 .= "var2=".$lang["$language"]['timeout']."&val2=".$timeout;
                $query2.="&title=".$lang["$language"]['disconnect_cause'];
                swf_bar($query2,350,211,"chart1",0);
                ?>
            </td>
            </tr>
            </thead>
            </table>


        <?php
        if(count($abandon_calls_queue)<=0) {
            $abandon_calls_queue[""]=0;
        }
        ?>
            <a id='2'></a>
            <table style="width: 99%; border-collapse: separate; border-spacing: 1px;" id='table2' >
            <caption>
            <a href='#0'><img alt='go up' src='images/go-up.png' width=16 height=16 class='icon' 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['unanswered_calls_qu']?>
            </caption>
            <thead>
            <tr>
                <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <table style="width: 99%; border-collapse: separate; border-spacing: 1px;">
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
                asort($abandon_calls_queue);
                foreach($abandon_calls_queue as $key=>$val) {
                    $cual = $countrow%2;
                    if($cual>0) { $odd = " class='odd' "; } else { $odd = ""; }
                    if($total_abandon > 0 ) {
                        $percent = $val * 100 / $total_abandon;
                    } else {
                        $percent = 0;
                    }
                    $percent =number_format($percent,2);
                    echo "<tr $odd><td>$key</td><td>$val calls</td><td>$percent ".$lang["$language"]['percent']."</td></tr>\n";
                    $countrow++;
                    $query2.="var$countrow=$key&val$countrow=$val&";
                }
                $query2.="title=".$lang["$language"]['unanswered_calls_qu'];
                ?>
              </tbody>
              </table>
            </td>
            <td style="width: 50%; vertical-align: top; background-color: #fffdf3;">
                <?php 
                //if ($countrow>1) {
                    swf_bar($query2,350,211,"chart2",0);
                   //} 
                   ?>
            </td>
            </tr>
            </thead>
            </table>
            <br/>
            <br/>
</div>
</div>
</div>
<div id='footer'>&copy; Copyright 2007 - <?php echo date('Y');?> by Nicol&aacute;s Gudi&ntilde;o - <a href='http://www.asternic.net'>Asternic Asterisk Tools</a> Licensed under <a href='http://www.opensource.org/licenses/gpl-3.0.html'>GPL3</a></div>
<script src="js/wz_tooltip.js"></script>
</body>
</html>
